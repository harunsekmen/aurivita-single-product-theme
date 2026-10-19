<?php
defined('ABSPATH') || exit;

function av_article_plain_text(string $html): string {
    $text = html_entity_decode(wp_strip_all_tags(preg_replace('~<br\s*/?>~i', ' ', $html)), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    return trim((string) preg_replace('/[\s\x{00a0}]+/u', ' ', $text));
}

function av_article_excerpt(WP_Post $post): string {
    if (post_password_required($post)) return '';
    $excerpt = trim($post->post_excerpt);
    $text = av_article_plain_text($excerpt);
    $body = av_article_plain_text($post->post_content);
    if ($text === '' || $text === av_article_plain_text($post->post_title) || str_starts_with($body, $text)) return '';
    return $excerpt;
}

function av_article_prepare_content(string $html, string $title = ''): array {
    $counts = [];
    $used = [];
    $scanner = new WP_HTML_Tag_Processor($html);
    while ($scanner->next_tag()) {
        $id = $scanner->get_attribute('id');
        if (is_string($id) && $id !== '') {
            $counts[$id] = ($counts[$id] ?? 0) + 1;
            $used[$id] = true;
        }
        if ($scanner->get_tag() === 'A') {
            $name = $scanner->get_attribute('name');
            if (is_string($name) && $name !== '') $used[$name] = true;
        }
    }

    $headings = [];
    $suffixes = [];
    $title = av_article_plain_text($title);
    // Skip comments, raw text and literal examples before matching article headings.
    $pattern = <<<'REGEX'
~<!--.*?-->|<!\[CDATA\[.*?\]\]>|<(?<literal>script|style|textarea|title|xmp|iframe|noembed|noframes|template|pre|code)\b(?:"[^"]*"|'[^']*'|[^'">])*?>.*?</\k<literal>\s*>|<h(?<level>[123])\b(?:"[^"]*"|'[^']*'|[^'">])*?>(?<body>.*?)</h\k<level>\s*>|<(?:"[^"]*"|'[^']*'|[^'">])*>~is
REGEX;
    $prepared = preg_replace_callback($pattern, static function (array $match) use (&$headings, &$used, &$suffixes, $counts, $title): string {
        if (empty($match['level'])) return $match[0];
        $level = (int) $match['level'];
        $label = av_article_plain_text($match['body']);
        $heading_html = $match[0];
        if ($level === 1) {
            if ($title === '' || $label !== $title) return $heading_html;
            $heading_html = (string) preg_replace(['~^<h1\b~i', '~</h1\s*>$~i'], ['<h2', '</h2>'], $heading_html);
            $level = 2;
        }
        if ($label === '') return $heading_html;

        $heading = new WP_HTML_Tag_Processor($heading_html);
        if (!$heading->next_tag()) return $heading_html;
        $id = $heading->get_attribute('id');
        if (!is_string($id) || $id === '' || ($counts[$id] ?? 0) !== 1) {
            $base = 'av-article-' . (rawurldecode(sanitize_title($label)) ?: 'bolum');
            $id = $base;
            $suffix = $suffixes[$base] ?? 2;
            while (isset($used[$id])) $id = $base . '-' . $suffix++;
            $suffixes[$base] = $suffix;
            $heading->set_attribute('id', $id);
        }
        $used[$id] = true;
        $headings[] = ['id' => $id, 'label' => $label, 'level' => $level];
        return $heading->get_updated_html();
    }, $html);

    return $prepared === null ? ['html' => $html, 'headings' => []] : ['html' => $prepared, 'headings' => $headings];
}
