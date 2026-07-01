<?php
/**
 * FMA Pure PHP Validator
 * 
 * Pure PHP syntax validation without any exec functions
 * Uses nikic/php-parser for accurate validation
 *
 * @package Advanced File Manager
 * @since 1.0.0
 */

if ( ! class_exists( 'FMA_Pure_PHP_Validator' ) ) :

	class FMA_Pure_PHP_Validator {

		private static $instance = null;
		private $parser = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->init_parser();
	}

	/**
	 * Initialize PHP Parser
	 */
	private function init_parser() {
		// Load Composer autoloader
		$autoload_path = dirname( __FILE__ ) . '/library/vendor/autoload.php';
		if ( file_exists( $autoload_path ) ) {
			require_once $autoload_path;
		}

		// Try to use nikic/php-parser if available
		if ( class_exists( 'PhpParser\\ParserFactory' ) ) {
			try {
				$factory = new \PhpParser\ParserFactory();
				$this->parser = $factory->createForHostVersion();
			} catch ( Exception $e ) {
				$this->parser = null;
			}
		}
	}

		/**
		 * Validate PHP syntax using pure PHP methods
		 */
		public function validate_syntax( $php_code ) {
			$result = array(
				'valid' => false,
				'errors' => array(),
				'message' => '',
				'php_version' => 'PHP ' . PHP_VERSION,
				'execution_time' => 0
			);

			$start_time = microtime( true );

			try {
				// Method 1: Try nikic/php-parser if available
				if ( $this->parser !== null ) {
					$parser_result = $this->validate_with_parser( $php_code );
					if ( $parser_result['valid'] || ! empty( $parser_result['errors'] ) ) {
						$result = $parser_result;
						$result['execution_time'] = round( ( microtime( true ) - $start_time ) * 1000, 2 );
						return $result;
					}
				}

				// Method 2: Enhanced basic syntax check
				$result = $this->validate_with_enhanced_check( $php_code );

			} catch ( Exception $e ) {
				// If all methods fail, fall back to basic check
				$result = $this->validate_with_enhanced_check( $php_code );
			}

			$result['execution_time'] = round( ( microtime( true ) - $start_time ) * 1000, 2 );
			return $result;
		}

		/**
		 * Validate using nikic/php-parser
		 */
		private function validate_with_parser( $php_code ) {
			$result = array(
				'valid' => false,
				'errors' => array(),
				'message' => '',
				'php_version' => 'PHP ' . PHP_VERSION,
				'execution_time' => 0
			);

			try {
				// Ensure code has proper PHP tags
				if ( strpos( $php_code, '<?php' ) === false && strpos( $php_code, '<?=' ) === false ) {
					$php_code = '<?php' . "\n" . $php_code;
				}

				$stmts = $this->parser->parse( $php_code );
				
				if ( $stmts === null ) {
					$result['valid'] = false;
					$result['message'] = 'PHP syntax errors found';
					$result['errors'][] = array(
						'type' => 'parse_error',
						'message' => 'Unable to parse PHP code',
						'file' => 'unknown',
						'line' => 0,
						'severity' => 'error'
					);
				} else {
					$result['valid'] = true;
					$result['message'] = 'PHP syntax is valid';
					$result['errors'] = array();
				}

			} catch ( \PhpParser\Error $e ) {
				$result['valid'] = false;
				$result['message'] = 'PHP syntax errors found';
				$result['errors'][] = array(
					'type' => 'parse_error',
					'message' => $e->getMessage(),
					'file' => 'unknown',
					'line' => $e->getStartLine(),
					'severity' => 'error'
				);
			}

			return $result;
		}

		/**
		 * Enhanced basic syntax check without exec functions
		 */
		private function validate_with_enhanced_check( $php_code ) {
			$result = array(
				'valid' => false,
				'errors' => array(),
				'message' => '',
				'php_version' => 'PHP ' . PHP_VERSION,
				'execution_time' => 0
			);

			// Ensure code has proper PHP tags
			if ( strpos( $php_code, '<?php' ) === false && strpos( $php_code, '<?=' ) === false ) {
				$php_code = '<?php' . "\n" . $php_code;
			}

			$errors = array();

			// Method 1: Use token_get_all
			$tokens = @token_get_all( $php_code );
			if ( $tokens === false ) {
				$errors[] = array(
					'type' => 'syntax_error',
					'message' => 'Invalid PHP syntax detected',
					'file' => 'unknown',
					'line' => 0,
					'severity' => 'error'
				);
			} else {
				// Method 2: Enhanced syntax analysis
				$syntax_errors = $this->analyze_syntax( $php_code );
				$errors = array_merge( $errors, $syntax_errors );
			}

			// Method 3: Try eval in safe way (last resort)
			if ( empty( $errors ) ) {
				$eval_errors = $this->safe_eval_check( $php_code );
				$errors = array_merge( $errors, $eval_errors );
			}

			if ( empty( $errors ) ) {
				$result['valid'] = true;
				$result['message'] = 'PHP syntax is valid';
				$result['errors'] = array();
			} else {
				$result['valid'] = false;
				$result['message'] = 'PHP syntax errors found';
				$result['errors'] = $errors;
			}

			return $result;
		}

		/**
		 * Enhanced syntax analysis
		 */
		private function analyze_syntax( $php_code ) {
			$errors = array();
			$lines = explode( "\n", $php_code );
			$line_number = 0;
			
			foreach ( $lines as $line ) {
				$line_number++;
				$trimmed = trim( $line );
				
				// Skip empty lines and comments
				if ( empty( $trimmed ) || strpos( $trimmed, '//' ) === 0 || strpos( $trimmed, '#' ) === 0 || strpos( $trimmed, '/*' ) === 0 ) {
					continue;
				}
				
				// Check for unclosed strings
				if ( $this->has_unclosed_string( $line ) ) {
					$errors[] = array(
						'type' => 'syntax_error',
						'message' => 'Unclosed string',
						'file' => 'unknown',
						'line' => $line_number,
						'severity' => 'error'
					);
				}
				
				// Check for unclosed brackets
				if ( $this->has_unclosed_brackets( $line ) ) {
					$errors[] = array(
						'type' => 'syntax_error',
						'message' => 'Unclosed brackets',
						'file' => 'unknown',
						'line' => $line_number,
						'severity' => 'error'
					);
				}

				// Check for common syntax errors
				$common_errors = $this->check_common_syntax_errors( $line, $line_number );
				$errors = array_merge( $errors, $common_errors );
			}
			
			// Check overall bracket balance
			$bracket_balance = $this->check_bracket_balance( $php_code );
			if ( $bracket_balance !== 0 ) {
				$errors[] = array(
					'type' => 'syntax_error',
					'message' => 'Unbalanced brackets',
					'file' => 'unknown',
					'line' => count( $lines ),
					'severity' => 'error'
				);
			}

			return $errors;
		}

		/**
		 * Check common syntax errors
		 */
		private function check_common_syntax_errors( $line, $line_number ) {
			$errors = array();
			$trimmed = trim( $line );

			// Skip empty lines, comments, and PHP tags
			if ( empty( $trimmed ) || 
				 strpos( $trimmed, '//' ) === 0 || 
				 strpos( $trimmed, '#' ) === 0 || 
				 strpos( $trimmed, '/*' ) === 0 ||
				 strpos( $trimmed, '*/' ) === 0 ||
				 strpos( $trimmed, '<?php' ) === 0 ||
				 strpos( $trimmed, '<?=' ) === 0 ||
				 strpos( $trimmed, '?>' ) === 0 ) {
				return $errors;
			}

			// Skip control structures that don't need semicolons
			if ( preg_match( '/^(if|else|for|while|foreach|function|class|interface|trait|switch|case|default|try|catch|finally)\s*[\(:]?/', $trimmed ) ||
				 preg_match( '/^[\s]*[{}][\s]*$/', $trimmed ) ||
				 preg_match( '/^[\s]*\/\//', $trimmed ) ||
				 preg_match( '/^[\s]*\/\*/', $trimmed ) ||
				 preg_match( '/^[\s]*\*/', $trimmed ) ) {
				return $errors;
			}

			// Check for missing semicolons only on statements that should have them
			if ( preg_match( '/^[^=]*[^;{}]\s*$/', $trimmed ) && 
				 ! preg_match( '/^[\s]*[{}][\s]*$/', $trimmed ) &&
				 ! preg_match( '/^[\s]*\/\//', $trimmed ) &&
				 ! preg_match( '/^[\s]*\/\*/', $trimmed ) &&
				 ! preg_match( '/^[\s]*\*/', $trimmed ) ) {
				
				// More sophisticated check - only flag if it looks like a statement
				if ( preg_match( '/^(echo|print|return|break|continue|throw|include|require|unset|isset|empty)\s+/', $trimmed ) ||
					 preg_match( '/\$[a-zA-Z_][a-zA-Z0-9_]*\s*[=]/', $trimmed ) ||
					 preg_match( '/[a-zA-Z_][a-zA-Z0-9_]*\s*\(/', $trimmed ) ||
					 preg_match( '/^\s*(private|public|protected|var|const)\s+/', $trimmed ) ) {
					$errors[] = array(
						'type' => 'syntax_error',
						'message' => 'Missing semicolon',
						'file' => 'unknown',
						'line' => $line_number,
						'severity' => 'error'
					);
				}
			}

			// Check for unclosed parentheses
			$open_parens = substr_count( $line, '(' );
			$close_parens = substr_count( $line, ')' );
			if ( $open_parens !== $close_parens ) {
				$errors[] = array(
					'type' => 'syntax_error',
					'message' => 'Unclosed parentheses',
					'file' => 'unknown',
					'line' => $line_number,
					'severity' => 'error'
				);
			}

			return $errors;
		}

		/**
		 * Safe eval check (last resort)
		 */
		private function safe_eval_check( $php_code ) {
			$errors = array();

			// Only try eval if it's safe and enabled
			if ( function_exists( 'eval' ) && ! in_array( 'eval', explode( ',', ini_get( 'disable_functions' ) ) ) ) {
				// Create a safe wrapper
				$safe_code = '<?php ' . $php_code;
				
				// Use output buffering to catch any errors
				ob_start();
				$old_error_handler = set_error_handler( function( $severity, $message, $file, $line ) use ( &$errors ) {
					$errors[] = array(
						'type' => 'eval_error',
						'message' => $message,
						'file' => 'unknown',
						'line' => $line,
						'severity' => 'error'
					);
					return true; // Don't execute PHP internal error handler
				});

				try {
					@eval( $safe_code );
				} catch ( ParseError $e ) {
					$errors[] = array(
						'type' => 'parse_error',
						'message' => $e->getMessage(),
						'file' => 'unknown',
						'line' => $e->getLine(),
						'severity' => 'error'
					);
				} catch ( Error $e ) {
					$errors[] = array(
						'type' => 'fatal_error',
						'message' => $e->getMessage(),
						'file' => 'unknown',
						'line' => $e->getLine(),
						'severity' => 'error'
					);
				}

				ob_end_clean();
				restore_error_handler();
			}

			return $errors;
		}

		/**
		 * Check if line has unclosed string
		 */
		private function has_unclosed_string( $line ) {
			$in_string = false;
			$string_char = '';
			$escaped = false;
			
			for ( $i = 0; $i < strlen( $line ); $i++ ) {
				$char = $line[$i];
				
				if ( $escaped ) {
					$escaped = false;
					continue;
				}
				
				if ( $char === '\\' ) {
					$escaped = true;
					continue;
				}
				
				if ( ( $char === '"' || $char === "'" ) && ! $in_string ) {
					$in_string = true;
					$string_char = $char;
				} elseif ( $char === $string_char && $in_string ) {
					$in_string = false;
					$string_char = '';
				}
			}
			
			return $in_string;
		}

		/**
		 * Check if line has unclosed brackets
		 */
		private function has_unclosed_brackets( $line ) {
			// Don't check single lines for bracket balance - this is handled globally
			return false;
		}

		/**
		 * Check overall bracket balance
		 */
		private function check_bracket_balance( $code ) {
			$balance = 0;
			$in_string = false;
			$string_char = '';
			$escaped = false;
			
			for ( $i = 0; $i < strlen( $code ); $i++ ) {
				$char = $code[$i];
				
				if ( $escaped ) {
					$escaped = false;
					continue;
				}
				
				if ( $char === '\\' ) {
					$escaped = true;
					continue;
				}
				
				if ( ( $char === '"' || $char === "'" ) && ! $in_string ) {
					$in_string = true;
					$string_char = $char;
				} elseif ( $char === $string_char && $in_string ) {
					$in_string = false;
					$string_char = '';
				} elseif ( ! $in_string ) {
					switch ( $char ) {
						case '{':
						case '(':
						case '[':
							$balance++;
							break;
						case '}':
						case ')':
						case ']':
							$balance--;
							break;
					}
				}
			}
			
			return $balance;
		}

		/**
		 * Test if validation is available
		 */
		public function is_available() {
			try {
				$test_code = '<?php echo "test";';
				$result = $this->validate_syntax( $test_code );
				return $result['valid'];
			} catch ( Exception $e ) {
				return false;
			}
		}

		/**
		 * Get validator info
		 */
		public function get_validator_info() {
			return array(
				'type' => 'Pure PHP Validator',
				'parser_available' => $this->parser !== null,
				'php_version' => PHP_VERSION,
				'exec_functions_required' => false,
				'features' => array(
					'Enhanced syntax checking',
					'No exec functions required',
					'Multiple validation methods',
					'Detailed error reporting'
				)
			);
		}
	}

endif;
