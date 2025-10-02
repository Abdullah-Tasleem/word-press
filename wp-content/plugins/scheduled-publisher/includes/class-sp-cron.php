<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class SP_Cron {

	public function __construct() {

		/* 1. keep the custom interval & the processor */
		add_filter( 'cron_schedules',     [ $this, 'add_custom_intervals' ] );
		add_action( 'sp_process_schedules', [ $this, 'process' ] );

		/* 2. 👉 NEW : make sure the recurring job is always there */
		if ( ! wp_next_scheduled( 'sp_process_schedules' ) ) {
			// first run one minute from now
			wp_schedule_event( time() + MINUTE_IN_SECONDS, 'minute', 'sp_process_schedules' );
		}
	}

	/**
	 * Adds custom cron intervals.
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function add_custom_intervals( $schedules ) {
		$schedules['minute'] = [
			'interval' => 60,
			'display'  => __( 'Every Minute', 'scheduled-publisher' ),
		];
		return $schedules;
	}

	// Cron callback
	public function process()
	{
		global $wpdb;

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM " . SP_TABLE . " 
				 WHERE schedule_time <= %s AND completed = 0",
				current_time('mysql', 1)
			)
		);

		foreach ($rows as $row) {
			// Skip if item doesn’t exist anymore
			$post = get_post($row->post_id);
			if (! $post) {
				$wpdb->update(SP_TABLE, ['completed' => 1], ['id' => $row->id]);
				continue;
			}

			// Update status
			$post_update = [
				'ID'          => $row->post_id,
				'post_status' => $row->change_status,
				'post_date'   => current_time('mysql'),
			];
			wp_update_post($post_update);

			// mark done
			$wpdb->update(SP_TABLE, ['completed' => 1], ['id' => $row->id]);
		}
	}
}