<?php
/**
 * File Manager Advanced - PHP Debug Analyzer
 * 
 * This class provides PHP code analysis using nikic/php-parser
 * It's designed to be conflict-free with other file managers
 * 
 * @package FileManagerAdvanced
 * @since 1.0.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Load composer autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

class FMA_PhpDebugAnalyzer {
    
    /**
     * Analyze PHP code and return debug information
     *
     * @param string $php_code The PHP code to analyze
     * @param string $filename The filename for context
     * @return array Debug analysis result
     */
    public static function analyze($php_code, $filename = 'temp.php') {
        $result = array(
            'valid' => true,
            'errors' => array(),
            'debug_info' => array(),
            'message' => ''
        );

        // Check if nikic/php-parser is available
        if (!class_exists('PhpParser\ParserFactory')) {
            $result['valid'] = false;
            $result['message'] = 'PHP Parser library not available';
            $result['errors'][] = array(
                'line' => 0,
                'message' => 'nikic/php-parser library not found',
                'type' => 'error'
            );
            return $result;
        }

        try {
            $parser = (new \PhpParser\ParserFactory())->createForNewestSupportedVersion();
            $ast = $parser->parse($php_code);
            
            if ($ast === null) {
                $result['valid'] = false;
                $result['message'] = 'PHP syntax error detected';
                $result['errors'][] = array(
                    'line' => 0,
                    'message' => 'Invalid PHP syntax',
                    'type' => 'error'
                );
                return $result;
            }

            // Analyze the AST for debug information
            $debug_info = self::extract_debug_info($ast, $php_code);
            $result['debug_info'] = $debug_info;
            $result['message'] = 'PHP code analyzed successfully';

        } catch (\PhpParser\Error $e) {
            $result['valid'] = false;
            $result['message'] = 'PHP parse error detected';
            
            // Get more accurate line number using fallback method
            $line_number = self::get_accurate_line_number($e, $php_code);
            $error_message = $e->getMessage();
            
            $result['errors'][] = array(
                'line' => $line_number,
                'message' => $error_message,
                'type' => 'error'
            );
        } catch (Exception $e) {
            $result['valid'] = false;
            $result['message'] = 'Error analyzing PHP code';
            $result['errors'][] = array(
                'line' => 0,
                'message' => $e->getMessage(),
                'type' => 'error'
            );
        }

        return $result;
    }

    /**
     * Extract debug information from PHP AST
     *
     * @param array $ast The parsed AST
     * @param string $php_code The original PHP code
     * @return array Debug information
     */
    private static function extract_debug_info($ast, $php_code) {
        $debug_info = array(
            'functions' => array(),
            'classes' => array(),
            'variables' => array(),
            'includes' => array(),
            'debug_statements' => array(),
            'complexity_score' => 0,
            'line_count' => 0,
            'statistics' => array(),
            'suggestions' => array()
        );

        $visitor = new class($debug_info, $php_code) extends \PhpParser\NodeVisitorAbstract {
            private $debug_info;
            private $php_code;
            private $line_count = 0;

            public function __construct(&$debug_info, $php_code) {
                $this->debug_info = &$debug_info;
                $this->php_code = $php_code;
            }

            public function enterNode(\PhpParser\Node $node) {
                // Count lines
                if (isset($node->getAttributes()['startLine'])) {
                    $this->line_count = max($this->line_count, $node->getAttributes()['startLine']);
                }

                // Extract functions
                if ($node instanceof \PhpParser\Node\Stmt\Function_) {
                    $this->debug_info['functions'][] = array(
                        'name' => $node->name->toString(),
                        'line' => $node->getLine(),
                        'params' => count($node->params),
                        'is_public' => true,
                        'visibility' => 'public'
                    );
                }

                // Extract class methods
                if ($node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                    $visibility = 'public';
                    if ($node->isPrivate()) $visibility = 'private';
                    if ($node->isProtected()) $visibility = 'protected';
                    
                    $this->debug_info['functions'][] = array(
                        'name' => $node->name->toString(),
                        'line' => $node->getLine(),
                        'params' => count($node->params),
                        'is_public' => $visibility === 'public',
                        'visibility' => $visibility,
                        'is_method' => true
                    );
                }

                // Extract classes
                if ($node instanceof \PhpParser\Node\Stmt\Class_) {
                    $this->debug_info['classes'][] = array(
                        'name' => $node->name->toString(),
                        'line' => $node->getLine(),
                        'methods' => count(array_filter($node->stmts, function($stmt) {
                            return $stmt instanceof \PhpParser\Node\Stmt\ClassMethod;
                        })),
                        'properties' => count(array_filter($node->stmts, function($stmt) {
                            return $stmt instanceof \PhpParser\Node\Stmt\Property;
                        }))
                    );
                }

                // Extract variables
                if ($node instanceof \PhpParser\Node\Expr\Variable) {
                    $var_name = is_string($node->name) ? $node->name : 'dynamic';
                    if (!in_array($var_name, $this->debug_info['variables'])) {
                        $this->debug_info['variables'][] = $var_name;
                    }
                }

                // Extract includes
                if ($node instanceof \PhpParser\Node\Expr\Include_) {
                    $this->debug_info['includes'][] = array(
                        'type' => $node->type,
                        'line' => $node->getLine()
                    );
                }

                // Extract debug statements
                if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
                    $func_name = $node->name->toString();
                    if (in_array($func_name, ['var_dump', 'print_r', 'var_export', 'debug_print_backtrace', 'error_log', 'error_reporting', 'ini_set'])) {
                        $this->debug_info['debug_statements'][] = array(
                            'function' => $func_name,
                            'line' => $node->getLine(),
                            'type' => 'debug'
                        );
                    }
                }

                // Extract potential issues
                if ($node instanceof \PhpParser\Node\Expr\FuncCall) {
                    $func_name = $node->name->toString();
                    if (in_array($func_name, ['eval', 'exec', 'system', 'shell_exec', 'passthru'])) {
                        $this->debug_info['suggestions'][] = array(
                            'type' => 'warning',
                            'message' => 'Potentially dangerous function: ' . $func_name . '()',
                            'line' => $node->getLine(),
                            'suggestion' => 'Consider using safer alternatives'
                        );
                    }
                }

                // Calculate complexity (simple cyclomatic complexity)
                if ($node instanceof \PhpParser\Node\Stmt\If_ ||
                    $node instanceof \PhpParser\Node\Stmt\For_ ||
                    $node instanceof \PhpParser\Node\Stmt\Foreach_ ||
                    $node instanceof \PhpParser\Node\Stmt\While_ ||
                    $node instanceof \PhpParser\Node\Stmt\Do_ ||
                    $node instanceof \PhpParser\Node\Stmt\Switch_ ||
                    $node instanceof \PhpParser\Node\Expr\BinaryOp) {
                    $this->debug_info['complexity_score']++;
                }
            }

            public function afterTraverse(array $nodes) {
                $this->debug_info['line_count'] = $this->line_count;
                $this->debug_info['statistics'] = array(
                    'total_functions' => count($this->debug_info['functions']),
                    'total_classes' => count($this->debug_info['classes']),
                    'total_variables' => count($this->debug_info['variables']),
                    'total_includes' => count($this->debug_info['includes']),
                    'total_debug_statements' => count($this->debug_info['debug_statements']),
                    'complexity_score' => $this->debug_info['complexity_score'],
                    'line_count' => $this->line_count,
                    'file_size' => strlen($this->php_code)
                );
            }
        };

        $traverser = new \PhpParser\NodeTraverser();
        $traverser->addVisitor($visitor);
        $traverser->traverse($ast);

        return $debug_info;
    }

    /**
     * Get accurate line number for parse errors
     *
     * @param \PhpParser\Error $e The parse error
     * @param string $php_code The PHP code
     * @return int Accurate line number
     */
    private static function get_accurate_line_number($e, $php_code) {
        $line_number = $e->getLine();
        $error_message = $e->getMessage();
        
        // First try to extract from error message
        if (preg_match('/on line (\d+)/', $error_message, $matches)) {
            $reported_line = (int)$matches[1];
            
            // For "unexpected '}'" errors, check if the previous line has missing semicolon
            if (strpos($error_message, "unexpected '}'") !== false) {
                $lines = explode("\n", $php_code);
                if (isset($lines[$reported_line - 2])) { // Check line before the reported error
                    $prev_line = trim($lines[$reported_line - 2]);
                    // If previous line ends without semicolon and is not a control structure
                    if (!empty($prev_line) && 
                        !preg_match('/[;{}]\s*$/', $prev_line) &&
                        !preg_match('/^(if|for|while|foreach|switch|function|class|interface|trait)\s*\(/', $prev_line) &&
                        !preg_match('/^(else|elseif|case|default):/', $prev_line)) {
                        return $reported_line - 1; // Return the actual error line
                    }
                }
            }
            
            return $reported_line;
        }
        
        // If that fails, try to find the error position in the code
        if (preg_match('/at position (\d+)/', $error_message, $matches)) {
            $position = (int)$matches[1];
            $lines = explode("\n", substr($php_code, 0, $position));
            return count($lines);
        }
        
        // Fallback: try to find syntax errors manually
        $lines = explode("\n", $php_code);
        $current_line = 1;
        
        foreach ($lines as $line) {
            $trimmed_line = trim($line);
            
            // Skip empty lines and comments
            if (empty($trimmed_line) || strpos($trimmed_line, '//') === 0 || strpos($trimmed_line, '/*') === 0) {
                $current_line++;
                continue;
            }
            
            // Check for missing semicolon (most common error)
            if (preg_match('/^[^;{}]*[^;{}]\s*$/', $trimmed_line) && 
                !preg_match('/^(if|for|while|foreach|switch|function|class|interface|trait)\s*\(/', $trimmed_line) &&
                !preg_match('/^(else|elseif|case|default):/', $trimmed_line) &&
                !preg_match('/^[{}]\s*$/', $trimmed_line) &&
                !preg_match('/^\/\*/', $trimmed_line) &&
                !preg_match('/^\*\//', $trimmed_line)) {
                
                // This might be a missing semicolon
                if (strpos($error_message, 'unexpected') !== false) {
                    return $current_line;
                }
            }
            
            $current_line++;
        }
        
        // Return the original line number as fallback
        return $line_number;
    }
}
