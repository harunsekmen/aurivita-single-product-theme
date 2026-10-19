<?php
get_header();
$listing_title = is_search() ? 'Search results' : (is_home() ? get_the_title((int) get_option('page_for_posts')) : get_the_archive_title());
if (!$listing_title) $listing_title = 'Guide';
$listing_image_sizes = $wp_query->post_count === 1 ? '(min-width:900px) 448px, (min-width:601px) 40vw, calc(100vw - 40px)' : '(min-width:900px) 360px, (min-width:601px) 45vw, calc(100vw - 40px)';
?>
<section class="av-listing av-page">
    <?php av_breadcrumb(); ?>
    <header class="av-page-heading">
        <p class="av-ui-kicker"><?php echo esc_html(av_brand_name()); ?> · <?php echo is_search() ? 'ARAMA' : 'REHBER'; ?></p>
        <h1><?php echo esc_html($listing_title); ?></h1>
        <p><?php echo is_search() ? esc_html(sprintf('Results for “%s”.', get_search_query())) : 'Articles about products, choices and our store.'; ?></p>
    </header>
    <?php if (is_search()) get_search_form(); ?>
    <?php if (have_posts()): ?>
        <div class="av-listing-grid">
            <?php while (have_posts()): the_post(); ?>
                <article class="av-listing-card">
                    <?php if (has_post_thumbnail()): ?><a class="av-listing-card__media" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail('large', ['sizes'=>$listing_image_sizes, 'loading'=>'lazy', 'alt'=>'']); ?></a><?php endif; ?>
                    <div class="av-listing-card__body">
                        <p class="av-ui-kicker"><?php echo get_post_type() === 'product' ? 'PRODUCT' : (get_post_type() === 'page' ? 'STORE INFORMATION' : 'REHBER'); ?></p>
                        <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
                        <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(get_the_excerpt()), 32, '…')); ?></p>
                        <a class="av-listing-card__link" href="<?php the_permalink(); ?>">Explore <?php echo av_account_icon('chevron'); ?></a>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php the_posts_pagination(['prev_text'=>'Previous', 'next_text'=>'Sonraki']); ?>
    <?php else: ?>
        <div class="av-empty-state"><span class="av-ui-icon" aria-hidden="true"><?php echo av_account_icon('dashboard'); ?></span><h2>No results found</h2><p>Try another search or browse our products.</p><?php if (!is_search()) get_search_form(); ?><a class="local-primary" href="<?php echo esc_url(av_site_url('shop')); ?>">Explore products</a></div>
    <?php endif; ?>
</section>
<?php get_footer(); ?>
