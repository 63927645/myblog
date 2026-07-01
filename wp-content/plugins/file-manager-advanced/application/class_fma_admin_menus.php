<?php
/*
@package: File Manager Advanced
@Class: fma_admin_menus
*/
if (class_exists('class_fma_admin_menus')) {
    return;
}
class class_fma_admin_menus
{
    var $langs;
    /**
     * AFM - Languages
     */
    public function __construct()
    {
        include('class_fma_lang.php');
        $this->langs = new class_fma_adv_lang();

        add_action('fma__settings_tab_notifications_content', array($this, 'notification_callback'));
        // Free: Show AI Integration (Code Pilot) tab content as a PRO teaser
        add_action('fma__settings_tab_ai_content', array($this, 'ai_integration_callback'));

        // Hide Freemius Add-Ons menu using Freemius filter hook
        add_filter('fs_is_submenu_visible_file-manager-advanced', array($this, 'hide_freemius_addons_menu'), 10, 2);

        // Fallback: Remove Freemius Add-Ons menu if it still exists
        add_action('admin_menu', array($this, 'remove_freemius_addons_menu'), 999);
    }

    /**
     * Hide Freemius Add-Ons menu using Freemius filter hook
     * This is the proper way to hide menu items in Freemius
     */
    public function hide_freemius_addons_menu($is_visible, $menu_id)
    {
        // Hide Add-Ons menu
        if ($menu_id === 'addons') {
            return false;
        }

        return $is_visible;
    }

    /**
     * Remove Freemius Add-Ons menu (fallback method)
     */
    public function remove_freemius_addons_menu()
    {
        global $fma_fs, $submenu;

        // Remove Add-Ons submenu if it exists (check various possible slugs)
        $parent_slug = 'file_manager_advanced_ui';
        $possible_slugs = array(
            'file-manager-advanced-addons',
            'file-manager-advanced-addons-network',
            'file_manager_advanced-addons',
            'file_manager_advanced-addons-network',
        );

        foreach ($possible_slugs as $slug) {
            remove_submenu_page($parent_slug, $slug);
        }

        // Also remove by menu title if slug-based removal didn't work
        if (isset($submenu[$parent_slug])) {
            foreach ($submenu[$parent_slug] as $key => $item) {
                if (isset($item[0]) && (stripos($item[0], 'Add-On') !== false || stripos($item[0], 'Addon') !== false)) {
                    unset($submenu[$parent_slug][$key]);
                }
            }
        }
    }

    /**
     * Notification Callback
     * @since 6.7.3
     */
    public function notification_callback()
    {
        if (!class_exists('AFMP\\Modules\\EmailNotification\\EmailNotification')) {
            echo '<div afmp-href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=email_notification&utm_campaign=plugin" class="fma__wrap">';
            echo '<h2 class="fma__heading"><strong>Email Notification Settings</strong> <span class="fma__heading-pro-tag">PRO</span></h2>';

            echo '<table class="form-table" role="presentation"><tbody><tr><th scope="row"><label for="enable">Enable</label></th><td>            <input type="checkbox" id="enable" name="afmp__email_notification_settings[enable]" value="yes">
            <label for="enable">Enable email notification</label>
            </td></tr><tr><th scope="row"><label for="email">Email Address</label></th><td>            <input type="text" id="email" name="afmp__email_notification_settings[email]" value="" class="regular-text">
            <p class="description">Email address to send notification.</p>
            </td></tr><tr><th scope="row"><label for="events">Events</label></th><td>                <label>
                    <input type="checkbox" name="afmp__email_notification_settings[events][]" value="rm">
                    Delete File & Folder                </label><br>
                                <label>
                    <input type="checkbox" name="afmp__email_notification_settings[events][]" value="mkfile">
                    Create File                </label><br>
                                <label>
                    <input type="checkbox" name="afmp__email_notification_settings[events][]" value="mkdir">
                    Create Folder                </label><br>
                </td></tr><tr><th scope="row"><label for="subject">Email Subject</label></th><td>            <input type="text" id="subject" name="afmp__email_notification_settings[subject]" class="regular-text">
            <p class="description">Subject of the email notification.</p>
            </td></tr><tr><th scope="row"><label for="message">Email Message</label></th><td>            <textarea id="message" name="afmp__email_notification_settings[message]" rows="5" class="large-text">The file action {event} was performed on {file_name} ({ext}) at {date_time} from IP {ip_address} on {site_name} by {username}.
Thank you for using Advanced File Manager.</textarea>
            <p class="description">Message body of the email notification. You can use placeholders like {file_name}, {action} etc.</p>
            <p class="description">Available placeholders:</p><ul class="afmp-email-placeholders"><li><code>{username}</code> - This will fetch the user’s name who did the action</li><li><code>{ip_address}</code> - This will fetch the user’s IP Address</li><li><code>{event}</code> - This will fetch which file the user has created or deleted</li><li><code>{file_name}</code> - This will fetch the file name on which that action was done</li><li><code>{ext}</code> - This will fetch the file extension on which that action was done</li><li><code>{date_time}</code> - This will fetch the date the user acted on the file</li><li><code>{site_name}</code> - This will fetch the site name on which the action was performed</li></ul></td></tr></tbody></table>';
            echo '</div>';
        }

        if (!class_exists('AFMP\\Modules\\SlackNotification\\SlackNotification')) {
            echo '<div afmp-href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=slack_notification&utm_campaign=plugin" class="fma__wrap">';
            echo '<h2 class="fma__heading"><strong>Slack Notification Settings</strong> <span class="fma__heading-pro-tag">PRO</span></h2>';

            echo '<table class="form-table" role="presentation"><tbody><tr><th scope="row"><label for="afmp_slack_enable">Enable Slack Notification</label></th><td><input type="checkbox" id="afmp_slack_enable" name="afmp__slack_notification_settings[enable]" value="yes"><label for="afmp_slack_enable">Enable Slack Notification</label></td></tr><tr><th scope="row"><label for="afmp_slack_webhook_url">Slack Webhook URL</label></th><td><input type="text" id="afmp_slack_webhook_url" name="afmp__slack_notification_settings[webhook_url]" class="large-text"><p class="description">Enter your Slack Webhook URL to receive notifications.</p><a href="#">Click here to get your webhook URL</a></td></tr><tr><th scope="row"><label for="afmp_slack_event_notification">Event Notification</label></th><td><label><input type="checkbox" name="afmp__slack_notification_settings[events][]" value="rm">Delete File & Folder</label><br><label><input type="checkbox" name="afmp__slack_notification_settings[events][]" value="mkfile">Create File</label><br><label><input type="checkbox" name="afmp__slack_notification_settings[events][]" value="mkdir">Create Folder</label><br></td></tr><tr><th scope="row"><label for="slack_notification_message">Slack Notification Message</label></th><td><textarea id="slack_notification_message" name="afmp__slack_notification_settings[message]" rows="5" class="large-text">A quick update from the Advance File Manager plugin on your site {site_name}.
A file was {event} by {username} on {date_time}. The file name is {file_name} with the extension {ext}, and the action was performed from the following IP Address: {ip_address}.</textarea><p class="description">Customize the message to be sent to Slack. You can use placeholders like {file_name}, {action}, etc.</p><p class="description">Available placeholders:</p><ul class="afmp-slack-placeholders"><li><code>{username}</code> - This will fetch the user’s name who did the action</li><li><code>{ip_address}</code> - This will fetch the user’s IP Address</li><li><code>{event}</code> - This will fetch which file the user has created or deleted</li><li><code>{file_name}</code> - This will fetch the file name on which that action was done</li><li><code>{ext}</code> - This will fetch the file extension on which that action was done</li><li><code>{date_time}</code> - This will fetch the date the user acted on the file</li><li><code>{site_name}</code> - This will fetch the site name on which the action was performed</li></ul></td></tr></tbody></table>';
            echo '</div>';
        }

    }

    /**
     * AI Integration (Code Pilot) Callback
     * Mirrors notification_callback pattern for the free version
     * @since 5.4.1
     */
    public function ai_integration_callback()
    {
        if (!class_exists('AFMP\\Modules\\AFMP_AI_Integration')) {
            // Defaults to avoid undefined variable notices in free view
            $enabled = '0';
            $api_key = '';
            ?>
            <style type="text/css">
                .ai__heading {
                    color: #000;
                    font-size: 18px;
                    font-weight: 600;
                    line-height: normal;
                }

                .ai__heading-pro-tag {
                    display: inline-block;
                    padding: 2px 8px;
                    background: linear-gradient(270deg, #011D33 0%, #3F6972 100%);
                    border-radius: 4px;
                    color: #fff;
                    font-size: 12px;
                    margin-left: 25px;
                }

                .ai__wrap {
                    opacity: 0.5;
                    position: relative;
                }

                .ai__wrap::before {
                    content: "";
                    display: block;
                    width: 100%;
                    height: 100%;
                    position: absolute;
                    top: 0;
                    left: 0;
                    z-index: 1;
                    background: transparent;
                }
            </style>
            <h2 class="ai__heading">AI Integration (Code Pilot) <span class="ai__heading-pro-tag">PRO</span></h2>
            <div class="ai__wrap fma__wrap"
                afmp-href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=ai_integration&utm_campaign=plugin">
                <div class="afmp-ai-settings">
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                        <?php wp_nonce_field('afmp_save_ai_settings', 'afmp_ai_nonce'); ?>
                        <input type="hidden" name="action" value="afmp_save_ai_settings" />
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="afmp_ai_enabled"><?php _e('Enable AI Integration', 'afm-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="checkbox" name="afmp_ai_enabled" id="afmp_ai_enabled" value="1" <?php checked($enabled, '1'); ?> />
                                    <p class="description">
                                        <?php _e('Enable Code Pilot AI assistant in the code editor.', 'afm-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row">
                                    <label for="afmp_gpt_api_key"><?php _e('GPT API Key', 'afm-pro'); ?></label>
                                </th>
                                <td>
                                    <input type="password" name="afmp_gpt_api_key" id="afmp_gpt_api_key"
                                        value="<?php echo esc_attr($api_key); ?>" class="regular-text" autocomplete="off" />
                                    <p class="description">
                                        <?php _e('Enter your OpenAI GPT API key. This key is stored only for your user account.', 'afm-pro'); ?>
                                    </p>
                                </td>
                            </tr>
                        </table>
                        <?php submit_button(__('Save Changes', 'afm-pro')); ?>
                    </form>

                    <div
                        style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #0073aa; border-radius: 4px;">
                        <h3 style="margin-top: 0; color: #0073aa;">How to Use Code Pilot</h3>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <h4 style="color: #333; margin-bottom: 10px;">🤖 Chat Assistant</h4>
                                <ol style="margin: 0; padding-left: 20px;">
                                    <li>Open any file in the File Manager editor</li>
                                    <li>Click the <strong>Code Pilot</strong> button (bottom right)</li>
                                    <li>Type your questions or requests in the chat</li>
                                    <li>AI will respond with code suggestions</li>
                                    <li>Click <strong>Apply</strong> to insert code into your file</li>
                                </ol>
                            </div>
                            <div>
                                <h4 style="color: #333; margin-bottom: 10px;">⚡ Inline Suggestions</h4>
                                <ol style="margin: 0; padding-left: 20px;">
                                    <li>Start typing code in the editor</li>
                                    <li>AI will show ghost text suggestions</li>
                                    <li>Press <strong>Tab</strong> to accept suggestions</li>
                                    <li>Press <strong>Esc</strong> to dismiss suggestions</li>
                                    <li>AI learns from your codebase for better suggestions</li>
                                </ol>
                            </div>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background: #fff; border-radius: 4px; border: 1px solid #ddd;">
                            <h4 style="margin-top: 0; color: #333;">💡 Example Requests</h4>
                            <ul style="margin: 0; padding-left: 20px;">
                                <li>"Add proper comments to this code"</li>
                                <li>"Fix the syntax error in this function"</li>
                                <li>"Optimize this database query"</li>
                                <li>"Add error handling to this code"</li>
                                <li>"Convert this to use WordPress hooks"</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }

    /**
     * Loading Menus
     */
    public function load_menus()
    {

        $fmaPer = $this->fmaPer();

        /** Authorizing only super admin to manage settings */
        $subPer = 'manage_options';
        if (is_multisite() && !is_network_admin()) {
            $subPer = 'manage_network';
            $fmaPer = $this->networkPer();
        }

        add_menu_page(
            __('File Manager', 'file-manager-advanced'),
            __('File Manager', 'file-manager-advanced'),
            $fmaPer,
            'file_manager_advanced_ui',
            array($this, 'file_manager_advanced_ui'),
            plugins_url('assets/icon/fma-new.png', __FILE__),
            4
        );
        add_submenu_page('file_manager_advanced_ui', 'Settings', 'Settings', $subPer, 'file_manager_advanced_controls', array(&$this, 'file_manager_advanced_controls'));

        if (!class_exists('AFMP\Modules\Integrations')) {
            add_submenu_page('file_manager_advanced_ui', 'Integrations', 'Integrations', 'manage_options', 'afm-integrations-pro', array($this, 'integrations_pro_menu'), 2);
        }

        if (!class_exists('AFMP\Modules\AFMP_AI_Integration')) {
            add_submenu_page(
                '',
                'AI - Code Pilot',
                'AI - Code Pilot <span class="update-plugins count-1" style="background: #d63638; color: #fff; font-size: 10px; padding: 2px 6px; border-radius: 10px; margin-left: 5px;">NEW</span>',
                'manage_options',
                'ai-code-pilot',
                array($this, 'ai_code_pilot_callback'),
                2
            );
        }

        // Blocks submenu is now handled by class_fma_blocks
        // Old submenu removed - blocks post type is used instead
        // if(!class_exists('file_manager_advanced_shortcode')) {
        //     add_submenu_page( 'file_manager_advanced_ui', 'Blocks', 'Blocks', $subPer, 'file_manager_advanced_shortcodes', array(&$this, 'file_manager_advanced_shortcodes'));
        // }

        if (!class_exists('AFMP\\Modules\\Adminer')) {
            add_submenu_page('file_manager_advanced_ui', 'DB Access', 'DB Access', 'manage_options', 'afmp-adminer', array($this, 'adminer_menu'));
        }

        if (!class_exists('AFMP\\Modules\\Dropbox')) {
            add_submenu_page('', 'Dropbox Settings', 'Dropbox', 'manage_options', 'afmp-dropbox', array($this, 'dropbox_menu'));
        }

        if (!class_exists('AFMP\\Modules\\FileLogs')) {
            add_submenu_page('file_manager_advanced_ui', 'File Logs', 'File Logs', 'manage_options', 'afmp-file-logs', array($this, 'afmp__file_logs'), 3);
        }

        if (!class_exists('AFMP\\Modules\\GoogleDrive')) {
            add_submenu_page('', 'Google Drive Settings', 'Google Drive', 'manage_options', 'afmp-googledrive', array($this, 'googledrive_menu'));
        }

        if (!class_exists('AFMP\Modules\Onedrive')) {
            add_submenu_page('', 'OneDrive Settings', 'OneDrive', 'manage_options', 'afmp-onedrive', array($this, 'onedrive_menu'));
        }

        if (!class_exists('AFMP\\Modules\\AmazonS3')) {
            add_submenu_page('', 'Amazon S3 (AWS) Settings', 'Amazon S3 (AWS)', 'manage_options', 'afmp-aws', array($this, 'aws_menu'));
        }

        if (!class_exists('AFMP\\Modules\\GitHub')) {
            add_submenu_page('', 'GitHub Settings', 'GitHub', 'manage_options', 'afmp-github', array($this, 'github_menu'));
        }

        if (!class_exists('AFMP\\Modules\\GoogleCloud')) {
            add_submenu_page('', 'Google Cloud Settings', 'Google Cloud', 'manage_options', 'afmp-googlecloud', array($this, 'googlecloud_menu'));
        }

        // Add GET PRO menu item at the bottom (only if pro plugin is not active)
        if (!class_exists('file_manager_advanced_shortcode')) {
            add_submenu_page(
                'file_manager_advanced_ui',
                'GET PRO',
                'GET PRO',
                'manage_options',
                'fma-get-pro',
                '__return_null'
            );

            if (isset($_GET['page']) && 'fma-get-pro' === sanitize_text_field(wp_unslash($_GET['page']))) {
                wp_redirect(
                    add_query_arg(
                        array(
                            'utm_source' => 'wordpress_admin_menu',
                            'utm_medium' => 'website',
                            'utm_campaign' => 'free_plugin',
                        ),
                        'https://advancedfilemanager.com/pricing/'
                    )
                );
                exit;
            }
        }

        // Add CSS styling for GET PRO menu
        add_action('admin_head', array($this, 'get_pro_menu_styles'));

        // Add JavaScript to redirect GET PRO menu directly
        // add_action('admin_footer', array($this, 'get_pro_menu_redirect_script'));
    }

    /**
     * GET PRO menu styles
     */
    public function get_pro_menu_styles()
    {
        ?>
        <style type="text/css">
            /* Style GET PRO menu item with gradient - Multiple selectors for better compatibility */
            #toplevel_page_file_manager_advanced_ui .wp-submenu a[href*="fma-get-pro"],
            #toplevel_page_file_manager_advanced_ui .wp-submenu li a[href*="fma-get-pro"],
            #adminmenu #toplevel_page_file_manager_advanced_ui .wp-submenu a[href*="fma-get-pro"],
            #adminmenu #toplevel_page_file_manager_advanced_ui .wp-submenu a.fma-get-pro-highlight {
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%) !important;
                background-color: #011D33 !important;
                color: #fff !important;
                font-weight: 600 !important;
                border-left: 4px solid #011D33 !important;
            }

            #toplevel_page_file_manager_advanced_ui .wp-submenu a[href*="fma-get-pro"]:hover,
            #toplevel_page_file_manager_advanced_ui .wp-submenu li a[href*="fma-get-pro"]:hover,
            #toplevel_page_file_manager_advanced_ui .wp-submenu a.fma-get-pro-highlight:hover,
            #adminmenu #toplevel_page_file_manager_advanced_ui .wp-submenu a[href*="fma-get-pro"]:hover,
            #adminmenu #toplevel_page_file_manager_advanced_ui .wp-submenu a.fma-get-pro-highlight:hover {
                background: linear-gradient(270deg, #001020 0%, #2d4f57 100%) !important;
                background-color: #001020 !important;
                color: #fff !important;
                border-left-color: #001020 !important;
            }

            /* Ensure the list item also has the gradient background */
            #toplevel_page_file_manager_advanced_ui .wp-submenu li:has(a[href*="fma-get-pro"]),
            #adminmenu #toplevel_page_file_manager_advanced_ui .wp-submenu li:has(a[href*="fma-get-pro"]) {
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%) !important;
                background-color: #011D33 !important;
            }

            #toplevel_page_file_manager_advanced_ui .wp-submenu li:has(a[href*="fma-get-pro"]):hover,
            #adminmenu #toplevel_page_file_manager_advanced_ui .wp-submenu li:has(a[href*="fma-get-pro"]):hover {
                background: linear-gradient(270deg, #001020 0%, #2d4f57 100%) !important;
                background-color: #001020 !important;
            }

            /* Fallback for browsers that don't support :has() */
            #toplevel_page_file_manager_advanced_ui .wp-submenu li a[href*="fma-get-pro"],
            #toplevel_page_file_manager_advanced_ui .wp-submenu li a.fma-get-pro-highlight {
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%) !important;
            }

            /* Add crown icon */
            #toplevel_page_file_manager_advanced_ui .wp-submenu a[href*="fma-get-pro"]::before,
            #toplevel_page_file_manager_advanced_ui .wp-submenu a.fma-get-pro-highlight::before {
                content: "";
                display: inline-block;
                width: 15px;
                height: 15px;
                margin-right: 8px;
                vertical-align: middle;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FFD700'%3E%3Cpath d='M5 16L3 5L8.5 10L12 4L15.5 10L21 5L19 16H5M19 19C19 19.6 18.6 20 18 20H6C5.4 20 5 19.6 5 19V18H19V19Z' /%3E%3C/svg%3E");
                background-repeat: no-repeat;
                background-size: contain;
            }
        </style>
        <script type="text/javascript">
            (function ($) {
                // Apply styles via JavaScript to ensure they persist after reload
                function applyGetProStyles() {
                    var $getProLink = $('#toplevel_page_file_manager_advanced_ui .wp-submenu a[href*="fma-get-pro"], #toplevel_page_file_manager_advanced_ui .wp-submenu a.fma-get-pro-highlight');
                    if ($getProLink.length) {
                        // Add highlight class for persistent styling
                        $getProLink.addClass('fma-get-pro-highlight');

                        $getProLink.css({
                            'background': 'linear-gradient(270deg, #011D33 0%, #3F6972 100%)',
                            'background-color': '#011D33',
                            'color': '#fff',
                            'font-weight': '600',
                            'border-left': '4px solid #011D33'
                        });

                        // Set target to _blank and update href for direct redirection in new tab
                        $getProLink.attr('target', '_blank');
                        var pricingUrl = 'https://advancedfilemanager.com/pricing/?utm_source=wordpress_admin_menu&utm_medium=website&utm_campaign=free_plugin';
                        if ($getProLink.attr('href') !== pricingUrl) {
                            $getProLink.attr('href', pricingUrl);
                        }

                        $getProLink.on('mouseenter', function () {
                            $(this).css({
                                'background': 'linear-gradient(270deg, #001020 0%, #2d4f57 100%)',
                                'background-color': '#001020',
                                'border-left-color': '#001020'
                            });
                        }).on('mouseleave', function () {
                            $(this).css({
                                'background': 'linear-gradient(270deg, #011D33 0%, #3F6972 100%)',
                                'background-color': '#011D33',
                                'border-left-color': '#011D33'
                            });
                        });
                    }
                }

                $(document).ready(function () {
                    applyGetProStyles();
                });

                // Reapply styles after menu toggle (WordPress admin menu collapse/expand)
                $(document).on('wp-menu-state-change', function () {
                    setTimeout(applyGetProStyles, 100);
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * GET PRO callback - redirect to pricing page (fallback if JS doesn't work)
     */
    public function get_pro_callback()
    {
        $pricing_url = 'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=file_manager_get_pro_menu&utm_campaign=plugin';
        wp_redirect($pricing_url);
        exit;
    }

    /**
     * AI Code Pilot callback - redirect to settings
     */
    public function ai_code_pilot_callback()
    {
        $target_url = admin_url('admin.php?page=file_manager_advanced_controls&tab=ai');
        ?>
        <script>
            window.location.href = '<?php echo $target_url; ?>';
        </script>
        <p>Redirecting to AI Integration Settings... <a href="<?php echo $target_url; ?>">Click here</a></p>
        <?php
        exit;
    }

    /**
     * Dropbox menu
     * @since 6.7.2
     */
    public function dropbox_menu()
    {

        echo '
        <h2 class="dropbox__heading">Dropbox Settings <span class="dropbox__heading-pro-tag">PRO</span></h2>

        <div class="dropbox__wrap">
            <table class="form-table">
                <tr>
                    <th>
                        <lable for="fma__enable">Enable</lable>
                    </th>
                    <td>
                        <input type="checkbox" id="fma__enable">
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__alias">Alias</label>
                    </th>
                    <td>
                        <input type="text" id="afm__alias" class="regular-text">
                        <p class="desc">
                            <strong>Enter a title which will be displayed on File Manager</strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__app_key">App Key</label>
                    </th>
                    <td>
                        <input type="text" id="afm__app_key" class="regular-text">
                        <p class="desc">
                            <strong>Enter your Dropbox App key, you will get your app key from <a href="#">Dropbox App Console</a></strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__app_secret">App Secret</label>
                    </th>
                    <td>
                        <input type="text" id="afm__app_secret" class="regular-text">
                        <p class="desc">
                            <strong>Enter your Dropbox App secret, you will get your app secret from <a href="#">Dropbox App Console</a></strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__redirect_url">Redirect URL</label>
                    </th>
                    <td>
                        <input type="text" id="afm__redirect_uri" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Copy this URL and paste it in your Dropbox App Console under Redirect URIs
                            </strong>
                        </p>
                    </td>
                </tr>
            </table>';

        submit_button();

        echo '</div>';
    }

    /**
     * OneDrive menu
     * @since 6.7.3
     */
    public function onedrive_menu()
    {

        echo '<style>
            .onedrive__heading {
                color: #000;
                font-size: 18px;
                font-style: normal;
                font-weight: 600;
                line-height: normal;
            }
            
            .onedrive__heading-pro-tag {
                display: inline-block;
                padding: 2px 8px;
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%);
                border-radius: 4px;
                color: #fff;
                font-size: 12px;
                margin-left: 25px;
            }
            
            .onedrive__wrap {
                opacity: 0.5;
                position:relative;
            }
            
            .onedrive__wrap::before {
                content: "";
                display: block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1;
                background: transparent;
            }
        </style>';

        echo '<h2 class="onedrive__heading">One Drive Settings <span class="onedrive__heading-pro-tag">PRO</span></h2>';
        echo '<div class="onedrive__wrap" afmp-href="">';
        echo '<h2></h2>';
        echo '<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="onedrive-enable">Enable</label>
						</th>
						<td>
							<input type="checkbox" name="afmp__onedrive_settings[enable]" id="onedrive-enable" value="yes">
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="onedrive-alias">Alias</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="afmp__onedrive_settings[title]" id="onedrive-alias" value=""><p class="desc"><strong>Enter a title which will be displayed on File Manager</strong></p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="onedrive-app-id">Application (client) ID</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="afmp__onedrive_settings[app_id]" id="onedrive-app-id" value="">
							<p class="desc">
								<strong>Enter your OneDrive Application (client) ID from your <a href="#">Azure AD app registration.</a></strong>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="onedrive-app-secret">Client secret</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="afmp__onedrive_settings[app_secret]" id="onedrive-app-secret" value="">
							<p class="desc">
								<strong>Enter your OneDrive Client secret from your <a href="#">Azure AD app registration.</a></strong>
							</p>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="onedrive-redirect-uri">Redirect URI</label>
						</th>
						<td>
							<input class="regular-text" type="text" name="afmp__onedrive_settings[redirect_uri]" id="onedrive-redirect-uri" value="">
							<p class="desc">
								<strong>Copy this URL and paste it in your Azure AD app registration under Redirect URIs.</strong>
							</p>
						</td>
					</tr>
				</tbody>
			</table>';

        submit_button();

        echo '</div>';
    }

    /**
     * Google Drive menu
     * @since 6.7.2
     */
    public function googledrive_menu()
    {

        echo '<style type="text/css">
            .googledrive__heading {
                color: #000;
                font-size: 18px;
                font-style: normal;
                font-weight: 600;
                line-height: normal;
            }
            
            .googledrive__heading-pro-tag {
                display: inline-block;
                padding: 2px 8px;
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%);
                border-radius: 4px;
                color: #fff;
                font-size: 12px;
                margin-left: 25px;
            }
            
            .googledrive__wrap {
                opacity: 0.5;
                position:relative;
            }
            
            .googledrive__wrap::before {
                content: "";
                display: block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1;
                background: transparent;
            }
        </style>
        <h2 class="googledrive__heading">Google Drive Settings <span class="googledrive__heading-pro-tag">PRO</span></h2>

        <div class="googledrive__wrap">
            <table class="form-table">
                <tr>
                    <th>
                        <lable for="fma__enable">Enable</lable>
                    </th>
                    <td>
                        <input type="checkbox" id="fma__enable">
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__alias">Alias</label>
                    </th>
                    <td>
                        <input type="text" id="afm__alias" value="" class="regular-text">
                        <p class="desc">
                            <strong>Enter a title which will be displayed on File Manager</strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__app_key">App Key</label>
                    </th>
                    <td>
                        <input type="text" id="afm__app_key" class="regular-text">
                        <p class="desc">
                            <strong>Enter your Google Drive App key, you will get your app key from <a href="#">Google Drive App Console</a></strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__app_secret">App Secret</label>
                    </th>
                    <td>
                        <input type="text" id="afm__app_secret" class="regular-text">
                        <p class="desc">
                            <strong>Enter your Google Drive App secret, you will get your app secret from <a href="#">Google Drive App Console</a></strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__redirect_url">Javascript Origin</label>
                    </th>
                    <td>
                        <input type="text" id="afm__redirect_uri" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Copy this URL and paste it in your Google Drive App Console under JavaScripts Origins
                            </strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__redirect_url">Redirect URL</label>
                    </th>
                    <td>
                        <input type="text" id="afm__redirect_uri" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Copy this URL and paste it in your Google Drive App Console under Redirect URIs
                            </strong>
                        </p>
                    </td>
                </tr>
            </table>';

        submit_button();

        echo '</div>';
    }

    /**
     * GitHub menu
     * @since 6.7.2
     */
    public function github_menu()
    {

        echo '<style type="text/css">
            .github__heading {
                color: #000;
                font-size: 18px;
                font-style: normal;
                font-weight: 600;
                line-height: normal;
            }

            .github__heading-pro-tag {
                display: inline-block;
                padding: 2px 8px;
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%);
                border-radius: 4px;
                color: #fff;
                font-size: 12px;
                margin-left: 25px;
            }

            .github__wrap {
                opacity: 0.5;
                position:relative;
            }

            .github__wrap::before {
                content: "";
                display: block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1;
                background: transparent;
            }
        </style>
        <h2 class="github__heading">GitHub Settings <span class="github__heading-pro-tag">PRO</span></h2>

        <div class="github__wrap">
            <table class="form-table">
                <tr>
                    <th>
                        <label for="fma__enable">Enable</label>
                    </th>
                    <td>
                        <input type="checkbox" id="fma__enable">
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__email">GitHub Email</label>
                    </th>
                    <td>
                        <input type="text" id="afm__email" class="regular-text">
                        <p class="desc">
                            <strong>Enter your email which you use for your GitHub account</strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__username">GitHub Username <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="afm__username" class="regular-text">
                        <p class="desc">
                            <strong>Enter your GitHub username</strong>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="afm__pat">GitHub PAT <span class="required">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="afm__pat" class="regular-text">
                        <p class="desc">
                            <strong>Enter GitHub Personal Access Token (PAT) for your account <a href="#">Click here to get Github PAT</a></strong>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="afm__access_dir">GitHub Access Directory</label>
                    </th>
                    <td>
                        <input type="text" id="afm__access_dir" class="regular-text">

                        <p class="desc">
                            <strong>This field is not a required as a default directory is set, but this can be changed</strong>
                        </p>
                    </td>
                </tr>

                <tr>
                    <th>
                        <label for="afm__master_dir">GitHub Master Access Directory</label>
                    </th>
                    <td>
                        <input type="text" id="afm__master_dir" class="regular-text">

                        <p class="desc">
                            <strong>This field is not a required, and if left empty all configuration will apply to the whole repository</strong>
                        </p>
                    </td>
                </tr>
            </table>';
        submit_button();
        echo '</div>';
    }

    /**
     * Amazon S3 (AWS) menu
     * @since 5.3.8
     */
    public function aws_menu()
    {

        echo '<style type="text/css">
            .aws__heading {
                color: #000;
                font-size: 18px;
                font-style: normal;
                font-weight: 600;
                line-height: normal;
            }
            
            .aws__heading-pro-tag {
                display: inline-block;
                padding: 2px 8px;
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%);
                border-radius: 4px;
                color: #fff;
                font-size: 12px;
                margin-left: 25px;
            }
            
            .aws__wrap {
                opacity: 0.5;
                position:relative;
            }
            
            .aws__wrap::before {
                content: "";
                display: block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1;
                background: transparent;
            }
        </style>
        <h2 class="aws__heading">Amazon S3 (AWS) Settings <span class="aws__heading-pro-tag">PRO</span></h2>

        <div class="aws__wrap">
            <table class="form-table">
                <tr>
                    <th>
                        <lable for="fma__enable">Enable</lable>
                    </th>
                    <td>
                        <input type="checkbox" id="fma__enable">
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__alias">Alias</label>
                    </th>
                    <td>
                        <input type="text" id="afm__alias" class="regular-text">
                        <p class="desc">
                            <strong>Enter a title which will be displayed on File Manager</strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__app_key">App Key <span style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="afm__app_key" class="regular-text">
                        <p class="desc">
                            <strong>Enter your Amazon S3 (AWS) App key. <a href="#">Learn how to get your credentials</a></strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__app_secret">App Secret <span style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="afm__app_secret" class="regular-text">
                        <p class="desc">
                            <strong>Enter your Amazon S3 (AWS) App secret. <a href="#">Learn how to get your credentials</a></strong>
                        </p>
                    </td>
                </tr>
                
                <tr>
                    <th>
                        <label for="afm__region">Region <span style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="afm__region" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Enter the AWS region where your bucket is located (e.g. us-east-1)
                            </strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__bucket">Bucket <span style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="afm__bucket" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Enter the name of your Amazon S3 bucket
                            </strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__user_role_access">User Role Access</label>
                    </th>
                    <td>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Editor</label><br>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Author</label><br>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Contributor</label><br>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Subscriber</label><br>
                        <p class="desc">
                            <strong>
                                Select the user roles allowed to access the AWS-connected file manager
                            </strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__private_folder_access">Private Folder Access</label>
                    </th>
                    <td>
                        <input type="text" id="afm__private_folder_access" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Enter a folder name to restrict access to that folder only. Leave blank to use the root directory (e.g. awsfolder) 
                            </strong>
                        </p>
                    </td>
                </tr>
            </table>';

        submit_button();

        echo '</div>';
    }

    /**
     * Google Cloud menu
     * @since 5.3.8
     */
    public function googlecloud_menu()
    {

        echo '<style type="text/css">
            .googlecloud__heading {
                color: #000;
                font-size: 18px;
                font-style: normal;
                font-weight: 600;
                line-height: normal;
            }
            
            .googlecloud__heading-pro-tag {
                display: inline-block;
                padding: 2px 8px;
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%);
                border-radius: 4px;
                color: #fff;
                font-size: 12px;
                margin-left: 25px;
            }
            
            .googlecloud__wrap {
                opacity: 0.5;
                position:relative;
            }
            
            .googlecloud__wrap::before {
                content: "";
                display: block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                z-index: 1;
                background: transparent;
            }
        </style>
        <h2 class="googlecloud__heading">Google Cloud Settings <span class="googlecloud__heading-pro-tag">PRO</span></h2>

        <div class="googlecloud__wrap">
            <table class="form-table">
                <tr>
                    <th>
                        <lable for="fma__enable">Enable</lable>
                    </th>
                    <td>
                        <input type="checkbox" id="fma__enable">
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__alias">Alias</label>
                    </th>
                    <td>
                        <input type="text" id="afm__alias" class="regular-text">
                        <p class="desc">
                            <strong>Enter a title which will be displayed on File Manager</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__auth_key">Authentication Key File <span style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="file" id="afm__auth_key" class="regular-text" accept=".json">
                        <p class="desc">
                            <strong>Upload your Google Cloud service account key file (.json format)</strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__bucket">Bucket <span style="color: red;">*</span></label>
                    </th>
                    <td>
                        <input type="text" id="afm__bucket" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Enter the name of your Google Cloud Storage bucket
                            </strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__user_role_access">User Role Access</label>
                    </th>
                    <td>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Editor</label><br>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Author</label><br>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Contributor</label><br>
                        <input type="checkbox" id="editor_role" value class="regular-text">
                        <label for="editor_role">Subscriber</label><br>
                        <p class="desc">
                            <strong>
                                Select the user roles allowed to access the Google Cloud file manager
                            </strong>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th>
                        <label for="afm__private_folder_access">Private Folder Access</label>
                    </th>
                    <td>
                        <input type="text" id="afm__private_folder_access" class="regular-text">
                        
                        <p class="desc">
                            <strong>
                                Enter a subfolder path within your bucket. Leave blank to use the root directory 
                            </strong>
                        </p>
                        <p class="note" style="color: red;">
                            <strong>Note: </strong>Folder paths and user role values are case-sensitive
                        </p>
                    </td>
                </tr>
            </table>';

        submit_button();

        echo '</div>';
    }

    /**
     * Adminer menu
     * @since 6.7.2
     */
    public function adminer_menu()
    {
        require_once FMAFILEPATH . 'templates/adminer.php';
    }

    public function afmp__file_logs()
    {
        echo <<<HTML
<div class="wrap">

<h2 class="filelogs__heading">File Logs <span class="filelogs__heading-pro-tag">PRO</span></h2>


<div class="file-logs__wrap" afmp-href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=file_log_banner&utm_campaign=plugin">
<div class="afma-datatable-header"><a class="button button-secondary" href="#" style="float: right;">Delete All</a><form method="get">
					
					<input type="hidden" name="page" value="afmp-file-logs">
				    <input type="hidden" name="_wpnonce" value="449f66b7cf">
					
					<select name="filter" id="filter" class="afmp__select">
						<option value="all">All</option><option value="renamed">Renamed</option><option value="duplicated">Duplicated</option><option value="uploaded">Uploaded</option><option value="created">Created</option><option value="deleted">Deleted</option><option value="pasted">Pasted</option><option value="updated">Updated</option>
					</select>
					
					<input type="text" name="date-range" id="date-range" class="afmp__input" autocomplete="off" value="" placeholder="Date Range">
					
					<input type="submit" class="button button-secondary" value="Filter">
				</form></div><table class="wp-list-table widefat fixed striped table-view-list ">
			<thead>
	<tr>
		<th scope="col" id="id" class="manage-column column-id column-primary sortable asc"><a href="#"><span>ID</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" id="user_name" class="manage-column column-user_name sortable desc"><a href="https://file-manager-advanced.test/wp-admin/admin.php?page=afmp-file-logs&amp;orderby=user_id&amp;order=asc"><span>User</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort ascending.</span></a></th><th scope="col" id="time" class="manage-column column-time sortable asc"><a href="#"><span>Date &amp; Time</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" id="action" class="manage-column column-action sortable asc"><a href="#"><span>Event</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" id="file_path" class="manage-column column-file_path sortable asc"><a href="#"><span>File Path</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" id="type" class="manage-column column-type sortable asc"><a href="#"><span>Type</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" id="ip" class="manage-column column-ip sortable asc"><a href="#"><span>IP Address</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" id="actions" class="manage-column column-actions">Actions</th>	</tr>
	</thead>

	<tbody id="the-list">
		<tr><td class="id column-id has-row-actions column-primary" data-colname="ID">6<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="user_name column-user_name" data-colname="User">admin<br>john@flywheel.local</td><td class="time column-time" data-colname="Date &amp; Time">May 02, 2025 12:40 pm</td><td class="action column-action" data-colname="Event">uploaded</td><td class="file_path column-file_path" data-colname="File Path">D:\Local Sites\file-manager-advanced\app\public\wp-content\HeidiSQL.lnk</td><td class="type column-type" data-colname="Type">application/x-ms-shortcut</td><td class="ip column-ip" data-colname="IP Address">127.0.0.1</td><td class="actions column-actions" data-colname="Actions"><div>
				<a class="afmp-show-details" afmp-details="The User ***(admin)*** with IP Address ***(127.0.0.1)*** has just ***(uploaded)*** the file ***(D:\Local Sites\file-manager-advanced\app\public\wp-content\HeidiSQL.lnk)*** of type ***(application/x-ms-shortcut)*** on ***(May 02, 2025 12:40 pm)*** using File Manager on website ***(https://file-manager-advanced.test)***" href="#">
					<span class="dashicons dashicons-visibility"></span>
				</a>
				<a href="#" onclick="return confirm( 'Are you sure you want to delete this file log?' )">
					<span class="dashicons dashicons-trash"></span>
				</a>
			</div></td></tr><tr><td class="id column-id has-row-actions column-primary" data-colname="ID">5<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="user_name column-user_name" data-colname="User">admin<br>john@flywheel.local</td><td class="time column-time" data-colname="Date &amp; Time">May 02, 2025 12:39 pm</td><td class="action column-action" data-colname="Event">pasted</td><td class="file_path column-file_path" data-colname="File Path">D:\Local Sites\file-manager-advanced\app\public\wp-content\index.php</td><td class="type column-type" data-colname="Type">text/x-php</td><td class="ip column-ip" data-colname="IP Address">127.0.0.1</td><td class="actions column-actions" data-colname="Actions"><div>
				<a class="afmp-show-details" afmp-details="The User ***(admin)*** with IP Address ***(127.0.0.1)*** has just ***(pasted)*** the file ***(D:\Local Sites\file-manager-advanced\app\public\wp-content\index.php)*** of type ***(text/x-php)*** on ***(May 02, 2025 12:39 pm)*** using File Manager on website ***(https://file-manager-advanced.test)***" href="#">
					<span class="dashicons dashicons-visibility"></span>
				</a>
				<a href="#" onclick="return confirm( 'Are you sure you want to delete this file log?' )">
					<span class="dashicons dashicons-trash"></span>
				</a>
			</div></td></tr><tr><td class="id column-id has-row-actions column-primary" data-colname="ID">4<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="user_name column-user_name" data-colname="User">admin<br>john@flywheel.local</td><td class="time column-time" data-colname="Date &amp; Time">May 02, 2025 12:39 pm</td><td class="action column-action" data-colname="Event">deleted</td><td class="file_path column-file_path" data-colname="File Path">D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\deletedme.php</td><td class="type column-type" data-colname="Type">text/x-php</td><td class="ip column-ip" data-colname="IP Address">127.0.0.1</td><td class="actions column-actions" data-colname="Actions"><div>
				<a class="afmp-show-details" afmp-details="The User ***(admin)*** with IP Address ***(127.0.0.1)*** has just ***(deleted)*** the file ***(D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\deletedme.php)*** of type ***(text/x-php)*** on ***(May 02, 2025 12:39 pm)*** using File Manager on website ***(https://file-manager-advanced.test)***" href="#">
					<span class="dashicons dashicons-visibility"></span>
				</a>
				<a href="#" onclick="return confirm( 'Are you sure you want to delete this file log?' )">
					<span class="dashicons dashicons-trash"></span>
				</a>
			</div></td></tr><tr><td class="id column-id has-row-actions column-primary" data-colname="ID">3<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="user_name column-user_name" data-colname="User">admin<br>john@flywheel.local</td><td class="time column-time" data-colname="Date &amp; Time">May 02, 2025 12:39 pm</td><td class="action column-action" data-colname="Event">updated</td><td class="file_path column-file_path" data-colname="File Path">D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\deletedme.php</td><td class="type column-type" data-colname="Type">text/x-php</td><td class="ip column-ip" data-colname="IP Address">127.0.0.1</td><td class="actions column-actions" data-colname="Actions"><div>
				<a class="afmp-show-details" afmp-details="The User ***(admin)*** with IP Address ***(127.0.0.1)*** has just ***(updated)*** the file ***(D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\deletedme.php)*** of type ***(text/x-php)*** on ***(May 02, 2025 12:39 pm)*** using File Manager on website ***(https://file-manager-advanced.test)***" href="#">
					<span class="dashicons dashicons-visibility"></span>
				</a>
				<a href="#" onclick="return confirm( 'Are you sure you want to delete this file log?' )">
					<span class="dashicons dashicons-trash"></span>
				</a>
			</div></td></tr><tr><td class="id column-id has-row-actions column-primary" data-colname="ID">2<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="user_name column-user_name" data-colname="User">admin<br>john@flywheel.local</td><td class="time column-time" data-colname="Date &amp; Time">May 02, 2025 12:39 pm</td><td class="action column-action" data-colname="Event">renamed</td><td class="file_path column-file_path" data-colname="File Path">D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\index copy 1.php</td><td class="type column-type" data-colname="Type">text/x-php</td><td class="ip column-ip" data-colname="IP Address">127.0.0.1</td><td class="actions column-actions" data-colname="Actions"><div>
				<a class="afmp-show-details" afmp-details="The User ***(admin)*** with IP Address ***(127.0.0.1)*** has just ***(renamed)*** the file ***(D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\index copy 1.php)*** of type ***(text/x-php)*** on ***(May 02, 2025 12:39 pm)*** using File Manager on website ***(https://file-manager-advanced.test)***" href="#">
					<span class="dashicons dashicons-visibility"></span>
				</a>
				<a href="#" onclick="return confirm( 'Are you sure you want to delete this file log?' )">
					<span class="dashicons dashicons-trash"></span>
				</a>
			</div></td></tr><tr><td class="id column-id has-row-actions column-primary" data-colname="ID">1<button type="button" class="toggle-row"><span class="screen-reader-text">Show more details</span></button></td><td class="user_name column-user_name" data-colname="User">admin<br>john@flywheel.local</td><td class="time column-time" data-colname="Date &amp; Time">May 02, 2025 12:38 pm</td><td class="action column-action" data-colname="Event">duplicated</td><td class="file_path column-file_path" data-colname="File Path">D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\index copy 1.php</td><td class="type column-type" data-colname="Type">text/x-php</td><td class="ip column-ip" data-colname="IP Address">127.0.0.1</td><td class="actions column-actions" data-colname="Actions"><div>
				<a class="afmp-show-details" afmp-details="The User ***(admin)*** with IP Address ***(127.0.0.1)*** has just ***(duplicated)*** the file ***(D:\Local Sites\file-manager-advanced\app\public\wp-content\themes\index copy 1.php)*** of type ***(text/x-php)*** on ***(May 02, 2025 12:38 pm)*** using File Manager on website ***(https://file-manager-advanced.test)***" href="#">
					<span class="dashicons dashicons-visibility"></span>
				</a>
				<a href="#" onclick="return confirm( 'Are you sure you want to delete this file log?' )">
					<span class="dashicons dashicons-trash"></span>
				</a>
			</div></td></tr>	</tbody>

	<tfoot>
	<tr>
		<th scope="col" class="manage-column column-id column-primary sortable asc"><a href="#"><span>ID</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" class="manage-column column-user_name sortable desc"><a href="https://file-manager-advanced.test/wp-admin/admin.php?page=afmp-file-logs&amp;orderby=user_id&amp;order=asc"><span>User</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort ascending.</span></a></th><th scope="col" class="manage-column column-time sortable asc"><a href="#"><span>Date &amp; Time</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" class="manage-column column-action sortable asc"><a href="#"><span>Event</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" class="manage-column column-file_path sortable asc"><a href="#"><span>File Path</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" class="manage-column column-type sortable asc"><a href="#"><span>Type</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" class="manage-column column-ip sortable asc"><a href="#"><span>IP Address</span><span class="sorting-indicators"><span class="sorting-indicator asc" aria-hidden="true"></span><span class="sorting-indicator desc" aria-hidden="true"></span></span> <span class="screen-reader-text">Sort descending.</span></a></th><th scope="col" class="manage-column column-actions">Actions</th>	</tr>
	</tfoot>

</table>
		<div class="afma-datatable-header">	<div class="tablenav bottom">

				<div class="alignleft actions bulkactions">
					</div>
			<div class="tablenav-pages one-page"><span class="displaying-num">6 items</span>
<span class="pagination-links"><span class="tablenav-pages-navspan button disabled" aria-hidden="true">«</span>
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">‹</span>
<span class="screen-reader-text">Current Page</span><span id="table-paging" class="paging-input"><span class="tablenav-paging-text">1 of <span class="total-pages">1</span></span></span>
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">›</span>
<span class="tablenav-pages-navspan button disabled" aria-hidden="true">»</span></span></div>
		<br class="clear">
	</div>
		</div></div></div>
HTML;

    }

    /** 
     * Fma permissions
     */
    public function fmaPer()
    {
        $settings = $this->get();
        $user = wp_get_current_user();
        $allowed_fma_user_roles = isset($settings['fma_user_roles']) ? $settings['fma_user_roles'] : array('administrator');

        if (!in_array('administrator', $allowed_fma_user_roles)) {
            $fma_user_roles = array_merge(array('administrator'), $allowed_fma_user_roles);
        } else {
            $fma_user_roles = $allowed_fma_user_roles;
        }

        $checkUserRoleExistance = array_intersect($fma_user_roles, $user->roles);

        if (count($checkUserRoleExistance) > 0 && !in_array('administrator', $checkUserRoleExistance)) {
            $fmaPer = 'read';
        } else {
            $fmaPer = 'manage_options';
        }
        return $fmaPer;
    }
    /**
     * Fma - Network Permissions
     */
    public function networkPer()
    {
        $settings = $this->get();
        $user = wp_get_current_user();
        $allowed_fma_user_roles = isset($settings['fma_user_roles']) ? $settings['fma_user_roles'] : array();

        $fma_user_roles = $allowed_fma_user_roles;

        $checkUserRoleExistance = array_intersect($fma_user_roles, $user->roles);

        if (count($checkUserRoleExistance) > 0) {
            if (!in_array('administrator', $checkUserRoleExistance)) {
                $fmaPer = 'read';
            } else {
                $fmaPer = 'manage_options';
            }
        } else {
            $fmaPer = 'manage_network';
        }
        return $fmaPer;
    }
    /**
     * Diaplying AFM
     */
    public function file_manager_advanced_ui()
    {
        $fmaPer = $this->fmaPer();
        if (current_user_can($fmaPer)) {
            include('pages/main.php');
        }
    }
    /**
     * Settings
     */
    public function file_manager_advanced_controls()
    {
        if (current_user_can('manage_options')) {
            include('pages/controls.php');
        }
    }
    /**
     * Shortcode
     */
    public function file_manager_advanced_shortcodes()
    {
        if (current_user_can('manage_options')) {
            include('pages/buy_shortcode.php');
        }
    }
    /**
     * Saving Options
     */
    public function save()
    {
        if (isset($_POST['submit']) && wp_verify_nonce($_POST['_fmaform'], 'fmaform')) {
            $save = array();
            $defaultRole = array('administrator');
            if (is_multisite()) {
                $defaultRole = array();
            }
            $public_dir = isset($_POST['public_path']) ? sanitize_text_field($_POST['public_path']) : '';
            $save['fma_user_roles'] = isset($_POST['fma_user_role']) ? array_map('sanitize_text_field', $_POST['fma_user_role']) : $defaultRole;
            $save['fma_theme'] = isset($_POST['fma_theme']) ? sanitize_text_field($_POST['fma_theme']) : 'light';
            $save['fma_locale'] = isset($_POST['fma_locale']) ? sanitize_text_field($_POST['fma_locale']) : 'en';
            /* Directory Traversal fix @220723 */
            $save['public_path'] = $this->afm_sanitize_directory($public_dir);
            $save['public_url'] = isset($_POST['public_url']) ? sanitize_text_field($_POST['public_url']) : '';
            //25122022
            $save['upload_max_size'] = isset($_POST['upload_max_size']) ? sanitize_text_field($_POST['upload_max_size']) : '0';
            $save['display_ui_options'] = isset($_POST['display_ui_options']) ? array_map('sanitize_text_field', $_POST['display_ui_options']) : array();
            $save['hide_path'] = isset($_POST['hide_path']) ? sanitize_text_field($_POST['hide_path']) : 0;
            $save['enable_trash'] = isset($_POST['enable_trash']) ? sanitize_text_field($_POST['enable_trash']) : 0;
            $save['enable_htaccess'] = isset($_POST['enable_htaccess']) ? sanitize_text_field($_POST['enable_htaccess']) : 0;
            $save['fma_upload_allow'] = isset($_POST['fma_upload_allow']) ? sanitize_text_field($_POST['fma_upload_allow']) : 'all';
            $save['fma_cm_theme'] = isset($_POST['fma_cm_theme']) ? sanitize_text_field($_POST['fma_cm_theme']) : 'default';
            $save['fma_debug_enabled'] = isset($_POST['fma_debug_enabled']) ? sanitize_text_field($_POST['fma_debug_enabled']) : 0;
            $save['fma_delete_data_on_uninstall'] = isset($_POST['fma_delete_data_on_uninstall']) ? sanitize_text_field($_POST['fma_delete_data_on_uninstall']) : 0;
            update_option('fmaoptions', $save);
            $this->f('?page=file_manager_advanced_controls&status=1');
        }
    }
    /**
     * Sanitize directory path
     */
    public function afm_sanitize_directory($path = '')
    {
        if (!empty($path)) {
            $path = str_replace('..', '', htmlentities(trim($path)));
        }
        return $path;
    }
    /**
     * Getting Options
     */
    public function get()
    {
        return get_option('fmaoptions');
    }
    /**
     * Diplay Notices
     */
    public function notice($type, $message)
    {
        if (isset($type) && !empty($type)) {
            $class = ($type == '1') ? 'updated' : 'error';
            return '<div class="' . $class . ' notice">
		  <p>' . $message . '</p>
		  </div>';
        }
    }
    /**
     * Redirection
     */
    public function f($u)
    {
        $url = esc_url_raw($u);
        wp_register_script('fma-redirect-script', '');
        wp_enqueue_script('fma-redirect-script');
        wp_add_inline_script(
            'fma-redirect-script',
            ' window.location.href="' . $url . '" ;'
        );
    }
    public static function shortcodeUpdateNotice()
    {
        if (class_exists('file_manager_advanced_shortcode')):
            if (defined('fmas_ver')) {
                if (fmas_ver < '2.4.1') {
                    return '<div class="error notice" style="background: #f7dfdf">
					<p><strong>Advanced File manager shortcode addon update:</strong> You are using version <strong>' . fmas_ver . '</strong> we recommend you to update to latest version. If you did not receive update please download from <a href="https://advancedfilemanager.com/my-account/" target="_blank">my account</a> page.</p>
					</div>';
                }
            } else {
                return '<div class="error notice" style="background: #f7dfdf">
					<p><strong>Advanced File manager shortcode addon update:</strong> You are using old version, we recommend you to update to latest version. If you did not receive update please download from <a href="https://advancedfilemanager.com/my-account/" target="_blank">my account</a> page.</p>
					</div>';
            }
        endif;
    }
    /**
     * Get User Roles
     */
    public function wpUserRoles()
    {
        global $wp_roles;
        return $wp_roles->roles;
    }

    /**
     * Integrations Pro Menu
     */
    public function integrations_pro_menu()
    {
        wp_enqueue_script('fma-admin-scripts');
        $pro_images_url = plugins_url('assets/images/', __FILE__);
        ?>
        <style>
            /* ... (keeping existing styles) ... */
            .afm-integrations-pro-wrap {
                margin-top: 20px;
                position: relative;
            }

            .afm-integrations-pro-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 20px;
                margin-top: 30px;
                opacity: 0.7;
                position: relative;
                pointer-events: none;
                user-select: none;
            }

            .afm-integration-pro-card {
                background: #fff;
                border: 1px solid #e0e0e0;
                border-radius: 4px;
                padding: 24px;
                display: flex;
                flex-direction: column;
                min-height: 160px;
            }

            .afm-integration-pro-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .afm-integration-pro-icon {
                width: 48px;
                height: 48px;
                background: #f8f9fa;
                border-radius: 6px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .afm-integration-pro-icon img {
                width: 24px;
                height: 24px;
            }

            .afm-integration-pro-title {
                margin: 0 0 5px 0;
                font-size: 16px;
                font-weight: 600;
                color: #1d2327;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            
            .afm-coming-soon-badge {
                display: inline-block;
                background: #f0b90b;
                color: #fff;
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
                padding: 3px 8px;
                border-radius: 3px;
                letter-spacing: 0.5px;
            }

            .afm-integration-pro-desc {
                margin: 0;
                font-size: 13px;
                color: #646970;
                line-height: 1.5;
            }

            .fma__blocks__heading-pro-tag {
                display: inline-block;
                padding: 2px 8px;
                background: linear-gradient(270deg, #011D33 0%, #3F6972 100%);
                border-radius: 4px;
                color: #fff;
                font-size: 12px;
                margin-left: 15px;
                vertical-align: middle;
                margin-bottom: 2px;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            }

            /* Dummy Switch styling */
            .afm-dummy-switch {
                position: relative;
                display: inline-block;
                width: 44px;
                height: 22px;
            }

            .afm-dummy-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #e0e0e0;
                transition: .4s;
                border-radius: 22px;
            }

            .afm-dummy-slider:before {
                position: absolute;
                content: "";
                height: 16px;
                width: 16px;
                left: 3px;
                bottom: 3px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            .afm-pro-overlay {
                position: absolute;
                top: 60px;
                left: 0;
                right: 0;
                bottom: 0;
                z-index: 100;
                cursor: pointer;
            }

            .afm-pro-overlay::before {
                content: "";
                display: block;
                width: 100%;
                height: 100%;
                position: absolute;
                top: 0;
                left: 0;
                background: transparent;
            }
        </style>
        <div class="wrap afm-integrations-pro-wrap">
            <h1 class="wp-heading-inline"><?php _e('Integrations', 'file-manager-advanced'); ?> <span
                    class="fma__blocks__heading-pro-tag">PRO</span></h1>
            <p class="description">
                <?php _e('Connect your Advanced File Manager with popular cloud storage services.', 'file-manager-advanced'); ?>
            </p>

            <div class="afm-pro-overlay fma__blocks__wrap"
                afmp-href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=integrations_banner&utm_campaign=plugin">
            </div>

            <div class="afm-integrations-pro-grid">
                <?php
                $integrations = array(
                    'onedrive' => array(
                        'name' => 'OneDrive',
                        'desc' => 'Connect and manage your Microsoft OneDrive files directly from the Advanced File Manager.',
                        'icon' => 'onedrive.svg'
                    ),
                    'googledrive' => array(
                        'name' => 'Google Drive',
                        'desc' => 'Sync and manage your Google Drive files and folders seamlessly with your WordPress setup.',
                        'icon' => 'googledrive.svg'
                    ),
                    'amazons3' => array(
                        'name' => 'Amazon S3',
                        'desc' => 'Securely connect and manage your AWS S3 buckets for flexible and scalable cloud storage.',
                        'icon' => 'amazons3.svg'
                    ),
                    'googlecloud' => array(
                        'name' => 'Google Cloud Storage',
                        'desc' => 'Access and organize your Google Cloud Storage buckets right from your WordPress dashboard.',
                        'icon' => 'googlecloud.svg'
                    ),
                    'github' => array(
                        'name' => 'GitHub',
                        'desc' => 'Connect your GitHub repositories to browse and manage your project files directly.',
                        'icon' => 'github.svg'
                    ),
                    'dropbox' => array(
                        'name' => 'Dropbox',
                        'desc' => 'Easily integrate and manage your Dropbox cloud storage files within the file manager.',
                        'icon' => 'dropbox.svg'
                    ),
                    'pcloud' => array(
                        'name' => 'pCloud',
                        'desc' => 'Connect and manage your pCloud files directly from the Advanced File Manager.',
                        'icon' => 'pcloud.svg'
                    ),
                    'cloudflarer2' => array(
                        'name' => 'Cloudflare R2',
                        'desc' => 'Connect and manage your Cloudflare R2 buckets/files directly from the Advanced File Manager.',
                        'icon' => 'cloudflarer2.svg'
                    ),
                    'digitalocean' => array(
                        'name' => 'DigitalOcean Spaces',
                        'desc' => 'Connect and manage your DigitalOcean Spaces buckets/files directly from the Advanced File Manager.',
                        'icon' => 'digitalocean.svg',
                        'coming_soon' => true
                    )
                );

                foreach ($integrations as $item):
                    ?>
                    <div class="afm-integration-pro-card">
                        <div class="afm-integration-pro-header">
                            <div class="afm-integration-pro-icon">
                                <img src="<?php echo $pro_images_url . $item['icon']; ?>" alt="<?php echo $item['name']; ?>">
                            </div>
                            <div class="afm-dummy-switch">
                                <span class="afm-dummy-slider"></span>
                            </div>
                        </div>
                        <div class="afm-integration-pro-body">
                            <h3 class="afm-integration-pro-title">
                                <?php echo $item['name']; ?>
                                <?php if (isset($item['coming_soon']) && $item['coming_soon']): ?>
                                    <span class="afm-coming-soon-badge">Coming Soon</span>
                                <?php endif; ?>
                            </h3>
                            <p class="afm-integration-pro-desc"><?php echo $item['desc']; ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
    }
}
