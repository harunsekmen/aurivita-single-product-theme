<?php
defined('ABSPATH') || exit;
$gallery = avp_gallery($product, false);
?>
<div class="fw-product" id="product">
    <div class="cc-pdp__main-wrap fw-product__gallery" aria-label="Product images">
        <?php foreach ($gallery as $index => $image) : ?>
            <div class="cc-pdp__main-slide" data-image-id="<?php echo esc_attr($image['id']); ?>" <?php if ($index) : ?>hidden<?php endif; ?> role="group" aria-label="<?php echo esc_attr(($index + 1) . ' / ' . count($gallery)); ?>">
                <?php echo avp_gallery_image($image, 'cc-pdp__main-image', '(min-width:1240px) 572px, (min-width:750px) calc((100vw - 96px) / 2), calc(100vw - 32px)', $index === 0); ?>
            </div>
        <?php endforeach; ?>
        <div class="fw-product__thumbs">
            <?php foreach ($gallery as $index => $image) : ?>
                <button type="button" class="cc-pdp__thumb" aria-label="<?php echo esc_attr('Image ' . ($index + 1)); ?>" aria-pressed="<?php echo $index === 0 ? 'true' : 'false'; ?>"><?php echo avp_gallery_image($image, 'cc-pdp__thumb-image', '72px'); ?></button>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="fw-product__details">
        <h1><?php echo esc_html($product->get_name()); ?></h1>
        <?php if ($product->get_short_description()): ?><div class="woocommerce-product-details__short-description"><?php echo wp_kses_post($product->get_short_description()); ?></div><?php endif; ?>
        <?php if ($product->is_type('variable')) : ?>
            <div class="fw-options" data-fw-options data-av-options>
                <?php foreach ($product->get_variation_attributes() as $attribute => $values) :
                    $key = 'attribute_' . sanitize_title($attribute); ?>
                    <fieldset class="fw-options__group">
                        <legend><?php echo esc_html(wc_attribute_label($attribute, $product)); ?></legend>
                        <div class="cc-pdp-dqs__pills">
                            <?php foreach ($values as $value) :
                                $active = ($selected['attributes'][$key] ?? '') === $value;
                                $term = taxonomy_exists($attribute) ? get_term_by('slug', $value, $attribute) : false; ?>
                                <label class="cc-pdp-dqs__pill<?php echo $active ? ' is-active' : ''; ?>">
                                    <input type="radio" data-fw-option data-attribute="<?php echo esc_attr($key); ?>" name="<?php echo esc_attr($section_id . '-' . $key); ?>" value="<?php echo esc_attr($value); ?>" <?php checked($active); ?>>
                                    <?php echo esc_html($term && !is_wp_error($term) ? $term->name : $value); ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php include __DIR__ . '/form.php'; ?>
        <?php if (!has_block('aurivita/av-product',$product->get_description())): ?><div class="rte"><?php echo wp_kses_post($product->get_description()); ?></div><?php endif; ?>
    </div>
</div>
