<?php
if (!defined('ABSPATH'))
    exit;

$settings = $this->get();
$locales = $this->langs->locales();
$path = str_replace('\\', '/', ABSPATH);
$url = site_url();
$type = (isset($_GET['status']) && !empty($_GET['status']) ? intval($_GET['status']) : '');
$message = ($type == '2') ? 'Unable to save settings.' : 'Settings updated successfully.';
$roles = $this->wpUserRoles();
$cm_themes = class_fma_main::cm_themes();
?>
<div class="wrap fma">
    <h1 class="wp-heading-inline"><?php _e('Settings', 'file-manager-advanced'); ?></h1>
    <?php if (!class_exists('file_manager_advanced_shortcode')) { ?>
        <a href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=settings_screen_top_button&utm_campaign=plugin"
            class="page-title-action" target="_blank">
            <?php _e('Get Advanced File Manager Pro', 'file-manager-advanced') ?>
        </a>
    <?php } ?>
    <a href="https://advancedfilemanager.com/documentation/" class="page-title-action" target="_blank">
        <?php _e('Documentation', 'file-manager-advanced'); ?>
    </a>
    <hr class="wp-header-end">
    <?php
    echo class_fma_admin_menus::shortcodeUpdateNotice();
    settings_errors();
    if (isset($type) && !empty($type)) {
        if ($type == '1') { ?>
            <div class="updated notice is-dismissible">
                <p><?php echo esc_attr($message) ?></p>
            </div>
        <?php } else if ($type == '2') { ?>
                <div class="error notice is-dismissible">
                    <p><?php echo esc_attr($message) ?></p>
                </div>
        <?php }
    }
    ?>
    <div style="background:#fff; padding: 20px; border:1px solid #ccc; margin-top:20px;">

        <?php
        $tabs_setup = array(
            'general' => array(
                'title' => __('General', 'file-manager-advanced'),
                'slug' => 'general',
                'icon' => '<i class="dashicons dashicons-admin-generic"></i>',
            ),
            'notifications' => array(
                'title' => __('Notifications', 'file-manager-advanced'),
                'slug' => 'notifications',
                'icon' => '<i class="dashicons dashicons-megaphone"></i>',
            ),
        );
        if (!class_exists('AFMP\Modules\AFMP_AI_Integration')) {
            $tabs_setup['ai'] = array(
                'title' => __('AI Integration', 'file-manager-advanced'),
                'slug' => 'ai',
                'icon' => '<img src="' . plugins_url('../assets/images/codepilot.png', __FILE__) . '" style="height:16px;vertical-align:middle;border-radius:50%;" alt="Code Pilot" />',
            );
        }
        $settings_tabs = apply_filters('fma__settings_tabs', $tabs_setup);

        $active_tab = isset($_GET['tab']) ? sanitize_text_field(wp_unslash($_GET['tab'])) : 'general';
        if (!empty($settings_tabs)) {

            echo '<h2 class="afmp-nav-tabs nav-tab-wrapper">';
            foreach ($settings_tabs as $tab_id => $tab_title) {
                $active = ($active_tab === $tab_id) ? ' nav-tab-active' : '';
                printf(
                    '<a href="%s" class="nav-tab%s">%s %s</a>',
                    esc_url(
                        add_query_arg(
                            array(
                                'page' => 'file_manager_advanced_controls',
                                'tab' => $tab_id,
                            ),
                            admin_url('admin.php')
                        )
                    ),
                    esc_attr($active),
                    $tab_title['icon'],
                    $tab_title['title']
                );
            }
            echo '</h2>';
        }

        $this->save();
        if (isset($_GET['page']) && 'file_manager_advanced_controls' === sanitize_text_field(wp_unslash($_GET['page']))) {
            if ('general' !== $active_tab && isset($settings_tabs[$active_tab])) {
                do_action('fma__settings_tab_' . $active_tab . '_content');
            } else {
                ?>
                

                
                <p style="width:100%; text-align:right;" class="description">
                    <span
                        id="thankyou"><?php _e('Thank you for using <a href="https://wordpress.org/plugins/file-manager-advanced/">File Manager Advanced</a>. If happy then ', 'file-manager-advanced') ?>
                        <a href="https://wordpress.org/support/plugin/file-manager-advanced/reviews/?filter=5"><?php _e('Rate Us', 'file-manager-advanced') ?>
                            <img src="<?php echo plugins_url('images/5stars.png', __FILE__); ?>"
                                style="width:100px; top: 11px; position: relative;"></a></span>
                </p>
                
                <form action="<?php echo admin_url('admin.php?page=file_manager_advanced_controls'); ?>" method="post">
                    <?php wp_nonce_field('fmaform', '_fmaform'); ?>
                    <table class="form-table">
                        <tbody>
                            <tr>
                                <th>
                                    <?php _e('Who can access File Manager?', 'file-manager-advanced'); ?>
                                </th>
                                <td>
                                    <?php
                                    unset($roles['administrator']); ?>
                                    <?php if (is_multisite()):
                                        $checked = '';
                                        if (isset($settings['fma_user_roles'])):
                                            if (in_array('administrator', $settings['fma_user_roles'])) {
                                                $checked = 'checked=checked';
                                            }
                                        endif;
                                        ?>
                                        <input type="checkbox" value="superadmin" name="fma_user_role[]" checked="checked"
                                            disabled="disabled" /> <?php _e('Super Admin (Default)', 'file-manager-advanced'); ?>
                                        <br />
                                        <input type="checkbox" value="administrator" name="fma_user_role[]" <?php echo esc_attr($checked); ?> /> <?php _e('Administrator', 'file-manager-advanced'); ?> <br />
                                    <?php else: ?>
                                        <input type="checkbox" value="administrator" name="fma_user_role[]" checked="checked"
                                            disabled="disabled" /> <?php _e('Administrator (Default)', 'file-manager-advanced'); ?>
                                        <br />
                                    <?php endif; ?>
                                    <?php
                                    foreach ($roles as $key => $role) {
                                        $checked = '';
                                        if (isset($settings['fma_user_roles'])):
                                            if (in_array($key, $settings['fma_user_roles'])) {
                                                $checked = 'checked=checked';
                                            }
                                        endif;
                                        ?>
                                        <input type="checkbox" value="<?php echo esc_attr($key); ?>" name="fma_user_role[]" <?php echo esc_attr($checked); ?> /> <?php echo esc_attr($role['name']); ?> <br />
                                    <?php } ?>
                                </td>

                            </tr>

                            <tr>
                                <th>
                                    <label for="fma_theme">
                                        <?php _e('Theme', 'file-manager-advanced') ?>
                                    </label>
                                </th>
                                <td>

                                    <?php
                                    $themes = array(
                                        'light' => array(
                                            'title' => __('Default', 'file-manager-advanced'),
                                            'pro' => false,
                                        ),
                                        'mono' => array(
                                            'title' => __('Mono', 'file-manager-advanced'),
                                            'pro' => false,
                                        ),
                                        'dark' => array(
                                            'title' => __('Material Dark', 'file-manager-advanced'),
                                            'pro' => true,
                                        ),
                                        'm-light' => array(
                                            'title' => __('Material Light', 'file-manager-advanced'),
                                            'pro' => true,
                                        ),
                                        'grey' => array(
                                            'title' => __('Material Grey', 'file-manager-advanced'),
                                            'pro' => true,
                                        ),
                                        'windows10' => array(
                                            'title' => __('Windows 10', 'file-manager-advanced'),
                                            'pro' => true,
                                        ),
                                        'bootstrap' => array(
                                            'title' => __('Bootstrap', 'file-manager-advanced'),
                                            'pro' => true,
                                        ),
                                    );
                                    ?>

                                    <select class="file-manager-advanced-select2 fma-theme regular-text" name="fma_theme"
                                        id="fma_theme">
                                        <?php foreach ($themes as $value => $theme): ?>
                                            <?php $disabled = $theme['pro'] && !class_fma_main::has_pro() ? 'disabled="disabled"' : ''; ?>
                                            <?php $selected = isset($settings['fma_theme']) && $settings['fma_theme'] === $value ? 'selected="selected"' : ''; ?>

                                            <?php printf('<option value="%s" %s %s>%s</option>', $value, $disabled, $selected, $theme['title']); ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">
                                        <?php _e('Select file manager advanced theme. Default: Light', 'file-manager-advanced') ?>
                                    </p>
                                </td>
                            </tr>

                            <tr>
                                <th><?php _e('Language', 'file-manager-advanced') ?></th>
                                <td>
                                    <select name="fma_locale" id="fma_locale">
                                        <?php foreach ($locales as $key => $locale) { ?>
                                            <option value="<?php echo esc_attr($locale); ?>" <?php echo (isset($settings['fma_locale']) && $settings['fma_locale'] == $locale) ? 'selected="selected"' : ''; ?>>
                                                <?php echo esc_attr($key); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <p class="description">
                                        <?php _e('Select file manager advanced language. Default: en (English)', 'file-manager-advanced'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Public Root Path', 'file-manager-advanced') ?></th>
                                <td>
                                    <input name="public_path" type="text" id="public_path"
                                        value="<?php echo isset($settings['public_path']) && !empty($settings['public_path']) ? esc_attr($settings['public_path']) : esc_attr($path); ?>"
                                        class="regular-text">
                                    <p class="description">
                                        <?php _e('File Manager Advanced Root Path, you can change according to your choice.', 'file-manager-advanced'); ?>
                                    </p>
                                    <p>Default: <code><?php echo esc_attr($path); ?></code></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Files URL', 'file-manager-advanced') ?></th>
                                <td>
                                    <input name="public_url" type="text" id="public_url"
                                        value="<?php echo isset($settings['public_url']) && !empty($settings['public_url']) ? esc_url($settings['public_url']) : esc_url($url); ?>"
                                        class="regular-text">
                                    <p class="description">
                                        <?php _e('File Manager Advanced Files URL, you can change according to your choice.', 'file-manager-advanced'); ?>
                                    </p>
                                    <p>Default: <code><?php echo esc_url($url); ?></code></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Maximum Upload Size', 'file-manager-advanced') ?></th>
                                <td>
                                    <input type="text" name="upload_max_size" id="upload_max_size" class="regular-text"
                                        value="<?php echo isset($settings['upload_max_size']) && !empty($settings['upload_max_size']) ? esc_attr($settings['upload_max_size']) : 0; ?>">
                                    <div>
                                        <p>
                                            <?php _e('Maximum upload file size. This size is per files. Can be set as number with unit like 10M, 500K, 1G. 0 means unlimited upload.', 'file-manager-advanced'); ?>
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Default View Type', 'file-manager-advanced') ?></th>
                                <td>
                                    <?php
                                    foreach (FMA_UI as $ui) {
                                        $checked = '';
                                        if (isset($settings['display_ui_options'])) {
                                            if (in_array($ui, $settings['display_ui_options'])) {
                                                $checked = 'checked=checked';
                                            }
                                        } else {
                                            $checked = 'checked=checked';
                                        }
                                        ?>
                                        <input type="checkbox" value="<?php echo esc_attr($ui); ?>" name="display_ui_options[]"
                                            <?php echo esc_attr($checked); ?> /> <?php echo esc_attr($ui); ?> <br />
                                    <?php } ?>
                                    <p><?php _e('You can control the view of file manager. By default, all options are checked.', 'file-manager-advanced'); ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Hide File Path on Preview ?', 'file-manager-advanced');
                                ?></th>
                                <td>
                                    <input name="hide_path" type="checkbox" id="hide_path" value="1" <?php echo isset($settings['hide_path']) && ($settings['hide_path'] == '1') ? 'checked="checked"' : ''; ?>>
                                    <p class="description">
                                        <?php _e('Hide real path of file on preview.', 'file-manager-advanced') ?>
                                    </p>
                                    <p>Default: <code><?php _e('Disabled', 'file-manager-advanced') ?></code></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Enable Trash?', 'file-manager-advanced');
                                ?></th>
                                <td>
                                    <input name="enable_trash" type="checkbox" id="enable_trash" value="1" <?php echo isset($settings['enable_trash']) && ($settings['enable_trash'] == '1') ? 'checked="checked"' : ''; ?>>
                                    <p class="description">
                                        <?php _e('Deleted files will go to trash folder, you can restore later.', 'file-manager-advanced') ?>
                                    </p>
                                    <p>Default: <code><?php _e('Disabled', 'file-manager-advanced') ?></code></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Display .htaccess?', 'file-manager-advanced');
                                ?></th>
                                <td>
                                    <input name="enable_htaccess" type="checkbox" id="enable_htaccess" value="1" <?php echo isset($settings['enable_htaccess']) && ($settings['enable_htaccess'] == '1') ? 'checked="checked"' : ''; ?>>
                                    <p class="description">
                                        <?php _e('Will Display .htaccess file (if exists) in file manager.', 'file-manager-advanced') ?>
                                    </p>
                                    <p>Default: <code><?php _e('Disabled', 'file-manager-advanced') ?></code></p>
                                </td>
                            </tr>
                            <tr>
                                <th><?php _e('Mimetypes allowed to upload', 'file-manager-advanced') ?></th>
                                <td>
                                    <textarea name="fma_upload_allow" id="fma_upload_allow" class="large-text" rows="3"
                                        cols="30"><?php echo isset($settings['fma_upload_allow']) && !empty($settings['fma_upload_allow']) ? esc_attr($settings['fma_upload_allow']) : 'all'; ?></textarea>
                                    <p class="description">
                                        <?php _e('Enter Mimetypes allowed to upload, multiple comma(,) separated. Example: <code>image/vnd.adobe.photoshop,image/png</code>', 'file-manager-advanced') ?>
                                    </p>
                                    <p>Default: <code><?php _e('all', 'file-manager-advanced') ?></code> <a
                                            href="https://advancedfilemanager.com/advanced-file-manager-mime-types/"
                                            target="_blank"><?php _e('MIME Types Help', 'file-manager-advanced'); ?></a></p>
                                </td>
                            </tr>

                            <tr>
                                <th>
                                    <label for="fma_cm_theme">
                                        <?php _e('Code Editor Theme <sup style="color:red;">New</sup>', 'file-manager-advanced') ?>
                                    </label>
                                </th>
                                <td>
                                    <select class="file-manager-advanced-select2 regular-text fma-code-editor-theme"
                                        name="fma_cm_theme" id="fma_cm_theme">
                                        <?php foreach ($cm_themes as $theme): ?>
                                            <?php $selected = isset($settings['fma_cm_theme']) && $settings['fma_cm_theme'] === $theme['title'] ? 'selected="selected"' : ''; ?>
                                            <?php $disabled = $theme['pro'] && !class_fma_main::has_pro() ? 'disabled="disabled"' : ''; ?>

                                            <?php printf('<option value="%s" %s %s>%s</option>', $theme['title'], $selected, $disabled, $theme['title']); ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">
                                        <?php _e('Select code editor theme. Default: default', 'file-manager-advanced') ?>
                                    </p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="fma_debug_enabled">
                                        <?php _e('PHP Debug Feature <sup style="color:red;">New</sup>', 'file-manager-advanced') ?>
                                    </label>
                                </th>
                                <td>
                                    <input name="fma_debug_enabled" type="checkbox" id="fma_debug_enabled" value="1" <?php echo isset($settings['fma_debug_enabled']) && ($settings['fma_debug_enabled'] == '1') ? 'checked="checked"' : ''; ?>>
                                    <p class="description">
                                        <?php _e('Enable real-time PHP syntax error detection, highlighting, and debugging features in the code editor.', 'file-manager-advanced') ?>
                                    </p>
                                    <p>Default: <code><?php _e('Disabled', 'file-manager-advanced') ?></code></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="fma_delete_data_on_uninstall">
                                        <?php _e('Uninstall Advanced File Manager', 'file-manager-advanced') ?>
                                    </label>
                                </th>
                                <td>
                                    <label>
                                        <input name="fma_delete_data_on_uninstall" type="checkbox"
                                            id="fma_delete_data_on_uninstall" value="1" <?php echo isset($settings['fma_delete_data_on_uninstall']) && ($settings['fma_delete_data_on_uninstall'] == '1') ? 'checked="checked"' : ''; ?>>
                                        <?php echo __('Remove ALL Advanced File Manager data when the plugin is deleted. ', 'file-manager-advanced'); ?>
                                    </label>
                                    <p class="description"><span style="color: red; font-weight: bold;"><?php _e('All Blocks, File logs, and created data will be permanently removed and cannot be recovered.', 'file-manager-advanced') ?></span></p>
                                    <p>Default: <code><?php _e('Disabled', 'file-manager-advanced') ?></code></p>
                                </td>
                            </tr>

                        </tbody>
                    </table>
                    <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary"
                            value="Save Changes"></p>
                </form>

                <?php
            }
        }
        ?>

    </div>
</div>