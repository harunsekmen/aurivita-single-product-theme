<?php
/**
 * Aurivita My Account dashboard.
 *
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

defined( 'ABSPATH' ) || exit;

$av_account_user = wp_get_current_user();
$av_account_name = trim( (string) $av_account_user->first_name );

if ( '' === $av_account_name ) {
	$av_account_name = $av_account_user->display_name;
}

$av_account_cards = array(
	array(
		'endpoint'    => 'orders',
		'icon'        => 'orders',
		'title'       => __( 'Orders', 'aurivita' ),
		'description' => __( 'Review your order status and details.', 'aurivita' ),
	),
	array(
		'endpoint'    => 'edit-address',
		'icon'        => 'address',
		'title'       => __( 'Adresler', 'aurivita' ),
		'description' => __( 'Update your billing and shipping addresses.', 'aurivita' ),
	),
	array(
		'endpoint'    => 'edit-account',
		'icon'        => 'user',
		'title'       => __( 'Hesap bilgileri', 'aurivita' ),
		'description' => __( 'Manage your name, email address and password.', 'aurivita' ),
	),
);

$av_recent_orders = $av_account_user->exists() ? wc_get_orders(
	array(
		'customer_id' => $av_account_user->ID,
		'limit'       => 3,
		'orderby'     => 'date',
		'order'       => 'DESC',
		'return'      => 'objects',
	)
) : array();

$av_order_columns = array(
	'order-number'  => __( 'Order', 'aurivita' ),
	'order-date'    => __( 'Tarih', 'aurivita' ),
	'order-status'  => __( 'Durum', 'aurivita' ),
	'order-total'   => __( 'Total', 'aurivita' ),
	'order-actions' => __( 'Actions', 'aurivita' ),
);
?>

<div class="av-account-welcome">
	<h2>
		<?php
		/* translators: %s: the customer's first name or display name. */
		echo esc_html( sprintf( __( 'Merhaba, %s', 'aurivita' ), $av_account_name ) );
		?>
	</h2>
	<p><?php esc_html_e( 'Track your orders and manage your account details with ease.', 'aurivita' ); ?></p>
</div>

<div class="av-account-cards">
	<?php foreach ( $av_account_cards as $av_account_card ) : ?>
		<a class="av-account-card" href="<?php echo esc_url( wc_get_account_endpoint_url( $av_account_card['endpoint'] ) ); ?>">
			<span class="av-account-card__icon" aria-hidden="true"><?php echo av_account_icon( $av_account_card['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The theme helper returns safe inline SVG. ?></span>
			<div class="av-account-card__body">
				<h3><?php echo esc_html( $av_account_card['title'] ); ?></h3>
				<p><?php echo esc_html( $av_account_card['description'] ); ?></p>
			</div>
			<span class="av-account-card__arrow" aria-hidden="true"><?php echo av_account_icon( 'chevron' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The theme helper returns safe inline SVG. ?></span>
		</a>
	<?php endforeach; ?>
</div>

<?php if ( $av_recent_orders ) : ?>
	<section class="av-account-orders" aria-labelledby="av-account-orders-title">
		<h2 id="av-account-orders-title"><?php esc_html_e( 'Recent orders', 'aurivita' ); ?></h2>
		<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr>
					<?php foreach ( $av_order_columns as $av_column_id => $av_column_name ) : ?>
						<th scope="col" class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $av_column_id ); ?>"><span class="nobr"><?php echo esc_html( $av_column_name ); ?></span></th>
					<?php endforeach; ?>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $av_recent_orders as $av_order ) : ?>
					<?php
					$av_order_date = $av_order->get_date_created();
					$av_item_count = $av_order->get_item_count() - $av_order->get_item_count_refunded();
					/* translators: %s: the order number. */
					$av_view_label = sprintf( __( 'View order %s', 'aurivita' ), $av_order->get_order_number() );
					?>
					<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $av_order->get_status() ); ?> order">
						<th scope="row" class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-number" data-title="<?php echo esc_attr( $av_order_columns['order-number'] ); ?>">
							<a href="<?php echo esc_url( $av_order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( $av_view_label ); ?>"><?php echo esc_html( '#' . $av_order->get_order_number() ); ?></a>
						</th>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-date" data-title="<?php echo esc_attr( $av_order_columns['order-date'] ); ?>">
							<?php if ( $av_order_date ) : ?>
								<time datetime="<?php echo esc_attr( $av_order_date->date( 'c' ) ); ?>"><?php echo esc_html( wc_format_datetime( $av_order_date ) ); ?></time>
							<?php else : ?>
								&mdash;
							<?php endif; ?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-status" data-title="<?php echo esc_attr( $av_order_columns['order-status'] ); ?>"><?php echo esc_html( wc_get_order_status_name( $av_order->get_status() ) ); ?></td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-total" data-title="<?php echo esc_attr( $av_order_columns['order-total'] ); ?>">
							<?php
							/* translators: 1: formatted order total, 2: number of items after refunds. */
							echo wp_kses_post( sprintf( __( '%1$s · %2$s items', 'aurivita' ), $av_order->get_formatted_order_total(), number_format_i18n( $av_item_count ) ) );
							?>
						</td>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-order-actions" data-title="<?php echo esc_attr( $av_order_columns['order-actions'] ); ?>">
							<a class="woocommerce-button button view av-account-button" href="<?php echo esc_url( $av_order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( $av_view_label ); ?>"><?php esc_html_e( 'View', 'aurivita' ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</section>
<?php else : ?>
	<section class="av-account-order-section" aria-labelledby="av-account-order-section-title">
		<h2 id="av-account-order-section-title"><?php esc_html_e( 'Your orders', 'aurivita' ); ?></h2>
		<div class="av-account-empty">
			<span class="av-account-card__icon" aria-hidden="true"><?php echo av_account_icon( 'orders' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- The theme helper returns safe inline SVG. ?></span>
			<div class="av-account-empty__copy">
				<h3><?php esc_html_e( 'No orders yet', 'aurivita' ); ?></h3>
				<p><?php esc_html_e( 'Discover products for you. Your first order details will appear here.', 'aurivita' ); ?></p>
			</div>
			<a class="woocommerce-Button wc-forward button av-account-button" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>"><?php esc_html_e( 'Explore products', 'aurivita' ); ?></a>
		</div>
	</section>
<?php endif; ?>

<?php
do_action( 'woocommerce_account_dashboard' );
do_action( 'woocommerce_before_my_account' );
do_action( 'woocommerce_after_my_account' );
