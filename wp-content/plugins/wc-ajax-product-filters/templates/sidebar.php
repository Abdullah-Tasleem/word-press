<?php
/* variables from parent scope: $enabled, $order */

foreach ( $order as $slug ) :
	if ( ! in_array( $slug, $enabled ) ) continue;

	switch ( $slug ):

	/*****************  GENDER  *****************/
	case 'gender':
		$terms = get_terms( array(
			'taxonomy'   => 'pa_gender',
			'hide_empty' => true,
		) );
		if ( empty( $terms ) || is_wp_error( $terms ) ) break; ?>
		<div class="wc-apf-block" data-filter="gender">
			<h4><?php _e( 'Gender', 'wc-apf' ); ?></h4>
			<ul>
				<?php foreach ( $terms as $t ) : ?>
					<li>
						<label>
							<input type="checkbox" value="<?php echo esc_attr( $t->slug ); ?>">
							<span><?php echo esc_html( $t->name ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php break;

	/*****************  PRICE  *****************/
	case 'price': ?>
		<div class="wc-apf-block" data-filter="price">
			<h4><?php _e( 'Price', 'wc-apf' ); ?></h4>
			<div class="price-fields" style="padding:10px 0;">
				<div style="margin-bottom:12px;">
					<input type="range" id="wc-apf-min-price" min="0" max="10000" step="1" value="0" style="width:45%;">
					<input type="range" id="wc-apf-max-price" min="0" max="10000" step="1" value="10000" style="width:45%;">
				</div>
				<div style="display:flex;justify-content:space-between;align-items:center;">
					<span><?php _e('Min:', 'wc-apf'); ?> <span id="wc-apf-min-price-val">0</span></span>
					<span><?php _e('Max:', 'wc-apf'); ?> <span id="wc-apf-max-price-val">10000</span></span>
				</div>
				<button class="button button-small wc-apf-price-go" style="margin-top:10px;"><?php _e( 'Go', 'wc-apf' ); ?></button>
			</div>
			<script>
			document.addEventListener('DOMContentLoaded', function() {
				var minRange = document.getElementById('wc-apf-min-price');
				var maxRange = document.getElementById('wc-apf-max-price');
				var minVal = document.getElementById('wc-apf-min-price-val');
				var maxVal = document.getElementById('wc-apf-max-price-val');
				if(minRange && minVal) {
					minRange.addEventListener('input', function() {
						minVal.textContent = minRange.value;
						if(parseInt(minRange.value) > parseInt(maxRange.value)) {
							maxRange.value = minRange.value;
							maxVal.textContent = maxRange.value;
						}
					});
				}
				if(maxRange && maxVal) {
					maxRange.addEventListener('input', function() {
						maxVal.textContent = maxRange.value;
						if(parseInt(maxRange.value) < parseInt(minRange.value)) {
							minRange.value = maxRange.value;
							minVal.textContent = minRange.value;
						}
					});
				}
			});
			</script>
		</div>
	<?php break;

	/*****************  SIZE  *****************/
	case 'size':
		$terms = get_terms( array(
			'taxonomy'   => 'pa_size',
			'hide_empty' => true,
		) );
		if ( empty( $terms ) || is_wp_error( $terms ) ) break; ?>
		<div class="wc-apf-block" data-filter="size">
			<h4><?php _e( 'Size', 'wc-apf' ); ?></h4>
			<ul>
				<?php foreach ( $terms as $t ) : ?>
					<li>
						<label>
							<input type="checkbox" value="<?php echo esc_attr( $t->slug ); ?>">
							<span><?php echo esc_html( $t->name ); ?></span>
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php break;

	/*****************  COLOR  *****************/
	case 'color':
		$terms = get_terms( array(
			'taxonomy'   => 'pa_color',
			'hide_empty' => true,
		) );
		if ( empty( $terms ) || is_wp_error( $terms ) ) break; ?>
		<div class="wc-apf-block" data-filter="color">
			<h4><?php _e( 'Color', 'wc-apf' ); ?></h4>
			<ul class="colors">
				<?php foreach ( $terms as $t ) : ?>
					<li>
						<label style="background:<?php echo esc_attr( $t->slug ); ?>;">
							<input type="checkbox" value="<?php echo esc_attr( $t->slug ); ?>">
						</label>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	<?php break;

	endswitch;

endforeach;