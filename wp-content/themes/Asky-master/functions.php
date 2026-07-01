<?php
// 禁止转义 $ \ 等符号，兼容 MathJax
remove_filter( 'the_content', 'wptexturize' );
remove_filter( 'the_excerpt', 'wptexturize' );

// 兼容 Markdown + MathJax，纯原生渲染
add_filter('the_content', function($content) {
    // 1. 先把 Markdown 标题、列表、引用转为 HTML
    $content = preg_replace('/^(#+)\s*(.*)$/m', '<h$1>$2</h$1>', $content);
    $content = preg_replace('/^\* (.*)$/m', '<li>$1</li>', $content);
    $content = preg_replace('/^> (.*)$/m', '<blockquote>$1</blockquote>', $content);

    // 2. 保留公式符号，不被 wpautop 吃掉
    $content = preg_replace('/\$([^\$]+)\$/s', '###MATHINLINE###$1###MATHINLINE###', $content);
    $content = preg_replace('/\$\$([^\$]+)\$\$/s', '###MATHBLOCK###$1###MATHBLOCK###', $content);

    // 3. 执行 WordPress 自动排版（wpautop）
    $content = wpautop($content);

    // 4. 把公式恢复回去，不被 <p> 标签包裹
    $content = preg_replace('/###MATHINLINE###(.*?)###MATHINLINE###/s', '$1', $content);
    $content = preg_replace('/###MATHBLOCK###(.*?)###MATHBLOCK###/s', '$1', $content);

    return $content;
}, 1);

// 下面是你原来的主题代码，不动


define( 'SIREN_VERSION', '2.0.5' );

// ====================== 最终方案：不使用外部 Parsedown，零报错 ======================
// 关闭古腾堡（使用经典编辑器）
add_filter('use_block_editor_for_post', '__return_false', 10);
add_filter('use_block_editor_for_post_type', '__return_false', 10);

// 关闭自动转义 $ 符号，让 MathJax 公式可以正常渲染
remove_filter( 'the_content', 'wptexturize' );
remove_filter( 'the_content', 'wp_make_content_images_responsive' );

// ====================== 以下是你主题原装完整代码，不动 ======================

if ( !function_exists( 'akina_setup' ) ) :

if ( !function_exists( 'optionsframework_init' ) ) {
	define( 'OPTIONS_FRAMEWORK_DIRECTORY', get_template_directory_uri() . '/inc/' );
	require_once dirname( __FILE__ ) . '/inc/options-framework.php';
}

function akina_setup() {
	load_theme_textdomain( 'akina', get_template_directory() . '/languages' );
	add_theme_support( 'post_thumbnails' );
	set_post_thumbnail_size( 150, 150, true );

	register_nav_menus( array(
		'primary' => esc_html__( '导航菜单', 'akina' ),
	) );

	add_theme_support( 'html5', array(
		'search-form', 'comment-form', 'comment-list', 'gallery', 'caption',
	) );

	add_theme_support( 'post_formats', array(
		'aside', 'image', 'status',
	) );

	add_theme_support( 'custom_background', apply_filters( 'akina_custom_background_args', array(
		'default_color' => 'ffffff',
		'default_image' => '',
	) ) );
	
	add_filter('pre_option_link_manager_enabled','__return_true');
	
	remove_action('wp_head', 'feed_links_extra', 3);
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'index_rel_link');
	remove_action('wp_head', 'start_post_rel_link', 10, 0);
	remove_action('wp_head', 'wp_generator');
	remove_filter('the_content', 'wptexturize');
	
	remove_action('rest_api_init', 'wp_oembed_register_route');
	remove_filter('rest_pre_serve_request', '_oembed_rest_pre_serve_request', 10, 4);
	remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
	remove_filter('oembed_response_data', 'get_oembed_response_data_rich', 10, 4);
	remove_action('wp_head', 'wp_oembed_add_discovery_links');
	remove_action('wp_head', 'wp_oembed_add_host_js');
	remove_action( 'template_redirect', 'rest_output_link_header', 11, 0 );
	
	function coolwp_remove_open_sans_from_wp_core() {
		wp_deregister_style( 'open-sans' );
		wp_register_style( 'open-sans', false );
		wp_enqueue_style('open-sans','');
	}
	add_action( 'init', 'coolwp_remove_open_sans_from_wp_core' );
	
	function disable_emojis() {
	 remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	 remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
	 remove_action( 'wp_print_styles', 'print_emoji_styles' );
	 remove_action( 'admin_print_styles', 'print_emoji_styles' ); 
	 remove_filter('the_content_feed', 'wp_staticize_emoji');
	 remove_filter('comment_text_rss', 'wp_staticize_emoji'); 
	 remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	 add_filter('tiny_mce_plugins', 'disable_emojis_tinymce' );
	}
	add_action( 'init', 'disable_emojis' );
	 
	function disable_emojis_tinymce( $plugins ) {
	 if ( is_array($plugins) ) {
	 return array_diff($plugins, array( 'wpemoji' ));
	 } else {
	 return array();
	 }
	}
	
	function custom_smilies_src($src, $img){
		return get_bloginfo('template_directory').'/images/smilies/' . $img;
	}
	add_filter('smilies_src', 'custom_smilies_src', 10, 2);

	function init_akinasmilie() {
			global $wpsmiliestrans;
			$wpsmiliestrans = array(
					':mrgreen:' => 'icon_mrgreen.gif',
					':neutral:' => 'icon_neutral.gif',
					':twisted:' => 'icon_twisted.gif',
					':arrow:' => 'icon_arrow.gif',
					':shock:' => 'icon_eek.gif',
					':smile:' => 'icon_smile.gif',
					':???:' => 'icon_confused.gif',
					':cool:' => 'icon_cool.gif',
					':evil:' => 'icon_evil.gif',
					':grin:' => 'icon_biggrin.gif',
					':idea:' => 'icon_idea.gif',
					':oops:' => 'icon_redface.gif',
					':razz:' => 'icon_razz.gif',
					':roll:' => 'icon_rolleyes.gif',
					':wink:' => 'icon_wink.gif',
					':cry:' => 'icon_cry.gif',
					':eek:' => 'icon_surprised.gif',
					':lol:' => 'icon_lol.gif',
					':mad:' => 'icon_mad.gif',
					':sad:' => 'icon_sad.gif',
					'8-)' => 'icon_cool.gif',
					'8-O' => 'icon_eek.gif',
					':-(' => 'icon_sad.gif',
					':-)' => 'icon_smile.gif',
					':-?' => 'icon_confused.gif',
					':-D' => 'icon_biggrin.gif',
					':-P' => 'icon_razz.gif',
					':-o' => 'icon_surprised.gif',
					':-x' => 'icon_mad.gif',
					':-|' => 'icon_neutral.gif',
					';-)' => 'icon_wink.gif',
					'8O' => 'icon_eek.gif',
					':(' => 'icon_sad.gif',
					':)' => 'icon_smile.gif',
					':?' => 'icon_confused.gif',
					':D' => 'icon_biggrin.gif',
					':P' => 'icon_razz.gif',
					':o' => 'icon_surprised.gif',
					':x' => 'icon_mad.gif',
					':|' => 'icon_neutral.gif',
					';)' => 'icon_wink.gif',
					':!:' => 'icon_exclaim.gif',
					':?:' => 'icon_question.gif',
			);
	}
	add_action('init', 'init_akinasmilie', 5); 

	add_filter('nav_menu_css_class', 'my_css_attributes_filter', 100, 1);
	add_filter('nav_menu_item_id', 'my_css_attributes_filter', 100, 1);
	add_filter('page_css_class', 'my_css_attributes_filter', 100, 1);
	function my_css_attributes_filter($var) {
	return is_array($var) ? array_intersect($var, array('current-menu-item','current-post-ancestor','current-menu-ancestor','current-menu-parent')) : '';
	}
		
}
endif;
add_action( 'after_setup_theme', 'akina_setup' );

function admin_lettering(){
    echo'<style type="text/css">body{font-family: Microsoft YaHei;}</style>';
}
add_action('admin_head', 'admin_lettering');

function akina_content_width() {
	$GLOBALS['content_width'] = apply_filters( 'akina_content_width', 640 );
}
add_action( 'after_setup_theme', 'akina_content_width', 0 );

function akina_scripts() {
	wp_enqueue_style( 'siren', get_stylesheet_uri(), array(), SIREN_VERSION );
	wp_enqueue_script( 'jq', get_template_directory_uri() . '/js/jquery.min.js', array(), SIREN_VERSION, true ); 
	wp_enqueue_script( 'pjax-libs', get_template_directory_uri() . '/js/jquery.pjax.js', array(), SIREN_VERSION, true );
	wp_enqueue_script( 'qrcode', get_template_directory_uri() . '/js/qrcode.min.js', array(), SIREN_VERSION, true );
    wp_enqueue_script( 'app', get_template_directory_uri() . '/js/app.js', array('qrcode','jquery','jq','pjax-libs'), SIREN_VERSION, true );
	wp_enqueue_script( 'input', get_template_directory_uri() . '/js/input.min.js', array(), SIREN_VERSION, true );
		
	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment_reply' );
	}

	$mv_live = akina_option('focus_mvlive') ? 'open' : 'close';
	$movies = akina_option('focus_amv') ? array('url' => akina_option('amv_url'), 'name' => akina_option('amv_title'), 'live' => $mv_live) : 'close';
	$auto_height = akina_option('focus_height') ? 'fixed' : 'auto';
	$code_lamp = akina_option('open_prism_codelamp') ? 'open' : 'close';
	if(wp_is_mobile()) $auto_height = 'fixed';
	wp_localize_script( 'app', 'Poi' , array(
		'pjax' => akina_option('poi_pjax'),
		'movies' => $movies,
		'windowheight' => $auto_height,
		'codelamp' => $code_lamp,
		'ajaxurl' => admin_url('admin-ajax.php'),
		'order' => get_option('comment_order'),
		'formpostion' => 'bottom'
	));
}
add_action( 'wp_enqueue_scripts', 'akina_scripts' );

require get_template_directory() .'/inc/decorate.php';
require get_template_directory() . '/inc/template-tags.php';
require get_template_directory() . '/inc/customizer.php';
require get_template_directory() . '/inc/siren-update.php';
require get_template_directory() . '/inc/categories-images.php';

if(!function_exists('akina_comment_format')){
	function akina_comment_format($comment, $args, $depth){
		$GLOBALS['comment'] = $comment;
		?>
		<li <?php comment_class(); ?> id="comment-<?php echo esc_attr(comment_ID()); ?>">
			<div class="contents">
				<div class="comment-arrow">
					<div class="main shadow">
						<div class="profile">
							<a href="<?php comment_author_url(); ?>" target="_blank"><?php echo get_avatar( $comment->comment_author_email, '80', '', get_comment_author() ); ?></a>
						</div>
						<div class="commentinfo">
							<section class="commeta">
								<div class="left">
									<h4 class="author"><a href="<?php comment_author_url(); ?>" target="_blank"><?php echo get_avatar( $comment->comment_author_email, '24', '', get_comment_author() ); ?><?php comment_author(); ?> <span class="isauthor" title="<?php esc_attr_e('Author', 'akina'); ?>">博主</span></a></h4>
								</div>
								<?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?>
								<div class="right">
									<div class="info"><time datetime="<?php comment_date('Y-m-d'); ?>"><?php echo poi_time_since(strtotime($comment->comment_date_gmt), true );?></time><?php echo siren_get_useragent($comment->comment_agent); ?></div>
								</div>
							</section>
						</div>
						<div class="body">
							<?php comment_text(); ?>
						</div>
					</div>
					<div class="arrow-left"></div>
				</div>
			</div>
			<hr>
		<?php
	}
}

function restyle_text($number) {
    if($number >= 1000) {
        return round($number/1000,2) . 'k';
    }else{
        return $number;
    }
}

function set_post_views() {
    global $post;
    if ( !empty($post)) {
		$post_id = intval($post->ID);
		$count_key = 'views';
		$views = get_post_custom($post_id);
		if( !empty($views['views'][0]) ) {
			$views = intval($views['views'][0]);
			if(is_single() || is_page()) {
				if(!update_post_meta($post_id, 'views', ($views + 1))) {
					add_post_meta($post_id, 'views', 1, true);
				}
			}
		}else{
			add_post_meta($post_id, 'views', 1, true);
		}
	}
}
add_action('get_header', 'set_post_views');

function get_post_views($post_id) {
    $count_key = 'views';
    $views = get_post_custom($post_id);
    if( !empty($views['views'][0]) ) {
        $views = intval($views['views'][0]);
        $post_views = intval(post_custom('views'));
        if($views == '') {
            return 0;
        }else{
            return restyle_text($views);
        }
    }else{
        add_post_meta($post_id, 'views', 1, true);
    }
} 

add_action('wp_ajax_nopriv_specs_zan', 'specs_zan');
add_action('wp_ajax_specs_zan', 'specs_zan');
function specs_zan(){
    global $wpdb,$post;
    $id = $_POST["um_id"];
    $action = $_POST["um_action"];
    if ( $action == 'ding'){
        $specs_raters = get_post_meta($id,'specs_zan',true);
        $expire = time() + 99999999;
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        setcookie('specs_zan_'.$id,$id,$expire,'/',$domain,false);
        if (!$specs_raters || !is_numeric($specs_raters)) {
            update_post_meta($id, 'specs_zan', 1);
        } 
        else {
            update_post_meta($id, 'specs_zan', ($specs_raters + 1));
        }
        echo get_post_meta($id,'specs_zan',true);
    } 
    die;
}

function get_the_link_items($id = null){
  $default_ico = get_template_directory_uri().'/images/none.png'; 
  $bookmarks = get_bookmarks('orderby=date&category=' .$id );
  $output = '';
  if ( !empty($bookmarks) ) {
      $output .= '<ul class="link-items fontSmooth">';
      foreach ($bookmarks as $bookmark) {
		 $link_favicon = $bookmark->link_url.'/favicon.ico';
         $link_img = $bookmark->link_image;
		 $link_ico = '';
		  if ( !empty($link_img) ){
			  $link_ico = $link_img;
		  }else{
			   $link_ico = $link_favicon;
		  } 
        $output .=  '<li class="link-item"><a class="link-item-inner effect-apollo" href="' . $bookmark->link_url . '" title="' . $bookmark->link_description . '" target="_blank" >
		<img class="linksimage" src="'. $link_ico.'" alt="" onerror="javascript:this.src=\'' . $default_ico . '\'" /><span class="sitename">'. $bookmark->link_name .'</span><div class="linkdes">'. ''. $bookmark->link_description .'</div></a></li>';
      }
      $output .= '</ul>';
  }
  return $output;
}

function get_link_items(){
  $linkcats = get_terms( 'link_category' );
  	if ( !empty($linkcats) ) {
      	foreach( $linkcats as $linkcat){            
        	$result .=  '<h3 class="link-title">'.$linkcat->name.'</h3>';
        	if( $linkcat->description ) $result .= '<div class="link-description">' . $linkcat->description . '</div>';
        	$result .=  get_the_link_items($linkcat->term_id);
      	}
  	} else {
    	$result = get_the_link_items();
  	}
  return $result;
}

if ( ! function_exists( 'get_weavatar_url' ) ) {
    function get_weavatar_url( $url ) {
        $sources = array('www.gravatar.com','0.gravatar.com','1.gravatar.com','2.gravatar.com','secure.gravatar.com','cn.gravatar.com','gravatar.com');
        return str_replace( $sources, 'weavatar.com', $url );
    }
    add_filter( 'um_user_avatar_url_filter', 'get_weavatar_url', 1 );
    add_filter( 'bp_gravatar_url', 'get_weavatar_url', 1 );
    add_filter( 'get_avatar_url', 'get_weavatar_url', 1 );
    add_filter( 'um_user_avatar_url_filter', 'get_weavatar_url', PHP_INT_MAX );
    add_filter( 'bp_gravatar_url', 'get_weavatar_url', PHP_INT_MAX );
    add_filter( 'get_avatar_url', 'get_weavatar_url', PHP_INT_MAX );
}

function theme_noself_ping( &$links ) { 
	$home = get_option( 'home' );
	foreach ( $links as $l => $link )
	if ( 0 === strpos( $link, $home ) )
	unset($links[$l]); 
}
add_action('pre_ping','theme_noself_ping');

function akina_body_classes( $classes ) {
  if ( is_multi_author() ) {
    $classes[] = 'group-blog';
  }
  if ( ! is_singular() ) {
    $classes[] = 'hfeed';
  }
  return $classes;
}
add_filter( 'body_class', 'akina_body_classes' );

add_filter( 'upload_dir', 'wpjam_custom_upload_dir' );
function wpjam_custom_upload_dir( $uploads ) {
	$upload_path = '';
	$upload_url_path = akina_option('qiniu_cdn');

	if ( empty( $upload_path ) || 'wp-content/uploads' == $upload_path ) {
		$uploads['basedir']  = WP_CONTENT_DIR . '/uploads';
	} elseif ( 0 !== strpos( $upload_path, ABSPATH ) ) {
		$uploads['basedir'] = path_join( ABSPATH, $upload_path );
	} else {
		$uploads['basedir'] = $upload_path;
	}

	$uploads['path'] = $uploads['basedir'].$uploads['subdir'];

	if ( $upload_url_path ) {
		$uploads['baseurl'] = $upload_url_path;
		$uploads['url'] = $uploads['baseurl'].$uploads['subdir'];
	}
	return $uploads;
}

function unregister_default_widgets() {
	unregister_widget("WP_Widget_Pages");
	unregister_widget("WP_Widget_Calendar");
	unregister_widget("WP_Widget_Archives");
	unregister_widget("WP_Widget_Links");
	unregister_widget("WP_Widget_Meta");
	unregister_widget("WP_Widget_Search");
	unregister_widget("WP_Widget_Text");
	unregister_widget("WP_Widget_Categories");
	unregister_widget("WP_Widget_Recent_Posts");
	unregister_widget("WP_Widget_Recent_Comments");
	unregister_widget("WP_Widget_RSS");
	unregister_widget("WP_Widget_Tag_Cloud");
	unregister_widget("WP_Nav_Member_Widget");
}
add_action("widgets_init", "unregister_default_widgets", 11);

function akina_jetpack_setup() {
  add_theme_support( 'infinite-scroll', array(
    'container' => 'main',
    'render'    => 'akina_infinite_scroll_render',
    'footer'    => 'page',
  ) );

  add_theme_support( 'jet-responsive-videos' );
}
add_action( 'after_setup_theme', 'akina_jetpack_setup' );

function akina_infinite_scroll_render() {
  while ( have_posts() ) {
    the_post();
    if ( is_search() ) :
        get_template_part( 'tpl/content', 'search' );
    else :
        get_template_part( 'tpl/content', get_post_format() );
    endif;
  }
}

function enable_more_buttons($buttons) { 
	$buttons[] = 'hr'; 
	$buttons[] = 'del'; 
	$buttons[] = 'sub'; 
	$buttons[] = 'sup';
	$buttons[] = 'fontselect';
	$buttons[] = 'fontsizeselect';
	$buttons[] = 'cleanup';
	$buttons[] = 'styleselect';
	$buttons[] = 'wp_page';
	$buttons[] = 'anchor'; 
	$buttons[] = 'backcolor'; 
	return $buttons;
} 
add_filter("mce_buttons_3", "enable_more_buttons");

function download($atts, $content = null) { 
	if (akina_option('download_zan')=='1'){
		$download_post_open = "specsZan";
	};
	$download_post_ID = get_the_ID(); 
return '<a  id = "download_link" class="download" href="'.$content.'" rel="external"  
target="_blank" title="下载地址" >  
<span data-action="ding" data-id="'.$download_post_ID.'" class="'.$download_post_open.'" ><i class="iconfont icon-download"></i>Download</span></a>';  } 
add_shortcode("download", "download"); 

function add_quicktags() {
 if (wp_script_is('quicktags')){
?>
 <script type="text/javascript">
     QTags.addButton( 'All', '分隔线', "————————————————————————————————————————");
	 QTags.addButton( 'All', '回复可见', "<!--hide start{reply_to_this=true}-->隐藏内容<!--hide end-->");
 </script>
<?php
 }
}
add_action( 'admin_print_footer_scripts', 'add_quicktags' );

function custom_login() {
	echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('template_directory') . '/inc/login.css" />'."\n";
	echo '<script type="text/javascript" src="'.get_bloginfo('template_directory').'/js/jquery.min.js"></script>'."\n";
}
add_action('login_head', 'custom_login');

function custom_headertitle ( $title ) {
	return get_bloginfo('name');
}
add_filter('login_headertitle','custom_headertitle');

function custom_loginlogo_url($url) {
	return esc_url( home_url('/') );
}
add_filter( 'login_headerurl', 'custom_loginlogo_url' );

function custom_html() {
	if ( akina_option('login_bg') ) {
		$loginbg = akina_option('login_bg'); 
	}else{
		$loginbg = get_bloginfo('template_directory').'/images/background.svg';
	}
	echo '<script type="text/javascript" src="'.get_bloginfo('template_directory').'/js/login.js"></script>'."\n";
	echo '<script type="text/javascript">'."\n";
	echo 'jQuery("body").prepend("<div class=\"loading\"><img src=\"'.get_bloginfo('template_directory').'/images/login_loading.gif\" width=\"58\" height=\"10\"></div><div id=\"bg\"><img /></div>");'."\n";
	echo 'jQuery(\'#bg\').children(\'img\').attr(\'src\', \''.$loginbg.'\').load(function(){'."\n";
	echo '	resizeImage(\'bg\');'."\n";
	echo '	jQuery(window).bind("resize", function() { resizeImage(\'bg\'); });'."\n";
	echo '	jQuery(\'.loading\').fadeOut();'."\n";
	echo '});';
	echo '</script>'."\n";
}
add_action('login_footer', 'custom_html');

if(!function_exists('get_totalviews')){
function get_totalviews($display = true) {   
	global $wpdb;   
	$total_views = intval($wpdb->get_var("SELECT SUM(meta_value+0) FROM $wpdb->postmeta WHERE meta_key = 'views'"));   
	if($display){
		return number_format_i18n($total_views);
	}else{
		   return $total_views; 
	}	 
}  
} 

function comment_mail_notify($comment_id){
	$mail_user_name = akina_option('mail_user_name') ? akina_option('mail_user_name') : 'poi';
    $comment = get_comment($comment_id);
    $parent_id = $comment->comment_parent ? $comment->comment_parent : '';
    $spam_confirmed = $comment->comment_approved;
    if(($parent_id != '') && ($spam_confirmed != 'spam')){
    $wp_email = $mail_user_name . '@' . 'email.' . preg_replace('#^www\.#', '', strtolower($_SERVER['SERVER_NAME']));
    $to = trim(get_comment($parent_id)->comment_author_email);
    $subject = '你在 [' . get_option("blogname") . '] 的留言有了回应';
    $message = '
    <div style="width: 650px;height: auto;border-radius: 8px;margin:0 auto;border:1px;box-shadow: 0px 0px 10px #888888;position: relative;padding-bottom: 5px;">
	<div style="background-image: url(https://cbu01.alicdn.com/img/ibank/O1CN01KAzzdW1PNj9gzK3bX_!!2207679801829-0-cib.jpg);width:650px;height: 250px;background-size: cover;background-repeat: no-repeat;border-radius: 8px 8px 0px 0px;">
	</div>
	<div style="width: 27%;;height: 40px;background-color:#FE9600;margin-top: -20px;margin-left: 20px;box-shadow: 2px 2px 2px rgba(0, 0, 0, 0.3);color: rgb(255, 255, 255);text-align: center;line-height: 40px;border-radius: 8px;">亲爱的: ' . trim(get_comment($parent_id)->comment_author) . '
	</div>
	<div style="background-color:white;padding:0 15px 12px;margin:35px auto;font-size:12px;">
		<h2 style="border-bottom:1px solid #DDD;font-size:14px;font-weight:normal;padding:13px 0 10px 8px;">
			<span style="color: #12ADDB;font-weight: bold;">
				&gt; &gt; &gt;
			</span>
			您在《
			<a style="text-decoration:none;color: #12ADDB;" href="'.get_permalink($comment->comment_post_ID) . '"target="_blank" rel="noopener">' . get_the_title($comment->comment_post_ID) . '
			</a>
			》的留言有了新的回复呐~
		</h2>
		<div style="padding:0 12px 0 12px;margin-top:18px">
		    <div>
			    <p style="float: left;margin:0px 10px 60px 0px;"><img src="' . get_avatar_url( get_comment($parent_id)->comment_author_email, '50') . '" style="border-radius: 8px; height: 50px; width: 50px;"></p>
				<p style="font-size: 14px;">' . trim(get_comment($parent_id)->comment_author) . '</p>
				<p style="color: #b1b1c1;">' . trim(get_comment($parent_id)->comment_date). '</p>
				<p style="background: #fafafa;box-shadow: 0 2px 5px rgb(0 0 0 / 15%);margin: 15px 0;padding:15px;border-radius:8px;font-size:14px;color:#555;overflow: hidden;">'. trim(get_comment($parent_id)->comment_content) . '</p>
			</div>
			<div style="margin: 0px 0px 0px 50px;">
				<p style="float: left;margin:0px 10px 60px 0px;"><img src="' . get_avatar_url( $comment->comment_author_email, '50') . '" style="border-radius: 8px; height: 50px; width: 50px;"></p>
				<p style="font-size: 14px;">' . trim($comment->comment_author) . '</p>
				<p style="color: #b1b1b1;">' . trim($comment->comment_date). '</p>
				<p style="background: #fafafa;box-shadow: 0 2px 5px rgb(0 0 0 / 15%);margin: 15 0;padding:15;border-radius:8px;font-size:14px;color:#555;overflow: hidden;">'. trim($comment->comment_content) . '</p>
			</div>
		</div>
	</div>
	<div style="color:#8c8c8c;font-size: 10px;text-align: center;">
		<p style="padding:20px;">邮件内容来源<a href="'. get_bloginfo('url').'" rel="noopener" target="_blank">'. get_option("blogname").'的小站</a>
		</p>
	</div>
	<a style="text-decoration:none;color:#FFF;width: 40%;text-align: center;background-color: #FE9600;line-height: 40px;box-shadow: 3px 3px 3px rgba(0, 0, 0, 0.30);margin: -10px auto;display: block;border-radius: 8px"href="' . htmlspecialchars(get_comment_link($parent_id)) . '" target="_blank" rel="noopener">查看回复的完整內容
	</a>
	<div style="color:#8c8c8c;font-size: 10px;text-align: center;margin-top: 30px;">
		本邮件为系统自动发送提醒，记得去原文回复哦~
	</div>
</div>';
    $from = "From: \"" . get_option('blogname') . "\" <$wp_email>";
    $headers = "$from\nContent-Type: text/html; charset=" . get_option('blog_charset') . "\n";
    wp_mail( $to, $subject, $message, $headers );
  }
}
add_action('comment_post', 'comment_mail_notify');

add_filter( 'the_content', 'image_alt');
function image_alt($c) {
	global $post;$title = $post->post_title;
	$s = array('/src="(.+?.(jpg|bmp|png|jepg|gif))"/i'=> 'src="$1" alt="'.$title.'"');
	foreach($s as$p => $r){$c = preg_replace($p,$r,$c);}
	return $c;
}

function sc_send( $comment_id ){
	$text = '起来！有大佬评论了！' ;
	$comment =get_comment($comment_id);
	$comment_text= $comment ->comment_content;
	$author = $comment ->comment_author;
	$comment_title = get_the_title($comment->comment_post_ID);
	$comment_link = get_comment_link($comment_id);
	$desp = '大佬【'.$author.'】在《'.$comment_title.'》里给你做了批示：“'.$comment_text.'。”---你要看么？<br>'.$comment_link;
	$key = akina_option('akina_server_key');
	
	$postdata = http_build_query(array('title' => $text,'desp' => $desp));
	$opts = array('http' => array('method'  => 'POST','header'  => 'Content-type: application/x-www-form-urlencoded','content' => $postdata));
	$context  = stream_context_create($opts);
	return $result = file_get_contents('https://sctapi.ftqq.com/'.$key.'.send', false, $context);
}
add_action('comment_post','sc_send',19,2);

function simple_stats() {
	global $wpdb;
$stats = array();
$stats['posts'] = number_format_i18n(wp_count_posts('post')->publish);
$stats['cats']  = number_format_i18n(wp_count_terms('category'));
$stats['tags'] = number_format_i18n(wp_count_terms('post_tag'));
$stats['comments'] = number_format_i18n(wp_count_comments()->approved);
$stats['total_view'] = $wpdb->get_var("SELECT SUM(meta_value+0) FROM $wpdb->postmeta WHERE meta_key = 'views'");
$stats['specs_zan'] = $wpdb->get_var("SELECT SUM(meta_value+0) FROM $wpdb->postmeta WHERE meta_key = 'specs_zan'");
echo 
'<p><i class="iconfont icon-message_fill"></i>发表<span>', $stats['posts'], '</span>篇文章</p><br>',
'<p><i class="iconfont icon-info"></i>建立<span>', $stats['cats'], '</span>个分类</p><br>',
'<p><i class="iconfont icon-tags"></i>生成<span>', $stats['tags'], '</span>个标签</p><br>',
'<p><i class="iconfont icon-communityfill"></i>收到<span>', $stats['comments'], '</span>条评论</p><br>',
'<p><i class="iconfont icon-heart"></i>收到<span>', $stats['specs_zan'], '</span>个赞</p><br>',
'<p><i class="iconfont icon-camera"></i>文章查阅:<span>', $stats['total_view'], '</span>次</p><br>';
}

function getip(){	
if(getenv('HTTP_CLIENT_IP')) {
    $onlineip = getenv('HTTP_CLIENT_IP');
} elseif(getenv('HTTP_X_FORWARDED_FOR')) {
    $onlineip = getenv('HTTP_X_FORWARDED_FOR');
} elseif(getenv('REMOTE_ADDR')) {
    $onlineip = getenv('REMOTE_ADDR');
} else {
    $onlineip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
}
return $onlineip;   			
}

// 首页特色图片（修复主题必须函数）
function get_post_thumb( $return_src = 'true' ){
	global $post, $posts;
	$content = $post->post_content;
	$imgResult = '';
	$pattern = '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i';
	$result = preg_match_all( $pattern, $content, $matches );
	if ( $return_src == 'true' ){
		if ( !empty( $result ) ){
			$imgResult = '<img src="'.get_bloginfo("template_url").'/timthumb.php?src='.$matches[1][0].'&amp;q=100&amp;w=210" alt="" />';
		}
	} else {
		$imgResult = $matches[1][0];
	}
	return $imgResult;
}

 function article_index($content) {
		 $matches = array();
		 $ul_li = '';
		 $rh = "/<h[23]>(.*)<\/h[23]>/im";
		 $h2_num = 0;
		 $h3_num = 0;
		 if(is_single() || !is_tag()){
					if(preg_match_all($rh, $content, $matches)) {
						 foreach($matches[1] as $num => $title) {
								 $hx = substr($matches[0][$num], 0, 3);
								 $start = stripos($content, $matches[0][$num]);
								 $end = strlen($matches[0][$num]);
								 if($hx == "<h2"){
										 $h2_num += 1;
										 $h3_num = 0;
										 $content = substr_replace($content, '<h2 id="h2-'.$num.'">'.$title.'</h2>',$start,$end);
										 $title = preg_replace('/<[^>]*>/', "", $title);
										 $ul_li .= '<li class="h2_nav"><a href="#h2-'.$num.'" class="tooltip" title="'.$title.'"><span>'.$title."</span></a></li>\n";
								 }else if($hx == "<h3"){
										 $h3_num += 1;
										 $content = substr_replace($content, '<h3 id="h3-'.$num.'">'.$title.'</h3>',$start,$end);
										 $title = preg_replace('/<[^>]*>/', "", $title);
										 $ul_li .= '<li class="h3_nav"><a href="#h3-'.$num.'" class="tooltip" title="'.$title.'"><span>'.$title."</span></a></li>\n";
								 }   
						 }
				 }
			     if($ul_li){
				 $content =  $content . "<div class=\"total_nav\"><div class=\"nav_icon breath_animation\"><div id =\"nav_icon\" >目录</div></div><div class=\"post_nav\"><ul class=\"post_nav_content\">\n" . $ul_li . "</ul></div></div>\n";
				 return $content;
				}else{
					 return $content;
				 }
		 }else if(is_home()){
				 return $content;
		 }
 }
 add_filter( "the_content", "article_index" );

add_action('login_head', 'wpdx_remove_language');
function wpdx_remove_language(){
	echo '<style type="text/css">.language-switcher { display:none; }</style>';
}