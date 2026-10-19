<?php get_header(); while (have_posts()): the_post(); ?>
<?php if (av_is_checkout()): ?>
<div class="av-checkout-page"><h1 class="screen-reader-text"><?php echo esc_html(av_site_text('checkout_title')); ?></h1><?php the_content(); ?></div>
<?php elseif (is_account_page()): $account_intro = av_account_intro(); ?>
<div class="av-account-page"><div class="av-account-intro"><div><p class="av-account-kicker"><?php echo esc_html(av_account_kicker()); ?></p><h1><?php echo esc_html($account_intro['title']); ?></h1><p class="av-account-description"><?php echo esc_html($account_intro['description']); ?></p></div><a class="av-account-return" href="<?php echo esc_url(av_site_url('product')); ?>"><?php echo av_account_icon('arrow'); ?>Continue shopping</a></div><?php the_content(); ?></div>
<?php elseif (has_block('aurivita/av-faq') && get_the_ID() !== absint(av_site('contact_page'))): ?>
<header class="av-page av-page-intro"><?php av_breadcrumb(); ?><p class="av-ui-kicker"><?php echo esc_html(av_brand_name()); ?> · YARDIM</p><h1><?php the_title(); ?></h1><p>Your questions about our products and shopping.</p></header>
<?php the_content(); ?>
<noscript><style>[data-av-content="av-faq"] .cc-faq__answer-wrap{grid-template-rows:1fr!important}[data-av-content="av-faq"] .cc-faq__answer{opacity:1!important;transform:none!important}</style></noscript>
<?php elseif (is_cart()): ?>
<div class="av-page av-commerce-page"><header class="av-page-heading"><p class="av-ui-kicker"><?php echo esc_html(av_brand_name()); ?> · SHOPPING</p><h1><?php the_title(); ?></h1></header><?php the_content(); ?></div>
<?php else: ?>
<section class="av-page av-document"><?php av_breadcrumb(); ?><header class="av-page-heading"><p class="av-ui-kicker"><?php echo esc_html(av_brand_name()); ?> · STORE INFORMATION</p><h1><?php the_title(); ?></h1><?php if (has_excerpt()): ?><p><?php echo esc_html(get_the_excerpt()); ?></p><?php endif; ?></header><div class="av-document-layout"><article class="av-document-content"><?php the_content(); ?>
<?php if (get_the_ID() === absint(av_site('contact_page'))): ?>
<?php if (av_public_contact_email()): ?><p><a href="<?php echo esc_url('mailto:' . av_public_contact_email()); ?>"><?php echo esc_html(av_public_contact_email()); ?></a></p><?php endif; ?>
<?php if (isset($_GET['contact']) && is_string($_GET['contact'])): ?><p class="notice" role="status"><?php echo $_GET['contact'] === 'sent' ? 'Your message has been received. Thank you.' : 'Your message could not be sent. Check your email address and message.'; ?></p><?php endif; ?>
<form class="av-contact" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post"><input type="hidden" name="action" value="av_contact"><?php wp_nonce_field('av_contact','av_nonce'); ?><label>Your name<input name="name" autocomplete="name" required maxlength="120"></label><label>Email<input type="email" name="email" autocomplete="email" required></label><label>Your message<textarea name="message" required rows="6" maxlength="10000"></textarea></label><div hidden><label>Website<input name="website" tabindex="-1" autocomplete="off"></label></div><button class="local-primary" type="submit">Send</button></form>
<?php endif; ?></article><?php av_info_navigation(); ?></div></section><?php endif; endwhile; get_footer(); ?>
