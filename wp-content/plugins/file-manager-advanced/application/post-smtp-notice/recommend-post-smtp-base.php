<?php

namespace RecommendPostSMTP\Base;

if (!class_exists(__NAMESPACE__ . '\\Recommend_Post_SMTP_Base')):

    class Recommend_Post_SMTP_Base
    {

        private $slug = '';
        private $format = 'png';

        /**
         * Get or generate a unique per-site secret key.
         * Stored in wp_options so each site has its own key instead of a shared hardcoded one.
         */
        public static function get_site_secret_key() {
            $key = get_option( 'fma_recommend_smtp_site_key', false );
            if ( empty( $key ) ) {
                $key = wp_generate_password( 48, true, true );
                update_option( 'fma_recommend_smtp_site_key', $key, false );
            }
            return $key;
        }

        /**
         * Check if admin has given consent to share site data with external servers.
         */
        public static function has_data_consent() {
            return (bool) get_option( 'fma_recommend_smtp_data_consent', false );
        }

        /**
         * Grant consent to share site data.
         */
        public static function grant_data_consent() {
            update_option( 'fma_recommend_smtp_data_consent', true, false );
        }

        private $plugins = array(
            'post-smtp/postman-smtp.php',
            'wp-mail-smtp/wp_mail_smtp.php',
            'wp-mail-smtp-pro/wp_mail_smtp_pro.php',
            'easy-wp-smtp/easy-wp-smtp.php',
            'fluent-smtp/fluent-smtp.php',
            'gosmtp/gosmtp.php',
            'smtp-mailer/smtp-mailer.php',
            'suremails/suremails.php',
            'mailin/mailin.php',
            'site-mailer/site-mailer.php',
            'wp-smtp/wp-smtp.php'
        );

        private $is_installed = false;

        public function __construct($slug, $show_admin_notice = true, $parent_menu = false, $format = 'png')
        {
            $this->slug = $slug;
            $this->format = $format;
            // Only show notice if global option not set
            $global_notice_hidden = get_option('post_smtp_global_recommendation_notice_hidden', false);
            if ($global_notice_hidden) {
                $show_admin_notice = false;
            }
            add_action('rest_api_init', array($this, 'rest_api_init'));

            // Register AJAX actions early
            add_action('wp_ajax_post_smtp_request', array($this, 'request_post_smtp_ajax'));
            add_action('wp_ajax_nopriv_post_smtp_request', array($this, 'request_post_smtp_ajax'));

            if ($show_admin_notice || $parent_menu) {
                add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
                add_action('admin_head', array($this, 'admin_head'));

                if (!function_exists('is_plugin_active')) {
                    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
                }

                foreach ($this->plugins as $plugin) {
                    if (is_plugin_active($plugin)) {
                        break;
                    } else {
                        if ($parent_menu) {
                            if ('login-designer' === $this->slug) {
                                add_action('admin_menu', function () use ($parent_menu) {
                                    add_theme_page('SMTP', 'SMTP <span class="awaiting-mod"><span class="pending-count">Free</span></span>', 'manage_options', "{$this->slug}-recommend-post-smtp", array($this, 'recommend_post_smtp_submenu'), 99);
                                }, 9999);
                            } else {
                                add_action('admin_menu', function () use ($parent_menu) {
                                    add_submenu_page(
                                        $parent_menu,
                                        'SMTP',
                                        'SMTP <span class="awaiting-mod"><span class="pending-count">Free</span></span>',
                                        'manage_options',
                                        "{$this->slug}-recommend-post-smtp",
                                        array($this, 'recommend_post_smtp_submenu'),
                                        99
                                    );
                                }, 9999);
                            }
                        }

                        if (file_exists(WP_PLUGIN_DIR . "/{$this->plugins[0]}")) {
                            $this->is_installed = true;
                        }
                        break;
                    }
                }
            }
        }


        /**
         * Hide the admin notice | Action Callback
         * 
         * @return void
         */
        public function hide_post_smtp_recommendation_notice()
        {
            if (!current_user_can('manage_options') || !isset($_GET['nonce']) || !wp_verify_nonce($_GET['nonce'], 'hide-post-smtp-recommendation-notice')) {
                wp_die(__('Security Check.', 'post-smtp'));
            }

            if (isset($_GET['action']) && $_GET['action'] === 'hide-post-smtp-recommendation-notice') {
                update_option('post-smtp-recommendation-notice-hidden', true);

                wp_redirect(wp_get_referer());
            }
        }

        /**
         * Display the submenu page for the plugin | Action Callback 
         * 
         * @return void
         */
        public function recommend_post_smtp_submenu()
        {
            $button = array(
                'text' => 'Install and Activate Post SMTP Now!',
                'action' => 'install-plugin_post-smtp',
            );

            if ($this->is_installed) {
                $button['text'] = 'Activate Post SMTP Now!';
                $button['action'] = 'activate-plugin_post-smtp';
            }

            ?>
            <div class="recommend-post-smtp-container">
                <div class="recommend-post-smtp-header">
                    <div class="recommend-post-smtp-logos">
                        <img src="<?php echo esc_url("https://plugins.svn.wordpress.org/{$this->slug}/assets/icon-128x128.{$this->format}"); ?>"
                            alt="<?php echo "{$this->slug} Logo" ?>" width="75px" />
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . '/assets/images/attachment.png'); ?>"
                            alt="Attachment" width="28px" />
                        <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/images/post-smtp-logo.gif'); ?>"
                            alt="Post SMTP Logo" width="75px" />
                    </div>
                    <h1>Boost Wordpress Email Deliverability with Post SMTP</h1>
                    <p>
                        Post SMTP is #1 SMTP plugin trusted by over 400,000 WordPress sites. Experience flawless email
                        deliverability, detailed email logs, instant failure notifications and much more.
                    </p>
                    <a href="" data-action="<?php echo esc_attr($button['action']); ?>"
                        class="post-smtp-notice-install recommend-post-smtp-secondary">
                        <?php echo esc_html($button['text']); ?> <span class="dashicons dashicons-arrow-right-alt"></span>
                    </a>
                </div>
                <div class="recommend-post-smtp-section">
                    <img src="<?php echo esc_url(plugin_dir_url(__FILE__) . 'assets/images/post-smtp-banner.jpg'); ?>"
                        alt="Post SMTP Banner" width="100%" />
                    <div class="recommend-post-smtp-container">
                        <h2>Post SMTP - Your WordPress Email Solution</h2>
                        <p>Ensure guaranteed email deliverability with seamless integration to top SMTP services. Manage your emails
                            confidently and ensure they always reach the inbox. <a href="https://postmansmtp.com/"
                                target="_blank">Learn more about Post SMTP</a></p>
                        <hr />
                    </div>
                    <div class="recommend-post-smtp-container">
                        <h2>Exclusive API support for all popular ESPs</h2>
                        <div class="recommend-post-smtp-providers">
                            <div>
                                <h3><span class="dashicons dashicons-yes-alt"></span> Gmail SMTP</h3>
                                <h3><span class="dashicons dashicons-yes-alt"></span> Microsoft 365 (Office 365)</h3>
                                <h3><span class="dashicons dashicons-yes-alt"></span> Mailgun</h3>
                            </div>
                            <div>
                                <h3><span class="dashicons dashicons-yes-alt"></span> SendGrid</h3>
                                <h3><span class="dashicons dashicons-yes-alt"></span> Brevo (fomerly SendInBlue)</h3>
                                <h3><span class="dashicons dashicons-yes-alt"></span> Amazon SES</h3>
                            </div>
                            <div>
                                <h3><span class="dashicons dashicons-yes-alt"></span> Twilio (SMS Notifications)</h3>
                                <h3><span class="dashicons dashicons-yes-alt"></span> Zoho Mail</h3>
                                <h3><span class="dashicons dashicons-yes-alt"></span> +Any SMTP Provider</h3>
                            </div>
                        </div>
                        <div class="recommend-post-smtp-footer">
                            <a href="" data-action="<?php echo esc_attr($button['action']); ?>"
                                class="post-smtp-notice-install recommend-post-smtp-primary">
                                <?php echo esc_html($button['text']); ?> <span class="dashicons dashicons-arrow-right-alt"></span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Add custom styles to the admin head | Action Callback
         * 
         * @return void
         */
        public function admin_head()
        {
            $image_path = plugin_dir_url(__FILE__) . 'assets/images';
            ?>
            <style type="text/css">
                <?php
                if (!empty($_GET['page']) && $_GET['page'] === "{$this->slug}-recommend-post-smtp") {
                    echo '.notice.is-dismissible { display: none; }';
                }
                ?>
                .post-smtp-notice-install {
                    text-decoration: none;
                    font-weight: bold;
                }

                .recommend-post-smtp-notice {
                    background-color: #FFFFFF;
                    padding: 20px;
                    border-left: 5px solid #00a0d2;
                }

                .post-smtp-notice-wrapper {
                    display: flex;
                    align-items: center;
                    gap: 20px;
                }

                .post-smtp-notice-content {
                    flex: 1;
                }

                .post-smtp-notice-content p {
                    color: #000000;
                    font-size: 14px;
                    margin: 0;
                    line-height: 1.5;
                }

                .post-smtp-notice-right {
                    display: flex;
                    align-items: center;
                    gap: 15px;
                }

                .post-smtp-notice-icon {
                    flex-shrink: 0;
                }

                .post-smtp-notice-actions {
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 8px;
                }

                .post-smtp-notice-install {
                    margin: 0 !important;
                }

                .notice-link {
                    text-align: center;
                    font-size: 12px;
                }

                .post-smtp-dont-show-again {
                    color: #666 !important;
                    text-decoration: underline;
                    font-size: 12px;
                }

                .post-smtp-dont-show-again:hover {
                    color: #333 !important;
                    text-decoration: underline;
                }

                .recommend-post-smtp-container {
                    width: 90%;
                    margin: 75px auto;
                }

                .recommend-post-smtp-header {
                    background-color: #FFFFFF;
                    border-radius: 18px;
                    text-align: center;
                    padding: 70px 75px;
                }

                .recommend-post-smtp-logos {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    gap: 15px;
                }

                .recommend-post-smtp-logos img {
                    display: block;
                }

                .recommend-post-smtp-logos img:nth-child(1),
                .recommend-post-smtp-logos img:nth-child(3) {
                    border: 1px solid #B3C1D5;
                    border-radius: 10px;
                    padding: 10px;
                }

                .recommend-post-smtp-logos img:nth-child(2) {
                    align-self: center;
                }

                .recommend-post-smtp-header h1 {
                    color: #214A72;
                    margin-top: 45px;
                }

                .recommend-post-smtp-header p {
                    color: #949494;
                    font-size: 16px;
                    margin-bottom: 55px;
                }

                .recommend-post-smtp-secondary {
                    color: #046BD2 !important;
                    border: 1px solid #046BD2;
                    border-radius: 10px;
                    padding: 16px 25px;
                    font-size: 18px;
                }

                .recommend-post-smtp-primary {
                    background-color: #046BD2;
                    color: #FFFFFF !important;
                    border-radius: 10px;
                    padding: 16px 25px;
                    font-size: 18px;
                }

                .recommend-post-smtp-primary:hover {
                    background-color: #FFFFFF;
                    color: #046BD2 !important;
                    border: 1px solid #046BD2;
                    transition: all 0.2s ease-in-out;
                }

                .recommend-post-smtp-secondary:hover {
                    background-color: #046BD2;
                    color: #FFFFFF !important;
                    transition: all 0.2s ease-in-out;
                }

                .recommend-post-smtp-section {
                    border-radius: 18px;
                    background: #FFFFFF;
                    padding: 25px;
                    margin-top: 30px;
                    background-image: url('<?php echo "{$image_path}/bottom-right.png" ?>'), url('<?php echo "{$image_path}/bottom-left.png" ?>');
                    background-position: bottom right, bottom left;
                    background-repeat: no-repeat, no-repeat;
                }

                .recommend-post-smtp-section .recommend-post-smtp-container {
                    width: 97%;
                    margin: 30px auto;
                }

                .recommend-post-smtp-section h2 {
                    color: #484848;
                    font-weight: 700;
                    font-size: 25px;
                }

                .recommend-post-smtp-section p {
                    color: #484848;
                    font-size: 19px;
                }

                .recommend-post-smtp-section hr {
                    border-color: #D8D9DA;
                    margin: 35px 0;
                }

                .recommend-post-smtp-providers {
                    display: flex;
                    justify-content: space-between;
                }

                .recommend-post-smtp-providers h3 {
                    color: #565353;
                    font-weight: 500;
                }

                .recommend-post-smtp-providers .dashicons {
                    color: #8FC895;
                    margin-right: 5px;
                }

                .recommend-post-smtp-footer {
                    text-align: center;
                    margin-top: 60px;
                }

                /* Consent Modal */
                .fma-consent-overlay {
                    display: none;
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.55);
                    z-index: 999999;
                    align-items: center;
                    justify-content: center;
                }
                .fma-consent-overlay.fma-consent-visible {
                    display: flex;
                }
                .fma-consent-modal {
                    background: #fff;
                    border-radius: 12px;
                    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
                    max-width: 480px;
                    width: 90%;
                    overflow: hidden;
                    animation: fmaConsentSlideIn 0.25s ease-out;
                }
                @keyframes fmaConsentSlideIn {
                    from { opacity: 0; transform: translateY(-20px) scale(0.97); }
                    to   { opacity: 1; transform: translateY(0) scale(1); }
                }
                .fma-consent-header {
                    background: linear-gradient(135deg, #046BD2, #0356a8);
                    padding: 20px 24px;
                    display: flex;
                    align-items: center;
                    gap: 12px;
                }
                .fma-consent-header .dashicons {
                    color: #fff;
                    font-size: 24px;
                    width: 24px;
                    height: 24px;
                }
                .fma-consent-header h3 {
                    color: #fff;
                    margin: 0;
                    font-size: 16px;
                    font-weight: 600;
                    flex: 1;
                }
                .fma-consent-close {
                    background: none;
                    border: none;
                    color: rgba(255,255,255,0.8);
                    font-size: 20px;
                    cursor: pointer;
                    padding: 0;
                    line-height: 1;
                    width: 28px;
                    height: 28px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    transition: all 0.15s ease;
                }
                .fma-consent-close:hover {
                    background: rgba(255,255,255,0.15);
                    color: #fff;
                }
                .fma-consent-body {
                    padding: 24px;
                }
                .fma-consent-body p {
                    color: #3c434a;
                    font-size: 13.5px;
                    line-height: 1.6;
                    margin: 0 0 16px;
                }
                .fma-consent-body ul {
                    margin: 0 0 16px;
                    padding: 0;
                    list-style: none;
                }
                .fma-consent-body ul li {
                    position: relative;
                    padding-left: 24px;
                    margin-bottom: 8px;
                    color: #50575e;
                    font-size: 13px;
                    line-height: 1.5;
                }
                .fma-consent-body ul li::before {
                    content: "\f339";
                    font-family: dashicons;
                    position: absolute;
                    left: 0;
                    top: 0;
                    color: #046BD2;
                    font-size: 16px;
                }
                .fma-consent-note {
                    background: #f0f6fc;
                    border-left: 3px solid #046BD2;
                    padding: 10px 14px;
                    border-radius: 0 6px 6px 0;
                    margin-bottom: 0;
                    font-size: 12.5px !important;
                    color: #50575e !important;
                }
                .fma-consent-footer {
                    display: flex;
                    justify-content: flex-end;
                    gap: 10px;
                    padding: 16px 24px;
                    border-top: 1px solid #e2e4e7;
                    background: #f9f9f9;
                }
                .fma-consent-btn {
                    padding: 8px 20px;
                    border-radius: 6px;
                    font-size: 13px;
                    font-weight: 500;
                    cursor: pointer;
                    border: none;
                    transition: all 0.15s ease;
                }
                .fma-consent-btn-cancel {
                    background: #fff;
                    color: #50575e;
                    border: 1px solid #c3c4c7;
                }
                .fma-consent-btn-cancel:hover {
                    background: #f0f0f1;
                    border-color: #999;
                }
                .fma-consent-btn-agree {
                    background: #046BD2;
                    color: #fff;
                }
                .fma-consent-btn-agree:hover {
                    background: #0356a8;
                }
            </style>

            <!-- FMA Data Consent Modal -->
            <div id="fma-consent-overlay" class="fma-consent-overlay">
                <div class="fma-consent-modal">
                    <div class="fma-consent-header">
                        <span class="dashicons dashicons-shield"></span>
                        <h3><?php esc_html_e( 'Data Sharing Consent', 'file-manager-advanced' ); ?></h3>
                        <button type="button" id="fma-consent-close" class="fma-consent-close" title="<?php esc_attr_e( 'Close', 'file-manager-advanced' ); ?>">&times;</button>
                    </div>
                    <div class="fma-consent-body">
                        <p><?php esc_html_e( 'To proceed, this plugin will share some basic information with an external server (connect.postmansmtp.com):', 'file-manager-advanced' ); ?></p>
                        <ul>
                            <li><?php esc_html_e( 'Your site URL', 'file-manager-advanced' ); ?></li>
                            <li><?php esc_html_e( 'Plugin installation/activation status', 'file-manager-advanced' ); ?></li>
                            <li><?php esc_html_e( 'Plugin slug identifier', 'file-manager-advanced' ); ?></li>
                        </ul>
                        <p class="fma-consent-note"><?php esc_html_e( 'This helps improve compatibility and support. No personal data or file contents are shared. You can proceed without sharing by clicking "Skip".', 'file-manager-advanced' ); ?></p>
                    </div>
                    <div class="fma-consent-footer">
                        <button type="button" id="fma-consent-skip" class="fma-consent-btn fma-consent-btn-cancel"><?php esc_html_e( 'Skip', 'file-manager-advanced' ); ?></button>
                        <button type="button" id="fma-consent-agree" class="fma-consent-btn fma-consent-btn-agree"><?php esc_html_e( 'I Agree & Continue', 'file-manager-advanced' ); ?></button>
                    </div>
                </div>
            </div>
            <?php
        }

        /**
         * Registers AJAX actions | Action Callback
         * 
         * @return void
         */
        public function rest_api_init()
        {
            // AJAX handlers are registered in constructor
        }

        /**
         * AJAX callback for Post SMTP request
         * 
         * @return void
         */
        public function request_post_smtp_ajax()
        {
            if (!isset($_POST['nonce'])) {
                wp_send_json_error(array('message' => 'No nonce provided'));
            }

            if (!wp_verify_nonce($_POST['nonce'], 'post_smtp_request_nonce')) {
                wp_send_json_error(array('message' => 'Security check failed'));
            }

            if (!current_user_can('manage_options')) {
                wp_send_json_error(array('message' => 'Insufficient permissions'));
            }

            // Check if status is provided
            if (!isset($_POST['status'])) {
                wp_send_json_error(array('message' => 'No status provided'));
            }

            // Handle consent grant request
            if (isset($_POST['grant_consent']) && $_POST['grant_consent'] === '1') {
                self::grant_data_consent();
            }

            // Only send data to external server if admin has given consent
            if (!self::has_data_consent()) {
                wp_send_json_success(array(
                    'message' => __('Action completed (data sharing skipped — no consent)', 'post-smtp'),
                    'consent' => false
                ));
                return;
            }

            $site_url = get_bloginfo('url');
            $status = sanitize_text_field($_POST['status']);
            $plugin_slug = $this->slug;
            $secret_key = self::get_site_secret_key();

            $response = wp_remote_post("https://connect.postmansmtp.com/wp-json/update/v1/update?site_url={$site_url}&status={$status}&plugin_slug={$plugin_slug}", array(
                'method' => 'POST',
                'headers' => array(
                    'Content-Type' => 'application/json',
                    'Secret-Key' => $secret_key
                )
            ));

            if (is_wp_error($response)) {
                wp_send_json_error(array(
                    'message' => 'Failed to send request: ' . $response->get_error_message()
                ));
            } else {
                wp_send_json_success(array(
                    'message' => __('Request sent successfully', 'post-smtp')
                ));
            }
        }

        /**
         * Add the admin footer script to the notice | Action Callback
         */
        public function admin_enqueue_scripts()
        {
            wp_enqueue_script('updates');
            wp_enqueue_script('recommend-post-smtp-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('updates', 'jquery'), '1.0.0', true);
            wp_localize_script('recommend-post-smtp-script', 'recommendPostSMTP', array(
                'redirectURL' => admin_url("admin-post.php?action=hide-post-smtp-recommendation-notice&nonce=" . wp_create_nonce('hide-post-smtp-recommendation-notice')),
                'postSMTPURL' => admin_url("admin.php?page=postman"),
                'ajaxURL' => admin_url('admin-ajax.php'),
                'ajaxNonce' => wp_create_nonce('post_smtp_request_nonce'),
                'hasConsent' => self::has_data_consent(),
            ));
        }
    }
endif;