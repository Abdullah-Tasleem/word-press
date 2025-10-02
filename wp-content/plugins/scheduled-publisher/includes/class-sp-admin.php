<?php
if (! defined('ABSPATH')) exit;

class SP_Admin
{


	public function __construct()
	{
		add_action('admin_menu',  [$this, 'menu']);
		add_action('admin_enqueue_scripts', [$this, 'assets']);

		// AJAX handlers
		add_action('wp_ajax_sp_fetch_items',         [$this, 'ajax_fetch_items']);
		add_action('wp_ajax_sp_save_schedules',      [$this, 'ajax_save']);
		add_action('wp_ajax_sp_delete_schedule',     [$this, 'ajax_delete']);
		add_action('wp_ajax_sp_run_schedule_now',    [$this, 'ajax_run_now']);
		add_action('wp_ajax_sp_refresh_schedules', [$this, 'ajax_refresh_schedules']);
	}

	/* --------------------------------------------------------------------- */
	/*  Admin Menu & Page                                                    */
	/* --------------------------------------------------------------------- */

	public function menu()
	{
		add_menu_page(
			__('Scheduled Publisher', 'scheduled-publisher'),
			__('Scheduler', 'scheduled-publisher'),
			'manage_options',
			'sp_scheduler',
			[$this, 'render_page'],
			'dashicons-calendar-alt',
			58
		);
	}

	public function render_page()
	{

		/* ------------------------------------------------------------------ */
		/*  Capability check & data                                           */
		/* ------------------------------------------------------------------ */
		if (! current_user_can('manage_options')) {
			return;
		}

		global $wpdb;
		$schedules = $wpdb->get_results("SELECT * FROM " . SP_TABLE . " ORDER BY schedule_time DESC");
		$statuses  = get_post_statuses();

		wp_nonce_field('sp_save_schedules', 'sp_nonce');
?>

		<div class="wrap sp-wrap"><!-- note: extra CSS class -->

			<h1 class="wp-heading-inline"><?php _e('Schedule Publisher', 'scheduled-publisher'); ?></h1>
			<hr class="wp-header-end">

			<!-- ░░ Toolbar ░░ -->
			<div class="sp-toolbar">
				<button class="button sp-add-row">
					<span class="dashicons dashicons-plus-alt"></span>
					<?php _e('Add Row', 'scheduled-publisher'); ?>
				</button>

				<button class="button button-primary" id="sp-save">
					<span class="dashicons dashicons-upload"></span>
					<?php _e('Save schedule(s)', 'scheduled-publisher'); ?>
				</button>
			</div>

			<!-- ░░ Add-rows table ░░ -->
			<table id="sp-schedule-rows"
				class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th class="column-date"><?php _e('Date / Time', 'scheduled-publisher'); ?></th>
						<th><?php _e('Type', 'scheduled-publisher'); ?></th>
						<th><?php _e('Item', 'scheduled-publisher'); ?></th>
						<th><?php _e('Publish Status', 'scheduled-publisher'); ?></th>
						<th class="column-tools"></th>
					</tr>
				</thead>
				<tbody>
					<tr class="sp-row">
						<td>
							<input type="datetime-local" name="schedule_time[]" required>
						</td>

						<td>
							<select name="post_type[]" class="sp-type">
								<option value="post"><?php _e('Post', 'scheduled-publisher'); ?></option>
								<option value="product"><?php _e('Product', 'scheduled-publisher'); ?></option>
							</select>
						</td>

						<td>
							<select name="post_id[]" class="sp-item" required>
								<option value=""><?php _e('Loading…', 'scheduled-publisher'); ?></option>
							</select>
						</td>

						<td>
							<select name="post_status[]" required>
								<?php foreach ($statuses as $k => $v) : ?>
									<option value="<?php echo esc_attr($k); ?>"><?php echo esc_html($v); ?></option>
								<?php endforeach; ?>
							</select>
						</td>

						<td class="column-tools">
							<span class="sp-remove-row dashicons dashicons-trash"
								title="<?php esc_attr_e('Remove row', 'scheduled-publisher'); ?>"></span>
						</td>
					</tr>
				</tbody>
			</table>

			<!-- ░░ Queue / Completed list ░░ -->
			<h2 class="title"><?php _e('Queued / Completed Schedules', 'scheduled-publisher'); ?></h2>

			<table id="sp-schedule-table"
				class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th width="60">ID</th>
						<th><?php _e('Item', 'scheduled-publisher'); ?></th>
						<th width="90"><?php _e('Type', 'scheduled-publisher'); ?></th>
						<th width="160"><?php _e('Scheduled For', 'scheduled-publisher'); ?></th>
						<th width="120"><?php _e('Publish As', 'scheduled-publisher'); ?></th>
						<th width="120"><?php _e('Status', 'scheduled-publisher'); ?></th>
						<th class="column-tools"><?php _e('Actions', 'scheduled-publisher'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php if (! $schedules) : ?>
						<tr>
							<td colspan="7"><?php _e('No schedules found.', 'scheduled-publisher'); ?></td>
						</tr>

						<?php else :
						foreach ($schedules as $row) :
							$post = get_post($row->post_id); ?>
							<tr data-id="<?php echo esc_attr($row->id); ?>">

								<td>#<?php echo esc_html($row->id); ?></td>

								<td>
									<?php echo esc_html($post ? $post->post_title : '—'); ?>
									<?php if ($post) : ?>
										<br><small>ID: <?php echo $post->ID; ?></small>
									<?php endif; ?>
								</td>

								<td><?php echo esc_html(ucfirst($row->post_type)); ?></td>
								<td><?php echo esc_html(get_date_from_gmt($row->schedule_time, 'Y-m-d H:i')); ?></td>
								<td><?php echo esc_html($row->change_status); ?></td>

								<!-- status badge -->
								<td>
									<span class="sp-badge <?php echo $row->completed ? 'completed' : 'queued'; ?>">
										<?php echo $row->completed
											? __('Completed', 'scheduled-publisher')
											: __('Queued',    'scheduled-publisher'); ?>
									</span>
								</td>

								<!-- action buttons -->
								<td class="column-tools">
									<?php if (! $row->completed) : ?>
										<a href="#" class="sp-run-now button button-small">
											<span class="dashicons dashicons-controls-play"></span>
											<?php _e('Run', 'scheduled-publisher'); ?>
										</a>
										<a href="#" class="sp-delete button button-small">
											<span class="dashicons dashicons-trash"></span>
											<?php _e('Delete', 'scheduled-publisher'); ?>
										</a>
									<?php else : ?>
										<a href="#" class="sp-delete button button-small">
											<span class="dashicons dashicons-trash"></span>
											<?php _e('Delete', 'scheduled-publisher'); ?>
										</a>
									<?php endif; ?>
								</td>

							</tr>
					<?php endforeach;
					endif; ?>
				</tbody>
			</table>

		</div><!-- /.wrap.sp-wrap -->

		<?php
	}

	/* --------------------------------------------------------------------- */
	/*  Assets                                                               */
	/* --------------------------------------------------------------------- */

	public function assets($hook)
	{
		if ('toplevel_page_sp_scheduler' !== $hook) return;

		wp_enqueue_style('sp-admin', SP_PLUGIN_URL . 'assets/css/admin.css', [], SP_VERSION);
		wp_enqueue_script('sp-admin', SP_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], SP_VERSION, true);

		wp_localize_script('sp-admin', 'spData', [
			'ajax'   => admin_url('admin-ajax.php'),
			'nonce'  => wp_create_nonce('sp_ajax'),
			'texts'  => [
				'loading' => __('Loading…', 'scheduled-publisher'),
				'error'   => __('Request failed', 'scheduled-publisher'),
			]
		]);
	}

	/* --------------------------------------------------------------------- */
	/*  AJAX: Fetch items for dropdown                                       */
	/* --------------------------------------------------------------------- */

	public function ajax_fetch_items()
	{
		check_ajax_referer('sp_ajax', 'nonce');

		if (! current_user_can('manage_options')) wp_send_json_error();

		$type = sanitize_key($_POST['post_type']);
		$args = [
			'post_type'      => $type,
			'post_status'    => ['publish', 'draft', 'pending'],
			'posts_per_page' => 100,
			'orderby'        => 'title',
			'order'          => 'ASC',
		];
		$q = get_posts($args);
		$data = [];

		foreach ($q as $p) {
			$data[] = [
				'id'    => $p->ID,
				'title' => $p->post_title ?: sprintf(__('(no title) ID %d', 'scheduled-publisher'), $p->ID),
			];
		}
		wp_send_json_success($data);
	}

	public function ajax_refresh_schedules()
	{
		check_ajax_referer('sp_ajax', 'nonce');
		if (! current_user_can('manage_options')) wp_send_json_error();

		global $wpdb;
		$schedules = $wpdb->get_results("SELECT * FROM " . SP_TABLE . " ORDER BY schedule_time ASC");

		ob_start();
		if ($schedules) {
			foreach ($schedules as $row) {
				$post = get_post($row->post_id); ?>
				<tr data-id="<?php echo esc_attr($row->id); ?>">
					<td>#<?php echo esc_html($row->id); ?></td>
					<td>
						<?php echo esc_html($post ? $post->post_title : '—'); ?>
						<?php if ($post) : ?>
							<br><small>ID: <?php echo $post->ID; ?></small>
						<?php endif; ?>
					</td>
					<td><?php echo esc_html(ucfirst($row->post_type)); ?></td>
					<td><?php echo esc_html(get_date_from_gmt($row->schedule_time, 'Y-m-d H:i')); ?></td>
					<td><?php echo esc_html($row->change_status); ?></td>
					<td>
						<span class="sp-badge <?php echo $row->completed ? 'completed' : 'queued'; ?>">
							<?php echo $row->completed ? __('Completed', 'scheduled-publisher') : __('Queued', 'scheduled-publisher'); ?>
						</span>
					</td>
					<td class="column-tools">
						<?php if (! $row->completed) : ?>
							<a href="#" class="sp-run-now button button-small">
								<span class="dashicons dashicons-controls-play"></span>
								<?php _e('Run', 'scheduled-publisher'); ?>
							</a>
							<a href="#" class="sp-delete button button-small">
								<span class="dashicons dashicons-trash"></span>
								<?php _e('Delete', 'scheduled-publisher'); ?>
							</a>
						<?php else : ?>
							—
						<?php endif; ?>
					</td>
				</tr>
<?php }
		} else {
			echo '<tr><td colspan="7">' . __('No schedules found.', 'scheduled-publisher') . '</td></tr>';
		}
		$html = ob_get_clean();

		wp_send_json_success(['html' => $html]);
	}
	/* --------------------------------------------------------------------- */
	/*  AJAX: Save schedules                                                 */
	/* --------------------------------------------------------------------- */

	public function ajax_save()
	{
		check_ajax_referer('sp_ajax', 'nonce');
		if (! current_user_can('manage_options')) wp_send_json_error();

		global $wpdb;

		$times    = (array) $_POST['schedule_time'];
		$types    = (array) $_POST['post_type'];
		$items    = (array) $_POST['post_id'];
		$statuses = (array) $_POST['post_status'];

		$inserted = 0;

		foreach ($times as $i => $time) {
			$time     = sanitize_text_field($time);
			$type     = sanitize_key($types[$i] ?? '');
			$item     = intval($items[$i] ?? 0);
			$status   = sanitize_key($statuses[$i] ?? '');

			if (! $time || ! $type || ! $item || ! $status) continue;

			$gmt = get_gmt_from_date($time);

			$wpdb->insert(
				SP_TABLE,
				[
					'post_id'       => $item,
					'post_type'     => $type,
					'change_status' => $status,
					'schedule_time' => $gmt,
					'completed'     => 0,
				],
				['%d', '%s', '%s', '%s', '%d']
			);

			$id = $wpdb->insert_id; // naye row ka ID

			wp_schedule_single_event(time() + 80, 'sp_auto_run_schedule', [$id]);

			$inserted++;
		}
		wp_send_json_success([
			'message'  => sprintf(__('%d schedule(s) saved', 'scheduled-publisher'), $inserted),
		]);
	}


	/* --------------------------------------------------------------------- */
	/*  AJAX: Delete schedule                                                */
	/* --------------------------------------------------------------------- */

	public function ajax_delete()
	{
		check_ajax_referer('sp_ajax', 'nonce');
		if (! current_user_can('manage_options')) wp_send_json_error();

		global $wpdb;
		$id = intval($_POST['id']);
		$wpdb->delete(SP_TABLE, ['id' => $id], ['%d']);
		wp_send_json_success();
	}

	/* --------------------------------------------------------------------- */
	/*  AJAX: Run now                                                        */
	/* --------------------------------------------------------------------- */

	public function ajax_run_now()
	{
		check_ajax_referer('sp_ajax', 'nonce');
		if (! current_user_can('manage_options')) wp_send_json_error();

		global $wpdb;
		$id = intval($_POST['id']);
		$row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . SP_TABLE . " WHERE id = %d", $id));
		if (! $row) wp_send_json_error();

		if ($row->completed) wp_send_json_error(__('Already completed.', 'scheduled-publisher'));

		// Run same logic as cron
		$post_update = [
			'ID'          => $row->post_id,
			'post_status' => $row->change_status,
			'post_date'   => current_time('mysql'),
		];
		wp_update_post($post_update);

		$wpdb->update(SP_TABLE, ['completed' => 1], ['id' => $id]);
		wp_send_json_success();
	}
}
// Auto run scheduled status change after 2 minutes
add_action('sp_auto_run_schedule', function ($id) {
	global $wpdb;

	$row = $wpdb->get_row($wpdb->prepare(
		"SELECT * FROM " . SP_TABLE . " WHERE id = %d",
		$id
	));

	if (! $row || $row->completed) return;

	// Post/Product status update
	$post_update = [
		'ID'          => $row->post_id,
		'post_status' => $row->change_status,
		'post_date'   => current_time('mysql'),
	];
	wp_update_post($post_update);

	// Mark as completed
	$wpdb->update(
		SP_TABLE,
		['completed' => 1],
		['id' => $id],
		['%d'],
		['%d']
	);
});
