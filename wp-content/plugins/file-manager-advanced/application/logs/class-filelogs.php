<?php
/**
 * File Logs
 *
 * @package Advanced File Manager Pro
 */

namespace AFMP\Modules;

defined( 'ABSPATH' ) || exit;

use AFMP\Modules\FileLogs\FileLogModel;

if ( ! class_exists( 'AFMP\\Modules\\FileLogs' ) ) :
	/**
	 * Class FileLogs.
	 *
	 * @since 2.8
	 */
	class FileLogs {

		/**
		 * FileLogs Instance.
		 *
		 * @since 2.8
		 * @var FileLogs $instance The single instance of the class.
		 */
		private static $instance = null;


        /**
         * FileLogs constructor.
         *
         * @since 2.8
         */
		private function __construct() {
			add_filter( 'fma__opts_override', array( $this, 'opts_override' ), 1000 );
			add_action( 'admin_init', array( $this, 'admin_init' ) );

			$this->include_files();
		}

		/**
		 * Override options.
		 *
		 * @param array $opts Options.
		 * @since 2.8
		 *
		 * @return array
		 */
		public function opts_override( $opts ) {
			$opts['bind']['mkdir mkfile rename duplicate upload rm paste put'] = array( $this, 'write_log' );
			return $opts;
		}

		/**
		 * Write log.
		 *
		 * @param string $cmd Command.
		 * @param array $result Result.
		 * @param array $args Arguments.
		 * @param \elFinder $elfinder elFinder instance.
		 * @param \elFinderVolumeLocalFileSystem $volume Volume instance.
		 *
		 * @since 2.8
		 */
		public function write_log( $cmd, $result, $args, $elfinder, $volume ) {
			if ( ! empty( $result['removed'] ) ) {
				foreach ( $result['removed'] as $file ) {
					$this->check_performed_action( $cmd, $elfinder, $file, 'removed' );
				}
			} elseif ( ! empty( $result['added'] ) ) {
				foreach ( $result['added'] as $file ) {
					$this->check_performed_action( $cmd, $elfinder, $file );
				}
			} elseif ( ! empty( $result['changed'] ) ) {
				foreach ( $result['changed'] as $file ) {
					$this->check_performed_action( $cmd, $elfinder, $file );
				}
			}
		}

        /**
         * Check performed action.
         *
         * @since 2.8
         * @param string    $cmd Command.
         * @param \elFinder $elfinder elFinder instance.
         * @param array     $file File data.
         * @param string    $action Action performed (added, removed, etc.).
         */
		private function check_performed_action( $cmd, $elfinder, $file, $action = '' ) {
			$file_path = 'removed' === $action ? $file['realpath'] : $elfinder->realpath( $file['hash'] );

			switch ( $cmd ) {
				case 'rename':
					FileLogModel::insert_item( 'renamed', $file_path, $file );
					break;
				case 'duplicate':
					FileLogModel::insert_item( 'duplicated', $file_path, $file );
					break;
				case 'upload':
					FileLogModel::insert_item( 'uploaded', $file_path, $file );
					break;
				case 'mkdir':
				case 'mkfile':
					FileLogModel::insert_item( 'created', $file_path, $file );
					break;
				case 'rm':
					FileLogModel::insert_item( 'deleted', $file_path, $file );
                    break;
				case 'paste':
					FileLogModel::insert_item( 'pasted', $file_path, $file );
					break;
				case 'put':
					FileLogModel::insert_item( 'updated', $file_path, $file );
					break;
				default:
					FileLogModel::insert_item( sprintf( 'Unknown: %s', $cmd ), $file_path, $file );
					break;
			}
		}

		/**
		 * Admin init.
		 *
		 * @since 2.8
		 */
		public function admin_init() {
			FileLogModel::create_table();
		}

        /**
         * Include required files.
         *
         * @since 2.8
         */
		private function include_files() {
			require_once plugin_dir_path( __FILE__ ) . 'filelogs/class-filelogmodel.php';
		}

		/**
		 * FileLogs get instance.
		 *
		 * @since 2.8
		 * @return FileLogs
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new FileLogs();
			}

			return self::$instance;
		}
	}

	FileLogs::get_instance();
endif;