<?php
/**
 * Plugin Name: WPHobby Demo Import
 * Plugin URI: http://wphobby.com
 * Description: Demo Import Plugin for WPHobby Themes.
 * Version: 1.1.2
 * Author: wphobby
 * Author URI: https://wphobby.com/
 *
 * @package WPHobby Demo Import
 */

if ( ! defined( 'ABSPATH' ) ) {
   exit;
} // Exit if accessed directly

/**
 * Set constants
 */
if ( ! defined( 'WHDI_NAME' ) ) {
    define( 'WHDI_NAME', __( 'WPHobby Demo Import', 'wphobby-demo-import' ) );
}

if ( ! defined( 'WHDI_DIR' ) ) {
    define( 'WHDI_DIR', plugin_dir_path(__FILE__) );
}

if ( ! defined( 'WHDI_URL' ) ) {
    define( 'WHDI_URL', plugin_dir_url(__FILE__) );
}

if ( ! defined( 'WHDI_OPTIONS' ) ) {
    define( 'WHDI_OPTIONS', 'whdi_general_data' );
}

if ( ! defined( 'WHDI_VERSION' ) ) {
    define( 'WHDI_VERSION', '1.1.2' );
}

if( ! function_exists( 'whdi_init' ) ) {
    /**
     * WPHobby Demo Import Setup
     *
     * @since 1.0.3
     */
    function whdi_init()
    {
        require_once WHDI_DIR . '/includes/class-whdi-init.php';
    }

    add_action('plugins_loaded', 'whdi_init');
}

if ( ! function_exists( 'hasium_freemius' ) ) {
    // Create a helper function for easy SDK access.
    function hasium_freemius() {
        global $hasium_freemius;

        if ( ! isset( $hasium_freemius ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $hasium_freemius = fs_dynamic_init( array(
                'id'                  => '8119',
                'slug'                => 'Hasium',
                'premium_slug'        => 'wphobby-demo-import',
                'type'                => 'plugin',
                'public_key'          => 'pk_03bdd319924966e5330f477523bd4',
                'is_premium'          => false,
                'is_premium_only'     => false,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'whdi-panel',
                    'first-path'     => 'admin.php?page=whdi-panel',
                    'support'        => true,
                ),
            ) );
        }

        return $hasium_freemius;
    }

    // Init Freemius.
    hasium_freemius();
    // Signal that SDK was initiated.
    do_action( 'hasium_freemius_loaded' );
}
?>
