<?php defined('ABSPATH') || exit; ?>
<form role="search" method="get" class="av-search-form" action="<?php echo esc_url(home_url('/')); ?>">
    <label><span class="screen-reader-text">Sitede ara</span><input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="What are you looking for?" required></label>
    <button type="submit">Search <?php echo av_account_icon('chevron'); ?></button>
</form>
