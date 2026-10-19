<?php
defined('ABSPATH') || exit;
?>
<div class="page-wrapper av-checkout-wrapper">
<header class="av-checkout-header"><div class="av-checkout-header__inner"><a class="av-checkout-brand" aria-label="<?php echo esc_attr(av_brand_name() . ' · Home'); ?>" href="<?php echo esc_url(av_site_url('home')); ?>"><?php echo av_brand_logo('checkout'); ?></a><a class="av-checkout-return" href="<?php echo esc_url(wc_get_cart_url()); ?>" aria-label="<?php esc_attr_e('Back to cart', 'aurivita'); ?>"><svg aria-hidden="true" width="24" height="24" viewBox="0 0 20 20" fill="none"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.2" d="M3.392 6.875h13.216v8.016c0 .567-.224 1.112-.624 1.513-.4.402-.941.627-1.506.627H5.522a2.13 2.13 0 0 1-1.506-.627 2.15 2.15 0 0 1-.624-1.513zM8.818 2.969h2.333c.618 0 1.211.247 1.649.686a2.35 2.35 0 0 1 .683 1.658v1.562H6.486V5.313c0-.622.246-1.218.683-1.658a2.33 2.33 0 0 1 1.65-.686"/></svg></a></div></header>
<main id="MainContent" class="av-checkout-main">
