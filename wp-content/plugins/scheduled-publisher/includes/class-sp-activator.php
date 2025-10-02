<?php
if (! defined('ABSPATH')) exit;

class SP_Activator
{

	// custom DB table
	public static function activate()
	{
		global $wpdb;

		/* 1. create / upgrade table … (unchanged) */
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta($sql);

		/* 2. make sure a “minute” schedule exists right now */
		add_filter('cron_schedules', function ($schedules) {
			$schedules['minute'] = [
				'interval' => 60,
				'display'  => __('Every Minute', 'scheduled-publisher'),
			];
			return $schedules;
		});

		/* 3. finally register the event */
		if (! wp_next_scheduled('sp_process_schedules')) {
			// first run one minute from now
			wp_schedule_event(time() + 60, 'minute', 'sp_process_schedules');
		}
	}

	public static function deactivate()
	{
		wp_clear_scheduled_hook('sp_process_schedules');
	}
}
