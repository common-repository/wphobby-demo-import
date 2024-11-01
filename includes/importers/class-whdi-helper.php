<?php
/**
 * WHDI Helper
 *
 * @since  1.0.0
 * @package WPHobby Demo Import
 */

if ( ! class_exists( 'WHDI_Helper' ) ) :

    /**
     * WHDI_Helper
     *
     * @since 1.0.0
     */
    class WHDI_Helper {

        /**
         * Instance
         *
         * @access private
         * @var object Instance
         * @since 1.0.0
         */
        private static $instance;

        /**
         * Initiator
         *
         * @since 1.0.0
         * @return object initialized object of class.
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
            add_filter( 'wie_import_data', array( $this, 'custom_menu_widget' ) );
            add_filter( 'wp_prepare_attachment_for_js', array( $this, 'add_svg_image_support' ), 10, 3 );
        }

        /**
         * Add svg image support
         *
         * @since 1.1.5
         *
         * @param array  $response    Attachment response.
         * @param object $attachment Attachment object.
         * @param array  $meta        Attachment meta data.
         */
        function add_svg_image_support( $response, $attachment, $meta ) {
            if ( ! function_exists( 'simplexml_load_file' ) ) {
                return $response;
            }

            if ( ! empty( $response['sizes'] ) ) {
                return $response;
            }

            if ( 'image/svg+xml' !== $response['mime'] ) {
                return $response;
            }

            $svg_path = get_attached_file( $attachment->ID );

            $dimensions = self::get_svg_dimensions( $svg_path );

            $response['sizes'] = array(
                'full' => array(
                    'url'         => $response['url'],
                    'width'       => $dimensions->width,
                    'height'      => $dimensions->height,
                    'orientation' => $dimensions->width > $dimensions->height ? 'landscape' : 'portrait',
                ),
            );

            return $response;
        }

        /**
         * Get SVG Dimensions
         *
         * @since 1.1.5
         *
         * @param  string $svg SVG file path.
         * @return array      Return SVG file height & width for valid SVG file.
         */
        public static function get_svg_dimensions( $svg ) {

            $svg = simplexml_load_file( $svg );

            if ( false === $svg ) {
                $width  = '0';
                $height = '0';
            } else {
                $attributes = $svg->attributes();
                $width      = (string) $attributes->width;
                $height     = (string) $attributes->height;
            }

            return (object) array(
                'width'  => $width,
                'height' => $height,
            );
        }

        /**
         * Custom Menu Widget
         *
         * In widget export we set the nav menu slug instead of ID.
         * So, In import process we check get menu id by slug and set
         * it in import widget process.
         *
         * @since 1.0.7
         *
         * @param  object $all_sidebars Widget data.
         * @return object               Set custom menu id by slug.
         */
        function custom_menu_widget( $all_sidebars ) {

            // Get current menu ID & Slugs.
            $menu_locations = array();
            $nav_menus      = (object) wp_get_nav_menus();
            if ( isset( $nav_menus ) ) {
                foreach ( $nav_menus as $menu_key => $menu ) {
                    if ( is_object( $menu ) ) {
                        $menu_locations[ $menu->term_id ] = $menu->slug;
                    }
                }
            }

            // Import widget data.
            $all_sidebars = (object) $all_sidebars;
            foreach ( $all_sidebars as $widgets_key => $widgets ) {
                foreach ( $widgets as $widget_key => $widget ) {

                    // Found slug in current menu list.
                    if ( isset( $widget->nav_menu ) ) {
                        $menu_id = array_search( $widget->nav_menu, $menu_locations, true );
                        if ( ! empty( $menu_id ) ) {
                            $all_sidebars->$widgets_key->$widget_key->nav_menu = $menu_id;
                        }
                    }
                }
            }

            return $all_sidebars;
        }

        /**
         * Download File Into Uploads Directory
         *
         * @param  string $file Download File URL.
         * @param  int    $timeout_seconds Timeout in downloading the XML file in seconds.
         * @return array        Downloaded file data.
         */
        public static function download_file( $file = '', $timeout_seconds = 300 ) {

            // Gives us access to the download_url() and wp_handle_sideload() functions.
            require_once( ABSPATH . 'wp-admin/includes/file.php' );

            // Download file to temp dir.
            $temp_file = download_url( $file, $timeout_seconds );

            // WP Error.
            if ( is_wp_error( $temp_file ) ) {
                return array(
                    'success' => false,
                    'data'    => $temp_file->get_error_message(),
                );
            }

            // Array based on $_FILE as seen in PHP file uploads.
            $file_args = array(
                'name'     => basename( $file ),
                'tmp_name' => $temp_file,
                'error'    => 0,
                'size'     => filesize( $temp_file ),
            );

            $overrides = array(

                // Tells WordPress to not look for the POST form
                // fields that would normally be present as
                // we downloaded the file from a remote server, so there
                // will be no form fields
                // Default is true.
                'test_form'   => false,

                // Setting this to false lets WordPress allow empty files, not recommended.
                // Default is true.
                'test_size'   => true,

                // A properly uploaded file will pass this test. There should be no reason to override this one.
                'test_upload' => true,

                'mimes'       => array(
                    'xml'  => 'text/xml',
                    'json' => 'text/plain',
                ),
            );

            // Move the temporary file into the uploads directory.
            $results = wp_handle_sideload( $file_args, $overrides );

            if ( isset( $results['error'] ) ) {
                return array(
                    'success' => false,
                    'data'    => $results,
                );
            }

            // Success.
            return array(
                'success' => true,
                'data'    => $results,
            );
        }

        /**
         * Downloads an image from the specified URL.
         *
         * Taken from the core media_sideload_image() function and
         * modified to return an array of data instead of html.
         *
         * @since 1.0.10
         *
         * @param string $file The image file path.
         * @return array An array of image data.
         */
        static public function _sideload_image( $file ) {
            $data = new stdClass();

            if ( ! function_exists( 'media_handle_sideload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/media.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            }

            if ( ! empty( $file ) ) {

                // Set variables for storage, fix file filename for query strings.
                preg_match( '/[^\?]+\.(jpe?g|jpe|svg|gif|png)\b/i', $file, $matches );
                $file_array         = array();
                $file_array['name'] = basename( $matches[0] );

                // Download file to temp location.
                $file_array['tmp_name'] = download_url( $file );

                // If error storing temporarily, return the error.
                if ( is_wp_error( $file_array['tmp_name'] ) ) {
                    return $file_array['tmp_name'];
                }

                // Do the validation and storage stuff.
                $id = media_handle_sideload( $file_array, 0 );

                // If error storing permanently, unlink.
                if ( is_wp_error( $id ) ) {
                    unlink( $file_array['tmp_name'] );
                    return $id;
                }

                // Build the object to return.
                $meta                = wp_get_attachment_metadata( $id );
                $data->attachment_id = $id;
                $data->url           = wp_get_attachment_url( $id );
                $data->thumbnail_url = wp_get_attachment_thumb_url( $id );
                $data->height        = isset( $meta['height'] ) ? $meta['height'] : '';
                $data->width         = isset( $meta['width'] ) ? $meta['width'] : '';
            }

            return $data;
        }

        /**
         * Checks to see whether a string is an image url or not.
         *
         * @since 1.0.10
         *
         * @param string $string The string to check.
         * @return bool Whether the string is an image url or not.
         */
        static public function _is_image_url( $string = '' ) {
            if ( is_string( $string ) ) {

                if ( preg_match( '/\.(jpg|jpeg|svg|png|gif)/i', $string ) ) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Get data from a file
         *
         * @param string $file_path file path where the content should be saved.
         * @return string $data, content of the file or WP_Error object with error message.
         */
        public static function data_from_file( $file_path ) {
            // Verify WP file-system credentials.
            $verified_credentials = self::check_wp_filesystem_credentials();

            if ( is_wp_error( $verified_credentials ) ) {
                return $verified_credentials;
            }

            // By this point, the $wp_filesystem global should be working, so let's use it to read a file.
            global $wp_filesystem;

            $data = $wp_filesystem->get_contents( $file_path );

            if ( ! $data ) {
                return new \WP_Error(
                    'failed_reading_file_from_server',
                    sprintf(
                        __( 'An error occurred while reading a file from your server! Tried reading file from path: %s%s.', 'wphobby-demo-import' ),
                        '<br>',
                        $file_path
                    )
                );
            }

            // Return the file data.
            return $data;
        }

        /**
         * Helper function: check for WP file-system credentials needed for reading and writing to a file.
         *
         * @return boolean|WP_Error
         */
        private static function check_wp_filesystem_credentials() {
            // Check if the file-system method is 'direct', if not display an error.
            if ( ! ( 'direct' === get_filesystem_method() ) ) {
                return new \WP_Error(
                    'no_direct_file_access',
                    sprintf(
                        __( 'This WordPress page does not have %sdirect%s write file access. This plugin needs it in order to save the demo import xml file to the upload directory of your site. You can change this setting with these instructions: %s.', 'wphobby-demo-import' ),
                        '<strong>',
                        '</strong>',
                        '<a href="http://gregorcapuder.com/wordpress-how-to-set-direct-filesystem-method/" target="_blank">How to set <strong>direct</strong> filesystem method</a>'
                    )
                );
            }

            // Get plugin page settings.
            $plugin_page_setup = apply_filters( 'pt-ocdi/plugin_page_setup', array(
                    'parent_slug' => 'themes.php',
                    'page_title'  => esc_html__( 'One Click Demo Import' , 'wphobby-demo-import' ),
                    'menu_title'  => esc_html__( 'Import Demo Data' , 'wphobby-demo-import' ),
                    'capability'  => 'import',
                    'menu_slug'   => 'pt-one-click-demo-import',
                )
            );

            // Get user credentials for WP file-system API.
            $demo_import_page_url = wp_nonce_url( $plugin_page_setup['parent_slug'] . '?page=' . $plugin_page_setup['menu_slug'], $plugin_page_setup['menu_slug'] );

            if ( false === ( $creds = request_filesystem_credentials( $demo_import_page_url, '', false, false, null ) ) ) {
                return new \WP_error(
                    'filesystem_credentials_could_not_be_retrieved',
                    __( 'An error occurred while retrieving reading/writing permissions to your server (could not retrieve WP filesystem credentials)!', 'wphobby-demo-import' )
                );
            }

            // Now we have credentials, try to get the wp_filesystem running.
            if ( ! WP_Filesystem( $creds ) ) {
                return new \WP_Error(
                    'wrong_login_credentials',
                    __( 'Your WordPress login credentials don\'t allow to use WP_Filesystem!', 'wphobby-demo-import' )
                );
            }

            return true;
        }


    }

    /**
     * Kicking this off by calling 'get_instance()' method
     */
    WHDI_Helper::get_instance();

endif;
