jQuery(document).ready(function () {
    // Check if debug feature is enabled
    var debugEnabled = afm_object && afm_object.debug_enabled === '1';

    // Global variables for error tracking
    var hasErrors = false;
    var currentEditor = null;
    var currentErrors = [];
    var lastButtonState = null;
    var tooltipTimeout = null;
    var lastTooltipLine = null;
    var isSaveButtonClicked = false;

    // CSS styles for error highlighting
    var errorStyles = `
        <style>
        .fma-error-line {
            background-color: #fed7d7 !important;
            border-left: 3px solid #c53030 !important;
        }
        .fma-error-underline {
            text-decoration: underline wavy #c53030 !important;
            text-decoration-thickness: 2px !important;
        }
        .fma-error-marker {
            color: #c53030 !important;
            font-size: 14px !important;
            font-weight: bold !important;
            text-align: center !important;
            line-height: 1 !important;
        }
        .fma-error-gutter {
            background-color: #fed7d7 !important;
            border-right: 2px solid #c53030 !important;
        }
        .fma-save-close-disabled {
            opacity: 0.5 !important;
            cursor: not-allowed !important;
            pointer-events: none !important;
        }
        .fma-error-tooltip {
            position: absolute;
            background: #2d3748;
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-family: 'Courier New', monospace;
            z-index: 10000;
            pointer-events: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            max-width: 300px;
            word-wrap: break-word;
            line-height: 1.4;
        }
        .fma-error-tooltip::before {
            content: '';
            position: absolute;
            top: -5px;
            left: 50%;
            transform: translateX(-50%);
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #2d3748;
        }
        .fma-error-tooltip .error-title {
            font-weight: bold;
            color: #feb2b2;
            margin-bottom: 4px;
        }
        .fma-error-tooltip .error-message {
            color: #e2e8f0;
        }
        .fma-error-tooltip .error-line {
            color: #a0aec0;
            font-size: 11px;
            margin-top: 4px;
        }
        </style>
    `;
    jQuery('head').append(errorStyles);

    if (1 == afm_object.hide_path) {
        var custom_css = `<style id="hide-path" type="text/css">.elfinder-info-path { display:none; } .elfinder-info-tb tr:nth-child(2) { display:none; }</style>`;
        jQuery("head").append(custom_css);
    }

    var hide_preferences_css = `<style id="hide-preferences" type="text/css">
        .elfinder-contextmenu-item:has( .elfinder-button-icon.elfinder-button-icon-preference.elfinder-contextmenu-icon ) {display: none;}
        .elfinder-button-icon-replace {
            background-image: url(${afm_object.plugin_url}application/assets/images/replace.png);
            background-size: 16px;
            background-repeat: no-repeat;
            background-position: center;
        }
        .elfinder-contextmenu-icon-replace {
            background-image: url(${afm_object.plugin_url}application/assets/images/replace.png);
            background-size: 16px;
            background-repeat: no-repeat;
            background-position: center;
        }
    </style>`;
    jQuery('head').append(hide_preferences_css);

    var fmakey = afm_object.nonce;
    var fma_locale = afm_object.locale;
    var fma_cm_theme = afm_object.cm_theme;

    // PHP Debug Analysis function
    function analyzePHPDebug(code, filename, callback) {
        jQuery.ajax({
            url: afm_object.ajaxurl,
            type: 'POST',
            data: {
                action: 'fma_debug_php',
                nonce: fmakey,
                php_code: code,
                filename: filename
            },
            success: function (response) {
                if (callback && typeof callback === 'function') {
                    callback(response);
                }
            },
            error: function () {
                if (callback && typeof callback === 'function') {
                    callback({
                        valid: false,
                        debug_info: {},
                        message: 'Failed to analyze PHP code'
                    });
                }
            }
        });
    }

    // Highlight error lines in CodeMirror
    function highlightErrorLines(editor, errors) {
        if (!editor || !errors || errors.length === 0) {
            clearErrorHighlights(editor);
            return;
        }

        currentErrors = errors;

        for (var i = 0; i < editor.lineCount(); i++) {
            editor.removeLineClass(i, 'background', 'fma-error-line');
            editor.removeLineClass(i, 'text', 'fma-error-underline');
            editor.setGutterMarker(i, 'fma-error-gutter', null);
        }

        errors.forEach(function (error) {
            if (error.line && error.line > 0) {
                var lineNumber = error.line - 1;
                editor.addLineClass(lineNumber, 'background', 'fma-error-line');
                editor.addLineClass(lineNumber, 'text', 'fma-error-underline');

                var marker = document.createElement('div');
                marker.className = 'fma-error-marker';
                marker.innerHTML = '⚠️';
                marker.title = error.message;
                editor.setGutterMarker(lineNumber, 'fma-error-gutter', marker);

                // Markers have titles which browser handles, but for the line text we use tooltips
            }
        });

        hasErrors = true;
        updateSaveCloseButton();
    }

    // Clear error highlights
    function clearErrorHighlights(editor) {
        if (!editor) return;

        for (var i = 0; i < editor.lineCount(); i++) {
            editor.removeLineClass(i, 'background', 'fma-error-line');
            editor.removeLineClass(i, 'text', 'fma-error-underline');
            editor.setGutterMarker(i, 'fma-error-gutter', null);
        }

        hideErrorTooltip();
        currentErrors = [];
        hasErrors = false;
        updateSaveCloseButton();
    }

    // Show error tooltip on hover
    function showErrorTooltip(event, error) {
        if (tooltipTimeout) clearTimeout(tooltipTimeout);

        if (lastTooltipLine === error.line) {
            // Just update position if moving within same line
            var tooltip = jQuery('.fma-error-tooltip');
            if (tooltip.length) {
                var x = event.pageX + 10;
                var y = event.pageY - 15;
                tooltip.css({ left: x + 'px', top: (y - tooltip.outerHeight()) + 'px' });
            }
            return;
        }

        tooltipTimeout = setTimeout(function () {
            jQuery('.fma-error-tooltip').remove();
            var tooltipHtml = `
                <div class="fma-error-tooltip">
                    <div class="error-title">⚠️ PHP Error</div>
                    <div class="error-message">${error.message}</div>
                    <div class="error-line">Line ${error.line}</div>
                </div>
            `;
            var tooltip = jQuery(tooltipHtml).appendTo('body');
            var x = event.pageX + 10;
            var y = event.pageY - 15;
            tooltip.css({ left: x + 'px', top: (y - tooltip.outerHeight()) + 'px' });
            lastTooltipLine = error.line;
        }, 50); // Faster response
    }

    // Hide error tooltip
    function hideErrorTooltip() {
        if (tooltipTimeout) clearTimeout(tooltipTimeout);
        jQuery('.fma-error-tooltip').remove();
        lastTooltipLine = null;
    }

    // Get error for specific line number
    function getErrorForLine(lineNumber) {
        for (var i = 0; i < currentErrors.length; i++) {
            if (currentErrors[i].line === lineNumber + 1) return currentErrors[i];
        }
        return null;
    }

    // Update Save & Close button state
    function updateSaveCloseButton() {
        var currentState = hasErrors ? 'disabled' : 'enabled';
        if (lastButtonState === currentState) return;
        lastButtonState = currentState;

        var selectors = [
            '.elfinder-button-save-close', '.elfinder-button-save',
            '[title*="Save"]', '[title*="save"]',
            'button[title*="Save"]', 'button[title*="save"]',
            'button:contains("Save")', 'button:contains("save")'
        ];
        var saveCloseBtn = jQuery(selectors.join(', ')).filter(':visible');

        if (saveCloseBtn.length > 0) {
            if (hasErrors) {
                saveCloseBtn.addClass('fma-save-close-disabled').attr('disabled', 'disabled');
                saveCloseBtn.css({ opacity: '0.5', cursor: 'not-allowed' });
                saveCloseBtn.off('click.fma-disable').on('click.fma-disable', function (e) {
                    e.preventDefault(); return false;
                });
            } else {
                saveCloseBtn.removeClass('fma-save-close-disabled').removeAttr('disabled');
                saveCloseBtn.css({ opacity: '1', cursor: 'pointer' });
                saveCloseBtn.off('click.fma-disable');
            }
        }
    }

    // Periodic check for Save button
    setInterval(function () {
        if (currentEditor && hasErrors) updateSaveCloseButton();
    }, 5000);

    // Show error popup on save
    function showErrorSavePopup(errors, callback) {
        var errorList = errors.map(function (error) {
            return `Line ${error.line}: ${error.message}`;
        }).join('<br>');

        var popupHtml = `
            <div class="fma-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10000; display: flex; justify-content: center; align-items: center;">
                <div class="fma-error-popup" style="background: white; border-radius: 8px; padding: 20px; max-width: 500px; width: 90%; box-shadow: 0 4px 20px rgba(0,0,0,0.3);">
                    <h3 style="color: #c53030;">PHP Syntax Errors Found</h3>
                    <div style="background: #fed7d7; padding: 10px; border-radius: 4px; font-family: monospace;">${errorList}</div>
                    <div style="margin-top: 15px; display: flex; gap: 10px; justify-content: flex-end;">
                        <button class="fma-error-okay" style="padding: 8px 15px;">Okay</button>
                        <button class="fma-error-save-anyway" style="padding: 8px 15px; background: #c53030; color: white; border: none;">Save Anyway</button>
                    </div>
                </div>
            </div>
        `;

        jQuery('.fma-modal-overlay').remove();
        var popup = jQuery(popupHtml).appendTo('body');

        popup.find('.fma-error-okay').on('click', function () {
            popup.remove();
            if (callback) callback(false);
        });

        popup.find('.fma-error-save-anyway').on('click', function () {
            popup.remove();
            if (callback) callback(true);
        });
    }

    // Success Modal
    function showSuccessModal(message) {
        if (jQuery('.fma-modal-overlay').length > 0) return;
        var modalHtml = `
            <div class="fma-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 10001; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; padding: 30px; border-radius: 8px; text-align: center;">
                    <div style="color: #46b450; font-size: 40px;">✓</div>
                    <p>${message}</p>
                    <button class="fma-modal-close">OK</button>
                </div>
            </div>
        `;
        var modal = jQuery(modalHtml).appendTo('body');
        modal.find('.fma-modal-close').on('click', function () { modal.remove(); });
        setTimeout(function () { modal.remove(); }, 3000);
    }

    // Initialize elFinder
    var elfinder_object = jQuery('#file_manager_advanced').elfinder({
        cssAutoLoad: false,
        url: afm_object.ajaxurl,
        customData: {
            action: 'fma_load_fma_ui',
            _fmakey: fmakey,
        },
        defaultView: 'list',
        height: 500,
        lang: fma_locale,
        ui: afm_object.ui,
        commandsOptions: {
            edit: {
                mimes: [],
                editors: [{
                    mimes: ['text/plain', 'text/html', 'text/javascript', 'text/css', 'text/x-php', 'application/x-php'],
                    info: { name: 'Code Editor' },
                    load: function (textarea) {
                        var mimeType = this.file.mime;
                        var filename = this.file.name;
                        var self = this;

                        var editor = CodeMirror.fromTextArea(textarea, {
                            mode: mimeType,
                            indentUnit: 4,
                            lineNumbers: true,
                            theme: fma_cm_theme,
                            gutters: ["CodeMirror-lint-markers", "CodeMirror-linenumbers"]
                        });

                        editor.fma_file_info = { filename: filename, mime: mimeType, hash: self.file.hash };

                        if (debugEnabled && filename.toLowerCase().endsWith('.php')) {
                            editor.on('change', function () {
                                var code = editor.getValue();
                                analyzePHPDebug(code, filename, function (result) {
                                    if (result.valid) {
                                        clearErrorHighlights(editor);
                                    } else {
                                        highlightErrorLines(editor, result.errors);
                                    }
                                });
                            });
                        }

                        // Hover tooltip: CodeMirror coordsChar() "page" mode needs document coords (pageX/pageY)
                        var wrapper = editor.getWrapperElement();
                        jQuery(wrapper).on('mousemove.fma-debug', function (e) {
                            if (!hasErrors || currentErrors.length === 0) return;

                            var docLeft = e.pageX != null ? e.pageX : (e.clientX + (window.scrollX || document.documentElement.scrollLeft || 0));
                            var docTop = e.pageY != null ? e.pageY : (e.clientY + (window.scrollY || document.documentElement.scrollTop || 0));
                            var coords = editor.coordsChar({ left: docLeft, top: docTop }, 'page');
                            if (coords.outside) return;
                            var error = getErrorForLine(coords.line);

                            if (error) {
                                showErrorTooltip(e, error);
                            } else {
                                hideErrorTooltip();
                            }
                        });

                        jQuery(wrapper).on('mouseleave.fma-debug', function () {
                            hideErrorTooltip();
                        });

                        currentEditor = editor;
                        return editor;
                    },
                    close: function (textarea, instance) {
                        if (instance) clearErrorHighlights(instance);
                        currentEditor = null;
                    },
                    save: function (textarea, editor) {
                        var code = editor.getValue();
                        var filename = editor.fma_file_info ? editor.fma_file_info.filename : 'unknown.php';

                        if (filename.toLowerCase().endsWith('.php') && hasErrors) {
                            analyzePHPDebug(code, filename, function (result) {
                                if (!result.valid && result.errors) {
                                    showErrorSavePopup(result.errors, function (saveAnyway) {
                                        if (saveAnyway) {
                                            jQuery(textarea).val(code);
                                            if (typeof editor.save === 'function') editor.save();
                                        }
                                    });
                                }
                            });
                            return false;
                        }
                        jQuery(textarea).val(code);
                        return true;
                    }
                }]
            }
        },
        workerBaseUrl: afm_object.plugin_url + 'application/library/js/worker/',
    });

    // Override search command to add contentTag parameter for content search (after init)
    function applyContentSearchOverride(fm) {
        if (!fm || !fm._commands || !fm._commands.search) return;
        if (fm._commands.search._fmaContentSearchPatched) return;
        fm._commands.search._fmaContentSearchPatched = true;
        var originalSearchExec = fm._commands.search.exec;

            // Override exec method to add contentTag for SearchTag type
            fm._commands.search.exec = function(q, target, mime, type) {
                var self = this;
                var sType = type || '';

                // If SearchTag type, we need to send contentTag parameter
                if (sType === 'SearchTag' && q) {
                    var contentTag = q; // Store the tag
                    var fm = this.fm;
                    var reqDef = [];
                    var onlyMimes = fm.options.onlyMimes;
                    var phash, targetVolids = [];

                    // Custom setType function that encodes tag in q parameter for SearchTag
                    var setType = function(data) {
                        if (sType && sType !== 'SearchName' && sType !== 'SearchMime') {
                            data.type = sType;
                        }
                        if (sType === 'SearchTag') {
                            data.q = '__CONTENT_SEARCH__:' + contentTag;
                        }
                        return data;
                    };

                    var rootCnt;

                    // Process target
                    if (typeof target == 'object') {
                        mime = target.mime || '';
                        target = target.target || '';
                    }
                    target = target ? target : '';

                    // Process mimes
                    if (mime) {
                        mime = jQuery.trim(mime).replace(',', ' ').split(' ');
                        if (onlyMimes.length) {
                            mime = jQuery.map(mime, function(m) {
                                m = jQuery.trim(m);
                                return m && (jQuery.inArray(m, onlyMimes) !== -1
                                    || jQuery.grep(onlyMimes, function(om) { return m.indexOf(om) === 0 ? true : false; }).length
                                ) ? m : null;
                            });
                        }
                    } else {
                        mime = [].concat(onlyMimes);
                    }

                    fm.trigger('searchstart', setType({query: '', target: target, mimes: mime}));

                    if (!onlyMimes.length || mime.length) {
                        if (target === '' && fm.api >= 2.1) {
                            rootCnt = Object.keys(fm.roots).length;
                            jQuery.each(fm.roots, function(id, hash) {
                                reqDef.push(fm.request({
                                    data: setType({cmd: 'search', target: hash, mimes: mime}),
                                    notify: {type: 'search', cnt: 1, hideCnt: (rootCnt > 1 ? false : true)},
                                    cancel: true,
                                    preventDone: true
                                }));
                            });
                        } else {
                            reqDef.push(fm.request({
                                data: setType({cmd: 'search', target: target, mimes: mime}),
                                notify: {type: 'search', cnt: 1, hideCnt: true},
                                cancel: true,
                                preventDone: true
                            }));
                            if (target !== '' && fm.api >= 2.1 && Object.keys(fm.leafRoots).length) {
                                jQuery.each(fm.leafRoots, function(hash, roots) {
                                    phash = hash;
                                    while (phash) {
                                        if (target === phash) {
                                            jQuery.each(roots, function() {
                                                var f = fm.file(this);
                                                f && f.volumeid && targetVolids.push(f.volumeid);
                                                reqDef.push(fm.request({
                                                    data: setType({cmd: 'search', target: this, mimes: mime}),
                                                    notify: {type: 'search', cnt: 1, hideCnt: false},
                                                    cancel: true,
                                                    preventDone: true
                                                }));
                                            });
                                        }
                                        phash = (fm.file(phash) || {}).phash;
                                    }
                                });
                            }
                        }
                    } else {
                        reqDef = [jQuery.Deferred().resolve({files: []})];
                    }

                    fm.searchStatus.mixed = (reqDef.length > 1) ? targetVolids : false;

                    return jQuery.when.apply(jQuery, reqDef).done(function(data) {
                        var argLen = arguments.length, i;
                        data.warning && fm.error(data.warning);
                        if (argLen > 1) {
                            data.files = (data.files || []);
                            for (i = 1; i < argLen; i++) {
                                arguments[i].warning && fm.error(arguments[i].warning);
                                if (arguments[i].files) {
                                    data.files.push.apply(data.files, arguments[i].files);
                                }
                            }
                        }
                        data.files && data.files.length && fm.cache(data.files);
                        fm.lazy(function() {
                            fm.trigger('search', data);
                        }).then(function() {
                            return fm.lazy(function() {
                                fm.trigger('searchdone');
                            });
                        }).then(function() {
                            data.sync && fm.sync();
                        });
                    });
                } else {
                    return originalSearchExec.call(self, q, target, mime, type);
                }
            };
    }
    if (elfinder_object && elfinder_object.length) {
        var fm = elfinder_object.elfinder('instance');
        if (fm) {
            fm.one('init', function() { applyContentSearchOverride(fm); });
            applyContentSearchOverride(fm);
        }
    }

    // When user clears search and presses Enter (or closes search), go back to folder they were in before search
    if (elfinder_object && elfinder_object.length) {
        var fm = elfinder_object.elfinder('instance');
        if (fm) {
            var cwdBeforeSearch = null;
            fm.bind('searchstart', function() {
                var cwd = fm.cwd();
                if (cwd && cwd.hash) {
                    cwdBeforeSearch = cwd.hash;
                }
            });
            fm.bind('searchend', function() {
                if (cwdBeforeSearch && fm.file(cwdBeforeSearch)) {
                    fm.exec('open', cwdBeforeSearch);
                }
                cwdBeforeSearch = null;
            });
        }
    }

    // Add Content search radio button after elFinder is initialized
    if (elfinder_object && elfinder_object.length) {
        var fm = elfinder_object.elfinder('instance');
        if (fm) {
            var addContentRadioButton = function() {
                var searchMenu = jQuery('.elfinder-button-search-menu');
                if (searchMenu.length) {
                    var namespace = fm.namespace || 'elfinder-';
                    var id = function(name) {
                        return namespace + (fm.escape ? fm.escape(name) : name);
                    };

                    var searchTypeSet = searchMenu.find('.elfinder-search-type');
                    if (searchTypeSet.length && !searchTypeSet.find('#' + id('SearchTag')).length) {
                        var tagRadio = jQuery('<input id="' + id('SearchTag') + '" name="serchcol" type="radio" value="SearchTag"/>');
                        var tagLabel = jQuery('<label for="' + id('SearchTag') + '">Content</label>');

                        searchTypeSet.append(tagRadio).append(tagLabel);
                        searchTypeSet.buttonset('refresh');

                        // Remember search type when target is "Here", so we can restore only when user had chosen Content then switched to "All"
                        var lastSearchTypeWhenHere = null;

                        var updateContentState = function() {
                            var isHere = jQuery('#' + id('SearchFromCwd')).prop('checked');
                            var tagRadio = jQuery('#' + id('SearchTag'));
                            var tagLabel = tagRadio.next('label');

                            if (tagRadio.length) {
                                if (isHere) {
                                    tagRadio.prop('disabled', false);
                                    tagLabel.css({'opacity': '1', 'cursor': 'pointer'});
                                    // Restore Content when switching back to "Here" if it was selected before
                                    if (lastSearchTypeWhenHere === 'SearchTag') {
                                        tagRadio.prop('checked', true);
                                        searchTypeSet.buttonset('refresh');
                                    }
                                } else {
                                    tagRadio.prop('disabled', true);
                                    tagLabel.css({'opacity': '0.5', 'cursor': 'not-allowed'});
                                    if (tagRadio.prop('checked')) {
                                        lastSearchTypeWhenHere = 'SearchTag'; // was Content before switching to All
                                        jQuery('#' + id('SearchName')).prop('checked', true).trigger('change');
                                        searchTypeSet.buttonset('refresh');
                                    }
                                }
                            }
                        };

                        // When user changes search type while target is "Here", remember it
                        searchMenu.off('change.contentsearch', 'input[name="serchcol"]').on('change.contentsearch', 'input[name="serchcol"]', function() {
                            if (jQuery('#' + id('SearchFromCwd')).prop('checked')) {
                                var val = jQuery(this).val();
                                if (val) lastSearchTypeWhenHere = val;
                            }
                        });

                        searchMenu.off('change.contentsearch', 'input[name="serchfrom"]').on('change.contentsearch', 'input[name="serchfrom"]', updateContentState);
                        updateContentState();
                    }
                }
            };

            jQuery(document).on('click focus', '.elfinder-button-search input[type="text"]', function() {
                setTimeout(addContentRadioButton, 200);
            });

            fm.bind('open', function() {
                setTimeout(addContentRadioButton, 500);
            });

            setTimeout(addContentRadioButton, 1000);
        }
    }
});
