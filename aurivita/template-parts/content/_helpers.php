<?php
defined('ABSPATH') || exit;

function av_content_media_url($value, int $width = 1600): string {
    if (is_numeric($value)) {
        return (string) (wp_get_attachment_image_url((int) $value, [$width, $width]) ?: '');
    }
    if (!is_string($value) || trim($value) === '') {
        return '';
    }
    return esc_url_raw(av_url(trim($value)));
}

function av_content_image($value, int $width, array $attributes): string {
    unset($attributes['widths']);
    if (is_numeric($value)) {
        return (string) wp_get_attachment_image((int) $value, [$width, $width], false, $attributes);
    }
    $url = av_content_media_url($value, $width);
    if ($url === '') {
        return '';
    }
    $html = '<img src="' . esc_url($url) . '"';
    foreach ($attributes as $key => $attribute) {
        $html .= ' ' . $key . '="' . esc_attr($attribute) . '"';
    }
    return $html . ' />';
}

function av_content_css_url($value, int $width): string {
    $url = av_content_media_url($value, $width);
    return "'" . strtr($url, ["'" => '%27', '"' => '%22', '\\' => '%5C', '(' => '%28', ')' => '%29']) . "'";
}
