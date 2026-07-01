<?php
/**
 * File Manager Advanced Rest API Controller
 * @since 5.3.0
 *
 * @package File Manager Advanced
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'FMA_Controller' ) ) :
	/**
	 * File Manager Advanced Rest API Controller
	 * @since 5.3.0
	 */
	class FMA_Controller {
		/**
		 * Singleton Instance
		 * @since 5.3.0
		 *
		 * @var FMA_Controller $instance Instance.
		 */
		private static $instance = null;

		/**
		 * Rest API Namespace
		 * @since 5.3.0
		 *
		 * @var string $namespace Rest API Namespace.
		 */
		private $namespace = 'file-manager-advanced/v1';

		/**
		 * File Manager Advanced Rest API Controller Constructor
		 * @since 5.3.0
		 */
		private function __construct() {
			add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
		}

		/**
		 * Rest API Init
		 * @since 5.3.0
		 *
		 * @param WP_REST_Server $wp_rest_server Rest API Namespace.
		 */
		public function rest_api_init( $wp_rest_server ) {
			register_rest_route(
				$this->namespace,
				'/hide-banner',
				array(
					'methods'  => $wp_rest_server::CREATABLE,
					'permission_callback' => array( $this, 'permission_callback' ),
					'callback' => array( $this, 'hide_banner' ),
				)
			);

			register_rest_route(
				$this->namespace,
				'/minimize-maximize-banner',
				array(
					'methods'  => $wp_rest_server::CREATABLE,
                    'permission_callback' => array( $this, 'permission_callback' ),
					'callback' => array( $this, 'minimize_maximize_banner' ),
				)
			);
		}

        /**
         * Permission Callback
         *
         * @since 5.3.2
         * @return bool
         */
        public function permission_callback() {
            return is_user_logged_in() && current_user_can( 'manage_options' );
        }

		/**
		 * Hide Banner
		 * @since 5.3.0
		 *
		 * @param WP_REST_Request $request Rest API Request.
		 *
		 * @return WP_REST_Response
		 */
		public function hide_banner( $request ) {
			update_option( '_fma_banner_hide', 'yes' );
			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		/**
		 * Minimize Maximize Banner
		 * @since 5.3.0
		 *
		 * @param WP_REST_Request $request Rest API Request.
		 *
		 * @return WP_REST_Response
		 */
		public function minimize_maximize_banner( $request ) {
			$action = $request->get_param( 'action' );
			$action = sanitize_text_field( wp_unslash( $action ) );
			update_option( '_fma_banner_minimize', $action );
			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		/**
		 * Get Singleton Instance
		 * @since 5.3.0
		 *
		 * @return FMA_Controller
		 */
		public static function get_instance() {

			if ( is_null( self::$instance ) ) {
				self::$instance = new FMA_Controller();
			}

			return self::$instance;
		}
	}

	FMA_Controller::get_instance();
endif;
