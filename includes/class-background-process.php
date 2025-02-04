<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * A simplified abstract background process class.
 */
if ( ! class_exists( 'WP_Background_Process' ) ) {
	abstract class WP_Background_Process {

		/**
		 * The action name.
		 *
		 * @var string
		 */
		protected $action = 'background_process';

		/**
		 * The queue items.
		 *
		 * @var array
		 */
		protected $queue = array();

		/**
		 * Transient key to store the queue.
		 *
		 * @var string
		 */
		protected $transient_key = '';

		public function __construct() {
			$this->transient_key = 'wpbp_' . $this->action;
			add_action( 'admin_init', array( $this, 'handle' ) );
		}

		/**
		 * Push data to the queue.
		 *
		 * @param mixed $data Data to process.
		 * @return $this
		 */
		public function push_to_queue( $data ) {
			$this->queue[] = $data;
			return $this;
		}

		/**
		 * Save the queue in a transient.
		 *
		 * @return $this
		 */
		public function save() {
			set_transient( $this->transient_key, $this->queue, 60 * 60 );
			return $this;
		}

		/**
		 * Dispatch the background process.
		 */
		public function dispatch() {
			if ( ! wp_next_scheduled( $this->action . '_cron' ) ) {
				wp_schedule_single_event( time() + 10, $this->action . '_cron' );
			}
		}

		/**
		 * Handle the background process.
		 */
		public function handle() {
			$queue = get_transient( $this->transient_key );
			if ( false !== $queue ) {
				foreach ( $queue as $item ) {
					$this->task( $item );
				}
				delete_transient( $this->transient_key );
			}
		}

		/**
		 * The task to process a single item.
		 *
		 * @param mixed $data The data to process.
		 * @return mixed
		 */
		abstract protected function task( $data );
	}
}
