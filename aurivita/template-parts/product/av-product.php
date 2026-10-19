<?php
defined('ABSPATH') || exit;
require_once __DIR__ . '/helpers.php';
$context = avp_context(is_array($args ?? null) ? $args : []);
if (!$context) { return; }
extract($context, EXTR_OVERWRITE);
$gallery = avp_gallery($product, $reference);
?>
<av-product class="av-root" data-section-id="<?php echo esc_attr($section_id); ?>" data-product-id="<?php echo (int) $product->get_id(); ?>" data-product-url="<?php echo esc_url($product->get_permalink()); ?>" data-fw-ux="true" data-reference="<?php echo $reference ? 'true' : 'false'; ?>" data-loading="Loading…" data-add="Add to cart" data-sold="Sold out" data-unavailable="This option is unavailable" data-stock="In stock" data-save="savings">
<script type="application/json" data-av-product-data>
<?php echo wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE); ?>
</script>
<?php if ($reference) : ?>
<section class="shopify-section section av-id-shopify-section-template--21992530247932__custom_product_qkafDR" id="av-<?php echo esc_attr($section_id); ?>-shopify-section-template--21992530247932__custom_product_qkafDR">
<section class="cc-pdp-complete fw-ref-e9f0c13177" data-cc-pdp="" id="av-<?php echo esc_attr($section_id); ?>-fw-ref-e9f0c13177">
<div class="cc-pdp__section av-id-product" id="product">
<div class="cc-pdp__container">
<div class="cc-pdp__mobile-intro">
<div class="cc-pdp-block cc-pdp-reviews cc-hide-mobile" style="--mt-desktop: 0px; --mt-mobile: 0px; --mb-desktop: 16px; --mb-mobile: 22px; --rating-size-desktop: 18px; --rating-size-mobile: 14px;">
<a class="cc-pdp-link-reset" href="#reviews">
<div class="cc-pdp-reviews__rating">
<span aria-hidden="true" class="cc-pdp-stars">
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
</span>
<p class="cc-pdp-reviews__text">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_0', '4.6 / 5.0 · Source product reviews')); ?>
</p>
</div>
</a>
</div>
<div class="cc-pdp-block cc-pdp-reviews cc-hide-desktop" style="--mt-desktop: 0px; --mt-mobile: 0px; --mb-desktop: 16px; --mb-mobile: 22px; --rating-size-desktop: 18px; --rating-size-mobile: 14px;">
<a class="cc-pdp-link-reset" href="#reviews">
<div class="cc-pdp-reviews__rating">
<span aria-hidden="true" class="cc-pdp-stars">
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
</span>
<p class="cc-pdp-reviews__text">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_1', '4.6 / 5.0 · Source product reviews')); ?>
</p>
</div>
</a>
</div>
<div class="cc-pdp-block cc-pdp-title" style="--mt-desktop: 0px; --mt-mobile: 14px; --mb-desktop: 16px; --mb-mobile: 0px; --title-size-desktop: 38px; --title-size-mobile: 28px;">
<div aria-level="1" class="cc-pdp-title__text" role="heading">
<?php echo esc_html($product->get_name()); ?>
</div>
</div>
<div class="cc-pdp-block cc-pdp-subtitle" style="--mt-desktop: 0px; --mt-mobile: 14px; --mb-desktop: 30px; --mb-mobile: 14px; --subtitle-size-desktop: 20px; --subtitle-size-mobile: 16px;">
<p class="cc-pdp-subtitle__text">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_2', 'Daily support for healthy circulation, blood pressure and heart health.')); ?>
<sup>†</sup>
</span>
</p>
</div>
</div>
<div class="cc-pdp__layout">
<div class="cc-pdp__media-col">
<div class="cc-pdp__gallery-layout">
<div class="cc-pdp__thumbs-wrap" style="height: 498.594px;">
<div class="swiper cc-pdp__thumbs js-cc-pdp-thumbs-template--21992530247932__custom_product_qkafDR swiper-backface-hidden swiper-thumbs swiper-initialized swiper-vertical swiper-free-mode swiper-watch-progress">
<div aria-live="polite" class="swiper-wrapper av-id-swiper-wrapper-b1253cc9e2f710e08" id="av-<?php echo esc_attr($section_id); ?>-swiper-wrapper-b1253cc9e2f710e08">
<?php foreach ($gallery as $index => $image) : ?>
<button type="button" class="swiper-slide cc-pdp__thumb<?php echo $index === 0 ? ' swiper-slide-thumb-active' : ''; ?>" aria-label="<?php echo esc_attr('Image ' . ($index + 1)); ?>" aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>">
<?php echo avp_gallery_image($image, 'cc-pdp__thumb-image', '110px'); ?>
</button>
<?php endforeach; ?>
</div>
</div>
</div>
<div class="cc-pdp__main-column">
<div class="cc-pdp__main-wrap">
<button aria-controls="av-<?php echo esc_attr($section_id); ?>-swiper-wrapper-9dbee5ce387d21be" aria-label="Previous image" class="cc-pdp__main-arrow cc-pdp__main-arrow--prev js-cc-pdp-main-prev-template--21992530247932__custom_product_qkafDR swiper-button-disabled" type="button">
<svg fill="none" height="14" style="transform: rotate(180deg);" viewBox="0 0 8 14" width="8" xmlns="http://www.w3.org/2000/svg">
<path d="M0.910645 13.0535L6.98207 6.98207L0.910645 0.910645" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.82143">
</path>
</svg>
</button>
<div class="swiper cc-pdp__main js-cc-pdp-main-template--21992530247932__custom_product_qkafDR swiper-backface-hidden swiper-initialized swiper-horizontal">
<div aria-live="polite" class="swiper-wrapper av-id-swiper-wrapper-9dbee5ce387d21be" id="av-<?php echo esc_attr($section_id); ?>-swiper-wrapper-9dbee5ce387d21be">
<?php foreach ($gallery as $index => $image) : ?>
<div class="swiper-slide cc-pdp__main-slide<?php echo $index === 0 ? ' swiper-slide-active' : ''; ?>" data-image-id="<?php echo esc_attr($image['id']); ?>" role="group" aria-label="<?php echo esc_attr(($index + 1) . ' / ' . count($gallery)); ?>" <?php if ($index) : ?>hidden<?php endif; ?>>
<?php if ($index === 0) : ?>
<div class="cc-pdp__badges">
</div>
<?php endif; ?>
<?php echo avp_gallery_image($image, 'cc-pdp__main-image', '(min-width:1400px) 570px, (min-width:990px) 48vw, calc(100vw - 64px)', $index === 0); ?>
</div>
<?php endforeach; ?>
</div>
</div>
<button aria-controls="av-<?php echo esc_attr($section_id); ?>-swiper-wrapper-9dbee5ce387d21be" aria-disabled="false" aria-label="Next image" class="cc-pdp__main-arrow cc-pdp__main-arrow--next js-cc-pdp-main-next-template--21992530247932__custom_product_qkafDR" tabindex="0" type="button">
<svg fill="none" height="14" viewBox="0 0 8 14" width="8" xmlns="http://www.w3.org/2000/svg">
<path d="M0.910645 13.0535L6.98207 6.98207L0.910645 0.910645" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.82143">
</path>
</svg>
</button>
<div aria-hidden="true" class="gallery-peek">
<?php $peek = av_block_value($section, 'content_2', 'field_3', ''); echo $peek ? avp_media_image($peek, '', '', 'calc(100vw - 64px)') : avp_gallery_image($gallery[min(1, count($gallery) - 1)], '', 'calc(100vw - 64px)'); ?>
</div>
</div>
<div class="cc-pdp-ugc-slot cc-pdp-ugc-slot--desktop" data-fw-video-home>
<div class="cc-pdp-block cc-pdp-ugc cc-pdp-ugc--ig-hidden" data-cc-ugc="" data-per-view-desktop="3" style="--mt-desktop: 24px; --mt-mobile: 20px; --mb-desktop: 0px; --mb-mobile: 0px; --ugc-title-desktop: 22px; --ugc-title-mobile: 18px;">
<p class="cc-pdp-ugc__title">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_3', 'What are our customers saying?*')); ?>
</p>
<div class="swiper js-cc-pdp-ugc-ugc_videos_Ffd7Hw swiper-initialized swiper-horizontal swiper-backface-hidden">
<div aria-live="polite" class="swiper-wrapper av-id-swiper-wrapper-d0f1651010f672705a" id="av-<?php echo esc_attr($section_id); ?>-swiper-wrapper-d0f1651010f672705a">
<div aria-label="1 / 3" class="swiper-slide cc-pdp__video-slide swiper-slide-active" role="group">
<?php if (av_block_value($section, 'content_2', 'field_8', '')) : ?>
<?php echo avp_media_video(av_block_value($section, 'content_2', 'field_8', '')); ?>
<?php elseif (av_block_allows_fallback($section, 'content_2', 'field_8')) : ?>
<video class="cc-pdp__video-video" muted="muted" playsinline="true" data-fw-poster="<?php echo esc_url(av_asset('av-82f99224c4584c0085e12bef3194d92f.thumbnail.0000000000_900x-9f622b0b23-w320.webp')); ?>" poster="<?php echo esc_url(av_asset('av-82f99224c4584c0085e12bef3194d92f.thumbnail.0000000000_900x-9f622b0b23-w320.webp')); ?>" preload="none" controls>
<source src="<?php echo esc_url(av_asset('av-82f99224c4584c0085e12bef3194d92f.HD-1080p-7.2Mbps-93809736-7b846e30d4.mp4')); ?>" type="video/mp4"/>
<img alt="" width="320" height="569" loading="lazy" src="<?php echo esc_url(av_asset('av-82f99224c4584c0085e12bef3194d92f.thumbnail.0000000000_900x-9f622b0b23-w320.webp')); ?>"/>
</video>
<?php endif; ?>
</div>
<div aria-label="2 / 3" class="swiper-slide cc-pdp__video-slide swiper-slide-next" role="group">
<?php if (av_block_value($section, 'content_2', 'field_9', '')) : ?>
<?php echo avp_media_video(av_block_value($section, 'content_2', 'field_9', '')); ?>
<?php elseif (av_block_allows_fallback($section, 'content_2', 'field_9')) : ?>
<video class="cc-pdp__video-video" muted="muted" playsinline="true" data-fw-poster="<?php echo esc_url(av_asset('av-9e286d2fedf24a7e86ff66d8332872f7.thumbnail.0000000000_900x-f8f7dc5932-w320.webp')); ?>" poster="<?php echo esc_url(av_asset('av-9e286d2fedf24a7e86ff66d8332872f7.thumbnail.0000000000_900x-f8f7dc5932-w320.webp')); ?>" preload="none" controls>
<source src="<?php echo esc_url(av_asset('av-9e286d2fedf24a7e86ff66d8332872f7.HD-1080p-4.8Mbps-93809735-62dd3d88f5.mp4')); ?>" type="video/mp4"/>
<img alt="" width="320" height="569" loading="lazy" src="<?php echo esc_url(av_asset('av-9e286d2fedf24a7e86ff66d8332872f7.thumbnail.0000000000_900x-f8f7dc5932-w320.webp')); ?>"/>
</video>
<?php endif; ?>
</div>
<div aria-label="3 / 3" class="swiper-slide cc-pdp__video-slide" role="group">
<?php if (av_block_value($section, 'content_2', 'field_10', '')) : ?>
<?php echo avp_media_video(av_block_value($section, 'content_2', 'field_10', '')); ?>
<?php elseif (av_block_allows_fallback($section, 'content_2', 'field_10')) : ?>
<video class="cc-pdp__video-video" muted="muted" playsinline="true" data-fw-poster="<?php echo esc_url(av_asset('av-892946d83e744d7d9b4881fc716d42d5.thumbnail.0000000000_900x-2fb9e4249e-w320.webp')); ?>" poster="<?php echo esc_url(av_asset('av-892946d83e744d7d9b4881fc716d42d5.thumbnail.0000000000_900x-2fb9e4249e-w320.webp')); ?>" preload="none" controls>
<source src="<?php echo esc_url(av_asset('av-892946d83e744d7d9b4881fc716d42d5.HD-1080p-4.8Mbps-93809733-a732827a9f.mp4')); ?>" type="video/mp4"/>
<img alt="" width="320" height="569" loading="lazy" src="<?php echo esc_url(av_asset('av-892946d83e744d7d9b4881fc716d42d5.thumbnail.0000000000_900x-2fb9e4249e-w320.webp')); ?>"/>
</video>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
<div class="cc-pdp__content-col">
<div class="cc-pdp-block cc-pdp-reviews cc-pdp__desktop-intro cc-hide-mobile" style="--mt-desktop: 0px; --mt-mobile: 0px; --mb-desktop: 16px; --mb-mobile: 22px; --rating-size-desktop: 18px; --rating-size-mobile: 14px;">
<a class="cc-pdp-link-reset" href="#reviews">
<div class="cc-pdp-reviews__rating">
<span aria-hidden="true" class="cc-pdp-stars">
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
</span>
<p class="cc-pdp-reviews__text">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_4', '4.6 / 5.0 · Source product reviews')); ?>
</p>
</div>
</a>
</div>
<div class="cc-pdp-block cc-pdp-reviews cc-pdp__desktop-intro cc-hide-desktop" style="--mt-desktop: 0px; --mt-mobile: 0px; --mb-desktop: 16px; --mb-mobile: 22px; --rating-size-desktop: 18px; --rating-size-mobile: 14px;">
<a class="cc-pdp-link-reset" href="#reviews">
<div class="cc-pdp-reviews__rating">
<span aria-hidden="true" class="cc-pdp-stars">
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
<svg fill="none" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z" fill="currentColor">
</path>
</svg>
</span>
<p class="cc-pdp-reviews__text">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_5', '4.6 / 5.0 · Source product reviews')); ?>
</p>
</div>
</a>
</div>
<div class="cc-pdp-block cc-pdp-title cc-pdp__desktop-intro" style="--mt-desktop: 0px; --mt-mobile: 14px; --mb-desktop: 16px; --mb-mobile: 0px; --title-size-desktop: 38px; --title-size-mobile: 28px;">
<h1 class="cc-pdp-title__text">
<?php echo esc_html($product->get_name()); ?>
</h1>
</div>
<div class="cc-pdp-block cc-pdp-subtitle cc-pdp__desktop-intro" style="--mt-desktop: 0px; --mt-mobile: 14px; --mb-desktop: 30px; --mb-mobile: 14px; --subtitle-size-desktop: 20px; --subtitle-size-mobile: 16px;">
<p class="cc-pdp-subtitle__text">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_6', 'Daily support for healthy circulation, blood pressure and heart health.')); ?>
<sup>†</sup>
</span>
</p>
</div>
<div class="cc-pdp-block cc-pdp-checks" style="--mt-desktop: 20px; --mt-mobile: 0px; --mb-desktop: 0px; --mb-mobile: 0px; --checks-size-desktop: 18px; --checks-size-mobile: 15px;">
<div class="cc-pdp-checks__item">
<svg aria-hidden="true" class="cc-pdp-check" fill="none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-1)">
<path d="M0.340825 8.0719L1.70332 6.70627C2.15958 6.25001 2.89707 6.25001 3.3502 6.70627L5.57836 8.93747L12.6472 1.86563C13.1003 1.40937 13.8378 1.40937 14.294 1.86563L15.6565 3.22813C16.1097 3.68438 16.1097 4.41875 15.6565 4.87501L6.41286 14.1344C5.9566 14.5906 5.21911 14.5906 4.76598 14.1344L0.341017 9.71869C-0.112103 9.26243 -0.115239 8.52806 0.341017 8.07181L0.340825 8.0719Z" fill="currentColor">
</path>
</g>
<defs>
<clippath class="av-id-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-1" id="av-<?php echo esc_attr($section_id); ?>-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-1">
<rect fill="white" height="16" width="16">
</rect>
</clippath>
</defs>
</svg>
<p class="cc-pdp-checks__text">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_7', 'Supports heart health')); ?>
<sup>†</sup>
</span>
</p>
</div>
<div class="cc-pdp-checks__item">
<svg aria-hidden="true" class="cc-pdp-check" fill="none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-2)">
<path d="M0.340825 8.0719L1.70332 6.70627C2.15958 6.25001 2.89707 6.25001 3.3502 6.70627L5.57836 8.93747L12.6472 1.86563C13.1003 1.40937 13.8378 1.40937 14.294 1.86563L15.6565 3.22813C16.1097 3.68438 16.1097 4.41875 15.6565 4.87501L6.41286 14.1344C5.9566 14.5906 5.21911 14.5906 4.76598 14.1344L0.341017 9.71869C-0.112103 9.26243 -0.115239 8.52806 0.341017 8.07181L0.340825 8.0719Z" fill="currentColor">
</path>
</g>
<defs>
<clippath class="av-id-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-2" id="av-<?php echo esc_attr($section_id); ?>-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-2">
<rect fill="white" height="16" width="16">
</rect>
</clippath>
</defs>
</svg>
<p class="cc-pdp-checks__text">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_8', 'Supports the body’s natural nitric oxide production')); ?>
<sup>†</sup>
</span>
</p>
</div>
<div class="cc-pdp-checks__item">
<svg aria-hidden="true" class="cc-pdp-check" fill="none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-3)">
<path d="M0.340825 8.0719L1.70332 6.70627C2.15958 6.25001 2.89707 6.25001 3.3502 6.70627L5.57836 8.93747L12.6472 1.86563C13.1003 1.40937 13.8378 1.40937 14.294 1.86563L15.6565 3.22813C16.1097 3.68438 16.1097 4.41875 15.6565 4.87501L6.41286 14.1344C5.9566 14.5906 5.21911 14.5906 4.76598 14.1344L0.341017 9.71869C-0.112103 9.26243 -0.115239 8.52806 0.341017 8.07181L0.340825 8.0719Z" fill="currentColor">
</path>
</g>
<defs>
<clippath class="av-id-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-3" id="av-<?php echo esc_attr($section_id); ?>-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-3">
<rect fill="white" height="16" width="16">
</rect>
</clippath>
</defs>
</svg>
<p class="cc-pdp-checks__text">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_9', 'Independently lab tested in the US')); ?>
</p>
</div>
<div class="cc-pdp-checks__item">
<svg aria-hidden="true" class="cc-pdp-check" fill="none" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
<g clip-path="url(#cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-4)">
<path d="M0.340825 8.0719L1.70332 6.70627C2.15958 6.25001 2.89707 6.25001 3.3502 6.70627L5.57836 8.93747L12.6472 1.86563C13.1003 1.40937 13.8378 1.40937 14.294 1.86563L15.6565 3.22813C16.1097 3.68438 16.1097 4.41875 15.6565 4.87501L6.41286 14.1344C5.9566 14.5906 5.21911 14.5906 4.76598 14.1344L0.341017 9.71869C-0.112103 9.26243 -0.115239 8.52806 0.341017 8.07181L0.340825 8.0719Z" fill="currentColor">
</path>
</g>
<defs>
<clippath class="av-id-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-4" id="av-<?php echo esc_attr($section_id); ?>-cc-pdp-check-template--21992530247932__custom_product_qkafDR-feature_checks_TEQNcH-4">
<rect fill="white" height="16" width="16">
</rect>
</clippath>
</defs>
</svg>
<p class="cc-pdp-checks__text">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_10', 'Made in an FDA-registered facility')); ?>
</p>
</div>
</div>
<div class="cc-pdp-block cc-pdp-dqs" data-fw-options data-cc-dqs="" data-default-dose="a" style="--mt-desktop: 20px; --mt-mobile: 18px; --mb-desktop: 0px; --mb-mobile: 0px; --offer-title-size-desktop: 18px; --offer-title-size-mobile: 15px; --offer-subtitle-size-desktop: 14px; --offer-subtitle-size-mobile: 12px; --offer-price-size-desktop: 18px; --offer-price-size-mobile: 14px; --offer-compare-size-desktop: 18px; --offer-compare-size-mobile: 14px;">
<p class="cc-pdp-dqs__heading">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_11', 'Choose your servings:')); ?>
</p>
<div class="cc-pdp-dqs__pills" role="tablist">
<button aria-selected="<?php echo (int) ($selected['servings'] ?? 30) === 30 ? 'true' : 'false'; ?>" class="cc-pdp-dqs__pill<?php echo (int) ($selected['servings'] ?? 30) === 30 ? ' is-active' : ''; ?>" data-cc-dqs-pill="" data-dose="a" data-servings="30" data-option-position="0" data-option-value="30 servings" role="tab" type="button">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_12', '30 servings')); ?>
</button>
<button aria-selected="<?php echo (int) ($selected['servings'] ?? 30) === 60 ? 'true' : 'false'; ?>" class="cc-pdp-dqs__pill<?php echo (int) ($selected['servings'] ?? 30) === 60 ? ' is-active' : ''; ?>" data-cc-dqs-pill="" data-dose="b" data-servings="60" data-option-position="0" data-option-value="60 servings" role="tab" type="button">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_13', '60 servings')); ?>
</button>
</div>
<p class="cc-pdp-dqs__heading cc-pdp-dqs__heading--qty">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_14', 'Choose your bundle:')); ?>
</p>
<div class="cc-pdp-offers cc-pdp-dqs__cards" data-cc-offers="">
<?php $card_variant = avp_card($variations, $selected, 1); ?>
<button aria-pressed="<?php echo (int) ($selected['packs'] ?? 0) === 1 ? 'true' : 'false'; ?>" class="cc-pdp-offer<?php echo (int) ($selected['packs'] ?? 0) === 1 ? ' is-selected' : ''; ?>" data-card-index="1" data-packs="1" <?php disabled(empty($card_variant['available'])); ?> data-cc-offer-card="" data-option-position="1" data-option-value="1 pack" data-price-quantity="1" data-quantity="1" type="button">
<span aria-hidden="true" class="cc-pdp-offer__radio">
<span class="cc-pdp-offer__radio-dot">
</span>
</span>
<div class="cc-pdp-offer__image-wrap">
<?php if (av_block_value($section, 'content_2', 'field_4', '')) : ?>
<?php echo avp_media_image(av_block_value($section, 'content_2', 'field_4', ''), 'cc-pdp-offer__image', '1 pack', '80px'); ?>
<?php elseif (av_block_allows_fallback($section, 'content_2', 'field_4')) : ?>
<img alt="1 pack" class="cc-pdp-offer__image" height="1080" loading="lazy" srcset="<?php echo esc_url(av_asset('av-19_5767f490-532f-4d3b-843a-15917f40b0a4-c37e23a524-w160.webp')); ?> 160w, <?php echo esc_url(av_asset('av-19_5767f490-532f-4d3b-843a-15917f40b0a4-c37e23a524-w320.webp')); ?> 320w, <?php echo esc_url(av_asset('av-19_5767f490-532f-4d3b-843a-15917f40b0a4-c37e23a524-w480.webp')); ?> 480w, <?php echo esc_url(av_asset('av-19_5767f490-532f-4d3b-843a-15917f40b0a4-c37e23a524-w640.webp')); ?> 640w, <?php echo esc_url(av_asset('av-19_5767f490-532f-4d3b-843a-15917f40b0a4-c37e23a524-w900.webp')); ?> 900w, <?php echo esc_url(av_asset('av-19_5767f490-532f-4d3b-843a-15917f40b0a4-c37e23a524.webp')); ?> 1080w" sizes="80px" src="<?php echo esc_url(av_asset('av-19_5767f490-532f-4d3b-843a-15917f40b0a4-c37e23a524.webp')); ?>" width="1080"/>
<?php endif; ?>
</div>
<div class="cc-pdp-offer__meta">
<p class="cc-pdp-offer__title">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_15', '1 pack')); ?>
</p>
<p class="cc-pdp-offer__subtitle" data-cc-save="" <?php if (($card_variant['regular_price'] ?? 0) <= ($card_variant['price'] ?? 0)) : ?>hidden<?php endif; ?> data-save-template="Save 20%">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_16', 'Save 33%')); ?>
</p>
</div>
<div class="cc-pdp-offer__price-box">
<p class="cc-pdp-offer__price" data-cc-price="">
<?php echo $card_variant ? av_money($card_variant['price']) : 'Unavailable'; ?>
</p>
<p class="cc-pdp-offer__compare" data-cc-compare="" <?php if (($card_variant['regular_price'] ?? 0) <= ($card_variant['price'] ?? 0)) : ?>hidden<?php endif; ?>>
<?php echo $card_variant ? av_money($card_variant['regular_price']) : ''; ?>
</p>
</div>
</button>
<?php $card_variant = avp_card($variations, $selected, 3); ?>
<button aria-pressed="<?php echo (int) ($selected['packs'] ?? 0) === 3 ? 'true' : 'false'; ?>" class="cc-pdp-offer is-highlighted<?php echo (int) ($selected['packs'] ?? 0) === 3 ? ' is-selected' : ''; ?>" data-card-index="2" data-packs="3" <?php disabled(empty($card_variant['available'])); ?> data-cc-offer-card="" data-option-position="1" data-option-value="3 packs" data-price-quantity="2" data-quantity="3" type="button">
<span aria-hidden="true" class="cc-pdp-offer__radio">
<span class="cc-pdp-offer__radio-dot">
</span>
</span>
<div class="cc-pdp-offer__image-wrap">
<?php if (av_block_value($section, 'content_2', 'field_5', '')) : ?>
<?php echo avp_media_image(av_block_value($section, 'content_2', 'field_5', ''), 'cc-pdp-offer__image', 'Buy 2, get 1 FREE', '80px'); ?>
<?php elseif (av_block_allows_fallback($section, 'content_2', 'field_5')) : ?>
<img alt="Buy 2, get 1 FREE" class="cc-pdp-offer__image" height="1080" loading="lazy" srcset="<?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w160.webp')); ?> 160w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w320.webp')); ?> 320w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w480.webp')); ?> 480w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w640.webp')); ?> 640w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w900.webp')); ?> 900w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf.webp')); ?> 1080w" sizes="80px" src="<?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf.webp')); ?>" width="1080"/>
<?php endif; ?>
</div>
<div class="cc-pdp-offer__meta">
<p class="cc-pdp-offer__title">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_17', 'Buy 2, get 1 FREE')); ?>
</p>
<p class="cc-pdp-offer__subtitle" data-cc-save="" <?php if (($card_variant['regular_price'] ?? 0) <= ($card_variant['price'] ?? 0)) : ?>hidden<?php endif; ?> data-save-template="Save 20%">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_0', 'Save 33%')); ?>
</p>
<span class="cc-pdp-offer__badge" style="background: #d27e0b;">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_1', 'Most popular')); ?>
</span>
</div>
<div class="cc-pdp-offer__price-box">
<p class="cc-pdp-offer__price" data-cc-price="">
<?php echo $card_variant ? av_money($card_variant['price']) : 'Unavailable'; ?>
</p>
<p class="cc-pdp-offer__compare" data-cc-compare="" <?php if (($card_variant['regular_price'] ?? 0) <= ($card_variant['price'] ?? 0)) : ?>hidden<?php endif; ?>>
<?php echo $card_variant ? av_money($card_variant['regular_price']) : ''; ?>
</p>
</div>
</button>
<?php $card_variant = avp_card($variations, $selected, 5); ?>
<button aria-pressed="<?php echo (int) ($selected['packs'] ?? 0) === 5 ? 'true' : 'false'; ?>" class="cc-pdp-offer<?php echo (int) ($selected['packs'] ?? 0) === 5 ? ' is-selected' : ''; ?>" data-card-index="3" data-packs="5" <?php disabled(empty($card_variant['available'])); ?> data-cc-offer-card="" data-option-position="1" data-option-value="5 packs" data-price-quantity="3" data-quantity="5" type="button">
<span aria-hidden="true" class="cc-pdp-offer__radio">
<span class="cc-pdp-offer__radio-dot">
</span>
</span>
<div class="cc-pdp-offer__image-wrap">
<?php if (av_block_value($section, 'content_2', 'field_6', '')) : ?>
<?php echo avp_media_image(av_block_value($section, 'content_2', 'field_6', ''), 'cc-pdp-offer__image', 'Buy 3, get 2 FREE', '80px'); ?>
<?php elseif (av_block_allows_fallback($section, 'content_2', 'field_6')) : ?>
<img alt="Buy 3, get 2 FREE" class="cc-pdp-offer__image" height="1080" loading="lazy" srcset="<?php echo esc_url(av_asset('av-Aurivita_package_replace_10-05aef77e78-w160.webp')); ?> 160w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_10-05aef77e78-w320.webp')); ?> 320w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_10-05aef77e78-w480.webp')); ?> 480w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_10-05aef77e78-w640.webp')); ?> 640w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_10-05aef77e78-w900.webp')); ?> 900w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_10-05aef77e78.webp')); ?> 1080w" sizes="80px" src="<?php echo esc_url(av_asset('av-Aurivita_package_replace_10-05aef77e78.webp')); ?>" width="1080"/>
<?php endif; ?>
</div>
<div class="cc-pdp-offer__meta">
<p class="cc-pdp-offer__title">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_2', 'Buy 3, get 2 FREE')); ?>
</p>
<p class="cc-pdp-offer__subtitle" data-cc-save="" <?php if (($card_variant['regular_price'] ?? 0) <= ($card_variant['price'] ?? 0)) : ?>hidden<?php endif; ?> data-save-template="Save 20%">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_3', 'Save 33%')); ?>
</p>
<span class="cc-pdp-offer__badge" style="background: #840104;">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_4', 'Best value')); ?>
</span>
</div>
<div class="cc-pdp-offer__price-box">
<p class="cc-pdp-offer__price" data-cc-price="">
<?php echo $card_variant ? av_money($card_variant['price']) : 'Unavailable'; ?>
</p>
<p class="cc-pdp-offer__compare" data-cc-compare="" <?php if (($card_variant['regular_price'] ?? 0) <= ($card_variant['price'] ?? 0)) : ?>hidden<?php endif; ?>>
<?php echo $card_variant ? av_money($card_variant['regular_price']) : ''; ?>
</p>
</div>
</button>
</div>
</div>
<div class="cc-pdp-block cc-pdp-stock-line" style="--mt-desktop: 24px; --mt-mobile: 28px; --mb-desktop: 20px; --mb-mobile: 16px;">
<span aria-hidden="true" class="cc-pdp-stock-line__dot">
</span>
<span class="cc-pdp-stock-line__status">
<?php echo $available ? esc_html((string) av_block_value($section, 'content_1', 'field_5', 'In stock')) : 'Sold out'; ?>
</span>
<span class="cc-pdp-stock-line__body">—</span>
<span class="cc-pdp-stock-line__link">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_6', 'ready for same-day shipping')); ?>
<sup>‡</sup>
</span>
</span>
</div>
<div class="cc-pdp-block cc-pdp-buy-buttons" data-cc-atc-block="" style="--mt-desktop: 0px; --mt-mobile: 0px; --mb-desktop: 0px; --mb-mobile: 12px; --cc-atc-guarantee-size-desktop: 14px; --cc-atc-guarantee-size-mobile: 12px; --cc-atc-guarantee-color: #ffffff; --cc-atc-guarantee-opacity: 0.7;">
<div style="--margin-top: 0.0rem;--margin-bottom: 0.0rem;">
<?php include __DIR__ . '/form.php'; ?>


</div>
</div>
<div class="cc-pdp-block cc-pdp-inline-perks" style="--mt-desktop: 12px; --mt-mobile: 0px; --mb-desktop: 0px; --mb-mobile: 12px; --perks-size-desktop: 18px; --perks-size-mobile: 12px;">
<a class="cc-pdp-inline-perks__item" href="#guarantee">
<svg class="cc-pdp-inline-perks__shield" fill="none" height="15" viewBox="0 0 20 20" width="15" xmlns="http://www.w3.org/2000/svg">
<path d="M8.66691 19.8347C9.10673 20.0533 9.61969 20.0533 10.0595 19.8347C12.0299 18.7574 18.5259 15.1857 18.5259 11.0838V3.81879C18.5259 3.01858 17.876 2.35452 17.0316 2.24C14.4149 1.88413 12.5547 1.26794 9.86028 0.138195C9.41676 -0.047521 8.91028 -0.045782 8.4677 0.141697C5.7955 1.27282 3.81584 1.88919 1.29352 2.23811C0.44908 2.35439 -0.00012207 3.01698 -0.00012207 3.81793V11.0836C-0.00012207 15.1856 6.4959 18.7573 8.66657 19.8346L8.66691 19.8347ZM14.8642 6.31541C15.1901 6.61661 15.1948 7.10971 14.8726 7.41525L8.43918 13.5399C7.93087 14.024 7.09847 14.0258 6.58642 13.5442L3.85668 10.9741C3.53353 10.6694 3.53446 10.1772 3.85946 9.87344C4.18445 9.57037 4.70945 9.57211 5.03259 9.87593L7.5093 12.2085L13.6906 6.31413C14.0119 6.00771 14.5369 6.00423 14.8637 6.31547L14.8642 6.31541Z" fill="#840104" fill-rule="evenodd">
</path>
</svg>
<p class="cc-pdp-inline-perks__text">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_7', '120-day money-back guarantee')); ?>
<sup>‡</sup>
</span>
</p>
</a>
</div>
<div class="cc-pdp-block cc-pdp-payment" style="--mt-desktop: 10px; --mt-mobile: 0px; --mb-desktop: 10px; --mb-mobile: 24px;">
<ul class="list-payment" role="list">
<?php if (WC()->payment_gateways()) : foreach (WC()->payment_gateways()->get_available_payment_gateways() as $gateway) : $icon = $gateway->get_icon(); if ($icon) : ?>
<li class="list-payment__item">
<?php echo wp_kses_post($icon); ?>
</li>
<?php endif; endforeach; endif; ?>
</ul>
</div>
<div class="cc-pdp-block cc-pdp-faqs" style="--mt-desktop: 22px; --mt-mobile: 0px; --mb-desktop: 0px; --mb-mobile: 0px; --faq-question-size-desktop: 20px; --faq-question-size-mobile: 16px; --faq-answer-size-desktop: 18px; --faq-answer-size-mobile: 15px;">
<div class="cc-pdp-faqs__item is-open">
<button aria-expanded="true" class="cc-pdp-faqs__toggle" type="button">
<span class="cc-pdp-faqs__question">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_8', 'About the product')); ?>
</span>
<span aria-hidden="true" class="cc-pdp-faqs__icon">
<svg class="cc-pdp-faqs__icon-minus" fill="none" height="2" viewBox="0 0 13 2" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M1 1H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
<svg class="cc-pdp-faqs__icon-plus" fill="none" height="13" viewBox="0 0 13 13" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M6.41699 1V11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
<path d="M1 6.4165H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
</span>
</button>
<div class="cc-pdp-faqs__answer-wrap">
<div class="cc-pdp-faqs__answer-inner">
<div class="cc-pdp-faqs__answer rte">
<p>
<span>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_9', 'A daily cayenne pepper softgel developed to support vascular health and circulation.')); ?>
<sup>†</sup>
</span>
</p>
</div>
</div>
</div>
</div>
<div class="cc-pdp-faqs__item">
<button aria-expanded="false" class="cc-pdp-faqs__toggle" type="button">
<span class="cc-pdp-faqs__question">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_10', 'Ingredients')); ?>
</span>
<span aria-hidden="true" class="cc-pdp-faqs__icon">
<svg class="cc-pdp-faqs__icon-minus" fill="none" height="2" viewBox="0 0 13 2" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M1 1H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
<svg class="cc-pdp-faqs__icon-plus" fill="none" height="13" viewBox="0 0 13 13" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M6.41699 1V11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
<path d="M1 6.4165H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
</span>
</button>
<div class="cc-pdp-faqs__answer-wrap">
<div class="cc-pdp-faqs__answer-inner">
<div class="cc-pdp-faqs__answer rte">
<p>
<?php echo wp_kses_post((string) av_block_value($section, 'content_1', 'field_11', '3 mg of capsaicin per serving; turmeric, hawthorn, beetroot, cinnamon, berberine, ginseng, black pepper and vitamins D3, K2 and E.')); ?>
</p>
</div>
</div>
</div>
</div>
<div class="cc-pdp-faqs__item">
<button aria-expanded="false" class="cc-pdp-faqs__toggle" type="button">
<span class="cc-pdp-faqs__question">
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_12', 'How to use')); ?>
</span>
<span aria-hidden="true" class="cc-pdp-faqs__icon">
<svg class="cc-pdp-faqs__icon-minus" fill="none" height="2" viewBox="0 0 13 2" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M1 1H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
<svg class="cc-pdp-faqs__icon-plus" fill="none" height="13" viewBox="0 0 13 13" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M6.41699 1V11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
<path d="M1 6.4165H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
</span>
</button>
<div class="cc-pdp-faqs__answer-wrap">
<div class="cc-pdp-faqs__answer-inner">
<div class="cc-pdp-faqs__answer rte">
<p>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_13', 'Take 1 softgel daily with food and water.')); ?>
</p>
</div>
</div>
</div>
</div>
<div class="cc-pdp-faqs__item">
<button aria-expanded="false" class="cc-pdp-faqs__toggle" type="button">
<span class="cc-pdp-faqs__question">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_14', 'Shipping and returns')); ?>
<sup>‡</sup>
</span>
</span>
<span aria-hidden="true" class="cc-pdp-faqs__icon">
<svg class="cc-pdp-faqs__icon-minus" fill="none" height="2" viewBox="0 0 13 2" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M1 1H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
<svg class="cc-pdp-faqs__icon-plus" fill="none" height="13" viewBox="0 0 13 13" width="13" xmlns="http://www.w3.org/2000/svg">
<path d="M6.41699 1V11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
<path d="M1 6.4165H11.8333" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2">
</path>
</svg>
</span>
</button>
<div class="cc-pdp-faqs__answer-wrap">
<div class="cc-pdp-faqs__answer-inner">
<div class="cc-pdp-faqs__answer rte">
<p>
<span>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_15', 'Ships within 24 hours. Delivery takes 3–7 days in the US and 5–12 days internationally.')); ?>
<sup>‡</sup>
</span>
</p>
<p>
<span>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_16', 'We offer a 120-day money-back guarantee.')); ?>
<sup>‡</sup>
</span>
</p>
</div>
</div>
</div>
</div>
</div>
<div class="cc-pdp-block cc-pdp-guarantee av-id-guarantee" id="guarantee" style="--mt-desktop: 40px; --mt-mobile: 30px; --mb-desktop: 0px; --mb-mobile: 0px;">
<h2 class="cc-pdp-guarantee__title">
<span>
<?php echo esc_html((string) av_block_value($section, 'content_1', 'field_17', '120-day guarantee')); ?>
<sup>‡</sup>
</span>
</h2>
<p class="cc-pdp-guarantee__subtitle">
<?php echo esc_html((string) av_block_value($section, 'content_2', 'field_0', 'Four months to decide.')); ?>
</p>
<p class="cc-pdp-guarantee__text">
<span>
<?php echo wp_kses_post((string) av_block_value($section, 'content_2', 'field_1', 'Try Aurivita for 120 days. If you are not satisfied, we refund the product price. According to the brand, fewer than 0.5% of customers request a refund.')); ?>
<sup>‡</sup>
</span>
</p>
<?php if (av_block_value($section, 'content_2', 'field_7', '')) : ?>
<?php echo avp_media_image(av_block_value($section, 'content_2', 'field_7', ''), 'cc-pdp-guarantee__seal', '120-day guarantee‡', '(min-width: 990px) 50vw, 100vw'); ?>
<?php elseif (av_block_allows_fallback($section, 'content_2', 'field_7')) : ?>
<img alt="120-day guarantee‡" class="cc-pdp-guarantee__seal" height="210" loading="lazy" srcset="<?php echo esc_url(av_asset('av-image_25_d23d859a-6e29-46fa-abd3-2b495f6140b0-bbcdf9fc64-w160.webp')); ?> 160w, <?php echo esc_url(av_asset('av-image_25_d23d859a-6e29-46fa-abd3-2b495f6140b0-bbcdf9fc64.webp')); ?> 210w" sizes="(min-width: 990px) 50vw, 100vw" src="<?php echo esc_url(av_asset('av-image_25_d23d859a-6e29-46fa-abd3-2b495f6140b0-bbcdf9fc64.webp')); ?>" width="210"/>
<?php endif; ?>
</div>
<div class="cc-pdp-ugc-slot cc-pdp-ugc-slot--mobile" data-fw-video-target>
</div>
<div class="cc-pdp__mobile-tail cc-pdp__mobile-only">
</div>
</div>
</div>
</div>
</div>
</section>
</section>
<?php else : include __DIR__ . '/generic.php'; endif; ?>
</av-product>
