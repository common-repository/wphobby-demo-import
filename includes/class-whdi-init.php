<?php
/**
 * WHDI class
 *
 * Plugin Initial Class
 *
 * @since  1.0.0
 * @package WPHobby Demo Import
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( ! class_exists( 'WHDI' ) ) :

    /**
     * WHDI
     */
    class WHDI {

        /**
         * API URL to get the response from.
         *
         * @since  1.0.0
         * @var (String) URL
         */
        public static $api_url;

        /**
         * Instance of WHDI
         *
         * @since  1.0.0
         * @var (Object) WHDI
         */
        private static $_instance = null;

        /**
         * Instance of WHDI
         *
         * @since  1.0.0
         *
         * @return object Class object
         */
        public static function get_instance() {
            if ( ! isset( self::$_instance ) ) {
                self::$_instance = new self;
            }
            return self::$_instance;
        }

        /**
         * Constructor
         *
         * @since  1.0.0
         */
        private function __construct() {

            self::set_api_url();

            add_action( 'plugins_loaded', array( $this, 'load_text_domain' ) );
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );
            add_action( 'admin_notices', array( $this, 'add_notice' ), 1 );
            //add_action( 'admin_notices', array( $this, 'install_premium_admin_notice' ), 1 );


            // Ajax.
            add_action( 'wp_ajax_whdi-activate-theme', array( $this, 'activate_theme' ) );
            add_action( 'wp_ajax_whdi-required-plugins', array( $this, 'required_plugin' ) );
            add_action( 'wp_ajax_whdi-required-plugin-activate', array( $this, 'required_plugin_activate' ) );
            add_action( 'wp_ajax_whdi-backup-settings', array( $this, 'backup_settings' ) );
            add_action( 'wp_ajax_whdi-set-reset-data', array( $this, 'get_reset_data' ) );



            $this->includes();

        }

        /**
         * Load all the required files
         *
         * @since  1.0.0
         */
        private function includes() {

            // Include files
            require_once WHDI_DIR . '/includes/class-whdi-admin.php';
            require_once WHDI_DIR . '/includes/class-whdi-importer.php';
            require_once WHDI_DIR . '/includes/addons/class-whdi-addons.php';
            require_once WHDI_DIR . '/includes/addons/class-whdi-addons-list.php';
            require_once WHDI_DIR . '/includes/compatibility/class-whdi-compatibility.php';

        }

        /**
         * Loads plugin text domain
         *
         * @since 1.0.1
         */
        function load_text_domain() {
            load_plugin_textdomain( 'wphobby-demo-import', false, WHDI_DIR . '/languages/' );

        }

        /**
         * Enqueue admin scripts
         *
         * @since  1.0.0
         *
         * @return void
         */
        public function admin_enqueue() {

            if ( is_admin() ) {
                wp_enqueue_style('font-awesome', WHDI_URL . 'assets/css/font-awesome.min.css', false, WHDI_VERSION);
                wp_enqueue_style('whdi-admin-style', WHDI_URL . 'assets/css/admin.css', false, WHDI_VERSION);
                wp_enqueue_script( 'whdi-admin-script', WHDI_URL . 'assets/js/admin.js', array( 'wp-util', 'updates', 'wp-url', 'jquery' ), WHDI_VERSION, true );
                wp_enqueue_script( 'whdi-notice-script', WHDI_URL . 'assets/js/notice.js', array( 'jquery' ), WHDI_VERSION, true );
                wp_enqueue_script( 'whdi-render-grid', WHDI_URL . 'assets/js/render-grid.js', array( 'wp-util', 'imagesloaded', 'jquery' ), WHDI_VERSION, true );

                $data = apply_filters(
                    'whdi_render_localize_vars',
                    array(
                        'ApiURL'  => self::$api_url,
                    )
                );
                wp_localize_script( 'whdi-render-grid', 'WHDIApi', $data );

                $data = apply_filters(
                    'whdi_localize_vars',
                    array(
                        'ajaxurl'           => esc_url( admin_url( 'admin-ajax.php' ) ),
                        '_ajax_nonce'       => wp_create_nonce( 'whdi' ),
                        'siteURL'           => site_url(),
                        'strings'           => array(
                            /* translators: %s are HTML tags. */
                            'warningXMLReader'         => sprintf( __( '%1$sRequired XMLReader PHP extension is missing on your server!%2$sWPHobby Demo Import import requires XMLReader extension to be installed. Please contact your web hosting provider and ask them to install and activate the XMLReader PHP extension.', 'wphobby-demo-import' ), '<div class="notice whdi-sites-xml-notice notice-error"><p><b>', '</b></p><p>', '</p></div>' ),
                            'warningBeforeCloseWindow' => __( 'Warning! WPHobby Demo Import Import process is not complete. Don\'t close the window until import process complete. Do you still want to leave the window?', 'wphobby-demo-import' ),
                            'importFailedBtnSmall'     => __( 'Error!', 'wphobby-demo-import' ),
                            'importFailedBtnLarge'     => __( 'Error! Read Possibilities.', 'wphobby-demo-import' ),
                            'importFailedURL'          => esc_url( 'https://hublip.com/docs/?p=1314&utm_source=demo-import-panel&utm_campaign=whdi-sites&utm_medium=import-failed' ),
                            'viewSite'                 => __( 'Done! View Site', 'wphobby-demo-import' ),
                            'btnActivating'            => __( 'Activating', 'wphobby-demo-import' ) . '&hellip;',
                            'btnActive'                => __( 'Active', 'wphobby-demo-import' ),
                            'importFailBtn'            => __( 'Import failed.', 'wphobby-demo-import' ),
                            'importFailBtnLarge'       => __( 'Import failed.', 'wphobby-demo-import' ),
                            'importDemo'               => __( 'Import This Site', 'wphobby-demo-import' ),
                            'importingDemo'            => __( 'Importing..', 'wphobby-demo-import' ),
                            'DescExpand'               => __( 'Read more', 'wphobby-demo-import' ) . '&hellip;',
                            'DescCollapse'             => __( 'Hide', 'wphobby-demo-import' ),
                            'responseError'            => __( 'There was a problem receiving a response from server.', 'wphobby-demo-import' ),
                            'searchNoFound'            => __( 'No Demos found, Try a different search.', 'wphobby-demo-import' ),
                        ),
                        'log'               => array(
                            'installingPlugin'        => __( 'Installing plugin ', 'wphobby-demo-import' ),
                            'installed'               => __( 'Plugin installed!', 'wphobby-demo-import' ),
                            'activating'              => __( 'Activating plugin ', 'wphobby-demo-import' ),
                            'activated'               => __( 'Plugin activated ', 'wphobby-demo-import' ),
                            'bulkActivation'          => __( 'Bulk plugin activation...', 'wphobby-demo-import' ),
                            'activate'                => __( 'Plugin activate - ', 'wphobby-demo-import' ),
                            'activationError'         => __( 'Error! While activating plugin  - ', 'wphobby-demo-import' ),
                            'bulkInstall'             => __( 'Bulk plugin installation...', 'wphobby-demo-import' ),
                            'api'                     => __( 'Site API ', 'wphobby-demo-import' ),
                            'importing'               => __( 'Importing..', 'wphobby-demo-import' ),
                            'processingRequest'       => __( 'Processing requests...', 'wphobby-demo-import' ),
                            'importCustomizer'        => __( 'Importing "Customizer Settings"...', 'wphobby-demo-import' ),
                            'importCustomizerSuccess' => __( 'Imported customizer settings!', 'wphobby-demo-import' ),
                            'importWPForms'           => __( 'Importing "Contact Forms"...', 'wphobby-demo-import' ),
                            'importWPFormsSuccess'    => __( 'Imported Contact Forms!', 'wphobby-demo-import' ),
                            'importXMLPrepare'        => __( 'Preparing "XML" Data...', 'wphobby-demo-import' ),
                            'importXMLPrepareSuccess' => __( 'Set XML data!', 'wphobby-demo-import' ),
                            'importXML'               => __( 'Importing "XML"...', 'wphobby-demo-import' ),
                            'importXMLSuccess'        => __( 'Imported XML!', 'wphobby-demo-import' ),
                            'importOptions'           => __( 'Importing "Options"...', 'wphobby-demo-import' ),
                            'importOptionsSuccess'    => __( 'Imported Options!', 'wphobby-demo-import' ),
                            'importWidgets'           => __( 'Importing "Widgets"...', 'wphobby-demo-import' ),
                            'importWidgetsSuccess'    => __( 'Imported Widgets!', 'wphobby-demo-import' ),
                            'serverConfiguration'     => esc_url( 'https://hublip.com/docs/?p=1314&utm_source=demo-import-panel&utm_campaign=import-error&utm_medium=wp-dashboard' ),
                            'success'                 => __( 'View site: ', 'wphobby-demo-import' ),
                            'gettingData'             => __( 'Getting Site Information..', 'wphobby-demo-import' ),
                            'importingCustomizer'     => __( 'Importing Customizer Settings..', 'wphobby-demo-import' ),
                            'importingWPForms'        => __( 'Importing Contact Forms..', 'wphobby-demo-import' ),
                            'importXMLPreparing'      => __( 'Setting up import data..', 'wphobby-demo-import' ),
                            'importingXML'            => __( 'Importing Content..', 'wphobby-demo-import' ),
                            'importingOptions'        => __( 'Importing Site Options..', 'wphobby-demo-import' ),
                            'importingWidgets'        => __( 'Importing Widgets..', 'wphobby-demo-import' ),
                            'importComplete'          => __( 'Import Complete..', 'wphobby-demo-import' ),
                            'preview'                 => __( 'Previewing ', 'wphobby-demo-import' ),
                            'importLogText'           => __( 'See Error Log &rarr;', 'wphobby-demo-import' ),
                        ),
                    )
                );

                wp_localize_script( 'whdi-admin-script', 'WHDI_Admin', $data );

                $data = apply_filters(
                    'whdi_install_theme_localize_vars',
                    array(
                        'installed'   => __( 'Installed! Activating..', 'wphobby-demo-import' ),
                        'activating'  => __( 'Activating..', 'wphobby-demo-import' ),
                        'activated'   => __( 'Activated! Reloading..', 'wphobby-demo-import' ),
                        'installing'  => __( 'Installing..', 'wphobby-demo-import' ),
                        'ajaxurl'     => esc_url( admin_url( 'admin-ajax.php' ) ),
                        '_ajax_nonce' => wp_create_nonce( 'whdi' ),
                    )
                );
                wp_localize_script( 'whdi-notice-script', 'WHDIInstallThemeVars', $data );
            }

        }

        /**
         * Setter for $api_url
         *
         * @since  1.0.0
         */
        public static function set_api_url() {
            self::$api_url = apply_filters( 'whdi_api_url', 'https://hublip.com/whdi/json/' );
        }

        /**
         * Add Admin Notice.
         */
        public function add_notice() {

            $theme_status = 'whdi-theme-' . $this->get_theme_status();

            switch ( $theme_status ) {

                case 'whdi-theme-not-installed':
                    echo sprintf( __( '<div id="whdi-theme-activation-nag" class="notice is-dismissible notice-error"><p>Hasium Theme needs to be install for you to use currently installed "%1$s" plugin. <a href="#" class="%3$s" data-theme-slug="hasium">Install & Activate Now</a></p></div>', 'wphobby-demo-import' ),
                        WHDI_NAME,
                        esc_url( admin_url( 'themes.php?theme=hasium' ) ),
                        $theme_status );
                    break;

                // nav menu locations.
                case 'whdi-theme-installed-but-inactive':
                    echo sprintf( __( '<div id="whdi-theme-activation-nag" class="notice is-dismissible notice-error"><p>Hasium Theme needs to be active for you to use currently installed "%1$s" plugin. <a href="#" class="%3$s" data-theme-slug="hasium">Install & Activate Now</a></p></div>', 'wphobby-demo-import' ),
                        WHDI_NAME,
                        esc_url( admin_url( 'themes.php?theme=hasium' ) ),
                        $theme_status );
                    break;
            }


        }


        /**
         * Display an admin notice for premium version link
         *
         * @since 1.0.1
         * @return void
         * @use admin_notices hooks
         */
        public function install_premium_admin_notice() {

            echo sprintf(
                '<div class="notice success"><p>%s</p></div>',
                sprintf(
                    __("You can import wordpress posts automatically by <a href='https://wphobby.com/hasium/auto-robot'>Auto Robot</a> and premium version <a href='https://wphobby.com/wp/hasium'>Here</a>", "hasium")
                )
            );
        }

        /**
         * Get theme install, active or inactive status.
         *
         * @since 1.0.4
         *
         * @return string Theme status
         */
        public function get_theme_status() {

            $theme = wp_get_theme();

            // Theme installed and activate.
            if ( 'Hasium' === $theme->name || 'Hasium' === $theme->parent_theme ) {
                return 'installed-and-active';
            }

            // Theme installed but not activate.
            foreach ( (array) wp_get_themes() as $theme_dir => $theme ) {
                if ( 'Hasium' === $theme->name || 'Hasium' === $theme->parent_theme ) {
                    return 'installed-but-inactive';
                }
            }

            return 'not-installed';
        }

        /**
         * Activate theme
         *
         * @since 1.0.4
         * @return void
         */
        public function activate_theme() {

            // Verify Nonce.
            check_ajax_referer( 'whdi', '_ajax_nonce' );

            if ( ! current_user_can( 'customize' ) ) {
                wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
            }

            switch_theme( 'hasium' );

            wp_send_json_success(
                array(
                    'success' => true,
                    'message' => __( 'Theme Activated', 'wphobby-demo-import' ),
                )
            );
        }

        /**
         * Required Plugins
         *
         * @since 1.0.6
         *
         * @param  array $required_plugins Required Plugins.
         * @return mixed
         */
        public function required_plugin( $required_plugins = array() ) {

            // Verify Nonce.
            if ( ! defined( 'WP_CLI' ) ) {
                check_ajax_referer( 'whdi', '_ajax_nonce' );
            }

            $response = array(
                'active'       => array(),
                'inactive'     => array(),
                'notinstalled' => array(),
            );

            if ( ! defined( 'WP_CLI' ) && ! current_user_can( 'customize' ) ) {
                wp_send_json_error( $response );
            }

            $required_plugins             = ( isset( $_POST['required_plugins'] ) ) ? $_POST['required_plugins'] : $required_plugins;

            if ( count( $required_plugins ) > 0 ) {
                foreach ( $required_plugins as $key => $plugin ) {

                        // Installed but Inactive.
                        if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) && is_plugin_inactive( $plugin['init'] ) ) {

                            $response['inactive'][] = $plugin;

                            //  Not Installed.
                        } elseif ( ! file_exists( WP_PLUGIN_DIR . '/' . $plugin['init'] ) ) {

                            $response['notinstalled'][] = $plugin;

                            // Lite - Active.
                        } else {
                            $response['active'][] = $plugin;
                        }

                }
            }

            $data = array(
                'required_plugins'             => $response
            );

            if ( defined( 'WP_CLI' ) ) {
                return $data;
            } else {
                // Send response.
                wp_send_json_success( $data );
            }

        }

        /**
         * Required Plugin Activate
         *
         * @since 1.0.6
         * @param  string $init               Plugin init file.
         * @return void
         */
        public function required_plugin_activate( $init = '', $options = array(), $enabled_extensions = array() ) {

            if ( ! defined( 'WP_CLI' ) ) {
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'install_plugins' ) || ! isset( $_POST['init'] ) || ! $_POST['init'] ) {
                    wp_send_json_error(
                        array(
                            'success' => false,
                            'message' => __( 'User have not plugin install permissions.', 'wphobby-demo-import' ),
                        )
                    );
                }
            }

            $plugin_init        = ( isset( $_POST['init'] ) ) ? esc_attr( $_POST['init'] ) : $init;

            $activate = activate_plugin( $plugin_init, '', false, true );

            if ( is_wp_error( $activate ) ) {
                if ( defined( 'WP_CLI' ) ) {
                    WP_CLI::line( 'Plugin Activation Error: ' . $activate->get_error_message() );
                } else {
                    wp_send_json_error(
                        array(
                            'success' => false,
                            'message' => $activate->get_error_message(),
                        )
                    );
                }
            }

            if ( defined( 'WP_CLI' ) ) {
                WP_CLI::line( 'Plugin Activated!' );
            } else {
                wp_send_json_success(
                    array(
                        'success' => true,
                        'message' => __( 'Plugin Activated', 'wphobby-demo-import' ),
                    )
                );
            }
        }

        /**
         * Backup existing settings.
         */
        function backup_settings() {

            if ( ! defined( 'WP_CLI' ) ) {
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'manage_options' ) ) {
                    wp_send_json_error( __( 'User not have permission!', 'wphobby-demo-import' ) );
                }
            }

            $file_name    = 'whdi-backup-' . gmdate( 'd-M-Y-h-i-s' ) . '.json';
            $old_settings = get_option( 'whdi-settings', array() );
            $upload_dir   = WHDI_Importer_Log::get_instance()->log_dir();
            $upload_path  = trailingslashit( $upload_dir['path'] );
            $log_file     = $upload_path . $file_name;
            $file_system  = WHDI_Importer_Log::get_instance()->get_filesystem();

            // If file system fails? Then take a backup in site option.
            if ( false === $file_system->put_contents( $log_file, json_encode( $old_settings ), FS_CHMOD_FILE ) ) {
                update_option( 'whdi_' . $file_name, $old_settings );
            }

            if ( defined( 'WP_CLI' ) ) {
                WP_CLI::line( 'File generated at ' . $log_file );
            } else {
                wp_send_json_success();
            }
        }

        /**
         * Set reset data
         */
        function get_reset_data() {

            if ( ! defined( 'WP_CLI' ) ) {
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'manage_options' ) ) {
                    return;
                }
            }

            global $wpdb;

            $post_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_whdi_sites_imported_post'" );
            $form_ids = $wpdb->get_col( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key='_whdi_sites_imported_wp_forms'" );
            $term_ids = $wpdb->get_col( "SELECT term_id FROM {$wpdb->termmeta} WHERE meta_key='_whdi_sites_imported_term'" );

            $data = array(
                'reset_posts'    => $post_ids,
                'reset_wp_forms' => $form_ids,
                'reset_terms'    => $term_ids,
            );

            if ( defined( 'WP_CLI' ) ) {
                return $data;
            } else {
                wp_send_json_success( $data );
            }

        }
    }

    /**
     * Start this plugin by calling 'get_instance()' method
     */
    WHDI::get_instance();

endif;