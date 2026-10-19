<?php
defined('ABSPATH') || exit;

$section = $args['section'] ?? [];
$section_id = sanitize_html_class((string) ($args['section_id'] ?? 'home_hero'));
$resource = av_main_product();
$selection = av_section_value($section, 'product');
if ($selection instanceof WC_Product) {
    $resource = $selection;
} elseif ($selection !== '') {
    $record = is_numeric($selection) ? null : get_page_by_path(basename((string) wp_parse_url((string) $selection, PHP_URL_PATH)), OBJECT, 'product');
    $selected_product = wc_get_product(is_numeric($selection) ? (int) $selection : ($record->ID ?? 0));
    if ($selected_product) {
        $resource = $selected_product;
    }
}
$product_link = $resource ? av_url($resource->get_permalink()) : av_url('/collections/all');
$heading = (string) av_section_value($section, 'heading', 'Meet your new favorite.');
$eyebrow = (string) av_section_value($section, 'eyebrow', 'EXPLORE THE PRODUCT');
$secondary = (string) av_section_value($section, 'secondary', 'Compare bundles');
$copy = new WP_HTML_Tag_Processor(wp_kses_post(av_section_value($section, 'copy', '<p>Explore product details and compare your options.</p>')));
while ($copy->next_tag('A')) {
    $href = $copy->get_attribute('href');
    if (is_string($href)) {
        $copy->set_attribute('href', av_url($href));
    }
}
$image = av_section_value($section, 'image');
$image_alt = $resource ? $resource->get_name() : $heading;
$facts = av_section_blocks($section);
$has_image = !empty($image) || ($resource && (av_is_reference_product($resource) || $resource->get_image_id()));
?>
<section class="av-home av-home-hero<?php echo $has_image ? '' : ' av-home-hero--text'; ?>" aria-labelledby="AvHomeHero-<?php echo esc_attr($section_id); ?>">
  <div class="av-home__container">
    <div class="av-home-hero__grid">
      <div>
        <?php if ($eyebrow !== '') : ?><p class="av-home__eyebrow"><?php echo esc_html($eyebrow); ?></p><?php endif; ?>
        <h1 id="AvHomeHero-<?php echo esc_attr($section_id); ?>" class="av-home__title av-home-hero__title"><?php echo nl2br(esc_html($heading)); ?></h1>
        <?php if ($copy->get_updated_html() !== '') : ?><div class="av-home__copy"><?php echo $copy->get_updated_html(); ?></div><?php endif; ?>
        <div class="av-home__actions">
          <a class="av-home__button" href="<?php echo esc_url($product_link); ?>"><?php echo esc_html(av_section_value($section, 'button', 'Explore the product')); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
          <?php if ($secondary !== '') : ?><a class="av-home__link" href="#AvHomePackages"><?php echo esc_html($secondary); ?></a><?php endif; ?>
        </div>
      </div>
      <div class="av-home-hero__visual">
        <?php if (is_numeric($image) && (int) $image > 0) : ?>
          <?php echo wp_get_attachment_image((int) $image, 'full', false, ['sizes' => '(min-width: 1304px) 596px, (min-width: 750px) 46vw, calc(100vw - 40px)', 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => get_post_meta((int) $image, '_wp_attachment_image_alt', true) ?: $image_alt]); ?>
        <?php elseif (is_string($image) && $image !== '') : ?>
          <img src="<?php echo esc_url(str_starts_with($image, 'shopify://') ? av_asset(basename($image)) : $image); ?>" width="1080" height="1080" sizes="(min-width: 1304px) 596px, (min-width: 750px) 46vw, calc(100vw - 40px)" alt="<?php echo esc_attr($image_alt); ?>" loading="eager" fetchpriority="high">
        <?php elseif ($resource && av_is_reference_product($resource)) : ?>
          <img srcset="<?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w160.webp')); ?> 160w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w320.webp')); ?> 320w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w480.webp')); ?> 480w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w640.webp')); ?> 640w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w720.webp')); ?> 720w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf-w900.webp')); ?> 900w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf.webp')); ?> 1080w" sizes="(min-width: 1304px) 596px, (min-width: 750px) 46vw, calc(100vw - 40px)" src="<?php echo esc_url(av_asset('av-Aurivita_package_replace_9-af1c95b8cf.webp')); ?>" width="1080" height="1080" alt="<?php echo esc_attr($image_alt); ?>" loading="eager" fetchpriority="high">
        <?php elseif ($resource && $resource->get_image_id()) : ?>
          <?php echo wp_get_attachment_image($resource->get_image_id(), 'full', false, ['sizes' => '(min-width: 1304px) 596px, (min-width: 750px) 46vw, calc(100vw - 40px)', 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => get_post_meta($resource->get_image_id(), '_wp_attachment_image_alt', true) ?: $image_alt]); ?>
        <?php endif; ?>
      </div>
    </div>
    <?php if ($facts) : ?>
      <div class="av-home-hero__facts">
        <?php foreach ($facts as $index => $block) : ?>
          <div class="av-home-hero__fact">
            <span class="av-home-hero__fact-mark" aria-hidden="true"><?php echo esc_html('0' . ($index + 1)); ?></span>
            <p><?php echo esc_html($block['settings']['title'] ?? ''); ?><br><span><?php echo esc_html($block['settings']['text'] ?? ''); ?></span></p>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
