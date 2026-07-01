jQuery( document ).ready( function() {

    if ( 1 == afm_object.hide_path ) {
        var custom_css = `<style id="hide-path" type="text/css">.elfinder-info-path { display:none; } .elfinder-info-tb tr:nth-child(2) { display:none; }</style>`;
        jQuery( "head" ).append( custom_css );
    }

    var fmakey       = afm_object.nonce;
    var fma_locale   = afm_object.locale;
    var fma_cm_theme = afm_object.cm_theme;

    jQuery( '#file_manager_advanced' ).elfinder(
        // 1st Arg - options
        {
            cssAutoLoad : false, // Disable CSS auto loading
            url : afm_object.ajaxurl,  // connector URL (REQUIRED)
            customData : {
                action: 'fma_load_fma_ui',
                _fmakey: fmakey,
            },
            defaultView : 'list',
            height: 500,
            lang : fma_locale,
            ui: afm_object.ui,
            commandsOptions: {
                edit : {
                    mimes : [],
                    editors : [
                        {
                            mimes : [ 'text/plain', 'text/html', 'text/javascript', 'text/css', 'text/x-php', 'application/x-php' ],

                            load : function( textarea ) {
                                var mimeType = this.file.mime;
                                var filename = this.file.name;
                                editor       = CodeMirror.fromTextArea( textarea, {
                                    mode: mimeType,
                                    indentUnit: 4,
                                    lineNumbers: true,
                                    lineWrapping: true,
                                    lint: true,
                                    theme: fma_cm_theme
                                } );
                                return editor;
                            },

                            close : function( textarea, instance ) {
                                this.myCodeMirror = null;
                            },

                            save: function( textarea, editor ) {
                                jQuery( textarea ).val( editor.getValue() );
                            },
                        },
                    ],
                },
            },
        }
    );

} );
