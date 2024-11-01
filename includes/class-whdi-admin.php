<?php
/**
 * WHDI Admin Class
 *
 * Plugin Admin Page Class
 *
 * @since  1.0.0
 * @package WPHobby Demo Import
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'WHDI_Admin' ) ) :
    /**
     * WHDI Admin Settings
     */
    class WHDI_Admin {

        /**
         * Member Variable
         *
         * @var instance
         */
        private static $instance;

        /**
         * Initiator
         *
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new self;
            }
            return self::$instance;
        }

        /**
         * Constructor
         *
         * @since 1.0.0
         */
        public function __construct() {

            if ( ! is_admin() ) {
                return;
            }

            add_action( 'admin_menu', array( $this, 'register_admin_menu' ) );

        }

        /**
         * Register admin menu
         * @return void
         * @since  1.0.0
         */
        public function register_admin_menu(){
            add_menu_page(
                __('WPHobby Demo Import', 'wphobby-demo-import'),
                __('Importer', 'wphobby-demo-import'),
                'manage_options',
                'whdi-panel',
                array( $this, 'whdi_panel_general' ),
                'dashicons-buddicons-forums'
            );

            // add_submenu_page(
            //     'whdi-panel',
            //     __('WPHobby Addons', 'wphobby-demo-import'),
            //     __('Addons', 'wphobby-demo-import'),
            //     'manage_options',
            //     'whdi-panel-addons',
            //     array( $this, 'whdi_panel_addons' )
            // );

        }

        /**
         * The admin panel content
         * @since 1.0.0
         */
        public function whdi_panel_general() {
            ?>
            <div class="wrap" id="whdi-admin">
            <?php
               require_once( WHDI_DIR . '/includes/admin/sections/general/top.php' );
               require_once( WHDI_DIR . '/includes/admin/sections/general/filter.php' );
               require_once( WHDI_DIR . '/includes/admin/sections/general/list.php' );
            ?>
            </div>
            <?php
        }

        /**
         * The admin panel content
         * @since 1.0.9
         */
        public function whdi_panel_addons() {
            ?>
            <div class="wrap" id="whdi-admin">
                <?php
                require_once( WHDI_DIR . '/includes/admin/sections/addons/list.php' );
                ?>
            </div>
            <?php
        }

    }

    /**
     * Start this plugin admin settings page by calling 'get_instance()' method
     */
    WHDI_Admin::get_instance();
endif;