<?php
/**
 * WPHobby Demo Import Compatibility for 3rd party plugins.
 *
 * @package WPHobby Demo Import
 * @since 1.0.11
 */

if ( ! class_exists( 'WHDI_Compatibility' ) ) :

	/**
	 * WPHobby Demo Import Compatibility
	 *
	 * @since 1.0.11
	 */
	class WHDI_Compatibility {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.11
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.11
		 * @return object initialized object of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.11
		 */
		public function __construct() {
			// Plugin - Elementor.
			require_once WHDI_DIR . 'includes/compatibility/elementor/class-whdi-compatibility-elementor.php';
		}

	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	WHDI_Compatibility::instance();

endif;


