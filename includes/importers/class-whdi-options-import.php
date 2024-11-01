<?php
/**
 * Customizer Site options importer class.
 *
 * @since  1.0.0
 * @package WPHobby Demo Importer
 */

defined( 'ABSPATH' ) or exit;

/**
 * Customizer Site options importer class.
 *
 * @since  1.0.0
 */
class WHDI_Options_Import {

    /**
     * Instance of WHDI_Options_Importer
     *
     * @since  1.0.0
     * @var (Object) WHDI_Options_Importer
     */
    private static $_instance = null;

    /**
     * Instanciate WHDI_Options_Importer
     *
     * @since  1.0.0
     * @return (Object) WHDI_Options_Importer
     */
    public static function instance() {
        if ( ! isset( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    /**
     * Site Options
     *
     * @since 1.0.2
     *
     * @return array    List of defined array.
     */
    private static function site_options() {
        return array(
            'custom_logo',
            'nav_menu_locations',
            'show_on_front',
            'page_on_front',
            'page_for_posts',

        );
    }

    /**
     * Import site options.
     *
     * @since  1.0.2    Updated option if exist in defined option array 'site_options()'.
     *
     * @since  1.0.0
     *
     * @param  (Array) $options Array of site options to be imported from the demo.
     */
    public function import_options( $options = array() ) {

        if ( ! isset( $options ) ) {
            return;
        }

        foreach ( $options as $option_name => $option_value ) {

            // Is option exist in defined array site_options()?
            if ( null !== $option_value ) {

                // Is option exist in defined array site_options()?
                if ( in_array( $option_name, self::site_options(), true ) ) {


                    switch ( $option_name ) {

//                        case 'page_for_posts':
//                        case 'page_on_front':
//                            $this->update_page_id_by_option_value( $option_name, $option_value );
//                            break;

                        // nav menu locations.
                        case 'nav_menu_locations':
                            $this->set_nav_menu_locations( $option_value );
                            break;

                        // insert logo.
                        case 'custom_logo':
                            $this->insert_logo( $option_value );
                            break;

                        default:
                            update_option( $option_name, $option_value );
                            break;
                    }
                }
            }
        }
    }

    /**
     * Update post option
     *
     * @since 1.0.2
     *
     * @param  string $option_name  Option name.
     * @param  mixed  $option_value Option value.
     * @return void
     */
    private function update_page_id_by_option_value( $option_name, $option_value ) {
        $page = get_page_by_title( $option_value );
        if ( is_object( $page ) ) {
            update_option( $option_name, $page->ID );
        }
    }

    /**
     * In WP nav menu is stored as ( 'menu_location' => 'menu_id' );
     * In export we send 'menu_slug' like ( 'menu_location' => 'menu_slug' );
     * In import we set 'menu_id' from menu slug like ( 'menu_location' => 'menu_id' );
     *
     * @since 1.0.0
     * @param array $nav_menu_locations Array of nav menu locations.
     */
    private function set_nav_menu_locations( $nav_menu_locations = array() ) {

        $menu_locations = array();

        // Update menu locations.
        if ( isset( $nav_menu_locations ) ) {

            foreach ( $nav_menu_locations as $menu => $value ) {

                $term = get_term_by( 'slug', $value, 'nav_menu' );

                if ( is_object( $term ) ) {
                    $menu_locations[ $menu ] = $term->term_id;
                }
            }

            set_theme_mod( 'nav_menu_locations', $menu_locations );
        }
    }

    /**
     * Insert Logo By URL
     *
     * @since 1.0.0
     * @param  string $image_url Logo URL.
     * @return void
     */
    private function insert_logo( $image_url = '' ) {
        $attachment_id = $this->download_image( $image_url );
        if ( $attachment_id ) {
            set_theme_mod( 'custom_logo', $attachment_id );
        }
    }

    /**
     * Download image by URL
     *
     * @since 1.3.13
     *
     * @param  string $image_url Logo URL.
     * @return mixed false|Attachment ID
     */
    private function download_image( $image_url = '' ) {
        $data = (object) WHDI_Helper::_sideload_image( $image_url );

        if ( ! is_wp_error( $data ) ) {
            if ( isset( $data->attachment_id ) && ! empty( $data->attachment_id ) ) {
                return $data->attachment_id;
            }
        }

        return false;
    }

}
