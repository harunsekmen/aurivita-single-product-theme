<?php
defined('ABSPATH') || exit;

$section = $args['section'] ?? [];
$section_id = sanitize_html_class((string) ($args['section_id'] ?? 'home_packages'));
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
if (!av_is_reference_product($resource)) {
    get_template_part('template-parts/home/options', null, ['section'=>$section, 'section_id'=>$section_id, 'product'=>$resource]);
    return;
}
$variations = [];
foreach (av_variations($resource) as $variation) {
    $servings = (int) preg_replace('/\D+/', '', (string) ($variation['servings'] ?? ''));
    $packs = (int) preg_replace('/\D+/', '', (string) ($variation['packs'] ?? ''));
    $variations[$servings][$packs] = $variation;
}
$serving_options = array_keys($variations);
sort($serving_options, SORT_NUMERIC);
$blocks = av_section_blocks($section);
$copy = new WP_HTML_Tag_Processor(wp_kses_post(av_section_value($section, 'copy', '<p>Compare available options and current prices.</p>')));
while ($copy->next_tag('A')) {
    $href = $copy->get_attribute('href');
    if (is_string($href)) {
        $copy->set_attribute('href', av_url($href));
    }
}
$note = (string) av_section_value($section, 'note', 'Displayed amounts are the total bundle price. Your selected bundle opens on the product page.');
?>
<style>
<?php foreach ($serving_options as $dose => $servings) : ?>
  .av-home-packs--<?php echo esc_attr($section_id); ?>:has(#AvHomeDose-<?php echo esc_attr($section_id); ?>-<?php echo (int) $dose; ?>:checked) .av-home-packs__group:not([data-dose="<?php echo (int) $dose; ?>"]) { display: none; }
<?php endforeach; ?>
</style>
<section class="av-home av-home-packs av-home-packs--<?php echo esc_attr($section_id); ?>" id="AvHomePackages" aria-labelledby="AvHomePacks-<?php echo esc_attr($section_id); ?>">
  <div class="av-home__container">
    <div class="av-home__heading">
      <p class="av-home__eyebrow"><?php echo esc_html(av_section_value($section, 'eyebrow', 'CHOOSE YOUR BUNDLE')); ?></p>
      <h2 id="AvHomePacks-<?php echo esc_attr($section_id); ?>" class="av-home__title"><?php echo esc_html(av_section_value($section, 'heading', 'A bundle for your routine.')); ?></h2>
      <div class="av-home__copy"><?php echo $copy->get_updated_html(); ?></div>
    </div>
    <fieldset class="av-home-packs__selector">
      <legend><?php echo esc_html__('Serving option', 'aurivita'); ?></legend>
      <?php foreach ($serving_options as $dose => $servings) : ?>
        <div class="av-home-packs__choice">
          <input type="radio" name="AvHomeDose-<?php echo esc_attr($section_id); ?>" id="AvHomeDose-<?php echo esc_attr($section_id); ?>-<?php echo (int) $dose; ?>" value="<?php echo esc_attr($servings . ' servings'); ?>" <?php checked($dose, 0); ?>>
          <label for="AvHomeDose-<?php echo esc_attr($section_id); ?>-<?php echo (int) $dose; ?>"><?php echo esc_html($servings . ' servings'); ?></label>
        </div>
      <?php endforeach; ?>
    </fieldset>
    <?php foreach ($serving_options as $dose => $servings) : ?>
      <div class="av-home-packs__group" data-dose="<?php echo (int) $dose; ?>">
        <?php foreach ($blocks as $block) :
            $settings = $block['settings'] ?? [];
            $pack_value = (string) ($settings['pack_value'] ?? '1 pack');
            $pack_count = (int) preg_replace('/\D+/', '', $pack_value);
            $variant = $variations[$servings][$pack_count] ?? null;
            $featured = filter_var($settings['featured'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $badge = (string) ($settings['badge'] ?? 'Featured bundle');
            $image = $settings['image'] ?? '';
            $fallback_image = match ($pack_count) {
                1 => 'av-cayenne_pepper_296x436_1-4f2766da94.webp',
                3 => 'av-Aurivita_package_replace_9-af1c95b8cf.webp',
                default => 'av-Aurivita_package_replace_10-05aef77e78.webp',
            };
            $image_width = $pack_count === 1 ? 296 : 1080;
            $image_height = $pack_count === 1 ? 436 : 1080;
            $price = (float) ($variant['price'] ?? 0);
            $regular_price = (float) ($variant['regular_price'] ?? 0);
            $saving = $regular_price > $price ? (int) round(($regular_price - $price) * 100 / $regular_price) : 0;
            $variant_link = '';
            if ($variant) {
                if (!empty($variant['source_id'])) {
                    $variant_link = av_url(add_query_arg('variant', (string) $variant['source_id'], '/products/' . $resource->get_slug()));
                } else {
                    $variant_link = av_url(add_query_arg($variant['attributes'] ?? [], $resource->get_permalink()));
                }
            }
        ?>
          <article class="av-home-packs__card<?php echo $featured ? ' av-home-packs__card--featured' : ''; ?>">
            <?php if ($featured && $badge !== '') : ?><span class="av-home-packs__badge"><?php echo esc_html($badge); ?></span><?php endif; ?>
            <?php if (is_numeric($image) && (int) $image > 0) : ?>
              <?php echo wp_get_attachment_image((int) $image, 'full', false, ['sizes' => '(min-width: 1304px) 340px, (min-width: 750px) 30vw, 380px', 'loading' => 'lazy', 'alt' => $pack_value]); ?>
            <?php elseif (is_string($image) && $image !== '') : ?>
              <img src="<?php echo esc_url(str_starts_with($image, 'shopify://') ? av_asset(basename($image)) : $image); ?>" width="<?php echo (int) $image_width; ?>" height="<?php echo (int) $image_height; ?>" sizes="(min-width: 1304px) 340px, (min-width: 750px) 30vw, 380px" loading="lazy" alt="<?php echo esc_attr($pack_value); ?>">
            <?php elseif (av_is_reference_product($resource)) : ?>
              <img srcset="<?php echo esc_url(av_asset(str_replace('.webp', '-w160.webp', $fallback_image))); ?> 160w, <?php if ($image_width > 480) : ?><?php echo esc_url(av_asset(str_replace('.webp', '-w320.webp', $fallback_image))); ?> 320w, <?php echo esc_url(av_asset(str_replace('.webp', '-w480.webp', $fallback_image))); ?> 480w, <?php endif; ?><?php echo esc_url(av_asset($fallback_image)); ?> <?php echo (int) $image_width; ?>w" sizes="210px" src="<?php echo esc_url(av_asset($fallback_image)); ?>" width="<?php echo (int) $image_width; ?>" height="<?php echo (int) $image_height; ?>" loading="lazy" alt="<?php echo esc_attr($pack_value); ?>">
            <?php elseif (!empty($variant['image'])) : ?>
              <img src="<?php echo esc_url($variant['image']); ?>" width="<?php echo (int) $image_width; ?>" height="<?php echo (int) $image_height; ?>" loading="lazy" alt="<?php echo esc_attr($variant['image_alt'] ?: $pack_value); ?>">
            <?php elseif ($resource->get_image_id()) : ?>
              <?php echo wp_get_attachment_image($resource->get_image_id(), 'full', false, ['sizes' => '(min-width: 1304px) 340px, (min-width: 750px) 30vw, 380px', 'loading' => 'lazy', 'alt' => $pack_value]); ?>
            <?php endif; ?>
            <h3><?php echo esc_html($settings['title'] ?? ''); ?></h3>
            <p class="av-home-packs__description"><?php echo esc_html($pack_value . ' · ' . $servings . ' servings'); ?></p>
            <?php if ($variant) : ?>
              <div class="av-home-packs__prices"><strong><?php echo av_money($price); ?></strong><?php if ($regular_price > $price) : ?><s><?php echo av_money($regular_price); ?></s><?php endif; ?></div>
              <p class="av-home-packs__saving"><?php if ($regular_price > $price) : ?><?php echo esc_html(sprintf(__('%s%% savings', 'aurivita'), $saving)); ?><?php endif; ?></p>
              <?php if (!empty($variant['available'])) : ?><a class="av-home__button" href="<?php echo esc_url($variant_link); ?>"><?php echo esc_html(av_section_value($section, 'button', 'Choose bundle')); ?><svg aria-hidden="true" focusable="false" viewBox="0 0 24 24" fill="none"><path d="M4 12h15m-6-6 6 6-6 6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg></a><?php else : ?><span class="av-home-packs__unavailable"><?php echo esc_html__('Sold out', 'aurivita'); ?></span><?php endif; ?>
            <?php else : ?>
              <p class="av-home-packs__saving"><?php echo esc_html__('This bundle is unavailable', 'aurivita'); ?></p>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
    <?php if ($note !== '') : ?><p class="av-home-packs__note"><?php echo esc_html($note); ?></p><?php endif; ?>
  </div>
</section>
