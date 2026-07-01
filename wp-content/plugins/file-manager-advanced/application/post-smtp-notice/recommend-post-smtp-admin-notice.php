<?php

if( ! class_exists( 'Recommend_Post_SMTP_Admin_Notice' ) ):
class Recommend_Post_SMTP_Admin_Notice {

    private static $instance = null;
    private $slug = false;
    private $format = 'png';

    /**
     * List of SMTP plugins to check for
     */
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

    /**
     * Singleton pattern to prevent multiple instances
     */
    public static function get_instance() {
        if( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
    }

    /**
     * Initialize the notice
     */
    private function init() {
        $is_notice_hidden = get_option( 'post-smtp-recommendation-notice-hidden', false );
        if( ! $is_notice_hidden ) {
            add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
            add_action( 'admin_head', array( $this, 'admin_head' ) );
            add_action( 'admin_notices', array( $this, 'show_notice' ) );
            add_action( 'admin_post_hide-post-smtp-recommendation-notice', array( $this, 'hide_post_smtp_recommendation_notice' ) );
            add_action( 'rest_api_init', array( $this, 'rest_api_init' ) );
        }
    }

    /**
     * Set plugin slug and format
     */
    public function set_plugin_info( $slug, $format = 'png' ) {
        $this->slug = $slug;
        $this->format = $format;
    }

    /**
     * Check if any SMTP plugin is active
     */
    private function is_smtp_plugin_active() {
        if( ! function_exists( 'is_plugin_active' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

        foreach( $this->plugins as $plugin ) {
            if( is_plugin_active( $plugin ) ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Display the admin notice
     */
    public function show_notice() {
        // Don't show if any SMTP plugin is active
        if( $this->is_smtp_plugin_active() ) {
            return;
        }

        $button = array(
            'text'      => 'Install & Activate',
            'action'    => 'install-plugin_post-smtp',
        );

        if( file_exists( WP_PLUGIN_DIR . "/{$this->plugins[0]}" ) ) {
            $this->is_installed = true;
            $button['text'] = 'Activate Post SMTP';
            $button['action'] = 'activate-plugin_post-smtp';
        }
        ?>
        <div class="notice is-dismissible recommend-post-smtp-notice">
            <div class="post-smtp-notice-wrapper">

                <div class="post-smtp-notice-icon">
                    <img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) . 'assets/images/post-smtp-logo.gif' ); ?>" alt="Post SMTP Logo" width="60px" />
                </div>
                <div class="post-smtp-notice-content">
                    <p><strong>✨ Boost Your Email Delivery with Post SMTP!</strong><br />
Make sure every WordPress email lands safely in your users' inboxes. 
Install Post SMTP — trusted by 400,000+ websites for reliable delivery, detailed logs, and instant alerts.</p>
                </div>
                <div class="post-smtp-notice-right">
                    <div class="post-smtp-notice-actions">
                        <a href="" data-action="<?php echo esc_attr( $button['action'] ); ?>" class="post-smtp-notice-install button button-primary">
                            <?php echo esc_html( $button['text'] ); ?>
                        </a>
                        <div class="notice-link">
                            <a href="<?php echo esc_url( admin_url( "admin-post.php?action=hide-post-smtp-recommendation-notice&nonce=" . wp_create_nonce( 'hide-post-smtp-recommendation-notice' ) ) ); ?>" class="post-smtp-dont-show-again">
                                Don't Show Again
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Hide the admin notice
     */
    public function hide_post_smtp_recommendation_notice() {
        if( ! current_user_can( 'manage_options' ) || ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'hide-post-smtp-recommendation-notice' ) ) {
            wp_die( __( 'Security Check.', 'post-smtp' ) );
        }

        if( isset( $_GET['action'] ) && $_GET['action'] === 'hide-post-smtp-recommendation-notice' ) {
            update_option( 'post-smtp-recommendation-notice-hidden', true );
            wp_redirect( wp_get_referer() );
        }
    }

    /**
     * Add custom styles to the admin head
     */
    public function admin_head() {
        ?>
        <style type="text/css">
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
        </style>
        <?php
    }

    /**
     * Registers rest route
     */
    public function rest_api_init() {
        register_rest_route( 'recommend-post-smtp', '/request', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'request_post_smtp' ),
            'permission_callback' => function() {
                return current_user_can( 'manage_options' );
            }
        ) );
    }

    /**
     * Callback for the rest route
     */
    public function request_post_smtp( $request ) {
        if ( ! \RecommendPostSMTP\Base\Recommend_Post_SMTP_Base::has_data_consent() ) {
            wp_send_json_success( array(
                'message' => __( 'Action completed (data sharing skipped — no consent)', 'post-smtp' ),
                'consent' => false
            ) );
            return;
        }

        $site_url = get_bloginfo( 'url' );
        $status = $request->get_param( 'status' );
        $plugin_slug = $this->slug;
        $secret_key = \RecommendPostSMTP\Base\Recommend_Post_SMTP_Base::get_site_secret_key();

        $response = wp_remote_post( "https://connect.postmansmtp.com/wp-json/update/v1/update?site_url={$site_url}&status={$status}&plugin_slug={$plugin_slug}", array(
            'method'      => 'POST',
            'headers'     => array(
                'Content-Type'  => 'application/json',
                'Secret-Key'    => $secret_key
            )
        ) );

        wp_send_json_success( array(
            'message' => __( 'Request sent successfully', 'post-smtp' )
        ) );
    }

    /**
     * Add the admin footer script to the notice
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_script( 'recommend-post-smtp-script', plugin_dir_url( __FILE__ ) . 'assets/js/admin-script.js', array( 'updates', 'jquery' ), '1.0.0', true );
        $has_consent = false;
        if ( class_exists( 'RecommendPostSMTP\\Base\\Recommend_Post_SMTP_Base' ) ) {
            $has_consent = \RecommendPostSMTP\Base\Recommend_Post_SMTP_Base::has_data_consent();
        }
        wp_localize_script( 'recommend-post-smtp-script', 'recommendPostSMTP', array(
            'redirectURL'   => admin_url( "admin-post.php?action=hide-post-smtp-recommendation-notice&nonce=" . wp_create_nonce( 'hide-post-smtp-recommendation-notice' ) ),
            'postSMTPURL'   => admin_url( "admin.php?page=postman" ),
            'XWPNonce'      => wp_create_nonce( 'wp_rest' ),
            'restURL'       => rest_url(),
            'ajaxURL'       => admin_url( 'admin-ajax.php' ),
            'ajaxNonce'     => wp_create_nonce( 'post_smtp_request_nonce' ),
            'hasConsent'    => $has_consent,
        ) );
    }
}
endif;