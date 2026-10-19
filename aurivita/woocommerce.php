<?php
get_header();
if (is_product()) {
    echo '<div class="av-product-trail">'; av_breadcrumb(); echo '</div>';
    while (have_posts()) { the_post(); $GLOBALS['product'] = wc_get_product(get_the_ID()); wc_print_notices();
        if (has_block('aurivita/av-product')) the_content();
        else echo av_render_section(['type'=>'av-product','settings'=>[]], 'product-' . get_the_ID());
        WC()->structured_data->generate_product_data($GLOBALS['product']);
        if (!av_is_reference_product($GLOBALS['product'])) { echo '<div class="av-page av-generic-product-tabs">'; woocommerce_output_product_data_tabs(); echo '</div>'; }
    }
} else { echo '<div class="av-commerce-page av-catalog">'; av_breadcrumb(); woocommerce_content(); echo '</div>'; }
get_footer();
