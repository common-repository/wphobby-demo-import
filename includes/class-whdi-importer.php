<?php
/**
 * WHDI Importer Class
 *
 * Plugin Importer Class
 *
 * @since  1.0.0
 * @package WPHobby Demo Import
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'WHDI_Importer' ) ) :

	/**
	 * WHDI Importer
	 */
	class WHDI_Importer {

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
		public function __construct() {

            require_once WHDI_DIR . '/includes/class-whdi-importer-log.php';
            require_once WHDI_DIR . '/includes/importers/class-whdi-customizer-option.php';
            require_once WHDI_DIR . '/includes/importers/class-whdi-customizer-import.php';
            require_once WHDI_DIR . '/includes/importers/class-whdi-helper.php';
            require_once WHDI_DIR . '/includes/importers/class-whdi-options-import.php';
            require_once WHDI_DIR . '/includes/importers/class-widgets-importer.php';


            // Import AJAX.
			add_action( 'wp_ajax_whdi-import-set-site-data', array( $this, 'import_start' ) );
            add_action( 'wp_ajax_whdi-import-wpforms', array( $this, 'import_wpforms' ) );
            add_action( 'wp_ajax_whdi-import-customizer-settings', array( $this, 'import_customizer_settings' ) );
            add_action( 'wp_ajax_whdi-import-prepare-xml', array( $this, 'prepare_xml_data' ) );
            add_action( 'wp_ajax_whdi-import-options', array( $this, 'import_options' ) );
            add_action( 'wp_ajax_whdi-import-widgets', array( $this, 'import_widgets' ) );
            add_action( 'wp_ajax_whdi-import-end', array( $this, 'import_end' ) );

            // Reset Post & Terms.
            add_action( 'wp_ajax_whdi-sites-delete-posts', array( $this, 'delete_imported_posts' ) );
            add_action( 'wp_ajax_whdi-sites-delete-wp-forms', array( $this, 'delete_imported_wp_forms' ) );

            add_action( 'init', array( $this, 'load_importer' ) );

            // Batch process after wxr importer finished
            require_once WHDI_DIR . '/includes/importers/batch-processing/class-whdi-batch-processing.php';

        }

		/**
		 * Start Site Import
		 *
		 * @since 1.1.0
		 * @return void
		 */
		function import_start() {

			// Verify Nonce.
			check_ajax_referer( 'whdi', '_ajax_nonce' );

			if ( ! current_user_can( 'customize' ) ) {
				wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
			}

			$demo_api_uri = isset( $_POST['api_url'] ) ? esc_url( $_POST['api_url'] ) : '';


			if ( ! empty( $demo_api_uri ) ) {

				$demo_data = self::get_single_demo( $demo_api_uri );
				update_option( 'whdi_import_data', $demo_data );

				if ( is_wp_error( $demo_data ) ) {
					wp_send_json_error( $demo_data->get_error_message() );
				} else {
					$log_file = WHDI_Importer_Log::add_log_file_url();
					if ( isset( $log_file['url'] ) && ! empty( $log_file['url'] ) ) {
						$demo_data['log_file'] = $log_file['url'];
					}
					do_action( 'whdi_import_start', $demo_data, $demo_api_uri );
				}

				wp_send_json_success( $demo_data );

			} else {
				wp_send_json_error( __( 'Request site API URL is empty. Try again!', 'wphobby-demo-import' ) );
			}

		}

        /**
         * Get single demo.
         *
         * @since  1.0.0
         *
         * @param  (String) $demo_api_uri API URL of a demo.
         *
         * @return (Array) $whdi_demo_data demo data for the demo.
         */
        public static function get_single_demo( $demo_api_uri ) {

            $api_args = apply_filters(
                'whdi_sites_api_args',
                array(
                    'timeout' => 15,
                )
            );

            // API Call.
            $response = wp_remote_get( $demo_api_uri, $api_args );

            if ( is_wp_error( $response ) || ( isset( $response->status ) && 0 === $response->status ) ) {
                if ( isset( $response->status ) ) {
                    $data = json_decode( $response, true );
                } else {
                    return new WP_Error( 'api_invalid_response_code', $response->get_error_message() );
                }
            }

            if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
                return new WP_Error( 'api_invalid_response_code', wp_remote_retrieve_body( $response ) );
            } else {
                $data = json_decode( wp_remote_retrieve_body( $response ), true );
            }

            $data = json_decode( wp_remote_retrieve_body( $response ), true );

            return $data;
        }

        /**
         * Import Customizer Settings.
         *
         * @since 1.0.14
         * @since 1.4.0  The `$customizer_data` was added.
         *
         * @param  array $customizer_data Customizer Data.
         * @return void
         */
        function import_customizer_settings( $customizer_data = array() ) {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }
            $customizer_data = ( isset( $_POST['customizer_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['customizer_data'] ), 1 ) : $customizer_data;

            if ( ! empty( $customizer_data ) ) {

                WHDI_Importer_Log::add( 'Imported Customizer Settings ' . $customizer_data);

                // Set meta for tracking the post.
                update_option( '_whdi_old_customizer_data', $customizer_data );

                WHDI_Customizer_Import::instance()->import( $customizer_data );

                if ( defined( 'WP_CLI' ) ) {
                    WP_CLI::line( 'Imported Customizer Settings!' );
                } else {
                    wp_send_json_success( $customizer_data );
                }
            } else {
                if ( defined( 'WP_CLI' ) ) {
                    WP_CLI::line( 'Customizer data is empty!' );
                } else {
                    wp_send_json_error( __( 'Customizer data is empty!', 'wphobby-demo-import' ) );
                }
            }

        }

        /**
         * Prepare XML Data.
         *
         * @since 1.1.0
         * @return void
         */
        function prepare_xml_data() {

            // Verify Nonce.
            check_ajax_referer( 'whdi', '_ajax_nonce' );

            if ( ! current_user_can( 'customize' ) ) {
                wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
            }

            if ( ! class_exists( 'XMLReader' ) ) {
                wp_send_json_error( __( 'If XMLReader is not available, it imports all other settings and only skips XML import. This creates an incomplete website. We should bail early and not import anything if this is not present.', 'wphobby-demo-import' ) );
            }

            $wxr_url = ( isset( $_REQUEST['wxr_url'] ) ) ? urldecode( $_REQUEST['wxr_url'] ) : '';

            if ( isset( $wxr_url ) ) {

                WHDI_Importer_Log::add( 'Importing from XML ' . $wxr_url );

                // Download XML file.
                $xml_path = WHDI_Helper::download_file( $wxr_url );

                if ( $xml_path['success'] ) {
                    if ( isset( $xml_path['data']['file'] ) ) {
                        $data        = WHDI_WXR_Importer::instance()->get_xml_data( $xml_path['data']['file'] );
                        $data['xml'] = $xml_path['data'];
                        wp_send_json_success( $data );
                    } else {
                        wp_send_json_error( __( 'There was an error downloading the XML file.', 'wphobby-demo-import' ) );
                    }
                } else {
                    wp_send_json_error( $xml_path['data'] );
                }
            } else {
                wp_send_json_error( __( 'Invalid site XML file!', 'wphobby-demo-import' ) );
            }

        }

        /**
         * Load WordPress WXR importer.
         */
        public function load_importer() {
            require_once WHDI_DIR . '/includes/importers/wxr-importer/class-whdi-wxr-importer.php';
        }

        /**
         * Import Options.
         *
         * @since 1.0.14
         * @since 1.4.0 The `$options_data` was added.
         *
         * @param  array $options_data Site Options.
         * @return void
         */
        function import_options( $options_data = array() ) {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }

            $options_data = ( isset( $_POST['options_data'] ) ) ? (array) json_decode( stripcslashes( $_POST['options_data'] ), 1 ) : $options_data;

            if ( ! empty( $options_data ) ) {
                // Set meta for tracking the post.
                if ( is_array( $options_data ) ) {
                    WHDI_Importer_Log::add( 'Imported - Site Options ' . json_encode( $options_data ) );
                    update_option( '_whdi_old_site_options', $options_data );
                }

                $options_importer = WHDI_Options_Import::instance();
                $options_importer->import_options( $options_data );
                if ( defined( 'WP_CLI' ) ) {
                    WP_CLI::line( 'Imported Site Options!' );
                } else {
                    wp_send_json_success( $options_data );
                }
            } else {
                if ( defined( 'WP_CLI' ) ) {
                    WP_CLI::line( 'Site options are empty!' );
                } else {
                    wp_send_json_error( __( 'Site options are empty!', 'wphobby-demo-import' ) );
                }
            }

        }

        /**
         * Import Widgets.
         *
         * @since 1.0.14
         * @since 1.4.0 The `$widgets_data` was added.
         *
         * @param  string $widgets_data Widgets Data.
         * @return void
         */
        function import_widgets( $widgets_data = '' ) {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }

            $widgets_data = ( isset( $_POST['widgets_data'] ) ) ? (object) json_decode( stripslashes( $_POST['widgets_data'] ) ) : (object) $widgets_data;

            WHDI_Importer_Log::add( 'Imported - Widgets ' . json_encode( $widgets_data ) );

            if ( ! empty( $widgets_data ) ) {

                $widgets_importer = WHDI_Widget_Importer::instance();
                $status           = $widgets_importer->import( $widgets_data );

                // Set meta for tracking the post.
                if ( is_object( $widgets_data ) ) {
                    $widgets_data = (array) $widgets_data;
                    update_option( '_whdi_old_widgets_data', $widgets_data );
                }

                if ( defined( 'WP_CLI' ) ) {
                    WP_CLI::line( 'Widget Imported!' );
                } else {
                    wp_send_json_success( $widgets_data );
                }
            } else {
                if ( defined( 'WP_CLI' ) ) {
                    WP_CLI::line( 'Widget data is empty!' );
                } else {
                    wp_send_json_error( __( 'Widget data is empty!', 'wphobby-demo-import' ) );
                }
            }

        }

        /**
         * Import End.
         *
         * @since 1.0.14
         * @return void
         */
        function import_end() {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }

            // update taxonomies(categories/tags) count field after bulk import
            global $wpdb;
            $sql = "UPDATE wp_term_taxonomy SET count = (
                    SELECT COUNT(*) FROM wp_term_relationships rel
                    LEFT JOIN wp_posts po ON (po.ID = rel.object_id)
                    WHERE
                    rel.term_taxonomy_id = wp_term_taxonomy.term_taxonomy_id
                    AND
                    wp_term_taxonomy.taxonomy NOT IN ('link_category')
                    AND
                    po.post_status IN ('publish', 'future')
                   )";
            $result = $wpdb->query($sql);

            if(false === $result ){
                // Write error to log file.
                WHDI_Importer_Log::add( 'Update taxonomies failed!');
            } else {
                // Add this message to log file.
                WHDI_Importer_Log::add( 'Update taxonomies success!');
            }


            do_action( 'whdi_import_complete' );
        }

        /**
         * Reset customizer data
         *
         * @since 1.0.9
         * @return void
         */
        function reset_customizer_data() {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }

            WHDI_Importer_Log::add( 'Deleted customizer Settings ' . json_encode( get_option( 'whdi-settings', array() ) ) );

            delete_option( 'whdi-settings' );

            if ( defined( 'WP_CLI' ) ) {
                WP_CLI::line( 'Deleted Customizer Settings!' );
            } else {
                wp_send_json_success();
            }
        }

        /**
         * Delete imported posts
         *
         * @since 1.3.0
         * @since 1.4.0 The `$post_id` was added.
         *
         * @param  integer $post_id Post ID.
         * @return void
         */
        function delete_imported_posts( $post_id = 0 ) {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }

            $post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : $post_id;

            $message = 'Deleted - Post ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );

            $message = '';
            if ( $post_id ) {
                $message = 'Deleted - Post ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );

                WHDI_Importer_Log::add( $message );
                wp_delete_post( $post_id, true );
            }

            if ( defined( 'WP_CLI' ) ) {
                WP_CLI::line( $message );
            } else {
                wp_send_json_success( $message );
            }
        }

        /**
         * Delete imported WP forms
         *
         * @since 1.3.0
         * @since 1.4.0 The `$post_id` was added.
         *
         * @param  integer $post_id Post ID.
         * @return void
         */
        function delete_imported_wp_forms( $post_id = 0 ) {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }

            $post_id = isset( $_REQUEST['post_id'] ) ? absint( $_REQUEST['post_id'] ) : $post_id;

            $message = '';
            if ( $post_id ) {
                $message = 'Deleted - Form ID ' . $post_id . ' - ' . get_post_type( $post_id ) . ' - ' . get_the_title( $post_id );
                WHDI_Importer_Log::add( $message );
                wp_delete_post( $post_id, true );
            }

            if ( defined( 'WP_CLI' ) ) {
                WP_CLI::line( $message );
            } else {
                wp_send_json_success( $message );
            }
        }

        /**
         * Import WP Forms
         *
         * @since 1.0.9
         *
         * @param  string $wpforms_url WP Forms JSON file URL.
         * @return void
         */
        function import_wpforms( $wpforms_url = '' ) {

            if ( ! defined( 'WP_CLI' ) ) {
                // Verify Nonce.
                check_ajax_referer( 'whdi', '_ajax_nonce' );

                if ( ! current_user_can( 'customize' ) ) {
                    wp_send_json_error( __( 'You are not allowed to perform this action', 'wphobby-demo-import' ) );
                }
            }

            $wpforms_url = ( isset( $_REQUEST['wpforms_url'] ) ) ? urldecode( $_REQUEST['wpforms_url'] ) : $wpforms_url;
            $ids_mapping = array();

            if ( ! empty( $wpforms_url ) && function_exists( 'wpforms_encode' ) ) {

                // Download XML file.
                $xml_path = WHDI_Helper::download_file( $wpforms_url );

                if ( $xml_path['success'] ) {
                    if ( isset( $xml_path['data']['file'] ) ) {

                        $ext = strtolower( pathinfo( $xml_path['data']['file'], PATHINFO_EXTENSION ) );

                        if ( 'json' === $ext ) {
                            $forms = json_decode( file_get_contents( $xml_path['data']['file'] ), true );

                            if ( ! empty( $forms ) ) {

                                foreach ( $forms as $form ) {
                                    $title = ! empty( $form['settings']['form_title'] ) ? $form['settings']['form_title'] : '';
                                    $desc  = ! empty( $form['settings']['form_desc'] ) ? $form['settings']['form_desc'] : '';

                                    $new_id = post_exists( $title );

                                    if ( ! $new_id ) {
                                        $new_id = wp_insert_post(
                                            array(
                                                'post_title'   => $title,
                                                'post_status'  => 'publish',
                                                'post_type'    => 'wpforms',
                                                'post_excerpt' => $desc,
                                            )
                                        );

                                        if ( defined( 'WP_CLI' ) ) {
                                            WP_CLI::line( 'Imported Form ' . $title );
                                        }

                                        // Set meta for tracking the post.
                                        update_post_meta( $new_id, '_whdi_sites_imported_wp_forms', true );
                                        WHDI_Importer_Log::add( 'Inserted WP Form ' . $new_id );
                                    }

                                    if ( $new_id ) {

                                        // ID mapping.
                                        $ids_mapping[ $form['id'] ] = $new_id;

                                        $form['id'] = $new_id;
                                        wp_update_post(
                                            array(
                                                'ID' => $new_id,
                                                'post_content' => wpforms_encode( $form ),
                                            )
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            update_option( 'whdi_sites_wpforms_ids_mapping', $ids_mapping );

            if ( defined( 'WP_CLI' ) ) {
                WP_CLI::line( 'WP Forms Imported.' );
            } else {
                wp_send_json_success( $ids_mapping );
            }
        }
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	WHDI_Importer::get_instance();

endif;


