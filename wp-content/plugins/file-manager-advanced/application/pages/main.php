<?php
if (!defined('ABSPATH'))
    exit;

$settings = get_option('fmaoptions');
$path = str_replace('\\', '/', ABSPATH);
$review_done = get_option('fma_hide_review_section');
$hide_banner = get_option('_fma_banner_hide', 'no');
$minimize_banner = get_option('_fma_banner_minimize', 'maximize');

if (class_fma_main::has_pro()) {
    $hide_banner = 'yes';
}
?>
<div class="wrap fma afm__clearfix">
    <h1 class="wp-heading-inline"><?php _e('Advanced File Manager', 'file-manager-advanced'); ?></h1>
    <?php if (!class_exists('file_manager_advanced_shortcode')) { ?>
        <a href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=file_manager_screen_top_button&utm_campaign=plugin"
            class="page-title-action"
            target="_blank"><?php _e('Get Advanced File Manager Pro', 'file-manager-advanced'); ?></a>
    <?php } ?>
    <hr class="wp-header-end">
    <?php
    echo class_fma_admin_menus::shortcodeUpdateNotice();
    settings_errors();
    ?>
    <div <?php echo 'yes' !== $hide_banner ? '' : 'style="width: 100%;max-width: 100%;"' ?> class="afm__left-side">

        <?php if ('done' != $review_done) { ?>
            <div class="gb-fm-row review-block" id="fma_rate_us">
                <div class="message">
                    <img src="<?php echo plugins_url('images/rateme.png', __FILE__); ?>" class="fma_img_rate_me">
                    <?php _e('<strong>Advanced File Manager</strong>, we always support you and provide better features for you, please spend some seconds to review our plugin.', 'file-manager-advanced') ?>
                </div>
                <div class="actions">
                    <a target="_blank" href="https://wordpress.org/support/plugin/file-manager-advanced/reviews/?filter=5"
                        class="btn btn-review fma_review_link" title="Leave us a review"
                        data-task="done"><?php _e('I love your plugin!', 'file-manager-advanced'); ?></a>
                    <a href="javascript:void(0)" class="btn fma_review_link" title="Remind me later"
                        data-task="done"><?php _e('Not Now', 'file-manager-advanced'); ?></a>
                </div>
            </div>
            <hr>
        <?php } ?>

        <div id="file_manager_advanced">
            <center>
                <img src="<?php echo plugins_url('images/wait.gif', __FILE__); ?>">
            </center>
        </div>

        <div style="width:100%; text-align:center;" class="description">
            <span>
                <a href="https://advancedfilemanager.com/documentation/"
                    target="_blank"><?php _e('Documentation', 'file-manager-advanced') ?></a> | <a
                    href="https://advancedfilemanager.com/contact/"
                    target="_blank"><?php _e('Support', 'file-manager-advanced') ?></a> | <a
                    href="https://advancedfilemanager.com/shortcodes/"
                    target="_blank"><?php _e('Shortcodes', 'file-manager-advanced') ?></a>
            </span>
            <span id="thankyou">
                <?php _e('Thank you for using <a href="https://wordpress.org/plugins/file-manager-advanced/">Advanced File Manager</a>. If you are happy then please ', 'file-manager-advanced'); ?>
                <a href="https://wordpress.org/support/plugin/file-manager-advanced/reviews/?filter=5"><?php _e('Rate Us', 'file-manager-advanced') ?>
                    <img src="<?php echo plugins_url('images/5stars.png', __FILE__); ?>"
                        style="width:100px; top: 11px; position: relative;"></a>
            </span>
        </div>
    </div>

    <?php if ('yes' !== $hide_banner): ?>
        <div class="afm__right-side">

            <div class="afm__container">
                <div class="fma__header">
                    <h2 class="afmp__title">
                        Simplify file management with more
                        <span>advanced features!</span>
                    </h2>
                </div>

                <div id="remove-on-minimize" <?php echo 'maximize' === $minimize_banner ? 'style="display: block;"' : 'style="display: none;"'; ?>>

                    <ul class="fma__pro-features">
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Multiple Cloud Integrations.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Frontend access.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Built-in database access.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Block & shortcode support.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Role-based access control.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Private folder access.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Hide sensitive files & folders.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Advanced themes selection.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Multilingual support.
                        </li>
                        <li>
                            <span>
                                <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/crown.svg' ?>" alt="crown">
                            </span>
                            Operation controls for users.
                        </li>
                    </ul>

                </div>

                <div class="fma_cta">
                    <a target="_blank"
                        href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=file_manager_screen_side_banner&utm_campaign=plugin">
                        Get Pro Now
                        <span>
                            <img src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/arrow.svg' ?>"
                                alt="arrow-right">
                        </span>
                    </a>
                </div>

                <div class="fma__footer" <?php echo 'maximize' === $minimize_banner ? 'style="display: block;"' : 'style="display: none;"'; ?>>
                    <a id="fma__hide-banner" href="#">Hide Banner</a>
                </div>
            </div>

            <div class="fma__status-mini-max">
                <a fma-maximized="<?php echo 'maximize' === $minimize_banner ? 'true' : 'false'; ?>"
                    id="fma__minimize-maximize" href="#">
                    <img class="fma__status <?php echo 'maximize' === $minimize_banner ? '' : 'fma__minimized'; ?>"
                        src="<?php echo FMA_PLUGIN_URL . 'application/assets/images/mini-max.svg'; ?>"
                        alt="minimize-maximize">
                </a>
            </div>

        </div>

    <?php endif; ?>
</div>

<?php if ('done' != $review_done) { ?>
    <style>
        .fma .review-block {
            background-color: #fff;
            min-height: 100px;
            margin: 5px 5px 20px;
            padding-top: 24px;
            padding-bottom: 24px;
            text-align: center;
            font-size: 1.2em;
            border: 1px dashed #0d5ed9;
            border-radius: 10px;
            display: none;
        }

        .fma .review-block .message {
            margin-top: 16px;
        }

        .fma .review-block .actions {
            margin-top: 24px;
        }

        .fma .review-block .actions .btn-review {
            background-color: #0d5ed9;
            color: #fff;
            font-weight: 700;
            border-radius: 5px;
            -webkit-transition-duration: .6s;
            transition-duration: .6s;
        }

        .fma .review-block .actions a {
            padding: 5px 10px;
            text-decoration: none;
            border: 1px solid #0d5ed9;
        }

        .fma .fma_img_rate_me {
            width: 20px;
        }
    </style>
    <script>
        jQuery(document).ready(function (e) {

            setTimeout(function () {

                jQuery('#fma_rate_us').slideDown('slow');

            }, 5000);


            jQuery('.fma_review_link').click(function () {
                var fmaajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
                var task = jQuery(this).data('task');
                var nonce = "<?php echo wp_create_nonce('afm_review'); ?>";
                jQuery.ajax({
                    type: "post",
                    url: fmaajaxurl,
                    data: { action: "fma_review_ajax", 'task': task, 'nonce': nonce },
                    success: function (response) {
                        jQuery('#fma_rate_us').slideUp('slow');
                    }
                });
            });

        });
    </script>
<?php } ?>