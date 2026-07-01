<?php
/*
@package: File Manager Advanced
@Class: class_fma_blocks
*/
if (!defined('ABSPATH')) {
	exit;
}

if (class_exists('class_fma_blocks')) {
	return;
}

class class_fma_blocks
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Register block post type
		add_action('init', array($this, 'register_blocks_post_type'));

		// Add meta boxes
		add_action('add_meta_boxes', array($this, 'add_blocks_meta_boxes'));

		// Save meta boxes
		add_action('save_post', array($this, 'save_blocks_meta_boxes'));

		// Change submenu to point to blocks post type
		// Use priority 100 to run after admin_menus class (which runs at default priority 10)
		add_action('admin_menu', array($this, 'change_blocks_submenu'), 100);

		// Enqueue admin scripts for popup
		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'), 999);

		// Add blur overlay to blocks list page
		// Use admin_footer to ensure scripts are loaded
		add_action('admin_footer', array($this, 'add_blocks_list_blur_overlay'));
		add_filter('manage_fma_blocks_posts_columns', array($this, 'set_custom_columns'));
		add_action('manage_fma_blocks_posts_custom_column', array($this, 'custom_column_content'), 10, 2);
	}

	public function set_custom_columns($columns)
	{
		// Remove and re-add columns in desired order
		$new_columns = [];

		// Keep the checkbox column first
		if (isset($columns['cb'])) {
			$new_columns['cb'] = $columns['cb'];
		}

		// 1️⃣ Title column
		$new_columns['title'] = __('Title');

		// 2️⃣ Custom columns (Shortcode, Status, Block Type)
		$new_columns['shortcode'] = __('Shortcode');
		$new_columns['status_col'] = __('Status');
		$new_columns['block_type'] = __('Block Type');
		$new_columns['updated'] = __('Updated');

		// 3️⃣ Bring back updated and date columns at the end if exist
		if (isset($columns['date'])) {
			$new_columns['date'] = $columns['date'];
		}

		return $new_columns;
	}

	public function custom_column_content($column, $post_id)
	{
		if ($column === 'shortcode') {
			$shortcode_id = get_post_meta($post_id, 'shortcode_id', true);
			$title = get_the_title($post_id);
			$shortcode = '[advanced_file_manager_front id="' . esc_attr($shortcode_id) . '" title="' . esc_attr($title) . '"]';

			echo '
			<div style="display:flex; align-items:center; gap:5px;"> <!-- smaller gap -->
				<input 
					type="text" 
					readonly 
					value="' . esc_attr($shortcode) . '" 
					style="
						width: 460px; /* ⬅️ wider input field */
						background: #f9f9f9;
						border: 1px solid #ddd;
						padding: 5px 8px;
						font-family: monospace;
						font-size: 13px;
						color: #333;
						border-radius: 4px;
						line-height: 1.4;
					"
				/>
				<button 
					class="copy-shortcode button button-small" 
					data-shortcode="' . esc_attr($shortcode) . '" 
					style="
						display: flex;
						align-items: center;
						justify-content: center;
						height: 28px;
						cursor: pointer;
						padding: 0 8px;
					"
					title="Copy Shortcode"
				>
					📋
				</button>
			</div>';
		}

		if ($column === 'status_col') {
			$status = get_post_meta($post_id, 'status_label', true);
			if ($status === 'Active') {
				echo '<span style="color:green;font-weight:600;">● Active</span>';
			} else {
				echo '<span style="color:red;font-weight:600;">● Inactive</span>';
			}
		}

		if ($column === 'block_type') {
			$block_type = get_post_meta($post_id, 'block_type', true);
			if ($block_type === 'Logged In') {
				echo '<span style="color:green;font-weight:600;">Logged In</span>';
			} else {
				echo '<span style="font-weight:600;">Non Logged In</span>';
			}
		}
		if ($column === 'updated') {
			$modified = get_post_modified_time('M j, Y g:i A', false, $post_id);
			echo esc_html($modified);
		}
	}

	/**
	 * Enqueue admin scripts
	 */
	public function enqueue_admin_scripts($hook)
	{
		// Only load on blocks post type pages
		$screen = get_current_screen();
		if ($screen && $screen->post_type === 'fma_blocks') {
			// Enqueue jQuery if not already loaded
			wp_enqueue_script('jquery');

			// Use the same select2 handle that's registered in class_fma_main
			// This ensures select2 is available before afm-scripts.js loads
			wp_enqueue_style('afm-jquery.select2', FMA_PLUGIN_URL . 'application/assets/css/select2/jquery.select2.min.css', array(), FMA_VERSION, 'all');
			wp_enqueue_script('afm-jquery.select2', FMA_PLUGIN_URL . 'application/assets/js/select2/jquery.select2.min.js', array('jquery'), FMA_VERSION, false);

			// Enqueue admin scripts that contain popup function
			// Use same handle as class_fma_main for consistency
			wp_enqueue_style('afm-admin', FMA_PLUGIN_URL . 'application/assets/css/afm-styles.css', array('afm-jquery.select2'), FMA_VERSION, 'all');
			wp_enqueue_script('afm-admin', FMA_PLUGIN_URL . 'application/assets/js/afm-scripts.js', array('afm-jquery.select2'), FMA_VERSION, false);

			// Localize script with admin URL (same as class_fma_main)
			wp_localize_script('afm-admin', 'afmAdmin', array(
				'assetsURL' => FMA_PLUGIN_URL . 'application/assets/',
				'jsonURL' => rest_url(),
			));
		}
	}

	/**
	 * Register Blocks Post Type
	 */
	public function register_blocks_post_type()
	{
		// Only register if pro plugin is not active
		if (class_exists('file_manager_advanced_shortcode')) {
			return;
		}

		$labels = array(
			'name' => _x('Blocks', 'Post type general name', 'file-manager-advanced'),
			'singular_name' => _x('Block', 'Post type singular name', 'file-manager-advanced'),
			'menu_name' => _x('File Managers', 'Admin Menu text', 'file-manager-advanced'),
			'add_new' => __('Add New', 'file-manager-advanced'),
			'add_new_item' => __('Add New Block', 'file-manager-advanced'),
			'new_item' => __('New Block', 'file-manager-advanced'),
			'edit_item' => __('Edit Block', 'file-manager-advanced'),
			'view_item' => __('View Block', 'file-manager-advanced'),
			'all_items' => __('All Blocks', 'file-manager-advanced'),
			'search_items' => __('Search Blocks', 'file-manager-advanced'),
			'not_found' => __('No blocks found.', 'file-manager-advanced'),
			'not_found_in_trash' => __('No blocks found in Trash.', 'file-manager-advanced'),
		);

		$args = array(
			'labels' => $labels,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => false, // We'll add it manually via submenu
			'query_var' => true,
			'rewrite' => false,
			'capability_type' => 'post',
			'has_archive' => false,
			'hierarchical' => false,
			'menu_position' => null,
			'menu_icon' => 'dashicons-media-archive',
			'supports' => array('title'),
			'show_in_rest' => false, // Disable Gutenberg
		);

		register_post_type('fma_blocks', $args);
		$this->register_blocks_dummy_data();
	}

	/**
	 * Register dummy data for blocks post type
	 */
	private function register_blocks_dummy_data()
	{
		// Run only once to prevent duplicates
		if (get_option('fma_blocks_dummy_data_created'))
			return;

		$dummy_blocks = [
			[
				'title' => 'Test Block 1',
				'block_type' => 'Logged In',
				'shortcode_id' => 'abc123xyz',
				'status' => 'Active',
			],
			[
				'title' => 'Test Block 2',
				'block_type' => 'Non Logged In',
				'shortcode_id' => 'xyz456abc',
				'status' => 'Inactive',
			],
			[
				'title' => 'Sample Block 3',
				'block_type' => 'Logged In',
				'shortcode_id' => 'pqr789lmn',
				'status' => 'Active',
			],
		];

		foreach ($dummy_blocks as $block) {
			$post_id = wp_insert_post([
				'post_type' => 'fma_blocks',
				'post_title' => $block['title'],
				'post_status' => 'publish',
			]);

			if (!is_wp_error($post_id)) {
				update_post_meta($post_id, 'block_type', $block['block_type']);
				update_post_meta($post_id, 'shortcode_id', $block['shortcode_id']);
				update_post_meta($post_id, 'status_label', $block['status']);
			}
		}

		update_option('fma_blocks_dummy_data_created', true);
	}
	/**
	 * Add meta boxes for blocks post type
	 */
	public function add_blocks_meta_boxes()
	{
		// Only add if pro plugin is not active
		if (class_exists('file_manager_advanced_shortcode')) {
			return;
		}

		add_meta_box(
			'fma_blocks_pro_overlay',
			__('Block Configuration', 'file-manager-advanced'),
			array($this, 'blocks_pro_overlay_meta_box_callback'),
			'fma_blocks',
			'normal',
			'high'
		);
	}

	/**
	 * Blocks Pro Overlay Meta Box Callback
	 */
	public function blocks_pro_overlay_meta_box_callback($post)
	{
		?>
		<div class="fma__blocks-wrapper" style="position: relative;">
			<!-- Blur Overlay -->
			<div class="fma__blocks-blur-overlay" style="
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
				background: rgba(255, 255, 255, 0.8);
				backdrop-filter: blur(5px);
				-webkit-backdrop-filter: blur(5px);
				z-index: 10;
				cursor: pointer;
				display: flex;
				align-items: center;
				justify-content: center;
				flex-direction: column;
			">
				<div style="text-align: center; padding: 20px;">
					<img src="<?php echo esc_url(FMA_PLUGIN_URL . 'application/assets/images/crown.svg'); ?>" alt="Pro Feature"
						style="width: 60px; margin-bottom: 15px;">
					<h3 style="margin: 10px 0; color: #333;"><?php _e('This is a Pro Feature', 'file-manager-advanced'); ?>
					</h3>
					<p style="color: #666; margin: 10px 0;">
						<?php _e('Upgrade to Advanced File Manager Pro to unlock this feature.', 'file-manager-advanced'); ?>
					</p>
					<a href="https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=blocks_feature&utm_campaign=plugin"
						target="_blank" class="button button-primary" style="margin-top: 10px;">
						<?php _e('Get Pro Now', 'file-manager-advanced'); ?>
					</a>
				</div>
			</div>

			<!-- Actual Content (Blurred) -->
			<div style="filter: blur(3px); pointer-events: none; opacity: 0.5; min-height: 400px;">
				<table class="form-table">
					<tbody>
						<tr>
							<th><label><?php _e('Block Type', 'file-manager-advanced'); ?></label></th>
							<td>
								<select disabled>
									<option><?php _e('Logged In Users', 'file-manager-advanced'); ?></option>
									<option><?php _e('Non Logged In Users', 'file-manager-advanced'); ?></option>
								</select>
								<p class="description">
									<?php _e('Select to generate block for logged in or non logged in users.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('Select User Roles', 'file-manager-advanced'); ?></label></th>
							<td>
								<div
									style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Administrator', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Editor', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Author', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Contributor', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Subscriber', 'file-manager-advanced'); ?></label>
								</div>
								<p class="description">
									<?php _e('Select user roles that can access this block.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('Path', 'file-manager-advanced'); ?></label></th>
							<td>
								<input type="text" disabled class="regular-text"
									placeholder="<?php _e('File manager root path', 'file-manager-advanced'); ?>" />
								<p class="description">
									<?php _e('File manager root path. Use "/" for root directory.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('Path Type', 'file-manager-advanced'); ?></label></th>
							<td>
								<select disabled>
									<option><?php _e('Inside WordPress', 'file-manager-advanced'); ?></option>
									<option><?php _e('Outside WordPress', 'file-manager-advanced'); ?></option>
								</select>
								<p class="description">
									<?php _e('Whether the path is inside or outside WordPress directory.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('Operations', 'file-manager-advanced'); ?></label></th>
							<td>
								<div
									style="border: 1px solid #ddd; padding: 10px; border-radius: 4px; max-height: 200px; overflow-y: auto;">
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Upload', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Download', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Delete', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Create Folder', 'file-manager-advanced'); ?></label>
									<label style="display: block; margin: 5px 0;"><input type="checkbox" disabled />
										<?php _e('Rename', 'file-manager-advanced'); ?></label>
								</div>
								<p class="description">
									<?php _e('Select allowed operations for this block.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('View Type', 'file-manager-advanced'); ?></label></th>
							<td>
								<select disabled>
									<option><?php _e('Grid', 'file-manager-advanced'); ?></option>
									<option><?php _e('List', 'file-manager-advanced'); ?></option>
								</select>
								<p class="description">
									<?php _e('Default view type for the file manager.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('Theme', 'file-manager-advanced'); ?></label></th>
							<td>
								<select disabled>
									<option><?php _e('Light', 'file-manager-advanced'); ?></option>
									<option><?php _e('Dark', 'file-manager-advanced'); ?></option>
									<option><?php _e('Grey', 'file-manager-advanced'); ?></option>
								</select>
								<p class="description">
									<?php _e('Choose your preferred theme for the file manager interface.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
						<tr>
							<th><label><?php _e('Language', 'file-manager-advanced'); ?></label></th>
							<td>
								<select disabled>
									<option><?php _e('English', 'file-manager-advanced'); ?></option>
								</select>
								<p class="description">
									<?php _e('Select the language for the file manager interface.', 'file-manager-advanced'); ?>
								</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<script>
			jQuery(document).ready(function ($) {
				$('.fma__blocks-blur-overlay').on('click', function (e) {
					e.preventDefault();
					if (typeof file_manager_advanced_popup === 'function') {
						file_manager_advanced_popup(
							'https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=blocks_feature&utm_campaign=plugin',
							'Get advanced features with Advanced File Manager Pro!',
							'Get Pro Now'
						);
					} else {
						// Fallback if popup function not available
						window.open('https://advancedfilemanager.com/pricing/?utm_source=plugin&utm_medium=blocks_feature&utm_campaign=plugin', '_blank');
					}
				});
			});
		</script>
		<?php
	}

	/**
	 * Save meta boxes
	 */
	public function save_blocks_meta_boxes($post_id)
	{
		// Only save if pro plugin is not active
		if (class_exists('file_manager_advanced_shortcode')) {
			return;
		}

		// Check if it's a blocks post type
		if (get_post_type($post_id) !== 'fma_blocks') {
			return;
		}

		// Don't save anything - this is just a placeholder
	}

	/**
	 * Change blocks submenu to point to post type
	 */
	public function change_blocks_submenu()
	{
		// Only change if pro plugin is not active
		if (class_exists('file_manager_advanced_shortcode')) {
			return;
		}

		global $submenu;

		// Remove old submenu if it exists
		if (isset($submenu['file_manager_advanced_ui'])) {
			foreach ($submenu['file_manager_advanced_ui'] as $key => $item) {
				if (isset($item[2]) && $item[2] === 'file_manager_advanced_shortcodes') {
					unset($submenu['file_manager_advanced_ui'][$key]);
				}
			}
		}

		// Add new submenu pointing to blocks post type
		// This will add it at the end, we'll reorder it below
		add_submenu_page(
			'file_manager_advanced_ui',
			__('Blocks', 'file-manager-advanced'),
			__('Blocks', 'file-manager-advanced'),
			'manage_options',
			'edit.php?post_type=fma_blocks'
		);

		// Reorder submenu to put Blocks at the original position (after AI Code Pilot, before DB Access)
		if (isset($submenu['file_manager_advanced_ui']) && is_array($submenu['file_manager_advanced_ui'])) {
			$blocks_item = null;
			$menu_items = array();

			// Find and extract blocks item
			foreach ($submenu['file_manager_advanced_ui'] as $key => $item) {
				if (isset($item[2]) && $item[2] === 'edit.php?post_type=fma_blocks') {
					$blocks_item = $item;
				} else {
					$menu_items[] = $item;
				}
			}

			// Insert blocks at the correct position
			if ($blocks_item) {
				$insert_position = 2; // Default: after Settings (0) and AI Code Pilot (1), so position 2
				$ai_code_pilot_found = false;

				// Find AI Code Pilot position
				foreach ($menu_items as $index => $item) {
					if (isset($item[2]) && $item[2] === 'ai-code-pilot') {
						$insert_position = $index + 1; // Insert after AI Code Pilot
						$ai_code_pilot_found = true;
						break;
					}
				}

				// If AI Code Pilot not found, insert after Settings (position 1)
				if (!$ai_code_pilot_found) {
					$insert_position = 1;
				}

				// Insert blocks at the calculated position
				array_splice($menu_items, $insert_position, 0, array($blocks_item));

				// Update submenu
				$submenu['file_manager_advanced_ui'] = $menu_items;
			}
		}
	}

	/**
	 * Add blur overlay to blocks list page (same style as OneDrive/Dropbox)
	 */
	public function add_blocks_list_blur_overlay()
	{
		// Only add if pro plugin is not active
		if (class_exists('file_manager_advanced_shortcode')) {
			return;
		}

		// Only on blocks post type pages
		$screen = get_current_screen();
		if (!$screen || $screen->post_type !== 'fma_blocks') {
			return;
		}

		// Only on list page (edit.php), not on edit page
		if ($screen->base === 'edit' && $screen->post_type === 'fma_blocks') {
			?>
			<style>
				/* Blocks heading - same style as OneDrive/Dropbox */
				.fma__blocks__heading {
					color: #000;
					font-size: 18px;
					font-style: normal;
					font-weight: 600;
					line-height: normal;
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
				}

				/* Blocks wrap - same style as OneDrive/Dropbox */
				.fma__blocks__wrap {
					opacity: 0.5;
					position: relative;
				}

				.fma__blocks__wrap::before {
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
			<script>
				jQuery(document).ready(function ($) {
					// Add PRO tag heading (same as OneDrive/Dropbox)
					if ($('.wp-heading-inline').length && !$('.wp-heading-inline').hasClass('fma__blocks__heading-added')) {
						$('.wp-heading-inline').addClass('fma__blocks__heading-added');
						$('.wp-heading-inline').addClass('fma__blocks__heading');
						if ($('.wp-heading-inline').find('.fma__blocks__heading-pro-tag').length === 0) {
							$('.wp-heading-inline').append('<span class="fma__blocks__heading-pro-tag">PRO</span>');
						}
					}

					// Wrap only the table/content area (same as OneDrive/Dropbox)
					// WordPress post type list page structure: .wrap > heading + #posts-filter + .wp-list-table
					// We need to wrap everything after the heading, just like OneDrive/Dropbox
					var $wrap = $('.wrap');
					if ($wrap.length && !$wrap.find('.fma__blocks__wrap').length) {
						// Find the heading element
						var $heading = $wrap.find('.wp-heading-inline').closest('h1, h2');

						if ($heading.length) {
							// Get all siblings after heading (everything after heading)
							var $contentToWrap = $heading.nextAll();

							if ($contentToWrap.length > 0) {
								// Wrap all content after heading (same as OneDrive/Dropbox)
								$contentToWrap.wrapAll('<div class="fma__blocks__wrap" afmp-href=""></div>');
							} else {
								// Fallback: wrap #posts-filter and .wp-list-table together
								var $postsFilter = $('#posts-filter');
								var $listTable = $('.wp-list-table');

								if ($postsFilter.length || $listTable.length) {
									if ($postsFilter.length && $listTable.length) {
										$postsFilter.add($listTable).wrapAll('<div class="fma__blocks__wrap" afmp-href=""></div>');
									} else if ($postsFilter.length) {
										$postsFilter.wrap('<div class="fma__blocks__wrap" afmp-href=""></div>');
									} else if ($listTable.length) {
										$listTable.wrap('<div class="fma__blocks__wrap" afmp-href=""></div>');
									}
								}
							}
						} else {
							// No heading found, wrap #posts-filter and .wp-list-table
							var $postsFilter = $('#posts-filter');
							var $listTable = $('.wp-list-table');

							if ($postsFilter.length || $listTable.length) {
								if ($postsFilter.length && $listTable.length) {
									$postsFilter.add($listTable).wrapAll('<div class="fma__blocks__wrap" afmp-href=""></div>');
								} else if ($postsFilter.length) {
									$postsFilter.wrap('<div class="fma__blocks__wrap" afmp-href=""></div>');
								} else if ($listTable.length) {
									$listTable.wrap('<div class="fma__blocks__wrap" afmp-href=""></div>');
								}
							}
						}
					}

				});
			</script>
			<?php
		}
	}
}