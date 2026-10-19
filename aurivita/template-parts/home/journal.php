<?php
defined('ABSPATH') || exit;

$section = $args['section'] ?? [];
$section_id = sanitize_html_class((string) ($args['section_id'] ?? 'home_journal'));
$blog = av_section_value($section, 'blog');
if (!$blog) {
    return;
}
$journal = is_numeric($blog) ? get_term((int) $blog, 'category') : get_term_by('slug', basename((string) wp_parse_url((string) $blog, PHP_URL_PATH)), 'category');
if (!$journal || is_wp_error($journal)) {
    return;
}
$articles = get_posts([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => max(1, min(3, (int) av_section_value($section, 'count', 3))),
    'category' => $journal->term_id,
    'orderby' => 'date',
    'order' => 'DESC',
    'ignore_sticky_posts' => true,
]);
if (!$articles) {
    return;
}
$journal_link = av_site('profile') === 'aurivita' && $journal->slug === 'rehber' ? av_site_url('blog') : get_category_link($journal);
?>
<section class="av-home av-home-journal" aria-labelledby="AvHomeJournal-<?php echo esc_attr($section_id); ?>">
  <div class="av-home__container">
    <div class="av-home-journal__head">
      <div><p class="av-home__eyebrow"><?php echo esc_html(av_section_value($section, 'eyebrow', 'REHBER')); ?></p><h2 id="AvHomeJournal-<?php echo esc_attr($section_id); ?>" class="av-home__title"><?php echo esc_html(av_section_value($section, 'heading', 'Before you choose.')); ?></h2></div>
      <a class="av-home__link" href="<?php echo esc_url($journal_link); ?>"><?php echo esc_html(av_section_value($section, 'button', 'All articles')); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
    </div>
    <div class="av-home-journal__articles">
      <?php foreach ($articles as $article) :
          $article_link = av_url(get_permalink($article));
          $excerpt = $article->post_excerpt !== '' ? $article->post_excerpt : $article->post_content;
          $excerpt = wp_trim_words(wp_strip_all_tags(strip_shortcodes($excerpt)), 36, '...');
      ?>
        <article class="av-home-journal__article">
          <?php if (has_post_thumbnail($article)) : ?>
            <a class="av-home-journal__media" href="<?php echo esc_url($article_link); ?>" tabindex="-1" aria-hidden="true"><?php echo get_the_post_thumbnail($article, 'full', ['sizes' => '(min-width: 1304px) 585px, (min-width: 750px) 44vw, 100vw', 'loading' => 'lazy', 'alt' => '']); ?></a>
          <?php endif; ?>
          <div class="av-home-journal__body">
            <p class="av-home__eyebrow"><?php echo esc_html($journal->name); ?></p>
            <h3><a href="<?php echo esc_url($article_link); ?>"><?php echo esc_html(get_the_title($article)); ?></a></h3>
            <div class="av-home-journal__excerpt"><?php echo esc_html($excerpt); ?></div>
            <a class="av-home__link" href="<?php echo esc_url($article_link); ?>"><?php echo esc_html__('Read the guide', 'aurivita'); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
</section>
