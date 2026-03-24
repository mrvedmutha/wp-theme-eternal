<?php
/**
 * WP_Rig\WP_Rig\Newsletter\Component class
 *
 * Handles newsletter subscription: CPT registration, REST API endpoint,
 * admin notification email, and CSV export.
 *
 * @package wp_rig
 *
 * @js-file  assets/js/newsletter.js   Async form submit + success/error UI
 */

namespace WP_Rig\WP_Rig\Newsletter;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use WP_Rig\WP_Rig\Component_Interface;

use function add_action;
use function register_post_type;
use function register_rest_route;
use function sanitize_email;
use function is_email;
use function get_posts;
use function wp_insert_post;
use function update_post_meta;
use function wp_send_json_error;
use function wp_send_json_success;
use function get_option;
use function wp_mail;
use function esc_html;
use function current_time;

/**
 * Newsletter subscription component.
 *
 * Registers:
 * - `newsletter_subscriber` private CPT
 * - REST endpoint: POST /wp-json/eternal/v1/newsletter/subscribe
 * - WP Admin list table columns + CSV bulk export
 * - JS asset enqueue for the subscription form
 */
class Component implements Component_Interface {

	const CPT_SLUG       = 'newsletter_subscriber';
	const REST_NAMESPACE = 'eternal/v1';
	const REST_ROUTE     = '/newsletter/subscribe';

	/**
	 * Gets the unique identifier for the theme component.
	 *
	 * @return string Component slug.
	 */
	public function get_slug(): string {
		return 'newsletter';
	}

	/**
	 * Adds the action and filter hooks to integrate with WordPress.
	 */
	public function initialize() {
		add_action( 'init', array( $this, 'register_cpt' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_route' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// Admin list table columns.
		add_filter( 'manage_' . self::CPT_SLUG . '_posts_columns', array( $this, 'admin_columns' ) );
		add_action( 'manage_' . self::CPT_SLUG . '_posts_custom_column', array( $this, 'admin_column_content' ), 10, 2 );

		// CSV bulk export.
		add_filter( 'bulk_actions-edit-' . self::CPT_SLUG, array( $this, 'register_bulk_export' ) );
		add_filter( 'handle_bulk_actions-edit-' . self::CPT_SLUG, array( $this, 'handle_bulk_export' ), 10, 3 );
	}

	/**
	 * Registers the newsletter_subscriber private CPT.
	 */
	public function register_cpt() {
		register_post_type(
			self::CPT_SLUG,
			array(
				'label'               => esc_html__( 'Newsletter Subscribers', 'wp-rig' ),
				'labels'              => array(
					'name'               => esc_html__( 'Newsletter Subscribers', 'wp-rig' ),
					'singular_name'      => esc_html__( 'Subscriber', 'wp-rig' ),
					'all_items'          => esc_html__( 'All Subscribers', 'wp-rig' ),
					'search_items'       => esc_html__( 'Search Subscribers', 'wp-rig' ),
					'not_found'          => esc_html__( 'No subscribers found.', 'wp-rig' ),
					'not_found_in_trash' => esc_html__( 'No subscribers found in trash.', 'wp-rig' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_rest'        => false,
				'capability_type'     => 'post',
				'capabilities'        => array(
					'create_posts' => 'do_not_allow',
				),
				'map_meta_cap'        => true,
				'supports'            => array( 'title' ),
				'menu_icon'           => 'dashicons-email-alt',
				'menu_position'       => 25,
			)
		);
	}

	/**
	 * Registers the REST API endpoint for newsletter subscriptions.
	 */
	public function register_rest_route() {
		register_rest_route(
			self::REST_NAMESPACE,
			self::REST_ROUTE,
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'handle_subscription' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'email' => array(
						'required'          => true,
						'type'              => 'string',
						'sanitize_callback' => 'sanitize_email',
						'validate_callback' => function ( $value ) {
							return is_email( $value );
						},
					),
				),
			)
		);
	}

	/**
	 * Handles an incoming subscription request.
	 *
	 * @param WP_REST_Request $request The REST request object.
	 * @return WP_REST_Response|WP_Error
	 */
	public function handle_subscription( WP_REST_Request $request ) {
		$email = sanitize_email( $request->get_param( 'email' ) );

		if ( ! is_email( $email ) ) {
			return new WP_Error(
				'invalid_email',
				esc_html__( 'Please enter a valid email address.', 'wp-rig' ),
				array( 'status' => 400 )
			);
		}

		// Check for duplicate.
		$existing = get_posts(
			array(
				'post_type'      => self::CPT_SLUG,
				'post_status'    => 'publish',
				'title'          => $email,
				'posts_per_page' => 1,
				'fields'         => 'ids',
			)
		);

		if ( ! empty( $existing ) ) {
			return new WP_Error(
				'already_subscribed',
				esc_html__( 'This email is already subscribed.', 'wp-rig' ),
				array( 'status' => 409 )
			);
		}

		// Save subscriber.
		$post_id = wp_insert_post(
			array(
				'post_type'   => self::CPT_SLUG,
				'post_title'  => $email,
				'post_status' => 'publish',
			),
			true
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error(
				'save_failed',
				esc_html__( 'Something went wrong. Please try again.', 'wp-rig' ),
				array( 'status' => 500 )
			);
		}

		update_post_meta( $post_id, '_subscriber_email', $email );
		update_post_meta( $post_id, '_subscribe_date', current_time( 'mysql' ) );
		update_post_meta( $post_id, '_subscriber_ip', hash( 'sha256', sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ?? '' ) ) ) );

		// Admin notification.
		$this->send_admin_notification( $email );

		return new WP_REST_Response(
			array(
				'success' => true,
				'message' => esc_html__( 'Newsletter subscribed successfully!', 'wp-rig' ),
			),
			200
		);
	}

	/**
	 * Sends an admin notification email when a new subscriber signs up.
	 *
	 * @param string $email The subscriber email address.
	 */
	private function send_admin_notification( string $email ) {
		$admin_email = get_option( 'admin_email' );
		$site_name   = get_option( 'blogname' );

		wp_mail(
			$admin_email,
			/* translators: %s: site name */
			sprintf( esc_html__( '[%s] New Newsletter Subscriber', 'wp-rig' ), $site_name ),
			/* translators: %s: subscriber email */
			sprintf( esc_html__( "A new subscriber has joined your newsletter:\n\nEmail: %s\n\nView all subscribers in WP Admin > Newsletter Subscribers.", 'wp-rig' ), $email )
		);
	}

	/**
	 * Enqueues the newsletter JS asset on the front end.
	 */
	public function enqueue_assets() {
		$asset_file = get_theme_file_path( '/assets/js/newsletter.js' );
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		wp_enqueue_script(
			'eternal-newsletter',
			get_theme_file_uri( '/assets/js/newsletter.js' ),
			array(),
			filemtime( $asset_file ),
			true
		);

		wp_localize_script(
			'eternal-newsletter',
			'eternalNewsletter',
			array(
				'endpoint' => rest_url( self::REST_NAMESPACE . self::REST_ROUTE ),
				'nonce'    => wp_create_nonce( 'wp_rest' ),
			)
		);
	}

	/**
	 * Registers custom columns for the admin list table.
	 *
	 * @param array $columns Existing columns.
	 * @return array Modified columns.
	 */
	public function admin_columns( array $columns ): array {
		return array(
			'cb'             => $columns['cb'],
			'email'          => esc_html__( 'Email', 'wp-rig' ),
			'subscribe_date' => esc_html__( 'Date', 'wp-rig' ),
		);
	}

	/**
	 * Outputs column content for the admin list table.
	 *
	 * @param string $column  Column slug.
	 * @param int    $post_id Post ID.
	 */
	public function admin_column_content( string $column, int $post_id ) {
		switch ( $column ) {
			case 'email':
				echo esc_html( get_post_meta( $post_id, '_subscriber_email', true ) );
				break;
			case 'subscribe_date':
				echo esc_html( get_post_meta( $post_id, '_subscribe_date', true ) );
				break;
		}
	}

	/**
	 * Registers the "Export to CSV" bulk action.
	 *
	 * @param array $bulk_actions Existing bulk actions.
	 * @return array Modified bulk actions.
	 */
	public function register_bulk_export( array $bulk_actions ): array {
		$bulk_actions['export_csv'] = esc_html__( 'Export to CSV', 'wp-rig' );
		return $bulk_actions;
	}

	/**
	 * Handles the CSV export bulk action.
	 *
	 * @param string $redirect_to Redirect URL after action.
	 * @param string $action      The action name.
	 * @param int[]  $post_ids    Selected post IDs.
	 * @return string Redirect URL.
	 */
	public function handle_bulk_export( string $redirect_to, string $action, array $post_ids ): string {
		if ( 'export_csv' !== $action ) {
			return $redirect_to;
		}

		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="newsletter-subscribers-' . gmdate( 'Y-m-d' ) . '.csv"' );
		header( 'Pragma: no-cache' );

		// phpcs:ignore WordPress.WP.AlternativeFunctions
		$output = fopen( 'php://output', 'w' );
		fputcsv( $output, array( 'Email', 'Subscribe Date' ) );

		foreach ( $post_ids as $post_id ) {
			fputcsv(
				$output,
				array(
					get_post_meta( $post_id, '_subscriber_email', true ),
					get_post_meta( $post_id, '_subscribe_date', true ),
				)
			);
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions
		fclose( $output );
		exit;
	}
}
