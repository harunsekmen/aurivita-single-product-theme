<?php
defined('ABSPATH') || exit;
require_once __DIR__ . '/helpers.php';
$context = avp_context(is_array($args ?? null) ? $args : []);
if (!$context || !$context['reference']) { return; }
extract($context, EXTR_OVERWRITE);
?>
<av-sticky class="av-root" data-product-id="<?php echo (int) $product->get_id(); ?>" data-add-label="<?php echo esc_attr((string) av_block_value($section, 'content_0', 'field_4', 'Add to cart')); ?>">
<section class="shopify-section section cc-sticky-atc-section av-id-shopify-section-template--21992530247932__sticky_atc_erCMxq" id="av-<?php echo esc_attr($section_id); ?>-shopify-section-template--21992530247932__sticky_atc_erCMxq">
<div aria-hidden="false" class="cc-sticky-atc is-visible fw-ref-b3ea9a620f" data-cc-sticky-atc="" id="av-<?php echo esc_attr($section_id); ?>-fw-ref-b3ea9a620f">
<div class="cc-sticky-atc__inner">
<div class="cc-sticky-atc__image">
<?php if (av_block_value($section, 'content_0', 'field_5', '')) : ?>
<?php echo avp_media_image(av_block_value($section, 'content_0', 'field_5', ''), '', 'Aurivita Cayenne Pepper', '(min-width: 990px) 50vw, 100vw'); ?>
<?php elseif (av_block_allows_fallback($section, 'content_0', 'field_5')) : ?>
<img alt="Aurivita Cayenne Pepper" height="1200" loading="lazy" srcset="<?php echo esc_url(av_asset('av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590-w160.webp')); ?> 160w, <?php echo esc_url(av_asset('av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590-w320.webp')); ?> 320w, <?php echo esc_url(av_asset('av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590-w480.webp')); ?> 480w, <?php echo esc_url(av_asset('av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590-w640.webp')); ?> 640w, <?php echo esc_url(av_asset('av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590-w900.webp')); ?> 900w, <?php echo esc_url(av_asset('av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590.webp')); ?> 1200w" sizes="(min-width: 990px) 50vw, 100vw" src="<?php echo esc_url(av_asset('av-hf_20260911_212915_1215f0f4-3814-4cb8-a663-c76faaa5b7ca-efbdad5590.webp')); ?>" width="1200"/>
<?php endif; ?>
</div>
<div class="cc-sticky-atc__meta">
<div class="cc-sticky-atc__reviews">
<span aria-hidden="true" class="cc-sticky-atc__stars">
<svg fill="currentColor" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z">
</path>
</svg>
<svg fill="currentColor" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z">
</path>
</svg>
<svg fill="currentColor" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z">
</path>
</svg>
<svg fill="currentColor" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z">
</path>
</svg>
<svg fill="currentColor" viewBox="0 0 17 16" xmlns="http://www.w3.org/2000/svg">
<path d="M8.02843 0.125618C8.08665 -0.0418634 8.32351 -0.041863 8.38172 0.125618L10.2784 5.58205C10.3041 5.65593 10.373 5.70603 10.4512 5.70762L16.2267 5.82532C16.404 5.82893 16.4772 6.0542 16.3359 6.16132L11.7326 9.65127C11.6703 9.69853 11.6439 9.77958 11.6666 9.85445L13.3394 15.3836C13.3907 15.5533 13.1991 15.6926 13.0535 15.5913L8.3119 12.2918C8.24769 12.2471 8.16246 12.2471 8.09826 12.2918L3.35662 15.5913C3.21108 15.6926 3.01946 15.5533 3.07081 15.3836L4.74359 9.85445C4.76624 9.77958 4.7399 9.69853 4.67757 9.65127L0.0742972 6.16132C-0.0669971 6.05419 0.00619644 5.82893 0.183471 5.82532L5.95894 5.70762C6.03715 5.70603 6.1061 5.65593 6.13178 5.58205L8.02843 0.125618Z">
</path>
</svg>
</span>
<p class="cc-sticky-atc__rating">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_0', '4.6 / 5.0 · Source product reviews')); ?>
</p>
</div>
<h3 class="cc-sticky-atc__title">
<?php echo esc_html($product->get_name()); ?>
</h3>
</div>
<div aria-hidden="false" class="cc-sticky-atc__stock">
<span aria-hidden="true" class="cc-sticky-atc__stock-dot">
</span>
<span class="cc-sticky-atc__stock-status">
<?php echo $available ? esc_html((string) av_block_value($section, 'content_0', 'field_2', 'In stock')) : 'Sold out'; ?>
</span>
<span class="cc-sticky-atc__stock-body">—</span>
<span class="cc-sticky-atc__stock-link">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_3', 'ready for same-day shipping')); ?>
</span>
</div>
<form class="av-sticky-form" data-av-sticky-form data-product-id="<?php echo (int) $product->get_id(); ?>" action="<?php echo esc_url($product->get_permalink()); ?>" method="post" style="display:contents">
<input type="hidden" name="add-to-cart" value="<?php echo (int) $product->get_id(); ?>">
<input type="hidden" name="product_id" value="<?php echo (int) $product->get_id(); ?>">
<input type="hidden" name="quantity" value="1">
<?php if ($product->is_type('variable')) : ?>
<input type="hidden" name="variation_id" value="<?php echo (int) ($selected['id'] ?? 0); ?>">
<div data-av-attributes hidden>
<?php foreach (($selected['attributes'] ?? []) as $name => $value) : ?>
<input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
<?php endforeach; ?>
</div>
<?php endif; ?>
<button aria-label="Add to cart" class="cc-sticky-atc__button" data-cc-sticky-atc-btn="" data-sticky-add="" type="submit" data-av-add data-fw-cart-state="idle" aria-busy="false" <?php disabled(!$available); ?>>
<span aria-hidden="true" class="cc-sticky-atc__spinner">
</span>
<span class="cc-sticky-atc__label">
<?php echo esc_html((string) av_block_value($section, 'content_0', 'field_4', 'Add to cart')); ?>
</span>
<span aria-hidden="true" class="cc-sticky-atc__dot">
</span>
<span class="cc-sticky-atc__price" data-cc-sticky-atc-price="">
<span class="cc-sticky-atc__price-current">
<?php echo av_money($selected['price'] ?? $data['price']); ?>
</span>
<span class="cc-sticky-atc__price-compare" <?php if (($selected['regular_price'] ?? $data['regular_price']) <= ($selected['price'] ?? $data['price'])) : ?>hidden<?php endif; ?>>
<?php echo av_money($selected['regular_price'] ?? $data['regular_price']); ?>
</span>
</span>
</button>
</form>
</div>
</div>
</section>
</av-sticky>
