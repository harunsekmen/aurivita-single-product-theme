<?php
/** @version 9.3.0 */
defined('ABSPATH') || exit;
do_action('woocommerce_before_account_navigation');
$customer = wp_get_current_user();
$name = av_account_customer_name($customer);
$initial = mb_substr($name, 0, 1);
$icons = ['dashboard' => 'dashboard', 'orders' => 'orders', 'downloads' => 'download', 'edit-address' => 'address', 'edit-account' => 'user', 'customer-logout' => 'logout'];
?>
<nav class="woocommerce-MyAccount-navigation" aria-label="<?php esc_attr_e('Account pages', 'woocommerce'); ?>">
    <div class="av-account-profile"><span class="av-account-avatar" aria-hidden="true"><?php echo esc_html(mb_strtoupper($initial)); ?></span><div><strong><?php echo esc_html($name); ?></strong><span><?php echo esc_html(av_brand_name() . ' your account'); ?></span></div></div>
    <ul>
        <?php foreach (wc_get_account_menu_items() as $endpoint => $label): ?>
        <li class="<?php echo esc_attr(wc_get_account_menu_item_classes($endpoint)); ?>"><a href="<?php echo esc_url(wc_get_account_endpoint_url($endpoint)); ?>" <?php echo wc_is_current_account_menu_item($endpoint) ? 'aria-current="page"' : ''; ?>><?php echo av_account_icon($icons[$endpoint] ?? 'user'); ?><span><?php echo esc_html($label); ?></span><?php echo av_account_icon('chevron'); ?></a></li>
        <?php endforeach; ?>
    </ul>
    <?php if (av_site_url('contact')): ?><div class="av-account-help"><span>Bir sorunuz mu var?</span><a href="<?php echo esc_url(av_site_url('contact')); ?>">Get in touch <?php echo av_account_icon('chevron'); ?></a></div><?php endif; ?>
</nav>
<?php do_action('woocommerce_after_account_navigation'); ?>
