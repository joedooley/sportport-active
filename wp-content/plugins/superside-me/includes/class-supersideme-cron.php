<?php
/**
 * SuperSide Me Cron class.
 *
 * @package   SuperSideMe
 * @author    Robin Cornett <hello@robincornett.com>
 * @copyright 2016 Robin Cornett
 * @license   GPL-2.0+
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * SuperSide_Me_Cron Class
 *
 * This class handles scheduled events
 *
 * @since 2.0.0
 */
class SuperSide_Me_Cron {
	/**
	 * Get things going
	 *
	 * @since 2.0.0
	 */
	public function __construct() {
		add_filter( 'cron_schedules', array( $this, 'add_schedules' ) );
		add_action( 'wp', array( $this, 'schedule_events' ) );
		register_deactivation_hook( SUPERSIDEME_PLUGIN_FILE, array( $this, 'deactivate' ) );
	}

	/**
	 * Registers new cron schedules
	 *
	 * @since 2.0.0
	 *
	 * @param array $schedules
	 * @return array
	 */
	public function add_schedules( $schedules = array() ) {
		// Adds once weekly to the existing schedules.
		$schedules['weekly'] = array(
			'interval' => 604800,
			'display'  => __( 'Once Weekly', 'superside-me' )
		);

		return $schedules;
	}

	/**
	 * Schedules our events
	 *
	 * @access public
	 * @since 2.0.0
	 * @return void
	 */
	public function schedule_events() {
		$this->weekly_events();
	}

	/**
	 * Schedule weekly events
	 *
	 * @access private
	 * @since 2.0.0
	 * @return void
	 */
	private function weekly_events() {
		if ( ! wp_next_scheduled( 'supersideme_weekly_events' ) ) {
			wp_schedule_event( current_time( 'timestamp' ), 'weekly', 'supersideme_weekly_events' );
		}
	}

	/**
	 * If the plugin is deactivated, remove the cron job.
	 * @since 2.0.0
	 */
	public function deactivate() {
		$timestamp = wp_next_scheduled( 'supersideme_weekly_events' );
		wp_unschedule_event( $timestamp, 'supersideme_weekly_events' );
	}
}
