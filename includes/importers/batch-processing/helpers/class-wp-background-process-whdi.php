<?php
/**
 * Image Background Process
 *
 * @package WPHobby Demo Import
 * @since 1.0.11
 */

if ( class_exists( 'WP_Background_Process' ) ) :

	/**
	 * Image Background Process
	 *
	 * @since 1.0.11
	 */
	class WP_Background_Process_WHDI extends WP_Background_Process {

		/**
		 * Image Process
		 *
		 * @var string
		 */
		protected $action = 'image_process';

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @since 1.0.11
		 *
		 * @param object $process Queue item object.
		 * @return mixed
		 */
		protected function task( $process ) {

			if ( method_exists( $process, 'import' ) ) {
				$process->import();
			}

			return false;
		}

		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 *
		 * @since 1.0.11
		 */
		protected function complete() {

			parent::complete();

			WHDI_Importer_Log::add( 'Batch Process Complete!' );

			// Delete Log file.
			delete_option( 'whdi_sites_recent_import_log_file' );

			do_action( 'whdi_sites_image_import_complete' );
		}

	}

endif;
