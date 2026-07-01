/**
 * elFinder custom commands for File Manager Advanced
 */
(function ($) {
    $(document).ready(function () {
        if (typeof elFinder !== 'undefined' && elFinder.prototype && elFinder.prototype.commands) {

            // Icon CSS
            var pluginUrl = (typeof afm_object !== 'undefined') ? afm_object.plugin_url : '';
            if (!pluginUrl) {
                // Try to find it from script src
                var script = $('script[src*="fma-elfinder-commands.js"]');
                if (script.length) {
                    pluginUrl = script.attr('src').split('application/assets/js/')[0];
                }
            }

            if (pluginUrl) {
                var iconStyle = `
                    <style id="fma-replace-icon">
                        .elfinder-button-icon-replace {
                            background-image: url(${pluginUrl}application/assets/images/replace.png) !important;
                            background-size: 16px !important;
                            background-repeat: no-repeat !important;
                            background-position: center !important;
                        }
                        .elfinder-contextmenu-icon-replace {
                            background-image: url(${pluginUrl}application/assets/images/replace.png) !important;
                            background-size: 16px !important;
                            background-repeat: no-repeat !important;
                            background-position: center !important;
                        }
                    </style>
                `;
                if ($('#fma-replace-icon').length === 0) {
                    $('head').append(iconStyle);
                }
            }

            // Translation
            var i18Obj = elFinder.prototype.i18 || elFinder.prototype.i18n;
            if (i18Obj) {
                // Ensure English is initialized if not present
                if (!i18Obj.en) {
                    i18Obj.en = { messages: {} };
                }
                for (var lang in i18Obj) {
                    if (i18Obj.hasOwnProperty(lang)) {
                        var msg = 'Replace';
                        // Add some common translations
                        if (lang === 'es') msg = 'Reemplazar';
                        if (lang === 'fr') msg = 'Remplacer';
                        if (lang === 'de') msg = 'Ersetzen';
                        if (lang === 'it') msg = 'Sostituisci';
                        if (lang === 'pt') msg = 'Substituir';
                        if (lang === 'ru') msg = 'Заменить';
                        if (lang === 'zh_CN') msg = '替换';

                        if (!i18Obj[lang].messages) {
                            i18Obj[lang].messages = {};
                        }
                        i18Obj[lang].messages.cmdreplace = msg;
                        i18Obj[lang].messages.replace = msg;
                    }
                }
            }

            // Replace command definition
            elFinder.prototype.commands.replace = function () {
                this.updateOnSelect = false;
                this.title = 'Replace';

                this.getstate = function (hashes) {
                    var fm = this.fm;
                    var sel = this.hashes(hashes);
                    var cnt = sel.length;

                    if (cnt !== 1) {
                        return -1;
                    }

                    var file = fm.file(sel[0]);
                    if (!file || file.mime === 'directory' || file.phash === null) {
                        return -1;
                    }

                    return fm.isCommandEnabled('upload', sel[0]) ? 0 : -1;
                };

                this.exec = function (hashes) {
                    var fm = this.fm;
                    var sel = this.hashes(hashes);
                    var file = fm.file(sel[0]);
                    var self = this;

                    if (!file) {
                        return $.Deferred().reject();
                    }

                    var input = $('<input type="file" />');

                    input.on('change', function () {
                        if (this.files && this.files.length) {
                            var selectedFile = this.files[0];
                            var formData = new FormData();
                            formData.append('cmd', 'upload');
                            formData.append('target', file.phash);
                            formData.append('overwrite', '1');
                            formData.append('upload[]', selectedFile, file.name);

                            // Add custom data (nonce, action, etc. required by WordPress and the plugin)
                            var customData = fm.options.customData || {};
                            if (typeof customData === 'function') {
                                customData = customData('upload', fm);
                            }


                            for (var key in customData) {
                                if (customData.hasOwnProperty(key)) {
                                    if (!formData.has(key)) { // Prevent overwriting existing formData keys like 'cmd' or 'target'
                                        formData.append(key, customData[key]);
                                    }
                                }
                            }

                            // Use jQuery.ajax directly for upload to avoid elFinder request stripping FormData issues
                            $.ajax({
                                url: fm.options.url,
                                type: 'POST',
                                data: formData,
                                processData: false,
                                contentType: false,
                                beforeSend: function () {
                                    fm.notify({ type: 'upload', cnt: 1, hide: false });
                                },
                                success: function (data) {
                                    // Always decrement the counter
                                    fm.notify({ type: 'upload', cnt: -1, hide: true });

                                    // Try to parse if it's a string
                                    if (typeof data === 'string') {
                                        try {
                                            data = JSON.parse(data);
                                        } catch (e) { }
                                    }

                                    if (data && data.error) {
                                        fm.error(data.error);
                                    } else {
                                        fm.notify({
                                            type: 'info',
                                            msg: 'File replaced successfully!',
                                            hide: 3000
                                        });
                                        // Refresh the UI to show the new file immediately
                                        if (typeof fm.exec === 'function') {
                                            fm.exec('reload');
                                        }
                                        if (typeof fm.sync === 'function') {
                                            fm.sync();
                                        }
                                    }
                                },
                                error: function (xhr, status, error) {
                                    fm.notify({ type: 'upload', cnt: -1, hide: true });
                                    console.error('FMA Replace Error:', error, xhr.responseText);
                                    fm.notify({
                                        type: 'error',
                                        msg: 'Failed to replace file. Error: ' + error,
                                        hide: 3000
                                    });
                                }
                            });
                        }
                    });

                    input.click();
                    return $.Deferred().resolve();
                };
            };

            // Function to add replace to context menu
            var addReplaceToContextMenu = function (opts) {
                if (opts && opts.contextmenu && opts.contextmenu.files) {
                    var filesMenu = opts.contextmenu.files;
                    if (filesMenu.indexOf('replace') === -1) {
                        var renameIdx = filesMenu.indexOf('rename');
                        if (renameIdx !== -1) {
                            filesMenu.splice(renameIdx + 1, 0, 'replace');
                        } else {
                            filesMenu.push('replace');
                        }
                    }
                }
            };

            // Add to context menu for ALL future instances (by modifying the prototype _options)
            if (elFinder && elFinder.prototype && elFinder.prototype._options) {
                addReplaceToContextMenu(elFinder.prototype._options);
            }

            // Also handle existing and new instances
            var setupInstance = function (fm) {
                if (fm && fm.options) {
                    addReplaceToContextMenu(fm.options);
                }

                // Get the translation based on current language
                var msg = 'Replace';
                var lang = fm.lang || (fm.options && fm.options.lang) || 'en';
                if (lang === 'es') msg = 'Reemplazar';
                if (lang === 'fr') msg = 'Remplacer';
                if (lang === 'de') msg = 'Ersetzen';
                if (lang === 'it') msg = 'Sostituisci';
                if (lang === 'pt') msg = 'Substituir';
                if (lang === 'ru') msg = 'Заменить';
                if (lang === 'zh_CN') msg = '替换';

                // Ensure instance has the translation (FORCE IT)
                if (fm && fm.messages) {
                    fm.messages.cmdreplace = msg;
                    fm.messages.replace = msg;
                }
                // Ensure the command title is capitalized
                if (fm && fm.commands && fm.commands.replace) {
                    fm.commands.replace.title = msg;
                }
            };

            // Apply to any elFinder instances that might already exist
            if (typeof elFinder !== 'undefined' && typeof elFinder.instances !== 'undefined') {
                $.each(elFinder.instances, function (id, fm) {
                    setupInstance(fm);
                });
            }

            // Hook into elFinder creation event to apply to new instances as they are created
            $(document).on('elfindercreate', function (event, fm) {
                setupInstance(fm);
            });
        }
    });
})(jQuery);
