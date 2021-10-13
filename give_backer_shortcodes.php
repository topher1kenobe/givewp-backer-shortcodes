<?php
/*
Plugin Name: GiveWP Backer Shortcodes
Description: Creates shortcodes to render totals related to ongoing gifts. Timeframe totals and number of backers
Author: Topher
Author URI: http://topher1kenobe.com
Version: 1.1
License: GPL
*/

/**
 * GiveWP Backer Shortcodes
 *
 * @since 1.0.0
 *
 * @package givewp-backer-shortcodes
 */

/**
 * Instantiate the GiveWP_Backer_Shortcodes instance
 *
 * @since GiveWP_Backer_Shortcodes 1.0
 */
class GiveWP_Backer_Shortcodes {

	/**
	 * Instance handle
	 *
	 * @static
	 * @since 1.2
	 * @var string
	 */
	private static $instance = null;

	/**
	 * Constructor, actually contains nothing
	 *
	 * @access public
	 * @return void
	 */
	public function __construct() {
	}

	/**
	 * Instance initiator, runs setup etc.
	 *
	 * @static
	 * @access public
	 * @return self
	 */
	public static function instance() {
		if ( ! is_a( self::$instance, __CLASS__ ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Runs things that would normally be in __construct
	 *
	 * @access private
	 * @return void
	 */
	public function setup() {

		add_shortcode( 'givewp_periodic_sum', array( $this, 'get_amount' ) );
		add_shortcode( 'givewp_periodic_subscribers', array( $this, 'get_subscribers' ) );

	}

	/**
	* Get total dollars committed during a period
	*
	* @param  array $input
	* @access public
	* @since  1.0
	* @return int financial amount
	*/
	public function get_amount( $input ) {

		global $wpdb;

		$period = 'month';

		if ( ! empty( $input['period'] ) && in_array( $input['period'], Give_Recurring::periods() ) ) {
			$period = $input['period'];
		}

		$amount = $wpdb->get_results(
			$wpdb->prepare(
				'
					SELECT
					ROUND(SUM(`recurring_amount`),2) as amount
					FROM
					`' . $wpdb->prefix . 'give_subscriptions`
					WHERE
					`period` = %s
					AND
					`status` = "active"
				',
				$period
			)
		);

		return  '$' .  number_format( $amount[0]->amount, 2, '.', ',' ) . "\n";

	}

	/**
	* Get total subcribers during a period
	*
	* @param  array $input
	* @access public
	* @since  1.0
	* @return int total subscribers
	*/
	public function get_subscribers( $input ) {

		global $wpdb;

		$period = 'month';

		if ( ! empty( $input['period'] ) && in_array( $input['period'], Give_Recurring::periods() ) ) {
			$period = $input['period'];
		}

		$subscribers = $wpdb->get_results(
			$wpdb->prepare(
				'
					SELECT
					COUNT( * ) as subscribers
					FROM
					`' . $wpdb->prefix . 'give_subscriptions`
					WHERE
					`period` = %s
					AND
					`status` = "active"
				',
				$period
			)
		);

		return number_format( $subscribers[0]->subscribers, 0, '', ',' );

	}

}
add_action( 'plugins_loaded', array( 'GiveWP_Backer_Shortcodes', 'instance' ) );
