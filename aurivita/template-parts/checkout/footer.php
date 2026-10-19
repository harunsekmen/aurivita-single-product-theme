<?php defined('ABSPATH') || exit; ?>
<footer class="av-checkout-footer"><nav aria-label="Store policies"><?php echo av_site_menu('checkout_policies'); ?></nav><span><?php echo esc_html(av_site_text('footer_copyright') ?: '© ' . wp_date('Y') . ' ' . av_brand_name()); ?></span></footer>
</div>
<?php av_checkout_policy_dialog(); ?>
