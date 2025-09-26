<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var WC_Product $product */
$pid = $product->get_id();
?>
<article class="wcasc-card" data-product-id="<?php echo esc_attr( $pid ); ?>">
	<a href="<?php echo esc_url( get_permalink( $pid ) ); ?>" class="wcasc-thumb"><?php echo $product->get_image( 'woocommerce_thumbnail' ); ?></a>
	<div class="wcasc-meta">
		<div class="wcasc-name"><?php echo esc_html( $product->get_name() ); ?></div>
		<div class="wcasc-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
	</div>
	<button class="wcasc-btn-add" data-wcasc-add="<?php echo esc_attr( $pid ); ?>"><?php esc_html_e( 'Add', 'wcasc' ); ?></button>
</article>