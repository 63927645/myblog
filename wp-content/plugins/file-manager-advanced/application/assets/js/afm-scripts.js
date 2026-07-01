(function ($) {
    $('.file-manager-advanced-select2.fma-code-editor-theme').select2({
        templateResult: function (a, b) {
            return disable_pro_themes(a, b, 'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=code_editor_pro_theme&utm_campaign=plugin');
        }
    });

    $('.file-manager-advanced-select2.fma-theme').select2({
        templateResult: function (a, b) {
            return disable_pro_themes(a, b, 'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=file_manager_pro_theme&utm_campaign=plugin');
        }
    });

    $(document).on('click', '.on-click-show-popup', function () {
        file_manager_advanced_popup($(this).attr('fma-href'));
    });

    $('#fma__hide-banner').on('click', function (e) {
        e.preventDefault();

        $('.afm__right-side').hide();
        $('.afm__left-side').css({ 'width': '100%', 'max-width': '100%' });

        rest_api_post('hide-banner')
            .catch(error => console.error('Error:', error));
    });

    $('#fma__minimize-maximize').on('click', function (e) {
        e.preventDefault();

        $(this).children().toggleClass('fma__minimized');

        var action = 'maximize';
        if ('true' === $(this).attr('fma-maximized')) {
            action = 'minimize';
        }

        minimize_maximize(this, action);

        rest_api_post(
            'minimize-maximize-banner',
            { action: action }
        ).catch(error => console.error('Error:', error));
    });

    $('.dropbox__wrap, .file-logs__wrap, .fma__wrap').on('click', function () {

        var redirect_url = $(this).attr('afmp-href');
        if (!redirect_url) {
            redirect_url = '';
        }

        file_manager_advanced_popup(redirect_url, '', '');
    });

    $('.googledrive__wrap').on('click', function () {
        file_manager_advanced_popup(
            'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=google_drive_banner&utm_campaign=plugin',
            '',
            ''
        );
    });

    $('.dropbox__wrap, .onedrive__wrap').on('click', function () {
        file_manager_advanced_popup('', '', '');
    });

    // Use event delegation for blocks wrap (since it's added dynamically)
    // Same behavior as OneDrive/Dropbox - empty string for default popup
    $(document).on('click', '.fma__blocks__wrap', function () {
        var redirect_url = $(this).attr('afmp-href');
        if (!redirect_url) {
            redirect_url = 'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=blocks_banner&utm_campaign=plugin';
        }
        file_manager_advanced_popup(redirect_url, '', '');
        return false;
    });

    $('.aws__wrap').on('click', function () {
        file_manager_advanced_popup('https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=aws_banner&utm_campaign=plugin', '', '');
    });

    $('.github__wrap').on('click', function () {
        file_manager_advanced_popup(
            'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=github_banner&utm_campaign=plugin',
            '',
            ''
        );
    });

    $('.googlecloud__wrap').on('click', function () {
        file_manager_advanced_popup(
            'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=googlecloud_banner&utm_campaign=plugin',
            '',
            ''
        );
    });

    function file_manager_advanced_popup(redirect_url = '', message = '', button_title = '') {
        if (!redirect_url.length) {
            redirect_url = 'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=dropbox_banner&utm_campaign=plugin';
        }

        if (!message.length) {
            message = 'Get advanced features with Advanced File Manager Pro!';
        }

        if (!button_title.length) {
            button_title = 'Get Pro Now';
        }
        var element = $('#fma__pro_popup');
        if (element.length) {
            element.show();
        } else {
            let fma__pro_popup = `<div class="fma__pro-popup" id="fma__pro_popup">
                <div class="fma__pro-popup-wrapper">
                    
                    <div class="fma__pro-close-button">
                        <a id="close-popup-btn" href="#">
                            <img src="${afmAdmin.assetsURL}images/close-popup.svg" alt="">
                        </a>
                    </div>
                    
                    <div class="fma__pro-popup-content">
                        
                        <div>
                            <img src="${afmAdmin.assetsURL}images/fma-logo.svg" alt="">
                        </div>
                        
                        <div class="afmp__pro-popup-desc">
                            <p>
                                ${message}
                            </p>
                        </div>
                        
                        <div class="fma__pro-popup-cta">
                            <a target="_blank" href="${redirect_url}">
                                <img style="width: 20px;margin-bottom: -3px;" src="${afmAdmin.assetsURL}images/crown.svg" alt="">
                                ${button_title}
                                <img style="width: 10px;margin-bottom: -2px;" src="${afmAdmin.assetsURL}images/right-arrow.svg" alt="">
                            </a>
                        </div>
                        
                    </div>
                    
                </div>
            </div>`;
            $('body').append(fma__pro_popup);
        }

        $(document).on('click', '#close-popup-btn', function (e) {
            e.preventDefault();

            $('#fma__pro_popup').remove();
        });
    }

    function minimize_maximize(element, action) {
        switch (action) {
            case 'maximize':
                $('#remove-on-minimize, .fma__footer').show();
                $(element).attr('fma-maximized', 'true');
                break;
            case 'minimize':
                $('#remove-on-minimize, .fma__footer').hide();
                $(element).attr('fma-maximized', 'false');

                break;
        }
    }

    var rest_api_post = async function (url, params = {}) {
        url = afmAdmin.jsonURL + 'file-manager-advanced/v1/' + url;

        return await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(params)
        }).then(response => response.json());
    };

    var disable_pro_themes = function (a, b, redirect_url) {
        if (a.id === '') {
            return a.text;
        }

        if (!a.disabled) {
            return a.text;
        }

        return $(`<span fma-href="${redirect_url}" class="fma__clearfix on-click-show-popup">
            <span class="fma__left">
                ${a.text}
            </span>
            <span style="font-size: 15px;" class="fma__right">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M11.341 11.0011H2.6642C2.57543 11.0011 2.49029 11.0364 2.42752 11.0992C2.36475 11.1619 2.32948 11.2471 2.32948 11.3358V12.0001C2.32948 12.0889 2.36475 12.174 2.42752 12.2368C2.49029 12.2996 2.57543 12.3348 2.6642 12.3348H11.341C11.4289 12.3348 11.5132 12.3003 11.5759 12.2386C11.6385 12.177 11.6743 12.0931 11.6757 12.0053V11.3358C11.6744 11.2475 11.6387 11.1631 11.5762 11.1006C11.5137 11.0381 11.4293 11.0024 11.341 11.0011ZM12.6747 4.30687C12.4015 4.30687 12.1396 4.41537 11.9464 4.60851C11.7533 4.80165 11.6448 5.06361 11.6448 5.33675C11.6469 5.479 11.6785 5.61926 11.7375 5.7487L10.2287 6.655C10.1524 6.70023 10.068 6.72985 9.98016 6.74216C9.89236 6.75447 9.80299 6.74921 9.71723 6.72669C9.63148 6.70418 9.55105 6.66485 9.48062 6.611C9.41019 6.55714 9.35116 6.48984 9.30696 6.41298L7.60765 3.43661C7.77678 3.3047 7.90051 3.12327 7.96156 2.91764C8.0226 2.71202 8.01793 2.49247 7.94818 2.28963C7.87844 2.08679 7.7471 1.91079 7.5725 1.7862C7.3979 1.66161 7.18876 1.59464 6.97427 1.59464C6.75977 1.59464 6.55063 1.66161 6.37603 1.7862C6.20144 1.91079 6.0701 2.08679 6.00035 2.28963C5.9306 2.49247 5.92593 2.71202 5.98698 2.91764C6.04803 3.12327 6.17175 3.3047 6.34089 3.43661L4.64158 6.41298C4.59737 6.48984 4.53834 6.55714 4.46791 6.611C4.39748 6.66485 4.31705 6.70418 4.2313 6.72669C4.14555 6.74921 4.05618 6.75447 3.96837 6.74216C3.88057 6.72985 3.79609 6.70023 3.71983 6.655L2.21105 5.7487C2.27125 5.61966 2.30287 5.47914 2.30374 5.33675C2.30464 5.13643 2.24559 4.94041 2.13421 4.77391C2.02282 4.60741 1.86417 4.47803 1.67867 4.4024C1.49316 4.32678 1.28928 4.30837 1.09323 4.34955C0.897177 4.39072 0.717924 4.48959 0.578513 4.63345C0.439103 4.77731 0.345909 4.95958 0.310913 5.15683C0.275917 5.35408 0.300718 5.55728 0.382129 5.74032C0.46354 5.92336 0.597838 6.07786 0.767757 6.18397C0.937676 6.29007 1.13545 6.34293 1.33565 6.33574C1.39044 6.34108 1.44563 6.34108 1.50043 6.33574L3.00921 10.3574H11.0063L12.5099 6.33574H12.6747C12.9279 6.31757 13.1648 6.20416 13.3378 6.01833C13.5108 5.8325 13.607 5.58805 13.607 5.33418C13.607 5.0803 13.5108 4.83585 13.3378 4.65002C13.1648 4.46419 12.9279 4.35078 12.6747 4.33261V4.30687Z" fill="#FFCA38"/>
                </svg>
            </span>
        </span>` );
    }

    // Fix for notices appearing inside the banner - run immediately and on load
    function fixAdminNotices() {
        var $notices = $('.afm__right-side .notice, .afm__right-side .updated, .afm__right-side .error, .afm__right-side .is-dismissible');
        if ($notices.length > 0) {
            $notices.insertBefore('.afm__left-side');
            $notices.css('margin-left', '0').css('margin-right', '0').css('margin-bottom', '20px').show();
        }
    }

    $(document).ready(function () {
        fixAdminNotices();
        // Check again after a short delay in case of dynamic insertions
        setTimeout(fixAdminNotices, 500);
        setTimeout(fixAdminNotices, 2000);
    });
})(jQuery);