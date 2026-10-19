<?php
defined('ABSPATH') || exit;
$form_id = 'AvForm-' . $section_id;
$price = $selected['price'] ?? $data['price'];
$compare = $selected['regular_price'] ?? $data['regular_price'];
$quantity = $product->is_type('variable') ? 1 : max(1, $product->get_min_purchase_quantity());
if (!$product->is_type('variable') && !$product->is_type('simple')) {
    $prior_product = $GLOBALS['product'] ?? null;
    $GLOBALS['product'] = $product;
    woocommerce_template_single_add_to_cart();
    $GLOBALS['product'] = $prior_product;
    return;
}
avp_native_picker(compact('product', 'selected', 'section_id'));
do_action('woocommerce_before_add_to_cart_form');
?>
<product-form-component data-section-id="<?php echo esc_attr($section_id); ?>" data-product-id="<?php echo (int) $product->get_id(); ?>" data-product-url="<?php echo esc_url($product->get_permalink()); ?>" data-quantity-default="1">
    <form id="<?php echo esc_attr($form_id); ?>" class="cart av-product-form" action="<?php echo esc_url($product->get_permalink()); ?>" method="post" enctype="multipart/form-data" data-av-product-form data-product-id="<?php echo (int) $product->get_id(); ?>">
        <input type="hidden" name="add-to-cart" value="<?php echo (int) $product->get_id(); ?>">
        <input type="hidden" name="product_id" value="<?php echo (int) $product->get_id(); ?>">
        <?php if ($product->is_type('variable')) : ?>
            <input type="hidden" ref="variantId" name="variation_id" value="<?php echo (int) ($selected['id'] ?? 0); ?>">
            <div data-av-attributes hidden>
                <?php foreach (($selected['attributes'] ?? []) as $name => $value) : ?>
                    <input type="hidden" name="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($value); ?>">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php do_action('woocommerce_before_add_to_cart_button'); do_action('woocommerce_before_add_to_cart_quantity'); ?>
        <?php if ($reference) : ?>
            <input type="hidden" name="quantity" value="1">
        <?php else : ?>
            <div class="fw-quantity">
                <label for="<?php echo esc_attr($form_id . '-quantity'); ?>">Adet</label>
                <input id="<?php echo esc_attr($form_id . '-quantity'); ?>" type="number" name="quantity" value="<?php echo (int) $quantity; ?>" min="<?php echo (int) $quantity; ?>" step="1" <?php if ($product->get_max_purchase_quantity() > 0) : ?>max="<?php echo (int) $product->get_max_purchase_quantity(); ?>"<?php endif; ?> <?php disabled($product->is_sold_individually()); ?>>
                <?php if ($product->is_sold_individually()) : ?><input type="hidden" name="quantity" value="1"><?php endif; ?>
            </div>
        <?php endif; ?>
        <?php do_action('woocommerce_after_add_to_cart_quantity'); ?>
        <div ref="productFormButtons">
            <add-to-cart-component ref="addToCartButtonContainer">
                <button class="button av-button main-product-atc single_add_to_cart_button" type="submit" ref="addToCartButton" data-av-add data-fw-cart-state="idle" aria-busy="false" <?php disabled(!$available); ?>>
                    <span data-cart-label><?php echo $available ? 'Add to cart' : ($product->is_type('variable') && !$selected ? 'This option is unavailable' : 'Sold out'); ?></span>
                    <span class="local-price"><?php echo av_money($price); ?></span>
                    <del class="local-compare" <?php if ($compare <= $price) : ?>hidden<?php endif; ?>><?php echo av_money($compare); ?></del>
                </button>
            </add-to-cart-component>
        </div>
        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
    </form>
    <div class="fw-cart-feedback" data-fw-cart-feedback data-state="idle">
        <p data-fw-cart-status role="status" aria-live="polite" aria-atomic="true"></p>
        <div class="fw-cart-feedback__actions" data-fw-cart-actions hidden>
            <a href="<?php echo esc_url(av_url('/cart')); ?>" data-av-view-cart>View cart</a>
            <button type="button" data-fw-continue>Continue shopping</button>
            <button type="button" data-av-retry hidden>Try again</button>
        </div>
    </div>
</product-form-component>
<?php do_action('woocommerce_after_add_to_cart_form'); ?>
<p data-fw-selection-status role="status" aria-live="polite"><?php if (!$available) : echo $selected ? 'Sold out' : 'This option is unavailable'; endif; ?></p>
