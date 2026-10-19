<?php
defined('ABSPATH') || exit;

$section = $args['section'] ?? [];
$section_id = sanitize_html_class((string) ($args['section_id'] ?? 'home_intro'));
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
if (!$resource) {
    return;
}
$page = av_section_value($section, 'page', 'icerikler');
$info_link = is_numeric($page) ? get_permalink((int) $page) : (string) $page;
if ($info_link !== '' && !preg_match('~^(?:[a-z][a-z0-9+.-]*:|/|#)~i', $info_link)) {
    $info_link = '/pages/' . $info_link;
}
$secondary = (string) av_section_value($section, 'secondary', 'Ingredient information');
$image = av_section_value($section, 'image');
$prices = array_column(av_variations($resource), 'price');
$starting_price = $prices ? min(array_map('floatval', $prices)) : (float) wc_get_price_to_display($resource);
$copy = new WP_HTML_Tag_Processor(wp_kses_post(av_section_value($section, 'copy', '<p>Explore product details and available options.</p>')));
while ($copy->next_tag('A')) {
    $href = $copy->get_attribute('href');
    if (is_string($href)) {
        $copy->set_attribute('href', av_url($href));
    }
}
?>
<section class="av-home av-home-intro<?php echo !empty($image) || av_is_reference_product($resource) || $resource->get_image_id() ? '' : ' av-home-intro--text'; ?>" aria-labelledby="AvHomeIntro-<?php echo esc_attr($section_id); ?>">
  <div class="av-home__container av-home-intro__grid">
    <div class="av-home-intro__visual">
      <?php if (is_numeric($image) && (int) $image > 0) : ?>
        <?php echo wp_get_attachment_image((int) $image, 'full', false, ['sizes' => '(min-width: 1304px) 575px, (min-width: 750px) 44vw, 480px', 'loading' => 'lazy', 'alt' => $resource->get_name()]); ?>
      <?php elseif (is_string($image) && $image !== '') : ?>
        <img src="<?php echo esc_url(str_starts_with($image, 'shopify://') ? av_asset(basename($image)) : $image); ?>" width="1200" height="1200" sizes="(min-width: 1304px) 575px, (min-width: 750px) 44vw, 480px" loading="lazy" alt="<?php echo esc_attr($resource->get_name()); ?>">
      <?php elseif (av_is_reference_product($resource)) : ?>
        <img srcset="<?php echo esc_url(av_asset('av-Aurivita_package_replace_17-d9b38dd70e-w160.webp')); ?> 160w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_17-d9b38dd70e-w320.webp')); ?> 320w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_17-d9b38dd70e-w480.webp')); ?> 480w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_17-d9b38dd70e-w640.webp')); ?> 640w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_17-d9b38dd70e-w900.webp')); ?> 900w, <?php echo esc_url(av_asset('av-Aurivita_package_replace_17-d9b38dd70e.webp')); ?> 1200w" sizes="(min-width: 990px) 50vw, 100vw" src="<?php echo esc_url(av_asset('av-Aurivita_package_replace_17-d9b38dd70e.webp')); ?>" width="1200" height="1200" loading="lazy" alt="<?php echo esc_attr($resource->get_name()); ?>">
      <?php elseif ($resource->get_image_id()) : ?>
        <?php echo wp_get_attachment_image($resource->get_image_id(), 'full', false, ['sizes' => '(min-width: 990px) 48vw, 100vw', 'loading' => 'lazy', 'alt' => get_post_meta($resource->get_image_id(), '_wp_attachment_image_alt', true) ?: $resource->get_name()]); ?>
      <?php endif; ?>
    </div>
    <div>
      <p class="av-home__eyebrow"><?php echo esc_html(av_section_value($section, 'eyebrow', 'OUR PRODUCT')); ?></p>
      <h2 id="AvHomeIntro-<?php echo esc_attr($section_id); ?>" class="av-home__title"><?php echo esc_html($resource->get_name()); ?></h2>
      <div class="av-home__copy"><?php echo $copy->get_updated_html(); ?></div>
      <?php if ($resource->get_price() !== ''): ?><div class="av-home-intro__price"><span><?php echo esc_html__('From', 'aurivita'); ?></span><strong><?php echo av_money($starting_price); ?></strong></div><?php endif; ?>
      <div class="av-home__actions">
        <a class="av-home__button" href="<?php echo esc_url(av_url($resource->get_permalink())); ?>"><?php echo esc_html(av_section_value($section, 'button', 'Get to know the product')); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <?php if ($secondary !== '' && $info_link !== '') : ?><a class="av-home__link" href="<?php echo esc_url(av_url($info_link)); ?>"><?php echo esc_html($secondary); ?></a><?php endif; ?>
      </div>
    </div>
  </div>
</section>
