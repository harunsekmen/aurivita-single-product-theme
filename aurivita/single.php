<?php
defined('ABSPATH') || exit;
require_once __DIR__ . '/inc/article.php';

get_header();
while (have_posts()):
    the_post();
    $article_post = get_post();
    $article = av_article_prepare_content(
        str_replace(']]>', ']]&gt;', (string) apply_filters('the_content', get_the_content())),
        (string) get_the_title()
    );
    $excerpt = av_article_excerpt($article_post);
    $author = get_userdata((int) $article_post->post_author);
    $blog_url = av_site_url('blog');
    $product = function_exists('av_main_product') ? av_main_product() : null;
    $product_url = $product && $product->get_status() === 'publish' ? $product->get_permalink() : '';
    ?>
    <article <?php post_class('av-page av-editorial'); ?>>
        <?php if (function_exists('av_breadcrumb')) av_breadcrumb(); ?>
        <header class="av-article-header">
            <p class="av-eyebrow"><?php echo esc_html(av_brand_name()); ?> · <?php esc_html_e('Articles', 'aurivita'); ?></p>
            <h1 class="av-article-title"><?php the_title(); ?></h1>
            <div class="av-article-meta">
                <span><?php esc_html_e('Published:', 'aurivita'); ?> <time datetime="<?php echo esc_attr(get_the_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_date()); ?></time></span>
                <?php if (get_post_modified_time('U') > get_post_time('U')): ?>
                    <span><?php esc_html_e('Updated:', 'aurivita'); ?> <time datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_modified_date()); ?></time></span>
                <?php endif; ?>
                <?php if ($author && trim($author->display_name) !== ''): ?>
                    <span><?php esc_html_e('Yazar:', 'aurivita'); ?> <?php echo esc_html($author->display_name); ?></span>
                <?php endif; ?>
            </div>
            <?php if ($excerpt !== ''): ?>
                <div class="av-article-lead"><?php echo wpautop(wp_kses_post($excerpt)); ?></div>
            <?php endif; ?>
        </header>
        <?php if (has_post_thumbnail()): ?>
            <figure class="av-article-image">
                <?php the_post_thumbnail('large', ['class' => 'av-article-featured-image', 'sizes' => '(max-width: 767px) calc(100vw - 40px), 880px', 'loading' => 'eager', 'decoding' => 'async']); ?>
            </figure>
        <?php endif; ?>
        <?php if ($article['headings']): ?>
            <nav class="av-article-toc" aria-label="<?php esc_attr_e('On this page', 'aurivita'); ?>">
                <p class="av-article-toc-title"><?php esc_html_e('On this page', 'aurivita'); ?></p>
                <ol>
                    <?php foreach ($article['headings'] as $heading): ?>
                        <li<?php if ($heading['level'] === 3) echo ' class="av-article-toc-subitem"'; ?>>
                            <a href="<?php echo esc_url('#' . rawurlencode($heading['id'])); ?>"><?php echo esc_html($heading['label']); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>
        <div class="av-article-content">
            <?php echo $article['html']; ?>
            <?php wp_link_pages(['before' => '<nav class="av-article-pagination" aria-label="' . esc_attr__('Article pages', 'aurivita') . '">', 'after' => '</nav>']); ?>
        </div>
        <?php if ($blog_url !== '' || $product_url !== ''): ?>
            <nav class="av-article-related" aria-label="<?php esc_attr_e('Related links', 'aurivita'); ?>">
                <?php if ($blog_url !== ''): ?><a href="<?php echo esc_url($blog_url); ?>"><?php esc_html_e('All articles', 'aurivita'); ?></a><?php endif; ?>
                <?php if ($product_url !== ''): ?><a href="<?php echo esc_url($product_url); ?>"><?php esc_html_e('Explore the product', 'aurivita'); ?></a><?php endif; ?>
            </nav>
        <?php endif; ?>
    </article>
<?php endwhile;
get_footer();
