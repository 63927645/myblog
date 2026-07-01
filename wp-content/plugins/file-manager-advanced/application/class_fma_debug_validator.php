<?php
/**
 * FMA Debug Validator
 * 
 * Robust PHP syntax validation using exec-with-fallback library
 * for better compatibility across different hosting environments
 *
 * @package Advanced File Manager
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

// Load Pure PHP validator (no exec functions required)
if ( file_exists( plugin_dir_path( __FILE__ ) . 'class_fma_pure_php_validator.php' ) ) {
    require_once plugin_dir_path( __FILE__ ) . 'class_fma_pure_php_validator.php';
}

if ( ! class_exists( 'FMA_Debug_Validator' ) ) :

	/**
	 * Class FMA_Debug_Validator
	 *
	 * @since 1.0.0
	 */
	class FMA_Debug_Validator {

		/**
		 * Instance of this class
		 *
		 * @since 1.0.0
		 * @var FMA_Debug_Validator
		 */
		private static $instance = null;

		/**
		 * PHP executable path
		 *
		 * @since 1.0.0
		 * @var string
		 */
		private $php_path = 'php';

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		private function __construct() {
		}

		/**
		 * Get instance
		 *
		 * @since 1.0.0
		 * @return FMA_Debug_Validator
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Validate PHP syntax
		 *
		 * @since 1.0.0
		 * @param string $php_code PHP code to validate
		 * @return array Validation result with success status and errors
		 */
		public function validate_syntax( $php_code ) {
			// Use Pure PHP Validator (no exec functions required)
			if ( class_exists( 'FMA_Pure_PHP_Validator' ) ) {
				$pure_validator = FMA_Pure_PHP_Validator::get_instance();
				return $pure_validator->validate_syntax( $php_code );
			}

			// Fallback to original method
			$result = array(
				'valid' => false,
				'errors' => array(),
				'message' => '',
				'php_version' => '',
				'execution_time' => 0
			);

			$start_time = microtime( true );

			try {
				// Create temporary file
				$temp_file = $this->create_temp_file( $php_code );
				if ( ! $temp_file ) {
					$result['errors'][] = 'Unable to create temporary file for validation';
					$result['message'] = 'PHP syntax errors found';
					return $result;
				}

				// Run PHP syntax check
				$output = array();
				$return_code = 0;
				$command = $this->php_path . ' -l "' . $temp_file . '" 2>&1';
				
				// Use exec-with-fallback if available
				if ( class_exists( 'ExecWithFallback\\ExecWithFallback' ) ) {
					$exec_result = \ExecWithFallback\ExecWithFallback::exec( $command, $output, $return_code );
				} else {
					// Fallback to regular exec
					$exec_result = exec( $command, $output, $return_code );
				}
				
				// Get PHP version
				$version_output = array();
				if ( class_exists( 'ExecWithFallback\\ExecWithFallback' ) ) {
					\ExecWithFallback\ExecWithFallback::exec( $this->php_path . ' -v', $version_output );
				} else {
					exec( $this->php_path . ' -v', $version_output );
				}
				$result['php_version'] = ! empty( $version_output ) ? $version_output[0] : 'PHP ' . PHP_VERSION;

				// Parse results
				if ( $return_code === 0 ) {
					$result['valid'] = true;
					$result['message'] = 'PHP syntax is valid';
					$result['errors'] = array();
				} else {
					$result['valid'] = false;
					$result['message'] = 'PHP syntax errors found';
					$result['errors'] = $this->parse_syntax_errors( $output );
				}

				// Clean up
				$this->cleanup_temp_file( $temp_file );

			} catch ( Exception $e ) {
				$result['errors'][] = 'Validation failed: ' . $e->getMessage();
				$result['message'] = 'PHP syntax errors found';
			}

			$result['execution_time'] = round( ( microtime( true ) - $start_time ) * 1000, 2 );

			return $result;
		}

		/**
		 * Create temporary file with PHP code
		 *
		 * @since 1.0.0
		 * @param string $php_code PHP code
		 * @return string|false Temporary file path or false on failure
		 */
		private function create_temp_file( $php_code ) {
			// Ensure code has proper PHP tags
			if ( strpos( $php_code, '<?php' ) === false && strpos( $php_code, '<?=' ) === false ) {
				$php_code = '<?php' . "\n" . $php_code;
			}

			$temp_file = tempnam( sys_get_temp_dir(), 'fma_php_validation_' );
			if ( $temp_file === false ) {
				return false;
			}

			$result = file_put_contents( $temp_file, $php_code );
			if ( $result === false ) {
				unlink( $temp_file );
				return false;
			}

			return $temp_file;
		}

		/**
		 * Clean up temporary file
		 *
		 * @since 1.0.0
		 * @param string $temp_file Temporary file path
		 */
		private function cleanup_temp_file( $temp_file ) {
			if ( file_exists( $temp_file ) ) {
				unlink( $temp_file );
			}
		}

		/**
		 * Parse syntax errors from PHP output
		 *
		 * @since 1.0.0
		 * @param array $output PHP command output
		 * @return array Parsed errors
		 */
		private function parse_syntax_errors( $output ) {
			$errors = array();
			$output_text = implode( "\n", $output );

			// Parse PHP syntax errors
			if ( preg_match( '/Parse error:\s*(.+?)\s+in\s+(.+?)\s+on\s+line\s+(\d+)/i', $output_text, $matches ) ) {
				$errors[] = array(
					'type' => 'parse_error',
					'message' => trim( $matches[1] ),
					'file' => basename( $matches[2] ),
					'line' => intval( $matches[3] ),
					'severity' => 'error'
				);
			}

			// Parse fatal errors
			if ( preg_match( '/Fatal error:\s*(.+?)\s+in\s+(.+?)\s+on\s+line\s+(\d+)/i', $output_text, $matches ) ) {
				$errors[] = array(
					'type' => 'fatal_error',
					'message' => trim( $matches[1] ),
					'file' => basename( $matches[2] ),
					'line' => intval( $matches[3] ),
					'severity' => 'error'
				);
			}

			// Parse warnings
			if ( preg_match( '/Warning:\s*(.+?)\s+in\s+(.+?)\s+on\s+line\s+(\d+)/i', $output_text, $matches ) ) {
				$errors[] = array(
					'type' => 'warning',
					'message' => trim( $matches[1] ),
					'file' => basename( $matches[2] ),
					'line' => intval( $matches[3] ),
					'severity' => 'warning'
				);
			}

			// If no specific errors found, add generic error
			if ( empty( $errors ) && ! empty( $output_text ) ) {
				$errors[] = array(
					'type' => 'unknown',
					'message' => trim( $output_text ),
					'file' => 'unknown',
					'line' => 0,
					'severity' => 'error'
				);
			}

			return $errors;
		}

		/**
		 * Get PHP configuration info
		 *
		 * @since 1.0.0
		 * @return array PHP configuration
		 */
		public function get_php_info() {
			$info = array(
				'php_path' => $this->php_path,
				'php_version' => PHP_VERSION,
				'php_binary' => PHP_BINARY,
				'exec_functions' => array(),
				'disabled_functions' => array(),
				'exec_with_fallback_available' => class_exists( 'ExecWithFallback\\ExecWithFallback' )
			);

			// Check available exec functions
			$exec_functions = array( 'exec', 'system', 'shell_exec', 'passthru', 'proc_open', 'popen' );
			foreach ( $exec_functions as $func ) {
				$info['exec_functions'][ $func ] = function_exists( $func ) && ! in_array( $func, explode( ',', ini_get( 'disable_functions' ) ) );
			}

			// Get disabled functions
			$disabled = ini_get( 'disable_functions' );
			$info['disabled_functions'] = ! empty( $disabled ) ? explode( ',', $disabled ) : array();

			return $info;
		}

		/**
		 * Test if validation is available
		 *
		 * @since 1.0.0
		 * @return bool
		 */
		public function is_available() {
			try {
				$test_code = '<?php echo "test";';
				$result = $this->validate_syntax( $test_code );
				return $result['success'];
			} catch ( Exception $e ) {
				return false;
			}
		}
	}

endif;
