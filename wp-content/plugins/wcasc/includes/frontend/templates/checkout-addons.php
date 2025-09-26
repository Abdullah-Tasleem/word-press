<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/** @var string $heading */
/** @var WC_Product[] $products */
?>
<section class="wcasc-checkout-addons">
	<h3 class="wcasc-title"><?php echo esc_html( $heading ); ?></h3>
	<div class="wcasc-grid">
		<?php foreach ( $products as $product ) : ?>
			<?php wcasc_get_template( 'addon-product-card.php', array( 'product' => $product ) ); ?>
		<?php endforeach; ?>
	</div>
</section>