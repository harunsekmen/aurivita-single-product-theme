<?php get_header(); ?>
<section class="av-page av-not-found">
    <p class="av-ui-kicker"><?php echo esc_html(av_brand_name()); ?> · 404</p>
    <span class="av-ui-icon" aria-hidden="true"><?php echo av_account_icon('arrow'); ?></span>
    <h1>We could not find this page.</h1>
    <p>The link may have changed. Find what you need or return to the shop.</p>
    <?php get_search_form(); ?>
    <div class="av-ui-actions"><a class="local-primary" href="<?php echo esc_url(home_url('/')); ?>">Back to home</a><a href="<?php echo esc_url(av_site_url('shop')); ?>">Explore products <?php echo av_account_icon('chevron'); ?></a></div>
</section>
<?php get_footer(); ?>
