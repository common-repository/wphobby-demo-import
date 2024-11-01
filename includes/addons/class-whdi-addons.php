<?php
/**
 * WHDI Addons Class
 *
 * Plugin Addons Class
 *
 * @since  1.0.0
 * @package WPHobby Demo Import
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WHDI_Addons' ) ) :

	/**
	 * WHDI Addons
	 */
	class WHDI_Addons {

        private $addon_data;

		/**
		 * Instance
		 *
		 * @since  1.0.0
		 * @var (Object) Class object
		 */
		public static $_instance = null;

		/**
		 * Set Instance
		 *
		 * @since  1.0.0
		 *
		 * @return object Class object.
		 */
		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @since  1.0.0
		 */
		public function __construct($arr='') {
            if(!empty($arr)){
                $this->setup_addon( $arr);
            }
        }

        // set up addon instance afterwards
		public function setup_addon($A){
			// assign initial values for instance of addon
			$this->addon_data = $A;
		}




	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	WHDI_Addons::get_instance();

endif;