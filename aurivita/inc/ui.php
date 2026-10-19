<?php
defined('ABSPATH') || exit;

function av_breadcrumb(): void {
    if (function_exists('rank_math_the_breadcrumbs')) rank_math_the_breadcrumbs([
        'wrap_before' => '<nav class="rank-math-breadcrumb av-breadcrumb" aria-label="Breadcrumb"><p>',
        'wrap_after' => '</p></nav>',
    ]);
}

function av_public_contact_email(): string {
    $email = (string) av_site('contact_email');
    if (!is_email($email)) return '';
    $domain = strtolower(substr(strrchr($email, '@'), 1));
    return preg_match('/(?:^|\.)(?:local|localhost|test|example|invalid)$/', $domain)
        || in_array($domain, ['example.com', 'example.org', 'example.net'], true) ? '' : $email;
}

function av_info_navigation(): void {
    $policies = av_site_menu('footer_policies');
    $contact = av_site_url('contact');
    if ($policies === '' && $contact === '') return;
    ?>
    <aside class="av-info-nav" aria-labelledby="av-info-title">
        <p class="av-ui-kicker">STORE INFORMATION</p>
        <h2 id="av-info-title">Information center</h2>
        <?php if ($policies !== ''): ?><nav aria-label="Shop bilgileri"><?php echo $policies; ?></nav><?php endif; ?>
        <?php if ($contact !== ''): ?><a class="av-info-contact" href="<?php echo esc_url($contact); ?>">Get in touch <?php echo av_account_icon('chevron'); ?></a><?php endif; ?>
    </aside>
    <?php
}

add_action('woocommerce_archive_description', static function(): void {
    if (!is_shop()) return;
    $shop = get_post(wc_get_page_id('shop'));
    if ($shop instanceof WP_Post && trim($shop->post_content) === '') {
        echo '<p class="av-catalog-description">Explore product options and current prices.</p>';
    }
}, 20);

add_action('wp', static function(): void {
    if (!is_shop() && !is_product_taxonomy()) return;
    foreach ([20 => 'woocommerce_result_count', 30 => 'woocommerce_catalog_ordering'] as $priority => $callback) {
        remove_action('woocommerce_before_shop_loop', $callback, $priority);
        add_action('woocommerce_before_shop_loop', static function() use ($callback): void {
            if (is_main_query() && !wc_get_loop_prop('is_shortcode') && (int) wc_get_loop_prop('total') <= 1) return;
            $callback();
        }, $priority);
    }
});
