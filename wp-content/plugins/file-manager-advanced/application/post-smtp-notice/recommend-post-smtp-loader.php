<?php
/**
 * Safe reusable loader for Recommend_Post_SMTP class
 * Prevents class name conflicts across multiple plugins
 */

 if ( ! function_exists( 'recommend_smtp_loader' ) ) {

    function recommend_smtp_loader( $plugin_id, $slug, $show_admin_notice = true, $parent_menu = false, $format = 'png' ) {
        if ( ! class_exists( 'RecommendPostSMTP\\Base\\Recommend_Post_SMTP_Base' ) ) {
            require_once __DIR__ . '/recommend-post-smtp-base.php';
        }

        $base_class = 'RecommendPostSMTP\\Base\\Recommend_Post_SMTP_Base';
        $alias_class = $plugin_id . '_Recommend_Post_SMTP';

        if ( ! class_exists( $alias_class ) ) {
            class_alias( $base_class, $alias_class );
        }

        $global_notice_hidden = get_option( 'post_smtp_global_recommendation_notice_hidden', false );
        if ( $global_notice_hidden ) {
            $show_admin_notice = false;
        }

        return new $alias_class( $slug, $show_admin_notice, $parent_menu, $format );
    }
}


