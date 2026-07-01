/**
 * FMA Enhanced Debug JavaScript
 * 
 * Enhanced debugging capabilities for code editor
 *
 * @package Advanced File Manager
 * @since 1.0.0
 */

(function($) {
    'use strict';

    // Debug panel object
    var FMA_Debug = {
        init: function() {
            this.bindEvents();
            this.createDebugPanel();
        },

        bindEvents: function() {
            // Add debug button to code editor
            $(document).on('click', '.fma-debug-toggle', this.toggleDebugPanel);
            $(document).on('click', '.fma-debug-close', this.closeDebugPanel);
            $(document).on('click', '.fma-debug-tab', this.switchTab);
            $(document).on('click', '#validate-php-btn', this.validatePHP);
            $(document).on('click', '#test-functions-btn', this.testExecFunctions);
        },

        createDebugPanel: function() {
            var debugPanel = `
                <div id="fma-debug-panel" class="fma-debug-panel" style="display: none;">
                    <div class="fma-debug-header">
                        <h3>Enhanced Debug Panel</h3>
                        <button type="button" class="fma-debug-close">&times;</button>
                    </div>
                    
                    <div class="fma-debug-content">
                        <div class="fma-debug-tabs">
                            <button class="fma-debug-tab active" data-tab="validation">PHP Validation</button>
                            <button class="fma-debug-tab" data-tab="functions">Exec Functions</button>
                            <button class="fma-debug-tab" data-tab="info">System Info</button>
                        </div>

                        <div class="fma-debug-tab-content" id="validation-tab">
                            <div class="fma-debug-section">
                                <h4>PHP Syntax Validation</h4>
                                <div class="fma-debug-status">
                                    <span class="fma-status-indicator" id="validation-status">
                                        <span class="fma-status-icon">⏳</span>
                                    </span>
                                    <span class="fma-status-text" id="validation-text">
                                        Checking validation availability...
                                    </span>
                                </div>
                                <button type="button" class="fma-debug-btn" id="validate-php-btn">
                                    Validate Current Code
                                </button>
                                <div id="validation-results" class="fma-debug-results"></div>
                            </div>
                        </div>

                        <div class="fma-debug-tab-content" id="functions-tab">
                            <div class="fma-debug-section">
                                <h4>Exec Functions Status</h4>
                                <button type="button" class="fma-debug-btn" id="test-functions-btn">
                                    Test All Functions
                                </button>
                                <div id="functions-results" class="fma-debug-results"></div>
                            </div>
                        </div>

                        <div class="fma-debug-tab-content" id="info-tab">
                            <div class="fma-debug-section">
                                <h4>System Information</h4>
                                <div class="fma-debug-info" id="system-info">
                                    <p>Loading system information...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            $('body').append(debugPanel);
            this.loadSystemInfo();
        },

        toggleDebugPanel: function(e) {
            e.preventDefault();
            $('#fma-debug-panel').slideToggle();
        },

        closeDebugPanel: function() {
            $('#fma-debug-panel').slideUp();
        },

        switchTab: function(e) {
            e.preventDefault();
            var tab = $(this).data('tab');
            
            // Update tab buttons
            $('.fma-debug-tab').removeClass('active');
            $(this).addClass('active');
            
            // Update tab content
            $('.fma-debug-tab-content').hide();
            $('#' + tab + '-tab').show();
        },

        validatePHP: function() {
            var $btn = $(this);
            var $results = $('#validation-results');
            
            $btn.prop('disabled', true).text('Validating...');
            $results.html('<div class="fma-loading">Validating PHP syntax...</div>');

            // Get current editor content
            var phpCode = '';
            if (typeof editor !== 'undefined' && editor.getValue) {
                phpCode = editor.getValue();
            } else {
                // Fallback to textarea
                phpCode = $('textarea[name="content"]').val() || '';
            }

            if (!phpCode.trim()) {
                $results.html('<div class="fma-error">No PHP code to validate</div>');
                $btn.prop('disabled', false).text('Validate Current Code');
                return;
            }

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fma_validate_php',
                    php_code: phpCode,
                    filename: 'debug.php',
                    nonce: fmaDebug.nonce
                },
                success: function(response) {
                    FMA_Debug.displayValidationResults(response);
                },
                error: function(xhr, status, error) {
                    $results.html('<div class="fma-error">Validation failed: ' + error + '</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Validate Current Code');
                }
            });
        },

        displayValidationResults: function(response) {
            var $results = $('#validation-results');
            var html = '';

            if (response.valid) {
                html = '<div class="fma-success">';
                html += '<strong>✓ PHP syntax is valid</strong>';
                if (response.php_version) {
                    html += '<br>PHP Version: ' + response.php_version;
                }
                if (response.execution_time) {
                    html += '<br>Validation time: ' + response.execution_time + 'ms';
                }
                html += '</div>';
            } else {
                html = '<div class="fma-error">';
                html += '<strong>✗ PHP syntax errors found</strong>';
                
                if (response.errors && response.errors.length > 0) {
                    html += '<ul class="fma-error-list">';
                    response.errors.forEach(function(error) {
                        html += '<li>';
                        html += '<strong>Line ' + error.line + ':</strong> ';
                        html += error.message;
                        if (error.type) {
                            html += ' <span class="fma-error-type">(' + error.type + ')</span>';
                        }
                        html += '</li>';
                    });
                    html += '</ul>';
                }
                
                html += '</div>';
            }

            $results.html(html);
        },

        testExecFunctions: function() {
            var $btn = $(this);
            var $results = $('#functions-results');
            
            $btn.prop('disabled', true).text('Testing...');
            $results.html('<div class="fma-loading">Testing exec functions...</div>');

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fma_test_exec_functions',
                    nonce: fmaDebug.nonce
                },
                success: function(response) {
                    FMA_Debug.displayFunctionResults(response.data);
                },
                error: function(xhr, status, error) {
                    $results.html('<div class="fma-error">Test failed: ' + error + '</div>');
                },
                complete: function() {
                    $btn.prop('disabled', false).text('Test All Functions');
                }
            });
        },

        displayFunctionResults: function(results) {
            var $results = $('#functions-results');
            var html = '<div class="fma-function-results">';

            Object.keys(results).forEach(function(func) {
                var result = results[func];
                var statusClass = result.working ? 'working' : (result.available ? 'available' : 'unavailable');
                var statusIcon = result.working ? '✓' : (result.available ? '⚠' : '✗');
                var statusText = result.working ? 'Working' : (result.available ? 'Available' : 'Unavailable');

                html += '<div class="fma-function-item ' + statusClass + '">';
                html += '<div class="fma-function-name">' + func + '</div>';
                html += '<div class="fma-function-status">';
                html += '<span class="fma-status-icon">' + statusIcon + '</span>';
                html += '<span class="fma-status-text">' + statusText + '</span>';
                html += '</div>';
                
                if (result.error) {
                    html += '<div class="fma-function-error">Error: ' + result.error + '</div>';
                }
                
                if (result.output) {
                    html += '<div class="fma-function-output">Output: ' + result.output + '</div>';
                }
                
                html += '</div>';
            });

            html += '</div>';
            $results.html(html);
        },

        loadSystemInfo: function() {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'fma_get_debug_info',
                    nonce: fmaDebug.nonce
                },
                success: function(response) {
                    FMA_Debug.displaySystemInfo(response.data);
                },
                error: function(xhr, status, error) {
                    $('#system-info').html('<div class="fma-error">Failed to load system info: ' + error + '</div>');
                }
            });
        },

        displaySystemInfo: function(info) {
            var html = '';
            
            html += '<p><strong>PHP Version:</strong> ' + info.php_version + '</p>';
            html += '<p><strong>PHP Binary:</strong> ' + info.php_binary + '</p>';
            
            if (info.php_path) {
                html += '<p><strong>PHP Path:</strong> ' + info.php_path + '</p>';
            }
            
            html += '<p><strong>ExecWithFallback:</strong> ';
            html += info.exec_with_fallback_available ? 
                '<span class="fma-success">✓ Available</span>' : 
                '<span class="fma-error">✗ Not Available</span>';
            html += '</p>';
            
            html += '<p><strong>Validation Available:</strong> ';
            html += info.validation_available ? 
                '<span class="fma-success">✓ Yes</span>' : 
                '<span class="fma-error">✗ No</span>';
            html += '</p>';

            // Update validation status
            $('#validation-status').html(
                info.validation_available ? 
                '<span class="fma-status-icon fma-success">✓</span>' : 
                '<span class="fma-status-icon fma-error">✗</span>'
            );
            $('#validation-text').text(
                info.validation_available ? 
                'PHP validation is available' : 
                'PHP validation is not available'
            );

            $('#system-info').html(html);
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        FMA_Debug.init();
    });

    // Add debug button to code editor when it loads
    $(document).on('DOMNodeInserted', function(e) {
        if ($(e.target).find('.CodeMirror').length > 0) {
            if (!$('.fma-debug-toggle').length) {
                var debugButton = '<button type="button" class="fma-debug-toggle button button-secondary" style="margin-left: 10px;">🐛 Debug</button>';
                $(e.target).find('.CodeMirror').after(debugButton);
            }
        }
    });

})(jQuery);
