<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** @var WC_Cart $cart */
/** @var array $settings */
/** @var float $threshold */
/** @var int $percent */
/** @var float $away */
/** @var WC_Product[] $recos */

$items = $cart ? $cart->get_cart() : array();
?>
<header class="wcasc-header">
	<h3><?php esc_html_e( 'Your Cart', 'wcasc' ); ?></h3>
	<button class="wcasc-close" aria-label="<?php esc_attr_e( 'Close cart', 'wcasc' ); ?>">×</button>
</header>

<?php if ( $threshold > 0 ) : ?>
	<div class="wcasc-free-ship">
		<div class="wcasc-free-ship-text">
			<?php
			printf(
				/* translators: %s: free shipping threshold */
				esc_html__( 'Free shipping on orders over %s', 'wcasc' ),
				wp_kses_post( wc_price( $threshold ) )
			);
			?>
			<?php if ( $away > 0 ) : ?>
				<span class="wcasc-away">
					<?php
					printf(
						/* translators: %s: remaining amount */
						esc_html__( '— you are %s away', 'wcasc' ),
						wp_strip_all_tags( wc_price( $away ) )
					);
					?>
				</span>
			<?php else : ?>
				<span class="wcasc-unlocked"><?php esc_html_e( '— unlocked!', 'wcasc' ); ?></span>
			<?php endif; ?>
		</div>
		<div class="wcasc-progress">
			<div class="wcasc-bar" style="width: <?php echo esc_attr( $percent ); ?>%;"></div>
		</div>
	</div>
<?php endif; ?>

<div class="wcasc-items">
	<?php if ( $items ) : ?>
		<?php foreach ( $items as $key => $line ) :
			$product = $line['data'];

			if ( ! $product || ! $product->exists() ) {
				continue;
			}

			$qty       = (int) $line['quantity'];
			$price     = $product->get_price();
			$regular   = $product->get_regular_price();
			$is_sale   = $product->is_on_sale();

			$variation = '';
			if ( ! empty( $line['variation'] ) ) {
				$variation = wc_get_formatted_variation( $line['variation'], true, false, true );
			}
			?>
			<div class="wcasc-item" data-cart-key="<?php echo esc_attr( $key ); ?>">
				<div class="wcasc-item-thumb">
					<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
				</div>
				<div class="wcasc-item-info">
					<div class="wcasc-item-title"><?php echo esc_html( $product->get_name() ); ?></div>

					<?php if ( $variation ) : ?>
						<div class="wcasc-item-variation"><?php echo wp_kses_post( $variation ); ?></div>
					<?php endif; ?>

					<div class="wcasc-item-prices">
						<?php if ( $is_sale && ! empty( $settings['show_strike'] ) ) : ?>
							<span class="wcasc-price-regular">
								<s><?php echo wp_kses_post( wc_price( $regular ) ); ?></s>
							</span>
						<?php endif; ?>
						<span class="wcasc-price"><?php echo wp_kses_post( wc_price( $price ) ); ?></span>
					</div>

					<div class="wcasc-qty">
						<button class="wcasc-qty-dec" aria-label="<?php esc_attr_e( 'Decrease quantity', 'wcasc' ); ?>">−</button>
						<input type="number" min="0" step="1" class="wcasc-qty-input" value="<?php echo esc_attr( $qty ); ?>">
						<button class="wcasc-qty-inc" aria-label="<?php esc_attr_e( 'Increase quantity', 'wcasc' ); ?>">+</button>
					</div>
				</div>
				<button class="wcasc-remove-item" aria-label="<?php esc_attr_e( 'Remove item', 'wcasc' ); ?>">×</button>
			</div>
		<?php endforeach; ?>
	<?php else : ?>
		<p class="wcasc-empty"><?php esc_html_e( 'Your cart is empty.', 'wcasc' ); ?></p>
	<?php endif; ?>
</div>

<?php if ( ! empty( $settings['show_subtotal'] ) && $cart ) : ?>
	<div class="wcasc-subtotal">
		<span><?php esc_html_e( 'Subtotal', 'wcasc' ); ?></span>
		<strong><?php echo wp_kses_post( $cart->get_cart_subtotal() ); ?></strong>
	</div>
<?php endif; ?>

<div class="wcasc-actions">
	<a class="wcasc-btn-secondary" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
		<?php esc_html_e( 'View Cart', 'wcasc' ); ?>
	</a>
	<a class="wcasc-btn-primary" href="<?php echo esc_url( wc_get_checkout_url() ); ?>">
		<?php esc_html_e( 'Checkout', 'wcasc' ); ?>
	</a>
</div>

<?php if ( $recos ) : ?>
	<div class="wcasc-recos">
		<h4 class="wcasc-recos-title"><?php esc_html_e( "You'll love these", 'wcasc' ); ?></h4>
		<div class="wcasc-recos-track" id="wcasc-recos-track">
			<?php foreach ( $recos as $index => $product ) : ?>
				<div class="wcasc-reco <?php echo $index === 0 ? 'active' : ''; ?>">
					<div class="wcasc-reco-thumb">
						<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
					</div>
					<div class="wcasc-reco-name"><?php echo esc_html( $product->get_name() ); ?></div>
					<button class="wcasc-reco-add" data-wcasc-add="<?php echo esc_attr( $product->get_id() ); ?>">
						<?php esc_html_e( 'Add', 'wcasc' ); ?>
					</button>
				</div>
			<?php endforeach; ?>
		</div>
		<!-- Controls -->
		<div class="wcasc-recos-nav">
			<button class="wcasc-recos-prev" aria-label="<?php esc_attr_e( 'Previous', 'wcasc' ); ?>">‹</button>
			<button class="wcasc-recos-next" aria-label="<?php esc_attr_e( 'Next', 'wcasc' ); ?>">›</button>
		</div>
	</div>
<?php endif; ?>
