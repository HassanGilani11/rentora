<?php
/**
 * Autoload
 *
 * @version 1.0.0
 * @package Multi-Vendor for WooCommerce\Admin\Meta Boxes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'MVR_Autoload' ) ) {
	/**
	 * Multi-Vendor for WooCommerce Autoloader.
	 */
	class MVR_Autoload {

		/**
		 * Path to the includes directory.
		 *
		 * @var String
		 */
		private $include_path = '';

		/**
		 * Path to the includes directory.
		 *
		 * @var String
		 */
		private $file = '';

		/**
		 * Construct MVR_Autoload
		 */
		public function __construct() {
			if ( function_exists( '__autoload' ) ) {
				spl_autoload_register( '__autoload' );
			}

			$this->include_path = MVR_ABSPATH . 'includes/';

			spl_autoload_register( array( $this, 'autoload' ) );
		}

		/**
		 * Take a class name and turn it into a file name.
		 *
		 * @since 1.0.0
		 * @param  string $class Class name.
		 * @return string
		 */
		private function get_file_name_from_class( $class ) {
			return 'class-' . str_replace( '_', '-', $class ) . '.php';
		}

		/**
		 * Include a class file.
		 *
		 * @since 1.0.0
		 * @return Boolean Successful or not.
		 */
		private function load_file() {
			if ( ! empty( $this->file ) && is_readable( $this->file ) ) {
				include_once $this->file;
				return true;
			}

			return false;
		}

		/**
		 * Auto-load our classes on demand to reduce memory consumption.
		 *
		 * @since 1.0.0
		 * @param string $class Class name.
		 */
		public function autoload( $class ) {
			$class = strtolower( $class );

			// Make sure our classes are going to load.
			if ( 0 !== strpos( $class, 'mvr_' ) ) {
				return;
			}

			$file = $this->get_file_name_from_class( $class ); // Retrieve file name from class name.
			$path = $this->include_path;

			if ( false !== strpos( $class, '_data_store' ) ) {
				$path = $this->include_path . 'data-stores/';
			} elseif ( false !== strpos( $class, 'meta_box_' ) ) {
				$path = $this->include_path . 'admin/meta-boxes/';
			} elseif ( false !== strpos( $class, 'compatible_' ) ) {
				$path = $this->include_path . 'compatibles/';
			} elseif ( false !== strpos( $class, '_shortcode_' ) ) {
				$path = $this->include_path . 'shortcodes/';
			} elseif ( in_array( $class, array_keys( mvr()->entity ), true ) ) {
				$path = $this->include_path . 'entity/';
			}

			// Include a class file.
			if ( is_readable( $path . $file ) ) {
				$this->file = $path . $file;
				$this->load_file();
			}
		}
	}

	new MVR_Autoload();
}
