<?php
defined('ABSPATH') || exit;
require_once __DIR__ . '/inc/helpers.php';
require_once __DIR__ . '/inc/account.php';
require_once __DIR__ . '/inc/site.php';
require_once __DIR__ . '/inc/performance.php';
require_once __DIR__ . '/inc/ui.php';
require_once __DIR__ . '/inc/checkout.php';
add_action('init','av_seed_site_settings',30);
function av_style_files(): array { return ['fw-styles-shared.css','fw-styles-av-header.css','av-home-styles.css','fw-styles-av-product.css','fw-product-ux.css','fw-styles-av-reviews.css','fw-styles-av-problem.css','fw-styles-av-mechanism.css','fw-styles-av-routine.css','fw-styles-av-timeline.css','fw-styles-av-stats.css','fw-styles-av-compare.css','fw-styles-av-marquee.css','fw-styles-av-faq.css','fw-styles-av-science.css','fw-styles-av-sticky.css','fw-styles-av-footer.css','fw-styles-av-page.css','fw-cart-drawer.css']; }
add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('woocommerce');
    add_theme_support('editor-styles');
    add_theme_support('align-wide');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script']);
    register_nav_menus(['primary'=>'Main menu','footer_support'=>'Alt alan · Support','footer_links'=>'Footer · Links','footer_policies'=>'Alt alan · Policies','checkout_policies'=>'Checkout · Policies']);
    add_editor_style(array_merge(['assets/fonts.css'], array_map(fn($file) => 'assets/source/' . $file, av_style_files()), ['assets/editor.css']));
});
add_action('wp_enqueue_scripts','av_enqueue_assets');
add_filter('show_admin_bar', '__return_false');
add_filter('body_class', function($classes) {
    if (av_site('profile') === 'generic') $classes[] = 'av-generic-site';
    if (av_is_checkout()) $classes[] = 'av-checkout';
    if (is_account_page()) {
        $classes[] = 'av-account';
        if (!is_user_logged_in() || is_wc_endpoint_url('lost-password')) $classes[] = 'av-account-guest';
        foreach (['orders', 'downloads'] as $endpoint) if (is_wc_endpoint_url($endpoint)) $classes[] = 'av-account-' . $endpoint;
    }
    return $classes;
});
add_filter('woocommerce_enqueue_styles', function($styles) { return is_product() && av_is_reference_product(wc_get_product(get_the_ID())) ? [] : $styles; });
add_filter('woocommerce_product_tabs', function($tabs) {
    if (is_product() && av_is_reference_product(wc_get_product(get_the_ID()))) return [];
    unset($tabs['description']);
    return $tabs;
});
add_filter('woocommerce_product_single_add_to_cart_text', fn() => 'Add to cart');
add_filter('woocommerce_product_add_to_cart_text', fn() => 'View product');
add_filter('woocommerce_is_purchasable', function($can, $product) { return $can; },10,2);
add_action('admin_post_av_contact', 'av_contact_submit');
add_action('admin_post_nopriv_av_contact', 'av_contact_submit');
function av_contact_submit(): void {
    if (!isset($_POST['av_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['av_nonce'])), 'av_contact')) wp_die('This form has expired. Please refresh the page.', '', ['response'=>403]);
    $email = sanitize_email(wp_unslash($_POST['email'] ?? ''));
    $message = sanitize_textarea_field(wp_unslash($_POST['message'] ?? ''));
    $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
    $ok = is_email($email) && $message !== '' && empty($_POST['website']);
    if ($ok) $ok = wp_mail(av_site('contact_email') ?: get_option('admin_email'), av_brand_name().' contact: ' . $name, $message . "\n\n" . $name . ' <' . $email . '>', ['Reply-To: ' . $email]);
    wp_safe_redirect(add_query_arg('contact', $ok ? 'sent' : 'error', av_site_url('contact') ?: home_url('/'))); exit;
}

add_action('wp_head',function() {
    $icon = av_site('favicon');
    if (!get_option('site_icon') && is_string($icon) && preg_match('~^https?://~i',$icon)) echo '<link rel="icon" href="'.esc_url($icon).'">';
});

add_filter('wp_theme_json_data_theme',function($data) {
    $colors = av_global();
    $palette=[];
    foreach (['ink'=>'Text','accent'=>'Accent','action'=>'Button','surface'=>'Surface'] as $key=>$label) {
        $color = sanitize_hex_color($colors['av_'.$key] ?? '');
        if ($color) $palette[]=['slug'=>$key,'name'=>$label,'color'=>$color];
    }
    if ($palette) $data->update_with(['version'=>3,'settings'=>['color'=>['palette'=>$palette]],'styles'=>['color'=>['text'=>sanitize_hex_color($colors['av_ink'] ?? '') ?: '#252525','background'=>'#ffffff']]]);
    return $data;
});

add_action('enqueue_block_editor_assets',function() {
    $vars=':root{';
    foreach(av_global() as $key=>$value) if(is_string($value) && preg_match('/^#[0-9a-f]{6}$/i',$value)) $vars.='--'.str_replace('_','-',$key).':'.$value.';';
    wp_register_style('aurivita-editor-tokens',false); wp_enqueue_style('aurivita-editor-tokens'); wp_add_inline_style('aurivita-editor-tokens',$vars.'}');
});
