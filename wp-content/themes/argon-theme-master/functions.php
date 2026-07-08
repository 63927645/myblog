<?php
if (version_compare( $GLOBALS['wp_version'], '4.4-alpha', '<' )) {
	echo "<div style='background: #5e72e4;color: #fff;font-size: 30px;padding: 50px 30px;position: fixed;width: 100%;left: 0;right: 0;bottom: 0;z-index: 2147483647;'>" . __("Argon 主题不支持 Wordpress 4.4 以下版本，请更新 Wordpress", 'argon') . "</div>";
}
function theme_slug_setup() {
	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	load_theme_textdomain('argon', get_template_directory() . '/languages');
}
add_action('after_setup_theme','theme_slug_setup');

$argon_version = !(wp_get_theme() -> Template) ? wp_get_theme() -> Version : wp_get_theme(wp_get_theme() -> Template) -> Version;
$GLOBALS['theme_version'] = $argon_version;
$GLOBALS['assets_version'] = $argon_version . '.' . @filemtime(get_template_directory() . '/style.css');
$argon_assets_path = get_option("argon_assets_path");
switch ($argon_assets_path) {
    case "jsdelivr":
	    $GLOBALS['assets_path'] = "https://cdn.jsdelivr.net/gh/solstice23/argon-theme@" . $argon_version;
        break;
    case "fastgit":
	    $GLOBALS['assets_path'] = "https://raw.fastgit.org/solstice23/argon-theme/v" . $argon_version;
        break;
    case "sourcegcdn":
	    $GLOBALS['assets_path'] = "https://gh.sourcegcdn.com/solstice23/argon-theme/v" . $argon_version;
        break;
	case "jsdelivr_gcore":
	    $GLOBALS['assets_path'] = "https://gcore.jsdelivr.net/gh/solstice23/argon-theme@" . $argon_version;
        break;
	case "jsdelivr_fastly":
	    $GLOBALS['assets_path'] = "https://fastly.jsdelivr.net/gh/solstice23/argon-theme@" . $argon_version;
        break;
	case "jsdelivr_cf":
	    $GLOBALS['assets_path'] = "https://testingcf.jsdelivr.net/gh/solstice23/argon-theme@" . $argon_version;
        break;
	case "custom":
		$GLOBALS['assets_path'] = preg_replace('/\/$/', '', get_option("argon_custom_assets_path"));
		$GLOBALS['assets_path'] = preg_replace('/%theme_version%/', $argon_version, $GLOBALS['assets_path']);
		break;
    default:
	    $GLOBALS['assets_path'] = get_bloginfo('template_url');
}

//翻译 Hook
function argon_locate_filter($locate){
	if (substr($locate, 0, 2) == 'zh'){
		if ($locate == 'zh_TW'){
			return $locate;
		}
		return 'zh_CN';
	}
	if (substr($locate, 0, 2) == 'en'){
		return 'en_US';
	}
	if (substr($locate, 0, 2) == 'ru'){
		return 'ru_RU';
	}
	return 'en_US';
}
function argon_get_locate(){
	if (function_exists("determine_locale")){
		return argon_locate_filter(determine_locale());
	}
	$determined_locale = get_locale();
	if (is_admin()){
		$determined_locale = get_user_locale();
	}
}
function theme_locale_hook($locate, $domain){
	if ($domain == 'argon'){
		return argon_locate_filter($locate);
	}
	return $locate;
}
add_filter('theme_locale', 'theme_locale_hook', 10, 2);

//更新主题版本后的兼容
$argon_last_version = get_option("argon_last_version");
if ($argon_last_version == ""){
	$argon_last_version = "0.0";
}
if (version_compare($argon_last_version, $GLOBALS['theme_version'], '<' )){
	if (version_compare($argon_last_version, '0.940', '<')){
		if (get_option('argon_mathjax_v2_enable') == 'true' && get_option('argon_mathjax_enable') != 'true'){
			update_option("argon_math_render", 'mathjax2');
		}
		if (get_option('argon_mathjax_enable') == 'true'){
			update_option("argon_math_render", 'mathjax3');
		}
	}
	if (version_compare($argon_last_version, '0.970', '<')){
		if (get_option('argon_show_author') == 'true'){
			update_option("argon_article_meta", 'time|views|comments|categories|author');
		}
	}
	if (version_compare($argon_last_version, '1.1.0', '<')){
		if (get_option('argon_enable_zoomify') != 'false'){
			update_option("argon_enable_fancybox", 'true');
			update_option("argon_enable_zoomify", 'false');
		}
	}
	if (version_compare($argon_last_version, '1.3.4', '<')){
		switch (get_option('argon_search_post_filter', 'post,page')){
			case 'post,page':
				update_option("argon_enable_search_filters", 'true');
				update_option("argon_search_filters_type", '*post,*page,shuoshuo');
				break;
			case 'post,page,shuoshuo':
				update_option("argon_enable_search_filters", 'true');
				update_option("argon_search_filters_type", '*post,*page,*shuoshuo');
				break;
			case 'post,page,hide_shuoshuo':
				update_option("argon_enable_search_filters", 'true');
				update_option("argon_search_filters_type", '*post,*page');
				break;
			case 'off':
			default:
				update_option("argon_enable_search_filters", 'false');
				break;
		}		
	}
	update_option("argon_last_version", $GLOBALS['theme_version']);
}


//检测更新
require_once(get_template_directory() . '/theme-update-checker/plugin-update-checker.php');
$argon_update_source = get_option('argon_update_source');
switch ($argon_update_source) {
	case "stop":
		break;
    case "fastgit":
	    $argonThemeUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://api.solstice23.top/argon/info.json?source=fastgit',
			get_template_directory() . '/functions.php',
			'argon'
		);
        break;
    case "cfworker":
	    $argonThemeUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://api.solstice23.top/argon/info.json?source=cfworker',
			get_template_directory() . '/functions.php',
			'argon'
		);
        break;
	case "solstice23top":
		$argonThemeUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://api.solstice23.top/argon/info.json?source=0',
			get_template_directory() . '/functions.php',
			'argon'
		);
		break;
	case "github":
    default:
		$argonThemeUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
			'https://raw.githubusercontent.com/solstice23/argon-theme/master/info.json',
			get_template_directory() . '/functions.php',
			'argon'
		);
}

//初次使用时发送安装量统计信息 (数据仅用于统计安装量)
function post_analytics_info(){
	if(function_exists('file_get_contents')){
		$contexts = stream_context_create(
			array(
				'http' => array(
					'method'=>"GET",
					'header'=>"User-Agent: ArgonTheme\r\n"
				)
			)
		);
		$result = file_get_contents('http://api.solstice23.top/argon_analytics/index.php?domain=' . urlencode($_SERVER['HTTP_HOST']) . '&version='. urlencode($GLOBALS['theme_version']), false, $contexts);
		update_option('argon_has_inited', 'true');
		return $result;
	}else{
		update_option('argon_has_inited', 'true');
	}
}
if (get_option('argon_has_inited') != 'true'){
	post_analytics_info();
}
//时区修正
if (get_option('argon_enable_timezone_fix') == 'true'){
	date_default_timezone_set('UTC');
}
//注册小工具
function argon_widgets_init() {
	register_sidebar(
		array(
			'name'          => __('左侧栏小工具', 'argon'),
			'id'            => 'leftbar-tools',
			'description'   => __( '左侧栏小工具 (如果设置会在侧栏增加一个 Tab)', 'argon'),
			'before_widget' => '<div id="%1$s" class="widget %2$s card bg-white border-0">',
			'after_widget'  => '</div>',
			'before_title'  => '<h6 class="font-weight-bold text-black">',
			'after_title'   => '</h6>',
		)
	);
	register_sidebar(
		array(
			'name'          => __('右侧栏小工具', 'argon'),
			'id'            => 'rightbar-tools',
			'description'   => __( '右侧栏小工具 (在 "Argon 主题选项" 中选择 "三栏布局" 才会显示)', 'argon'),
			'before_widget' => '<div id="%1$s" class="widget %2$s card shadow-sm bg-white border-0">',
			'after_widget'  => '</div>',
			'before_title'  => '<h6 class="font-weight-bold text-black">',
			'after_title'   => '</h6>',
		)
	);
	register_sidebar(
		array(
			'name'          => __('站点概览额外内容', 'argon'),
			'id'            => 'leftbar-siteinfo-extra-tools',
			'description'   => __( '站点概览额外内容', 'argon'),
			'before_widget' => '<div id="%1$s" class="widget %2$s card bg-white border-0">',
			'after_widget'  => '</div>',
			'before_title'  => '<h6 class="font-weight-bold text-black">',
			'after_title'   => '</h6>',
		)
	);
}
add_action('widgets_init', 'argon_widgets_init');
//注册新后台主题配色方案
function argon_add_admin_color(){
	wp_admin_css_color(
		'argon',
		'Argon',
		get_bloginfo('template_directory') . "/admin.css",
		array("#5e72e4", "#324cdc", "#e8ebfb"),
		array('base' => '#525f7f', 'focus' => '#5e72e4', 'current' => '#fff')
	);
}
add_action('admin_init', 'argon_add_admin_color');
function argon_admin_themecolor_css(){
	$themecolor = get_option("argon_theme_color", "#5e72e4");
	$RGB = hexstr2rgb($themecolor);
	$HSL = rgb2hsl($RGB['R'], $RGB['G'], $RGB['B']);
	echo "
		<style id='themecolor_css'>
			:root{
				--themecolor: {$themecolor} ;
				--themecolor-R: {$RGB['R']} ;
				--themecolor-G: {$RGB['G']} ;
				--themecolor-B: {$RGB['B']} ;
				--themecolor-H: {$HSL['H']} ;
				--themecolor-S: {$HSL['S']} ;
				--themecolor-L: {$HSL['L']} ;
			}
		</style>
	";
	if (get_option("argon_enable_immersion_color", "false") == "true"){
		echo "<script> document.documentElement.classList.add('immersion-color'); </script>";
	}
}
add_filter('admin_head', 'argon_admin_themecolor_css');
function array_remove(&$arr, $item){
	$pos = array_search($item, $arr);
	if ($pos !== false){
		array_splice($arr, $pos, 1);
	}
}
//数字格式化
function format_number_in_kilos($number) {
	if ($number < 1000){
		return $number;
	}
	if (1000 <= $number && $number < 1000000){
		if (1000 <= $number && $number < 10000){
			return round($number / 1000, 1) . "K";
		}else{
			return round($number / 1000, 0) . "K";
		}
	}
	if (1000000 <= $number && $number <= 10000000){
		return round($number / 1000000, 1) . "M";
	}else{
		return round($number / 1000000, 0) . "M";
	}
}
//表情包
require_once(get_template_directory() . '/emotions.php');
//文章特色图片
function argon_get_first_image_of_article(){
	global $post;
	if (post_password_required()){
		return false;
	}
	$post_content_full = apply_filters('the_content', preg_replace( '<!--more(.*?)-->', '', $post -> post_content));
	preg_match('/<img(.*?)(src|data-original)=[\"\']((http:|https:)?\/\/(.*?))[\"\'](.*?)\/?>/', $post_content_full, $match);
	if (isset($match[3])){
		return $match[3];
	}
	return false;
}
function argon_has_post_thumbnail($postID = 0){
	if ($postID == 0){
		global $post;
		$postID = $post -> ID;
	}
	if (has_post_thumbnail()){
		return true;
	}
	$argon_first_image_as_thumbnail = get_post_meta($postID, 'argon_first_image_as_thumbnail', true);
	if ($argon_first_image_as_thumbnail == ""){
		$argon_first_image_as_thumbnail = "default";
	}
	if ($argon_first_image_as_thumbnail == "true" || ($argon_first_image_as_thumbnail == "default" && get_option("argon_first_image_as_thumbnail_by_default", "false") == "true")){
		if (argon_get_first_image_of_article() != false){
			return true;
		}
	}
	return false;
}
function argon_get_post_thumbnail($postID = 0){
	if ($postID == 0){
		global $post;
		$postID = $post -> ID;
	}
	if (has_post_thumbnail()){
		return apply_filters("argon_post_thumbnail", wp_get_attachment_image_src(get_post_thumbnail_id($postID), "full")[0]);
	}
	return apply_filters("argon_post_thumbnail", argon_get_first_image_of_article());
}
//文末附加内容
function get_additional_content_after_post(){
	global $post;
	$postID = $post -> ID;
	$res = get_post_meta($post -> ID, 'argon_after_post', true);
	if ($res == "--none--"){
		return "";
	}
	if ($res == ""){
		$res = get_option("argon_additional_content_after_post");
	}
	$res = str_replace("\n", "</br>", $res);
	$res = str_replace("%url%", get_permalink($postID), $res);
	$res = str_replace("%link%", '<a href="' . get_permalink($postID) . '" target="_blank">' . get_permalink($postID) . '</a>', $res);
	$res = str_replace("%title%", get_the_title(), $res);
	$res = str_replace("%author%", get_the_author(), $res);
	return $res;
}
//输出分页页码
function get_argon_formatted_paginate_links($maxPageNumbers, $extraClasses = ''){
	$args = array(
		'prev_text' => '',
		'next_text' => '',
		'before_page_number' => '',
		'after_page_number' => '',
		'show_all' => True
	);
	$res = paginate_links($args);
	//单引号转双引号 & 去除上一页和下一页按钮
	$res = preg_replace(
		'/\'/',
		'"',
		$res
	);
	$res = preg_replace(
		'/<a class="prev page-numbers" href="(.*?)">(.*?)<\/a>/',
		'',
		$res
	);
	$res = preg_replace(
		'/<a class="next page-numbers" href="(.*?)">(.*?)<\/a>/',
		'',
		$res
	);
	//寻找所有页码标签
	preg_match_all('/<(.*?)>(.*?)<\/(.*?)>/' , $res , $pages);
	$total = count($pages[0]);
	$current = 0;
	$urls = array();
	for ($i = 0; $i < $total; $i++){
		if (preg_match('/<span(.*?)>(.*?)<\/span>/' , $pages[0][$i])){
			$current = $i + 1;
		}else{
			preg_match('/<a(.*?)href="(.*?)">(.*?)<\/a>/' , $pages[0][$i] , $tmp);
			$urls[$i + 1] = $tmp[2];
		}
	}

	if ($total == 0){
		return "";
	}

	//计算页码起始
	$from = max($current - ($maxPageNumbers - 1) / 2 , 1);
	$to = min($current + $maxPageNumbers - ( $current - $from + 1 ) , $total);
	if ($to - $from + 1 < $maxPageNumbers){
		$to = min($current + ($maxPageNumbers - 1) / 2 , $total);
		$from = max($current - ( $maxPageNumbers - ( $to - $current + 1 ) ) , 1);
	}
	//生成新页码
	$html = "";
	if ($from > 1){
		$html .= '<li class="page-item"><a aria-label="First Page" class="page-link" href="' . $urls[1] . '"><i class="fa fa-angle-double-left" aria-hidden="true"></i></a></li>';
	}
	if ($current > 1){
		$html .= '<li class="page-item"><a aria-label="Previous Page" class="page-link" href="' . $urls[$current - 1] . '"><i class="fa fa-angle-left" aria-hidden="true"></i></a></li>';
	}
	for ($i = $from; $i <= $to; $i++){
		if ($current == $i){
			$html .= '<li class="page-item active"><span class="page-link" style="cursor: default;">' . $i . '</span></li>';
		}else{
			$html .= '<li class="page-item"><a class="page-link" href="' . $urls[$i] . '">' . $i . '</a></li>';
		}
	}
	if ($current < $total){
		$html .= '<li class="page-item"><a aria-label="Next Page" class="page-link" href="' . $urls[$current + 1] . '"><i class="fa fa-angle-right" aria-hidden="true"></i></a></li>';
	}
	if ($to < $total){
		$html .= '<li class="page-item"><a aria-label="Last Page" class="page-link" href="' . $urls[$total] . '"><i class="fa fa-angle-double-right" aria-hidden="true"></i></a></li>';
	}
	return '<nav><ul class="pagination' . $extraClasses . '">' . $html . '</ul></nav>';
}
function get_argon_formatted_paginate_links_for_all_platforms(){
	return get_argon_formatted_paginate_links(7) . get_argon_formatted_paginate_links(5, " pagination-mobile");
}
//访问者 Token & Session
function get_random_token(){
	return md5(uniqid(microtime(true), true));
}
function set_user_token_cookie(){
	if (!isset($_COOKIE["argon_user_token"]) || strlen($_COOKIE["argon_user_token"]) != 32){
		$newToken = get_random_token();
		setcookie("argon_user_token", $newToken, time() + 10 * 365 * 24 * 60 * 60, "/");
		$_COOKIE["argon_user_token"] = $newToken;
	}
}
function session_init(){
	set_user_token_cookie();
	if (!session_id()){
		session_start();
	}
}
session_init();
//add_action('init', 'session_init');
//页面 Description Meta
function get_seo_description(){
	global $post;
	if (is_single() || is_page()){
		if (get_the_excerpt() != ""){
			return preg_replace('/ \[&hellip;]$/', '&hellip;', get_the_excerpt());
		}
		if (!post_password_required()){
			return htmlspecialchars(mb_substr(str_replace("\n", '', strip_tags($post -> post_content)), 0, 50)) . "...";
		}else{
			return __("这是一个加密页面，需要密码来查看", 'argon');
		}
	}else{
		return get_option('argon_seo_description');
	}
}
//页面 Keywords
function argon_get_post_home_preview($post_id = null){
	global $post;
	$post_id = $post_id ? intval($post_id) : get_the_ID();
	$post_obj = get_post($post_id);
	if (!$post_obj){
		return "";
	}

	$custom_preview = get_post_meta($post_id, 'argon_home_preview', true);
	$custom_limit = intval(get_post_meta($post_id, 'argon_home_preview_limit', true));
	if (trim($custom_preview) !== ""){
		$preview = wp_kses_post($custom_preview);
		if ($custom_limit > 0){
			$plain_preview = wp_strip_all_tags($preview);
			if (mb_strlen($plain_preview) > $custom_limit){
				$preview = esc_html(mb_substr($plain_preview, 0, $custom_limit)) . "...";
			}
		}
		return $preview;
	}

	$trim_words_count = intval(get_option('argon_trim_words_count', 175));
	if ($trim_words_count <= 0){
		return "";
	}
	if (get_option("argon_hide_shortcode_in_preview") == 'true'){
		$preview = wp_trim_words(do_shortcode(get_the_content('...')), $trim_words_count);
	}else{
		$preview = wp_trim_words(get_the_content('...'), $trim_words_count);
	}
	if (post_password_required($post_obj)){
		$preview = __("这篇文章受密码保护，输入密码才能阅读", 'argon');
	}
	if ($preview == ""){
		$preview = __("这篇文章没有摘要", 'argon');
	}
	if ($post_obj -> post_excerpt){
		$preview = $post_obj -> post_excerpt;
	}
	return $preview;
}

function get_seo_keywords(){
	if (is_single()){
		global $post;
		$tags = get_the_tags('', ',', '', $post -> ID);
		if ($tags != null){
			$res = "";
			foreach ($tags as $tag){
				if ($res != ""){
					$res .= ",";
				}
				$res .= $tag -> name;
			}
			return $res;
		}
	}
	if (is_category()){
		return single_cat_title('', false);
	}
	if (is_tag()){
		return single_tag_title('', false);
	}
	if (is_author()){
		return get_the_author();
	}
	if (is_post_type_archive()){
		return post_type_archive_title('', false);
	}
	if (is_tax()){
		return single_term_title('', false);
	}
	return get_option('argon_seo_keywords');
}
//页面分享预览图
function get_og_image(){
	global $post;
	$postID = $post -> ID;
	$argon_first_image_as_thumbnail = get_post_meta($postID, 'argon_first_image_as_thumbnail', 'true');
	if (has_post_thumbnail() || $argon_first_image_as_thumbnail == 'true'){
		return argon_get_post_thumbnail($postID);
	}
	return '';
}
//页面浏览量
function get_post_views($post_id){
	$count_key = 'views';
	$count = get_post_meta($post_id, $count_key, true);
	if ($count==''){
		delete_post_meta($post_id, $count_key);
		add_post_meta($post_id, $count_key, '0');
		$count = '0';
	}
	return number_format_i18n($count);
}
function set_post_views(){
	if (!is_single() && !is_page()) {
		return;
	}
	if (!isset($post_id)){
		global $post;
		$post_id = $post -> ID;
	}
	if (post_password_required($post_id)){
		return;
	}
	if (isset($_GET['preview'])){
		if ($_GET['preview'] == 'true'){
			if (current_user_can('publish_posts')){
				return;
			}
		}
	}
	$noPostView = 'false';
	if (isset($_POST['no_post_view'])){
		$noPostView = $_POST['no_post_view'];
	}
	if ($noPostView == 'true'){
		return;
	}
	global $post;
	if (!isset($post -> ID)){
		return;
	}
	$post_id = $post -> ID;
	$count_key = 'views';
	$count = get_post_meta($post_id, $count_key, true);
	if (is_single() || is_page()) {
		if ($count==''){
			delete_post_meta($post_id, $count_key);
			add_post_meta($post_id, $count_key, '0');
		} else {
			update_post_meta($post_id, $count_key, $count + 1);
		}
	}
}
add_action('get_header', 'set_post_views');
//字数和预计阅读时间
function get_article_words($str){
	preg_match_all('/<pre(.*?)>[\S\s]*?<code(.*?)>([\S\s]*?)<\/code>[\S\s]*?<\/pre>/im', $str, $codeSegments, PREG_PATTERN_ORDER);
	$codeSegments = $codeSegments[3];
	$codeTotal = 0;
	foreach ($codeSegments as $codeSegment){
		$codeLines = preg_split('/\r\n|\n|\r/', $codeSegment);
		foreach ($codeLines as $line){
			if (strlen(trim($str)) > 0){
				$codeTotal++;
			}
		}
	}

	$str = preg_replace(
		'/<code(.*?)>[\S\s]*?<\/code>/im',
		'',
		$str
	);
	$str = preg_replace(
		'/<pre(.*?)>[\S\s]*?<\/pre>/im',
		'',
		$str
	);
	$str = preg_replace(
		'/<style(.*?)>[\S\s]*?<\/style>/im',
		'',
		$str
	);
	$str = preg_replace(
		'/<script(.*?)>[\S\s]*?<\/script>/im',
		'',
		$str
	);
	$str =  preg_replace('/<[^>]+?>/', ' ', $str);
	$str = html_entity_decode(strip_tags($str));
	preg_match_all('/[\x{4e00}-\x{9fa5}]/u' , $str , $cnRes);
	$cnTotal = count($cnRes[0]);
	$enRes = preg_replace('/[\x{4e00}-\x{9fa5}]/u', '', $str);
	preg_match_all('/[a-zA-Z0-9_\x{0392}-\x{03c9}\x{0400}-\x{04FF}]+|[\x{4E00}-\x{9FFF}\x{3400}-\x{4dbf}\x{f900}-\x{faff}\x{3040}-\x{309f}\x{ac00}-\x{d7af}\x{0400}-\x{04FF}]+|[\x{00E4}\x{00C4}\x{00E5}\x{00C5}\x{00F6}\x{00D6}]+|\w+/u' , $str , $enRes);
	$enTotal = count($enRes[0]);
	return array(
		'cn' => $cnTotal,
		'en' => $enTotal,
		'code' => $codeTotal,
	);
}
function get_article_words_total($str){
	$res = get_article_words($str);
	return $res['cn'] + $res['en'] + $res['code'];
}
function get_reading_time($len){
	$speedcn = get_option('argon_reading_speed', 300);
	$speeden = get_option('argon_reading_speed_en', 160);
	$speedcode = get_option('argon_reading_speed_code', 20);
	$reading_time = $len['cn'] / $speedcn + $len['en'] / $speeden + $len['code'] / $speedcode;
	if ($reading_time < 0.3){
		return __("几秒读完", 'argon');
	}
	if ($reading_time < 1){
		return __("1 分钟内", 'argon');
	}
	if ($reading_time < 60){
		return ceil($reading_time) . " " . __("分钟", 'argon');
	}
	return round($reading_time / 60 , 1) . " " . __("小时", 'argon');
}
//当前文章是否可以生成目录
function have_catalog(){
	if (!is_single() && !is_page()){
		return false;
	}
	if (post_password_required()){
		return false;
	}
	if (is_page() && is_page_template('timeline.php')){
		return true;
	}
	$content = get_post(get_the_ID()) -> post_content;
	if (preg_match('/<h[1-6](.*?)>/',$content)){
		return true;
	}else{
		return false;
	}
}
//获取文章 Meta
function get_article_meta($type){
	if ($type == 'sticky'){
		return '<div class="post-meta-detail post-meta-detail-stickey">
					<i class="fa fa-thumb-tack" aria-hidden="true"></i>
					' . _x('置顶', 'pinned', 'argon') . '
				</div>';
	}
	if ($type == 'needpassword'){
		return '<div class="post-meta-detail post-meta-detail-needpassword">
					<i class="fa fa-lock" aria-hidden="true"></i>
					' . __('需要密码', 'argon') . '
				</div>';
	}
	if ($type == 'time'){
		return '<div class="post-meta-detail post-meta-detail-time">
					<i class="fa fa-clock-o" aria-hidden="true"></i>
					<time title="' . __('发布于', 'argon') . ' ' . get_the_time('Y-n-d G:i:s') . ' | ' . __('编辑于', 'argon') . ' ' . get_the_modified_time('Y-n-d G:i:s') . '">' .
						get_the_time('Y-n-d G:i') . '
					</time>
				</div>';
	}
	if ($type == 'edittime'){
		return '<div class="post-meta-detail post-meta-detail-edittime">
					<i class="fa fa-clock-o" aria-hidden="true"></i>
					<time title="' . __('发布于', 'argon') . ' ' . get_the_time('Y-n-d G:i:s') . ' | ' . __('编辑于', 'argon') . ' ' . get_the_modified_time('Y-n-d G:i:s') . '">' .
						get_the_modified_time('Y-n-d G:i') . '
					</time>
				</div>';
	}
	if ($type == 'views'){
		if (function_exists('pvc_get_post_views')){
			$views = pvc_get_post_views(get_the_ID());
		}else{
			$views = get_post_views(get_the_ID());
		}
		return '<div class="post-meta-detail post-meta-detail-views">
					<i class="fa fa-eye" aria-hidden="true"></i> ' .
					$views .
				'</div>';
	}
	if ($type == 'comments'){
		return '<div class="post-meta-detail post-meta-detail-comments">
					<i class="fa fa-comments-o" aria-hidden="true"></i> ' .
					get_post(get_the_ID()) -> comment_count .
				'</div>';
	}
	if ($type == 'categories'){
		$res = '<div class="post-meta-detail post-meta-detail-categories">
				<i class="fa fa-bookmark-o" aria-hidden="true"></i> ';
		$categories = get_the_category();
		foreach ($categories as $index => $category){
			$res .= '<a href="' . get_category_link($category -> term_id) . '" target="_blank" class="post-meta-detail-catagory-link">' . $category -> cat_name . '</a>';
			if ($index != count($categories) - 1){
				$res .= '<span class="post-meta-detail-catagory-space">,</span>';
			}
		}
		$res .= '</div>';
		return $res;
	}
	if ($type == 'author'){
		$res = '<div class="post-meta-detail post-meta-detail-author">
					<i class="fa fa-user-circle-o" aria-hidden="true"></i> ';
					global $authordata;
		$res .= '<a href="' . get_author_posts_url($authordata -> ID, $authordata -> user_nicename) . '" target="_blank">' . get_the_author() . '</a>
				</div>';
		return $res;
	}
}
//获取文章字数统计和预计阅读时间
function get_article_reading_time_meta($post_content_full){
	$post_content_full = apply_filters("argon_html_before_wordcount", $post_content_full);
	$words = get_article_words($post_content_full);
	$res = '</br><div class="post-meta-detail post-meta-detail-words">
		<i class="fa fa-file-word-o" aria-hidden="true"></i>';
	if ($words['code'] > 0){
		$res .= '<span title="' . sprintf(__( '包含 %d 行代码', 'argon'), $words['code']) . '">';
	}else{
		$res .= '<span>';
	}
	$res .= ' ' . get_article_words_total($post_content_full) . " " . __("字", 'argon');
	$res .= '</span>
		</div>
		<div class="post-meta-devide">|</div>
		<div class="post-meta-detail post-meta-detail-words">
			<i class="fa fa-hourglass-end" aria-hidden="true"></i>
			' . get_reading_time(get_article_words($post_content_full)) . '
		</div>
	';
	return $res;
}
//当前文章是否隐藏 阅读时间 Meta
function is_readingtime_meta_hidden(){
	if (strpos(get_the_content() , "[hide_reading_time][/hide_reading_time]") !== False){
		return true;
	}
	global $post;
	if (get_post_meta($post -> ID, 'argon_hide_readingtime', true) == 'true'){
		return true;
	}
	return false;
}
//当前文章是否隐藏 发布时间和分类 (简洁 Meta)
function is_meta_simple(){
	global $post;
	if (get_post_meta($post -> ID, 'argon_meta_simple', true) == 'true'){
		return true;
	}
	return false;
}
//根据文章 id 获取标题
function get_post_title_by_id($id){
	return get_post($id) -> post_title;
}
//解析 UA 和相应图标
require_once(get_template_directory() . '/useragent-parser.php');
$argon_comment_ua = get_option("argon_comment_ua");
$argon_comment_show_ua = Array();
if (strpos($argon_comment_ua, 'platform') !== false){
	$argon_comment_show_ua['platform'] = true;
}
if (strpos($argon_comment_ua, 'browser') !== false){
	$argon_comment_show_ua['browser'] = true;
}
if (strpos($argon_comment_ua, 'version') !== false){
	$argon_comment_show_ua['version'] = true;
}
function parse_ua_and_icon($userAgent){
	global $argon_comment_ua;
	global $argon_comment_show_ua;
	if ($argon_comment_ua == "" || $argon_comment_ua == "hidden"){
		return "";
	}
	$parsed = argon_parse_user_agent($userAgent);
	$out = "<div class='comment-useragent'>";
	if (isset($argon_comment_show_ua['platform']) && $argon_comment_show_ua['platform'] == true){
		if (isset($GLOBALS['UA_ICON'][$parsed['platform']])){
			$out .= $GLOBALS['UA_ICON'][$parsed['platform']] . " ";
		}else{
			$out .= $GLOBALS['UA_ICON']['Unknown'] . " ";
		}
		$out .= $parsed['platform'];
	}
	if (isset($argon_comment_show_ua['browser']) && $argon_comment_show_ua['browser'] == true){
		if (isset($GLOBALS['UA_ICON'][$parsed['browser']])){
			$out .= " " . $GLOBALS['UA_ICON'][$parsed['browser']];
		}else{
			$out .= " " . $GLOBALS['UA_ICON']['Unknown'];
		}
		$out .= " " . $parsed['browser'];
		if (isset($argon_comment_show_ua['version']) && $argon_comment_show_ua['version'] == true){
			$out .= " " . $parsed['version'];
		}
	}
	$out .= "</div>";
	return apply_filters("argon_comment_ua_icon", $out);
}
//发送邮件
function send_mail($to, $subject, $content){
	wp_mail($to, $subject, $content, array('Content-Type: text/html; charset=UTF-8'));
}
function check_email_address($email){
	return (bool) preg_match( "/^\w+((-\w+)|(\.\w+))*@[A-Za-z0-9]+(([.\-])[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/", $email );
}
//检验评论 Token 和用户 Token 是否一致
function check_comment_token($id){
	if (strlen($_COOKIE['argon_user_token']) != 32){
		return false;
	}
	if ($_COOKIE['argon_user_token'] != get_comment_meta($id, "user_token", true)){
		return false;
	}
	return true;
}
//检验评论发送者 ID 和当前登录用户 ID 是否一致
function check_login_user_same($userid){
	if ($userid == 0){
		return false;
	}
	if ($userid != (wp_get_current_user() -> ID)){
		return false;
	}
	return true;
}
function get_comment_user_id_by_id($comment_ID){
	$comment = get_comment($comment_ID);
	return $comment -> user_id;
}
function check_comment_userid($id){
	if (!check_login_user_same(get_comment_user_id_by_id($id))){
		return false;
	}
	return true;
}
//悄悄话
function is_comment_private_mode($id){
	if (strlen(get_comment_meta($id, "private_mode", true)) != 32){
		return false;
	}
	return true;
}
function user_can_view_comment($id){
	if (!is_comment_private_mode($id)){
		return true;
	}
	if (current_user_can("manage_options")){
		return true;
	}
	if ($_COOKIE['argon_user_token'] == get_comment_meta($id, "private_mode", true)){
		return true;
	}
	return false;
}
//过滤 RSS 中悄悄话
function remove_rss_private_comment_title_and_author($str){
	global $comment;
	if (isset($comment -> comment_ID) && is_comment_private_mode($comment -> comment_ID)){
		return "***";
	}
	return $str;
}
add_filter('the_title_rss' , 'remove_rss_private_comment_title_and_author');
add_filter('comment_author_rss' , 'remove_rss_private_comment_title_and_author');
function remove_rss_private_comment_content($str){
	global $comment;
	if (is_comment_private_mode($comment -> comment_ID)){
		$comment -> comment_content = __('该评论为悄悄话', 'argon');
		return $comment -> comment_content;
	}
	return $str;
}
add_filter('comment_text_rss' , 'remove_rss_private_comment_content');
//评论回复信息
function get_comment_parent_info($comment){
	if (!$GLOBALS['argon_comment_options']['show_comment_parent_info']){
		return "";
	}
	if ($comment -> comment_parent == 0){
		return "";
	}
	$parent_comment = get_comment($comment -> comment_parent);
	return '<div class="comment-parent-info" data-parent-id=' . $parent_comment -> comment_ID . '><i class="fa fa-reply" aria-hidden="true"></i> ' . get_comment_author($parent_comment -> comment_ID) . '</div>';
}
//是否可以查看评论编辑记录
function can_visit_comment_edit_history($id){
	$who_can_visit_comment_edit_history = get_option("argon_who_can_visit_comment_edit_history");
	if ($who_can_visit_comment_edit_history == ""){
		$who_can_visit_comment_edit_history = "admin";
	}
	switch ($who_can_visit_comment_edit_history) {
		case 'everyone':
			return true;

		case 'commentsender':
			if (check_comment_token($id) || check_comment_userid($id)){
				return true;
			}
			return false;

		default:
			if (current_user_can("moderate_comments")){
				return true;
			}
			return false;
	}
}
//获取评论编辑记录
function get_comment_edit_history(){
	$id = $_POST['id'];
	if (!can_visit_comment_edit_history($id)){
		exit(json_encode(array(
			'id' => $_POST['id'],
			'history' => ""
		)));
	}
	$editHistory = json_decode(get_comment_meta($id, "comment_edit_history", true));
	$editHistory = array_reverse($editHistory);
	$res = "";
	$position = count($editHistory) + 1;
	date_default_timezone_set(get_option('timezone_string'));
	foreach ($editHistory as $edition){
		$position -= 1;
		$res .= "<div class='comment-edit-history-item'>
					<div class='comment-edit-history-title'>
						<div class='comment-edit-history-id'>
							#" . $position . "
						</div>
						" . ($edition -> isfirst ? "<span class='badge badge-primary badge-admin'>" . __("最初版本", 'argon') . "</span>" : "") . "
					</div>
					<div class='comment-edit-history-time'>" . date('Y-m-d H:i:s', $edition -> time) . "</div>
					<div class='comment-edit-history-content'>" . str_replace("\n", "</br>", $edition -> content) . "</div>
				</div>";
	}
	exit(json_encode(array(
		'id' => $_POST['id'],
		'history' => $res
	)));
}
add_action('wp_ajax_get_comment_edit_history', 'get_comment_edit_history');
add_action('wp_ajax_nopriv_get_comment_edit_history', 'get_comment_edit_history');
//是否可以置顶/取消置顶
function is_comment_pinable($id){
	if (get_comment($id) -> comment_approved != "1"){
		return false;
	}
	if (get_comment($id) -> comment_parent != 0){
		return false;
	}
	if (is_comment_private_mode($id)){
		return false;
	}
	return true;
}
//评论内容格式化
function argon_get_comment_text($comment_ID = 0, $args = array()) {
	$comment = get_comment($comment_ID);
	$comment_text = get_comment_text($comment, $args);
	$enableMarkdown = get_comment_meta(get_comment_ID(), "use_markdown", true);
	/*if ($enableMarkdown == false){
		return $comment_text;
	}*/
	//图片
	$comment_text = preg_replace(
		'/<a data-src="(.*?)" title="(.*?)" class="comment-image"(.*?)>([\w\W]*)<\/a>/',
		'<img src="$1" alt="$2" />',
		$comment_text
	);
	$comment_text = preg_replace(
		'/<img src="(.*?)" alt="(.*?)" \/>/',
		'<a href="$1" title="$2" data-fancybox="comment-' . $comment -> comment_ID . '-image" class="comment-image" rel="nofollow">
			<i class="fa fa-image" aria-hidden="true"></i>
			' . __('查看图片', 'argon') . '
			<img src="" alt="$2" class="comment-image-preview">
			<i class="comment-image-preview-mask"></i>
		</a>',
		$comment_text
	);
	//表情
	if (get_option("argon_comment_emotion_keyboard", "true") != "false"){
		global $emotionListDefault;
		$emotionList = apply_filters("argon_emotion_list", $emotionListDefault);
		foreach ($emotionList as $groupIndex => $group){ 
			foreach ($group['list'] as $index => $emotion){
				if ($emotion['type'] != 'sticker'){
					continue;
				}
				if (!isset($emotion['code']) || mb_strlen($emotion['code']) == 0){
					continue;
				}
				if (!isset($emotion['src']) || mb_strlen($emotion['src']) == 0){
					continue;
				}
				$comment_text = str_replace(':' . $emotion['code'] . ':', "<img class='comment-sticker lazyload' src='data:image/svg+xml;base64,PHN2ZyBjbGFzcz0iZW1vdGlvbi1sb2FkaW5nIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGZpbGw9Im5vbmUiIHZpZXdCb3g9Ii04IC04IDQwIDQwIiBzdHJva2U9IiM4ODgiIG9wYWNpdHk9Ii41IiB3aWR0aD0iNjAiIGhlaWdodD0iNjAiPgogIDxwYXRoIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIgc3Ryb2tlLXdpZHRoPSIxLjUiIGQ9Ik0xNC44MjggMTQuODI4YTQgNCAwIDAxLTUuNjU2IDBNOSAxMGguMDFNMTUgMTBoLjAxTTIxIDEyYTkgOSAwIDExLTE4IDAgOSA5IDAgMDExOCAweiIvPgo8L3N2Zz4=' data-original='" . $emotion['src'] . "'/><noscript><img class='comment-sticker' src='" . $emotion['src'] . "'/></noscript>", $comment_text);
			}
		}
	}
	return apply_filters( 'comment_text', $comment_text, $comment, $args );
}
//评论点赞
function get_comment_upvotes($id) {
	$comment = get_comment($id);
	if ($comment == null){
		return 0;
	}
	$upvotes = get_comment_meta($comment -> comment_ID, "upvotes", true);
	if ($upvotes == null) {
		$upvotes = 0;
	}
	return $upvotes;
}
function set_comment_upvotes($id){
	$comment = get_comment($id);
	if ($comment == null){
		return 0;
	}
	$upvotes = get_comment_meta($comment -> comment_ID, "upvotes", true);
	if ($upvotes == null) {
		$upvotes = 0;
	}
	$upvotes++;
	update_comment_meta($comment -> comment_ID, "upvotes", $upvotes);
	return $upvotes;
}
function is_comment_upvoted($id){
	$upvotedList = isset( $_COOKIE['argon_comment_upvoted'] ) ? $_COOKIE['argon_comment_upvoted'] : '';
	if (in_array($id, explode(',', $upvotedList))){
		return true;
	}
	return false;
}
function upvote_comment(){
	if (get_option("argon_enable_comment_upvote", "false") != "true"){
		return;
	}
	header('Content-Type:application/json; charset=utf-8');
	$ID = $_POST["comment_id"];
	$comment = get_comment($ID);
	if ($comment == null){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('评论不存在', 'argon'),
			'total_upvote' => 0
		)));
	}
	$upvotedList = isset( $_COOKIE['argon_comment_upvoted'] ) ? $_COOKIE['argon_comment_upvoted'] : '';
	if (in_array($ID, explode(',', $upvotedList))){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('该评论已被赞过', 'argon'),
			'total_upvote' => get_comment_upvotes($ID)
		)));
	}
	set_comment_upvotes($ID);
	setcookie('argon_comment_upvoted', $upvotedList . $ID . "," , time() + 3153600000 , '/');
	exit(json_encode(array(
		'ID' => $ID,
		'status' => 'success',
		'msg' => __('点赞成功', 'argon'),
		'total_upvote' => format_number_in_kilos(get_comment_upvotes($ID))
	)));
}
add_action('wp_ajax_upvote_comment' , 'upvote_comment');
add_action('wp_ajax_nopriv_upvote_comment' , 'upvote_comment');
//评论样式格式化
$GLOBALS['argon_comment_options']['enable_upvote'] = (get_option("argon_enable_comment_upvote", "false") == "true");
$GLOBALS['argon_comment_options']['enable_pinning'] = (get_option("argon_enable_comment_pinning", "false") == "true");
$GLOBALS['argon_comment_options']['current_user_can_moderate_comments'] = current_user_can('moderate_comments');
$GLOBALS['argon_comment_options']['show_comment_parent_info'] = (get_option("argon_show_comment_parent_info", "true") == "true");
function argon_sanitize_comment_wechat_id($wechat_id){
	$wechat_id = sanitize_text_field($wechat_id);
	$wechat_id = preg_replace('/[^\p{L}\p{N}_\-\.\s]/u', '', $wechat_id);
	return function_exists('mb_substr') ? mb_substr($wechat_id, 0, 40, 'UTF-8') : substr($wechat_id, 0, 40);
}
function argon_sanitize_comment_github_id($github_id){
	$github_id = trim(sanitize_text_field($github_id));
	$github_id = preg_replace('/^@/', '', $github_id);
	if (preg_match('/github\.com\/([^\/\?\#]+)/i', $github_id, $matches)){
		$github_id = $matches[1];
	}
	$github_id = preg_replace('/[^A-Za-z0-9\-]/', '', $github_id);
	return substr($github_id, 0, 39);
}
function argon_get_comment_identity_badges($comment_id = 0){
	if ($comment_id == 0){
		$comment_id = get_comment_ID();
	}
	$wechat_id = get_comment_meta($comment_id, "wechat_id", true);
	$github_id = get_comment_meta($comment_id, "github_id", true);
	$html = "";
	if ($wechat_id != ""){
		$html .= '<span class="badge badge-comment-identity badge-comment-wechat" title="微信昵称：' . esc_attr($wechat_id) . '"><i class="fa fa-weixin" aria-hidden="true"></i> ' . esc_html($wechat_id) . '</span>';
	}
	if ($github_id != ""){
		$github_url = 'https://github.com/' . rawurlencode($github_id);
		$html .= '<a class="badge badge-comment-identity badge-comment-github" href="' . esc_url($github_url) . '" target="_blank" rel="nofollow noopener" title="GitHub：' . esc_attr($github_id) . '"><i class="fa fa-github" aria-hidden="true"></i> ' . esc_html($github_id) . '</a>';
	}
	$clogin_id = get_comment_meta($comment_id, "clogin_id", true);
	if ($clogin_id != ""){
		$clogin_type = get_comment_meta($comment_id, "clogin_type", true);
		$clogin_label = $clogin_type != "" ? strtoupper($clogin_type) : "UR";
		$html .= '<span class="badge badge-comment-identity badge-comment-clogin" title="UR互联（微信）登录：' . esc_attr($clogin_id) . '"><i class="fa fa-user-circle" aria-hidden="true"></i> ' . esc_html($clogin_label) . '</span>';
	}
	return $html;
}

function argon_get_comment_oauth_avatar_html($comment, $size = 40){
	$comment_id = is_object($comment) ? intval($comment -> comment_ID) : intval($comment);
	if (!$comment_id){
		return '';
	}
	$avatar_url = get_comment_meta($comment_id, 'oauth_avatar', true);
	if ($avatar_url == ''){
		return '';
	}
	$size = max(16, intval($size));
	return '<img src="' . esc_url($avatar_url) . '" class="avatar avatar-' . esc_attr($size) . ' photo comment-oauth-image-avatar" width="' . esc_attr($size) . '" height="' . esc_attr($size) . '" loading="lazy" referrerpolicy="no-referrer" alt="">';
}

function argon_comment_oauth_config($provider){
	if ($provider == "github"){
		return array(
			'client_id' => defined('ARGON_GITHUB_CLIENT_ID') ? ARGON_GITHUB_CLIENT_ID : '',
			'client_secret' => defined('ARGON_GITHUB_CLIENT_SECRET') ? ARGON_GITHUB_CLIENT_SECRET : ''
		);
	}
	if ($provider == "clogin"){
		return array(
			'client_id' => defined('ARGON_URLOGIN_APPID') ? ARGON_URLOGIN_APPID : (defined('ARGON_CLOGIN_APPID') ? ARGON_CLOGIN_APPID : ''),
			'client_secret' => defined('ARGON_URLOGIN_APPKEY') ? ARGON_URLOGIN_APPKEY : (defined('ARGON_CLOGIN_APPKEY') ? ARGON_CLOGIN_APPKEY : ''),
			'endpoint' => defined('ARGON_URLOGIN_ENDPOINT') ? ARGON_URLOGIN_ENDPOINT : (defined('ARGON_CLOGIN_ENDPOINT') ? ARGON_CLOGIN_ENDPOINT : 'https://uniqueker.top/connect.php'),
			'type' => defined('ARGON_URLOGIN_TYPE') ? ARGON_URLOGIN_TYPE : (defined('ARGON_CLOGIN_TYPE') ? ARGON_CLOGIN_TYPE : 'qq')
		);
	}
	return array('client_id' => '', 'client_secret' => '');
}
function argon_comment_oauth_is_configured($provider){
	$config = argon_comment_oauth_config($provider);
	return $config['client_id'] != '' && $config['client_secret'] != '';
}
function argon_comment_oauth_current_url(){
	$scheme = is_ssl() ? 'https://' : 'http://';
	$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : wp_parse_url(home_url(), PHP_URL_HOST);
	$uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
	return esc_url_raw(remove_query_arg(array('comment_login_error', 'argon_comment_oauth', 'state', 'code', 'type'), $scheme . $host . $uri));
}
function argon_comment_oauth_login_url($provider){
	return add_query_arg(array(
		'argon_comment_oauth' => $provider,
		'redirect_to' => rawurlencode(argon_comment_oauth_current_url())
	), home_url('/'));
}
function argon_comment_oauth_cookie_name(){
	return 'argon_comment_identity';
}
function argon_comment_oauth_sign($payload){
	return hash_hmac('sha256', $payload, wp_salt('auth'));
}
function argon_set_comment_oauth_identity($identity){
	$payload = base64_encode(wp_json_encode($identity));
	$value = $payload . '.' . argon_comment_oauth_sign($payload);
	setcookie(argon_comment_oauth_cookie_name(), $value, 0, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
	$_COOKIE[argon_comment_oauth_cookie_name()] = $value;
}
function argon_clear_comment_oauth_identity(){
	setcookie(argon_comment_oauth_cookie_name(), '', time() - HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
	unset($_COOKIE[argon_comment_oauth_cookie_name()]);
}
function argon_get_comment_oauth_identity(){
	if (empty($_COOKIE[argon_comment_oauth_cookie_name()])){
		return false;
	}
	$parts = explode('.', $_COOKIE[argon_comment_oauth_cookie_name()], 2);
	if (count($parts) != 2 || !hash_equals(argon_comment_oauth_sign($parts[0]), $parts[1])){
		return false;
	}
	$identity = json_decode(base64_decode($parts[0]), true);
	if (!is_array($identity) || empty($identity['provider']) || empty($identity['id']) || empty($identity['name'])){
		return false;
	}
	if (!in_array($identity['provider'], array('github', 'clogin'))){
		return false;
	}
	return $identity;
}
function argon_comment_oauth_fail($message, $redirect_to = ''){
	$redirect_to = $redirect_to == '' ? home_url('/') : $redirect_to;
	wp_safe_redirect(add_query_arg('comment_login_error', rawurlencode($message), $redirect_to));
	exit;
}
function argon_comment_oauth_callback_action(){
	if (!empty($_GET['argon_comment_oauth'])){
		return sanitize_key($_GET['argon_comment_oauth']);
	}
	$request_path = isset($_SERVER['REQUEST_URI']) ? wp_parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
	if (preg_match('#/argon-comment-oauth/clogin-callback/([^/]+)/?#', $request_path, $matches)){
		$_GET['state'] = sanitize_text_field(rawurldecode($matches[1]));
		return 'clogin_callback';
	}
	return '';
}
function argon_handle_comment_oauth(){
	$action = argon_comment_oauth_callback_action();
	if ($action == ''){
		return;
	}
	if ($action == 'logout'){
		$redirect_to = !empty($_GET['redirect_to']) ? esc_url_raw(rawurldecode($_GET['redirect_to'])) : home_url('/');
		argon_clear_comment_oauth_identity();
		wp_safe_redirect($redirect_to);
		exit;
	}
	if ($action == 'github' || $action == 'clogin'){
		if (!argon_comment_oauth_is_configured($action)){
			argon_comment_oauth_fail($action == 'github' ? 'GitHub 登录尚未配置 Client ID/Secret' : 'UR互联（微信）登录尚未配置 APPID/APPKEY', !empty($_GET['redirect_to']) ? esc_url_raw(rawurldecode($_GET['redirect_to'])) : home_url('/'));
		}
		$state = wp_generate_password(24, false, false);
		$redirect_to = !empty($_GET['redirect_to']) ? esc_url_raw(rawurldecode($_GET['redirect_to'])) : home_url('/');
		set_transient('argon_comment_oauth_' . $state, array('provider' => $action, 'redirect_to' => $redirect_to), 10 * MINUTE_IN_SECONDS);
		$config = argon_comment_oauth_config($action);
		$callback = $action == 'clogin'
			? add_query_arg(array(
				'argon_comment_oauth' => 'clogin_callback',
				'state' => $state
			), home_url('/'))
			: add_query_arg('argon_comment_oauth', $action . '_callback', home_url('/'));
		if ($action == 'clogin'){
			$login_response = wp_remote_get(add_query_arg(array(
				'act' => 'login',
				'appid' => $config['client_id'],
				'appkey' => $config['client_secret'],
				'type' => $config['type'],
				'redirect_uri' => $callback
			), $config['endpoint']), array('timeout' => 15));
			if (is_wp_error($login_response)){
				argon_comment_oauth_fail('UR互联（微信）登录请求失败：' . $login_response->get_error_message(), $redirect_to);
			}
			$login_body = json_decode(wp_remote_retrieve_body($login_response), true);
			if (!is_array($login_body)){
				$raw_body = wp_remote_retrieve_body($login_response);
				$msg = $raw_body != '' ? 'UR互联（微信）登录返回非 JSON：' . wp_trim_words(wp_strip_all_tags($raw_body), 20) : 'UR互联（微信）登录返回为空';
				argon_comment_oauth_fail($msg, $redirect_to);
			}
			$login_url = !empty($login_body['url']) ? $login_body['url'] : (!empty($login_body['qrcode']) ? $login_body['qrcode'] : '');
			if (!isset($login_body['code']) || intval($login_body['code']) !== 0 || $login_url == ''){
				$msg = !empty($login_body['msg']) ? sanitize_text_field($login_body['msg']) : 'UR互联（微信）登录跳转地址获取失败';
				$msg .= isset($login_body['code']) ? '（错误码：' . intval($login_body['code']) . '）' : '';
				argon_comment_oauth_fail($msg, $redirect_to);
			}
			wp_redirect(esc_url_raw($login_url));
			exit;
		}
		wp_redirect(add_query_arg(array(
			'client_id' => $config['client_id'],
			'redirect_uri' => add_query_arg('argon_comment_oauth', 'github_callback', home_url('/')),
			'scope' => 'read:user user:email',
			'state' => $state
		), 'https://github.com/login/oauth/authorize'));
		exit;
	}
	if ($action != 'github_callback' && $action != 'clogin_callback'){
		return;
	}
	$provider = str_replace('_callback', '', $action);
	$state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
	$code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
	$state_data = $state != '' ? get_transient('argon_comment_oauth_' . $state) : false;
	if (!$state_data || $state_data['provider'] != $provider || $code == ''){
		argon_comment_oauth_fail('第三方登录状态已过期，请重试');
	}
	delete_transient('argon_comment_oauth_' . $state);
	$config = argon_comment_oauth_config($provider);
	if ($provider == 'clogin'){
		$type = isset($_GET['type']) ? sanitize_key($_GET['type']) : $config['type'];
		$user_response = wp_remote_get(add_query_arg(array(
			'act' => 'callback',
			'appid' => $config['client_id'],
			'appkey' => $config['client_secret'],
			'type' => $type,
			'code' => $code
		), $config['endpoint']), array('timeout' => 15));
		$user = json_decode(wp_remote_retrieve_body($user_response), true);
		if (!isset($user['code']) || intval($user['code']) !== 0 || empty($user['social_uid'])){
			$msg = !empty($user['msg']) ? sanitize_text_field($user['msg']) : 'UR互联（微信）登录失败，请重试';
			argon_comment_oauth_fail($msg, $state_data['redirect_to']);
		}
		$name = !empty($user['nickname']) ? sanitize_text_field($user['nickname']) : 'UR访客';
		argon_set_comment_oauth_identity(array(
			'provider' => 'clogin',
			'id' => sanitize_text_field($user['social_uid']),
			'name' => $name,
			'type' => $type,
			'avatar' => !empty($user['faceimg']) ? esc_url_raw($user['faceimg']) : '',
			'url' => ''
		));
		wp_safe_redirect($state_data['redirect_to']);
		exit;
	}
	$token_response = wp_remote_post('https://github.com/login/oauth/access_token', array(
		'headers' => array('Accept' => 'application/json'),
		'body' => array(
			'client_id' => $config['client_id'],
			'client_secret' => $config['client_secret'],
			'code' => $code,
			'redirect_uri' => add_query_arg('argon_comment_oauth', 'github_callback', home_url('/'))
		),
		'timeout' => 15
	));
	$token_body = json_decode(wp_remote_retrieve_body($token_response), true);
	if (empty($token_body['access_token'])){
		argon_comment_oauth_fail('GitHub 登录失败，请重试', $state_data['redirect_to']);
	}
	$user_response = wp_remote_get('https://api.github.com/user', array(
		'headers' => array(
			'Authorization' => 'Bearer ' . $token_body['access_token'],
			'User-Agent' => 'Argon Comment OAuth'
		),
		'timeout' => 15
	));
	$user = json_decode(wp_remote_retrieve_body($user_response), true);
	if (empty($user['login'])){
		argon_comment_oauth_fail('GitHub 用户信息获取失败', $state_data['redirect_to']);
	}
	argon_set_comment_oauth_identity(array(
		'provider' => 'github',
		'id' => argon_sanitize_comment_github_id($user['login']),
		'name' => sanitize_text_field($user['login']),
		'avatar' => !empty($user['avatar_url']) ? esc_url_raw($user['avatar_url']) : '',
		'url' => !empty($user['html_url']) ? esc_url_raw($user['html_url']) : ''
	));
	wp_safe_redirect($state_data['redirect_to']);
	exit;
}
add_action('init', 'argon_handle_comment_oauth');
function argon_force_session_only_wp_login() {
	if (isset($_POST['rememberme'])) {
		$_POST['rememberme'] = '';
	}
	if (isset($_REQUEST['rememberme'])) {
		$_REQUEST['rememberme'] = '';
	}
}
add_action('login_init', 'argon_force_session_only_wp_login', 1);
function argon_hide_remember_me_login_option() {
	?>
		<style>
			.forgetmenot,
			.login-remember {
				display: none !important;
			}
		</style>
		<script>
			document.addEventListener('DOMContentLoaded', function () {
				var remember = document.getElementById('rememberme');
				if (remember) {
					remember.checked = false;
				}
			});
		</script>
	<?php
}
add_action('login_enqueue_scripts', 'argon_hide_remember_me_login_option');
function argon_disable_frontend_remember_login($defaults) {
	$defaults['remember'] = false;
	return $defaults;
}
add_filter('login_form_defaults', 'argon_disable_frontend_remember_login');
function argon_rewrite_auth_cookies_as_browser_session() {
	if (!is_user_logged_in()) {
		return;
	}
	$cookie_domain = (defined('COOKIE_DOMAIN') && COOKIE_DOMAIN) ? COOKIE_DOMAIN : '';
	$cookie_specs = array();
	if (defined('LOGGED_IN_COOKIE')) {
		$cookie_specs[] = array('name' => LOGGED_IN_COOKIE, 'paths' => array(COOKIEPATH, SITECOOKIEPATH), 'secure' => is_ssl());
	}
	if (defined('AUTH_COOKIE')) {
		$cookie_specs[] = array('name' => AUTH_COOKIE, 'paths' => array(ADMIN_COOKIE_PATH, PLUGINS_COOKIE_PATH), 'secure' => false);
	}
	if (defined('SECURE_AUTH_COOKIE')) {
		$cookie_specs[] = array('name' => SECURE_AUTH_COOKIE, 'paths' => array(ADMIN_COOKIE_PATH, PLUGINS_COOKIE_PATH), 'secure' => true);
	}
	foreach ($cookie_specs as $spec) {
		if (empty($_COOKIE[$spec['name']])) {
			continue;
		}
		$paths = array_unique(array_filter($spec['paths']));
		foreach ($paths as $path) {
			setcookie($spec['name'], $_COOKIE[$spec['name']], 0, $path, $cookie_domain, $spec['secure'], true);
		}
	}
}
add_action('init', 'argon_rewrite_auth_cookies_as_browser_session', 20);
function argon_comment_format($comment, $args, $depth){
	global $comment_enable_upvote, $comment_enable_pinning;
	$GLOBALS['comment'] = $comment;
	if (!($comment -> placeholder) && user_can_view_comment(get_comment_ID())){
	?>
	<li class="comment-item" id="comment-<?php comment_ID(); ?>" data-id="<?php comment_ID(); ?>" data-use-markdown="<?php echo get_comment_meta(get_comment_ID(), "use_markdown", true);?>">
		<div class="comment-item-left-wrapper">
			<div class="comment-item-avatar">
				<?php if(function_exists('get_avatar') && get_option('show_avatars')){
					$oauth_avatar = function_exists('argon_get_comment_oauth_avatar_html') ? argon_get_comment_oauth_avatar_html($comment, 40) : '';
					echo $oauth_avatar != '' ? $oauth_avatar : get_avatar($comment, 40);
				}?>
			</div>
			<?php if ($GLOBALS['argon_comment_options']['enable_upvote']){ ?>
				<button class="comment-upvote btn btn-icon btn-outline-primary btn-sm <?php echo (is_comment_upvoted(get_comment_ID()) ? 'upvoted' : ''); ?>" type="button" data-id="<?php comment_ID(); ?>">
					<span class="btn-inner--icon"><i class="fa fa-caret-up"></i></span>
					<span class="btn-inner--text">
						<span class="comment-upvote-num"><?php echo format_number_in_kilos(get_comment_upvotes(get_comment_ID())); ?></span>
					</span>
				</button>
			<?php } ?>
		</div>
		<div class="comment-item-inner" id="comment-inner-<?php comment_ID();?>">
			<div class="comment-item-title">
				<div class="comment-name">
					<div class="comment-author"><?php echo get_comment_author_link();?></div>
					<?php echo argon_get_comment_identity_badges(get_comment_ID()); ?>
					<?php if (user_can($comment -> user_id , "update_core")){
						echo '<span class="badge badge-primary badge-admin">' . __('博主', 'argon') . '</span>';}
					?>
					<?php echo get_comment_parent_info($comment); ?>
					<?php if ($GLOBALS['argon_comment_options']['enable_pinning'] && get_comment_meta(get_comment_ID(), "pinned", true) == "true"){
						echo '<span class="badge badge-danger badge-pinned"><i class="fa fa-thumb-tack" aria-hidden="true"></i> ' . _x('置顶', 'pinned', 'argon') . '</span>';
					}?>
					<?php if (is_comment_private_mode(get_comment_ID()) && user_can_view_comment(get_comment_ID())){
						echo '<span class="badge badge-success badge-private-comment">' . __('悄悄话', 'argon') . '</span>';}
					?>
					<?php if ($comment -> comment_approved == 0){
						echo '<span class="badge badge-warning badge-unapproved">' . __('待审核', 'argon') . '</span>';}
					?>
					<?php
						echo parse_ua_and_icon($comment -> comment_agent);
					?>
				</div>
				<div class="comment-info">
					<?php if (get_comment_meta(get_comment_ID(), "edited", true) == "true") { ?>
						<div class="comment-edited<?php if (can_visit_comment_edit_history(get_comment_ID())){echo ' comment-edithistory-accessible';}?>">
							<i class="fa fa-pencil" aria-hidden="true"></i><?php _e('已编辑', 'argon')?>
						</div>
					<?php } ?>
					<div class="comment-time">
						<span class="human-time" data-time="<?php echo get_comment_time('U', true);?>"><?php echo human_time_diff(get_comment_time('U') , current_time('timestamp')) . __("前", "argon");?></span>
						<div class="comment-time-details"><?php echo get_comment_time('Y-n-d G:i:s');?></div>
					</div>
				</div>
			</div>
			<div class="comment-item-text">
				<?php echo argon_get_comment_text();?>
			</div>
			<div class="comment-item-source" style="display: none;" aria-hidden="true"><?php echo htmlspecialchars(get_comment_meta(get_comment_ID(), "comment_content_source", true));?></div>

			<div class="comment-operations">
				<?php if ($GLOBALS['argon_comment_options']['enable_pinning'] && $GLOBALS['argon_comment_options']['current_user_can_moderate_comments'] && is_comment_pinable(get_comment_ID())) {
					if (get_comment_meta(get_comment_ID(), "pinned", true) == "true") { ?>
						<button class="comment-unpin btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button" style="margin-right: 2px;"><?php _e('取消置顶', 'argon')?></button>
					<?php } else { ?>
						<button class="comment-pin btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button" style="margin-right: 2px;"><?php _ex('置顶', 'to pin', 'argon')?></button>
				<?php }
					} ?>
				<?php if ((check_comment_token(get_comment_ID()) || check_login_user_same($comment -> user_id)) && (get_option("argon_comment_allow_editing") != "false")) { ?>
					<button class="comment-edit btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button" style="margin-right: 2px;"><?php _e('编辑', 'argon')?></button>
				<?php } ?>
				<button class="comment-reply btn btn-sm btn-outline-primary" data-id="<?php comment_ID(); ?>" type="button"><?php _e('回复', 'argon')?></button>
			</div>
		</div>
	</li>
	<li class="comment-divider"></li>
	<li>
<?php }}
//评论样式格式化 (说说预览界面)
function argon_comment_shuoshuo_preview_format($comment, $args, $depth){
	$GLOBALS['comment'] = $comment;?>
	<li class="comment-item" id="comment-<?php comment_ID(); ?>">
		<div class="comment-item-inner " id="comment-inner-<?php comment_ID();?>">
			<span class="shuoshuo-comment-item-title">
				<?php echo get_comment_author_link();?>
				<?php if( user_can($comment -> user_id , "update_core") ){
					echo '<span class="badge badge-primary badge-admin">' . __('博主', 'argon') . '</span>';}
				?>
				<?php if( $comment -> comment_approved == 0 ){
					echo '<span class="badge badge-warning badge-unapproved">' . __('待审核', 'argon') . '</span>';}
				?>
				:
			</span>
			<span class="shuoshuo-comment-item-text">
				<?php echo strip_tags(get_comment_text());?>
			</span>
		</div>
	</li>
	<li>
<?php }
function comment_author_link_filter($html){
	return str_replace('href=', 'target="_blank" href=', $html);
}
add_filter('get_comment_author_link', 'comment_author_link_filter');
//评论验证码生成 & 验证
function get_comment_captcha_seed($refresh = false){
	if (isset($_SESSION['captchaSeed']) && !$refresh){
		$res = $_SESSION['captchaSeed'];
		if (empty($_POST)){
			session_write_close();
		}
		return $res;
	}
	$captchaSeed = rand(0 , 500000000);
	$_SESSION['captchaSeed'] = $captchaSeed;
	session_write_close();
	return $captchaSeed;
}
get_comment_captcha_seed();
class captcha_calculation{ //数字验证码
	var $captchaSeed;
	function __construct($seed) {
		$this -> captchaSeed = $seed;
	}
	function getChallenge(){
		mt_srand($this -> captchaSeed + 10007);
		$oper = mt_rand(1 , 4);
		$num1 = 0;
		$num2 = 0;
		switch ($oper){
			case 1:
				$num1 = mt_rand(1 , 20);
				$num2 = mt_rand(0 , 20 - $num1);
				return $num1 . " + " . $num2 . " = ";
				break;
			case 2:
				$num1 = mt_rand(10 , 20);
				$num2 = mt_rand(1 , $num1);
				return $num1 . " - " . $num2 . " = ";
				break;
			case 3:
				$num1 = mt_rand(3 , 9);
				$num2 = mt_rand(3 , 9);
				return $num1 . " * " . $num2 . " = ";
				break;
			case 4:
				$num2 = mt_rand(2 , 9);
				$num1 = $num2 * mt_rand(2 , 9);
				return $num1 . " / " . $num2 . " = ";
				break;
			default:
				break;
		}
	}
	function getAnswer(){
		mt_srand($this -> captchaSeed + 10007);
		$oper = mt_rand(1 , 4);
		$num1 = 0;
		$num2 = 0;
		switch ($oper){
			case 1:
				$num1 = mt_rand(1 , 20);
				$num2 = mt_rand(0 , 20 - $num1);
				return $num1 + $num2;
				break;
			case 2:
				$num1 = mt_rand(10 , 20);
				$num2 = mt_rand(1 , $num1);
				return $num1 - $num2;
				break;
			case 3:
				$num1 = mt_rand(3 , 9);
				$num2 = mt_rand(3 , 9);
				return $num1 * $num2;
				break;
			case 4:
				$num2 = mt_rand(2 , 9);
				$num1 = $num2 * mt_rand(2 , 9);
				return $num1 / $num2;
				break;
			default:
				break;
		}
		return "";
	}
	function check($answer){
		if ($answer == self::getAnswer()){
			return true;
		}
		return false;
	}
}
function wrong_captcha(){
	exit(json_encode(array(
		'status' => 'failed',
		'msg' => __('验证码错误', 'argon'),
		'isAdmin' => current_user_can('level_7')
	)));
	//wp_die('验证码错误，评论失败');
}
function get_comment_captcha(){
	$captcha = new captcha_calculation(get_comment_captcha_seed());
	return $captcha -> getChallenge();
}
function get_comment_captcha_answer(){
	$captcha = new captcha_calculation(get_comment_captcha_seed());
	return $captcha -> getAnswer();
}
function check_comment_captcha($comment){
	if (get_option('argon_comment_need_captcha') == 'false'){
		return $comment;
	}
	$answer = $_POST['comment_captcha'];
	if(current_user_can('level_7')){
		return $comment;
	}
	$captcha = new captcha_calculation(get_comment_captcha_seed());
	if (!($captcha -> check($answer))){
		wrong_captcha();
	}
	return $comment;
}
add_filter('preprocess_comment' , 'check_comment_captcha');

function ajax_get_captcha(){
	if (get_option('argon_get_captcha_by_ajax', 'false') != 'true') {
		return;
	}
	exit(json_encode(array(
		'captcha' => get_comment_captcha(get_comment_captcha_seed())
	)));
}
add_action('wp_ajax_get_captcha', 'ajax_get_captcha');
add_action('wp_ajax_nopriv_get_captcha', 'ajax_get_captcha');
//Ajax 发送评论
function ajax_post_comment(){
	$parentID = $_POST['comment_parent'];
	if (is_comment_private_mode($parentID)){
		if (!user_can_view_comment($parentID)){
			//如果父级评论是悄悄话模式且当前 Token 与父级不相同则返回
			exit(json_encode(array(
				'status' => 'failed',
				'msg' =>  __('不能回复其他人的悄悄话评论', 'argon'),
				'isAdmin' => current_user_can('level_7')
			)));
		}
	}
	$oauth_identity = function_exists('argon_get_comment_oauth_identity') ? argon_get_comment_oauth_identity() : false;
	if (!$oauth_identity && !is_user_logged_in()){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' =>  '请先使用 GitHub 或 UR互联（微信）登录后再评论',
			'isAdmin' => current_user_can('level_7')
		)));
	}
	$identity_type = "";
	$wechat_id = "";
	$github_id = "";
	$clogin_id = "";
	if ($oauth_identity){
		$identity_type = $oauth_identity['provider'] == "clogin" ? "clogin" : "github";
		$github_id = $identity_type == "github" ? argon_sanitize_comment_github_id($oauth_identity['id']) : "";
		$clogin_id = $identity_type == "clogin" ? sanitize_text_field($oauth_identity['id']) : "";
		$_POST['identity_type'] = $identity_type;
		$_POST['wechat_id'] = "";
		$_POST['github_id'] = $github_id;
		$_POST['clogin_id'] = $clogin_id;
		$_POST['clogin_type'] = $identity_type == "clogin" && !empty($oauth_identity['type']) ? sanitize_key($oauth_identity['type']) : "";
		$_POST['oauth_avatar'] = !empty($oauth_identity['avatar']) ? esc_url_raw($oauth_identity['avatar']) : "";
		$_POST['author'] = sanitize_text_field($oauth_identity['name']);
		$_POST['url'] = !empty($oauth_identity['url']) ? esc_url_raw($oauth_identity['url']) : "";
	}
	if ($github_id != ""){
		$_POST['author'] = $github_id;
	}
	if (empty($_POST['email']) && ($github_id != "" || $clogin_id != "")){
		$_POST['email'] = $github_id != "" ? "github-" . $github_id . "@comments.local" : "clogin-" . md5($clogin_id) . "@comments.local";
		$_POST['enable_mailnotice'] = "false";
	}
	if (get_option('argon_comment_enable_qq_avatar') == 'true'){
		if (check_qqnumber($_POST['email'])){
			$_POST['qq'] = $_POST['email'];
			$_POST['email'] .= "@qq.com";
		}else{
			$_POST['qq'] = "";
		}
	}
	$comment = wp_handle_comment_submission(wp_unslash($_POST));
	if (is_wp_error($comment)){
		$msg = $comment -> get_error_data();
		if (!empty($msg)){
			$msg = $comment -> get_error_message();
		}
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => $msg,
			'isAdmin' => current_user_can('level_7')
		)));
	}
	$user = wp_get_current_user();
	do_action('set_comment_cookies', $comment, $user);
	if (isset($_POST['qq'])){
		if (!empty($_POST['qq']) && get_option('argon_comment_enable_qq_avatar') == 'true'){
			$_comment = $comment;
			$_comment -> comment_author_email = $_POST['qq'] . "@avatarqq.com";
			do_action('set_comment_cookies', $_comment, $user);
		}
	}
	$html = wp_list_comments(
		array(
			'type'      => 'comment',
			'callback'  => 'argon_comment_format',
			'echo'      => false
		),
		array($comment)
	);
	$newCaptchaSeed = get_comment_captcha_seed(true);
	$newCaptcha = get_comment_captcha($newCaptchaSeed);
	if (current_user_can('level_7')){
		$newCaptchaAnswer = get_comment_captcha_answer($newCaptchaSeed);
	}else{
		$newCaptchaAnswer = "";
	}
	exit(json_encode(array(
		'status' => 'success',
		'html' => $html,
		'id' => $comment -> comment_ID,
		'parentID' => $comment -> comment_parent,
		'commentOrder' => (get_option("comment_order") == "" ? "desc" : get_option("comment_order")),
		'newCaptchaSeed' => $newCaptchaSeed,
		'newCaptcha' => $newCaptcha,
		'newCaptchaAnswer' => $newCaptchaAnswer,
		'isAdmin' => current_user_can('level_7'),
		'isLogin' => is_user_logged_in()
	)));
}
add_action('wp_ajax_ajax_post_comment', 'ajax_post_comment');
add_action('wp_ajax_nopriv_ajax_post_comment', 'ajax_post_comment');
//评论 Markdown 解析
require_once(get_template_directory() . '/parsedown.php');
function comment_markdown_parse($comment_content){
	//HTML 过滤
	global $allowedtags;
	//$comment_content = wp_kses($comment_content, $allowedtags);
	//允许评论中额外的 HTML Tag
	$allowedtags['pre'] = array('class' => array());
	$allowedtags['i'] = array('class' => array(), 'aria-hidden' => array());
	$allowedtags['img'] = array('src' => array(), 'alt' => array(), 'class' => array());
	$allowedtags['ol'] = array();
	$allowedtags['ul'] = array();
	$allowedtags['li'] = array();
	$allowedtags['a']['class'] = array();
	$allowedtags['a']['data-src'] = array();
	$allowedtags['a']['target'] = array();
	$allowedtags['h1'] = $allowedtags['h2'] = $allowedtags['h3'] = $allowedtags['h4'] = $allowedtags['h5'] = $allowedtags['h6'] = array();

	//解析 Markdown
	$parsedown = new _Parsedown();
	$res = $parsedown -> text($comment_content);
	/*$res = preg_replace(
		'/<code>([\s\S]*?)<\/code>/',
		'<pre>$1</pre>',
		$res
	);*/

	$res = preg_replace(
		'/<a (.*?)>(.*?)<\/a>/',
		'<a $1 target="_blank">$2</a>',
		$res
	);
	return $res;
}
//评论发送处理
function post_comment_preprocessing($comment){
	//保存评论未经 Markdown 解析的源码
	$_POST['comment_content_source'] = $comment['comment_content'];
	//Markdown
	if ($_POST['use_markdown'] == 'true' && get_option("argon_comment_allow_markdown") != "false"){
		$comment['comment_content'] = comment_markdown_parse($comment['comment_content']);
	}
	return $comment;
}
add_filter('preprocess_comment' , 'post_comment_preprocessing');
//发送评论通知邮件
function comment_mail_notify($comment){
	if (get_option("argon_comment_allow_mailnotice") != "true"){
		return;
	}
	if ($comment == null){
		return;
	}
	$id = $comment -> comment_ID;
	$commentPostID = $comment -> comment_post_ID;
	$commentAuthor = $comment -> comment_author;
	$parentID = $comment -> comment_parent;
	if ($parentID == 0){
		return;
	}
	$parentComment = get_comment($parentID);
	$parentEmail =  $parentComment -> comment_author_email;
	$parentName = $parentComment -> comment_author;
	$emailTo = "$parentName <$parentEmail>";
	if (get_comment_meta($parentID, "enable_mailnotice", true) == "true"){
		if (check_email_address($parentEmail)){
			$title = __("您在", 'argon') . " 「" . wp_trim_words(get_post_title_by_id($commentPostID), 20) . "」 " . __("的评论有了新的回复", 'argon');
			$fullTitle = __("您在", 'argon') . " 「" . get_post_title_by_id($commentPostID) . "」 " . __("的评论有了新的回复", 'argon');
			$content = htmlspecialchars(get_comment_meta($id, "comment_content_source", true));
			$link = get_permalink($commentPostID) . "#comment-" . $id;
			$unsubscribeLink = site_url("unsubscribe-comment-mailnotice?comment=" . $parentID . "&token=" . get_comment_meta($parentID, "mailnotice_unsubscribe_key", true));
			$html = '
					<!DOCTYPE html>
					<html>
						<head>
							<meta http-equiv="Content-Type" content="text/html charset=UTF-8" />
						</head>
						<body>
							<div style="background: #fff;box-shadow: 0 15px 35px rgba(50,50,93,.1), 0 5px 15px rgba(0,0,0,.07);border-radius: 6px;margin: 15px auto 50px auto;padding: 35px 30px;max-width: min(calc(100% - 100px), 1200px);">
								<div style="font-size:30px;text-align:center;margin-bottom:15px;">' . htmlspecialchars($fullTitle)  .'</div>
								<div style="background: rgba(0, 0, 0, .15);height: 1px;width: 300px;margin: auto;margin-bottom: 35px;"></div>
								<div style="font-size: 18px;border-left: 4px solid rgba(0, 0, 0, .15);width: max-content;width: -moz-max-content;margin: auto;padding: 20px 30px;background: rgba(0,0,0,.08);border-radius: 6px;box-shadow: 0 2px 4px rgba(0,0,0,.075)!important;min-width: 60%;max-width: 90%;margin-bottom: 40px;">
									<div style="margin-bottom: 10px;"><strong><span style="color: #5e72e4;">@' . htmlspecialchars($commentAuthor) . '</span> ' . __('回复了你', "argon") . ':</strong></div>
									' . str_replace('\n', '<div></div>', $content) . ' 
								</div>
								<table width="100%" style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
									<tbody style="box-sizing:border-box">
										<tr style="box-sizing:border-box" align="center">
											<td style="min-width:5px;box-sizing:border-box">
												<table style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
													<tbody style="box-sizing:border-box">
														<tr style="box-sizing:border-box">
															<td style="box-sizing:border-box">
																<a href="' . $link . '" style="display: block; line-height: 1; color: #fff;background-color: #5e72e4;border-color: #5e72e4;box-shadow: 0 4px 6px rgba(50,50,93,.11), 0 1px 3px rgba(0,0,0,.08);padding: 15px 25px;font-size: 18px;border-radius: 4px;text-decoration: none; margin: 10px;">' . __('前往查看', "argon") . '</a>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
								<table width="100%" style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
									<tbody style="box-sizing:border-box">
										<tr style="box-sizing:border-box" align="center">
											<td style="min-width:5px;box-sizing:border-box">
												<table style="border-collapse:collapse;border:none;empty-cells:show;max-width:100%;box-sizing:border-box" cellspacing="0" cellpadding="0">
													<tbody style="box-sizing:border-box">
														<tr style="box-sizing:border-box">
															<td style="box-sizing:border-box">
																<a href="' . $unsubscribeLink . '" style="display: block; line-height: 1;color: #5e72e4;font-size: 16px;text-decoration: none; margin: 10px;">' . __('退订该评论的邮件提醒', "argon") . '</a>
															</td>
														</tr>
													</tbody>
												</table>
											</td>
										</tr>
									</tbody>
								</table>
							</div>
						</body>
					</html>';
			$html = apply_filters("argon_comment_mail_notification_content", $html); 
			send_mail($emailTo, $title, $html);
		}
	}
}
//评论发送完成添加 Meta
function post_comment_updatemetas($id){
	$parentID = $_POST['comment_parent'];
	$comment = get_comment($id);
	$commentPostID = $comment -> comment_post_ID;
	$commentAuthor = $comment -> comment_author;
	$mailnoticeUnsubscribeKey = get_random_token();
	//评论 Markdown 源码
	update_comment_meta($id, "comment_content_source", $_POST['comment_content_source']);
	//评论者 Token
	set_user_token_cookie();
	update_comment_meta($id, "user_token", $_COOKIE["argon_user_token"]);
	//保存初次编辑记录
	$editHistory = array(array(
		'content' => $_POST['comment_content_source'],
		'time' => time(),
		'isfirst' => true
	));
	update_comment_meta($id, "comment_edit_history", addslashes(json_encode($editHistory, JSON_UNESCAPED_UNICODE)));
	//是否启用 Markdown
	if ($_POST['use_markdown'] == 'true' && get_option("argon_comment_allow_markdown") != "false"){
		update_comment_meta($id, "use_markdown", "true");
	}else{
		update_comment_meta($id, "use_markdown", "false");
	}
	//是否启用悄悄话模式
	if ($_POST['private_mode'] == 'true' && get_option("argon_comment_allow_privatemode") == "true"){
		update_comment_meta($id, "private_mode", $_COOKIE["argon_user_token"]);
	}else{
		update_comment_meta($id, "private_mode", "false");
	}
	if (is_comment_private_mode($parentID)){
		//如果父级评论是悄悄话模式则将当前评论可查看者的 Token 跟随父级评论者的 Token
		update_comment_meta($id, "private_mode", get_comment_meta($parentID, "private_mode", true));
	}
	if ($parentID!= 0 && !is_comment_private_mode($parentID)){
		//如果父级评论不是悄悄话模式则当前评论也不是悄悄话模式
		update_comment_meta($id, "private_mode", "false");
	}
	//是否启用邮件通知
	if ($_POST['enable_mailnotice'] == 'true' && get_option("argon_comment_allow_mailnotice") == "true"){
		update_comment_meta($id, "enable_mailnotice", "true");
		update_comment_meta($id, "mailnotice_unsubscribe_key", $mailnoticeUnsubscribeKey);
	}else{
		update_comment_meta($id, "enable_mailnotice", "false");
	}
	//向父级评论发送邮件
	if ($comment -> comment_approved == 1){
		comment_mail_notify($comment);
	}
	//保存 QQ 号
	if (get_option('argon_comment_enable_qq_avatar') == 'true'){
		if (!empty($_POST['qq'])){
			update_comment_meta($id, "qq_number", $_POST['qq']);
		}
	}
	//保存访客 GitHub 账号
	$github_id = isset($_POST['github_id']) ? argon_sanitize_comment_github_id(wp_unslash($_POST['github_id'])) : "";
	if ($github_id != ""){
		update_comment_meta($id, "github_id", $github_id);
		if (!empty($_POST['oauth_avatar'])){
			update_comment_meta($id, "oauth_avatar", esc_url_raw(wp_unslash($_POST['oauth_avatar'])));
		}else{
			delete_comment_meta($id, "oauth_avatar");
		}
		delete_comment_meta($id, "wechat_id");
		delete_comment_meta($id, "clogin_id");
		delete_comment_meta($id, "clogin_type");
	}
	$clogin_id = isset($_POST['clogin_id']) ? sanitize_text_field(wp_unslash($_POST['clogin_id'])) : "";
	if ($clogin_id != ""){
		update_comment_meta($id, "clogin_id", $clogin_id);
		update_comment_meta($id, "clogin_type", isset($_POST['clogin_type']) ? sanitize_key(wp_unslash($_POST['clogin_type'])) : "");
		if (!empty($_POST['oauth_avatar'])){
			update_comment_meta($id, "oauth_avatar", esc_url_raw(wp_unslash($_POST['oauth_avatar'])));
		}else{
			delete_comment_meta($id, "oauth_avatar");
		}
		delete_comment_meta($id, "github_id");
		delete_comment_meta($id, "wechat_id");
	}
}
add_action('comment_post' , 'post_comment_updatemetas');
add_action('comment_unapproved_to_approved', 'comment_mail_notify');
add_rewrite_rule('^unsubscribe-comment-mailnotice/?(.*)$', '/wp-content/themes/argon/unsubscribe-comment-mailnotice.php$1', 'top');
//编辑评论
function user_edit_comment(){
	header('Content-Type:application/json; charset=utf-8');
	if (get_option("argon_comment_allow_editing") == "false"){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('博主关闭了编辑评论功能', 'argon')
		)));
	}
	$id = $_POST["id"];
	$content = $_POST["comment"];
	$contentSource = $content;
	if (!check_comment_token($id) && !check_login_user_same(get_comment_user_id_by_id($id))){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('您不是这条评论的作者或 Token 已过期', 'argon')
		)));
	}
	if ($_POST["comment"] == ""){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('新的评论为空', 'argon')
		)));
	}
	if (get_comment_meta($id, "use_markdown", true) == "true"){
		$content = comment_markdown_parse($content);
	}
	$res = wp_update_comment(array(
		'comment_ID' => $id,
		'comment_content' => $content
	));
	if ($res == 1){
		update_comment_meta($id, "comment_content_source", $contentSource);
		update_comment_meta($id, "edited", "true");
		//保存编辑历史
		$editHistory = json_decode(get_comment_meta($id, "comment_edit_history", true));
		if (is_null($editHistory)){
			$editHistory = array();
		}
		array_push($editHistory, array(
			'content' => htmlspecialchars(stripslashes($contentSource)),
			'time' => time(),
			'isfirst' => false
		));
		update_comment_meta($id, "comment_edit_history", addslashes(json_encode($editHistory, JSON_UNESCAPED_UNICODE)));
		exit(json_encode(array(
			'status' => 'success',
			'msg' => __('编辑评论成功', 'argon'),
			'new_comment' => apply_filters('comment_text', argon_get_comment_text($id), $id),
			'new_comment_source' => htmlspecialchars(stripslashes($contentSource)),
			'can_visit_edit_history' => can_visit_comment_edit_history($id)
		)));
	}else{
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('编辑评论失败，可能原因: 与原评论相同', 'argon'),
		)));
	}
}
add_action('wp_ajax_user_edit_comment', 'user_edit_comment');
add_action('wp_ajax_nopriv_user_edit_comment', 'user_edit_comment');
//置顶评论
function pin_comment(){
	header('Content-Type:application/json; charset=utf-8');
	if (get_option("argon_enable_comment_pinning") == "false"){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('博主关闭了评论置顶功能', 'argon')
		)));
	}
	if (!current_user_can("moderate_comments")){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('您没有权限进行此操作', 'argon')
		)));
	}
	$id = $_POST["id"];
	$newPinnedStat = $_POST["pinned"] == "true";
	$origPinnedStat = get_comment_meta($id, "pinned", true) == "true";
	if ($newPinnedStat == $origPinnedStat){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => $newPinnedStat ? __('评论已经是置顶状态', 'argon') : __('评论已经是取消置顶状态', 'argon')
		)));
	}
	if (get_comment($id) -> comment_parent != 0){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('不能置顶子评论', 'argon')
		)));
	}
	if (is_comment_private_mode($id)){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('不能置顶悄悄话', 'argon')
		)));
	}
	update_comment_meta($id, "pinned", $newPinnedStat ? "true" : "false");
	exit(json_encode(array(
		'status' => 'success',
		'msg' => $newPinnedStat ? __('置顶评论成功', 'argon') : __('取消置顶成功', 'argon'),
	)));
}
add_action('wp_ajax_pin_comment', 'pin_comment');
add_action('wp_ajax_nopriv_pin_comment', 'pin_comment');
//输出评论分页页码
function get_argon_formatted_comment_paginate_links($maxPageNumbers, $extraClasses = ''){
	$args = array(
		'prev_text' => '',
		'next_text' => '',
		'before_page_number' => '',
		'after_page_number' => '',
		'show_all' => True,
		'echo' => False
	);
	$res = paginate_comments_links($args);
	//单引号转双引号 & 去除上一页和下一页按钮
	$res = preg_replace(
		'/\'/',
		'"',
		$res
	);
	$res = preg_replace(
		'/<a class="prev page-numbers" href="(.*?)">(.*?)<\/a>/',
		'',
		$res
	);
	$res = preg_replace(
		'/<a class="next page-numbers" href="(.*?)">(.*?)<\/a>/',
		'',
		$res
	);
	//寻找所有页码标签
	preg_match_all('/<(.*?)>(.*?)<\/(.*?)>/' , $res , $pages);
	$total = count($pages[0]);
	$current = 0;
	$urls = array();
	for ($i = 0; $i < $total; $i++){
		if (preg_match('/<span(.*?)>(.*?)<\/span>/' , $pages[0][$i])){
			$current = $i + 1;
		}else{
			preg_match('/<a(.*?)href="(.*?)">(.*?)<\/a>/' , $pages[0][$i] , $tmp);
			$urls[$i + 1] = $tmp[2];
		}
	}

	if ($total == 0){
		return "";
	}

	//计算页码起始
	$from = max($current - ($maxPageNumbers - 1) / 2 , 1);
	$to = min($current + $maxPageNumbers - ( $current - $from + 1 ) , $total);
	if ($to - $from + 1 < $maxPageNumbers){
		$to = min($current + ($maxPageNumbers - 1) / 2 , $total);
		$from = max($current - ( $maxPageNumbers - ( $to - $current + 1 ) ) , 1);
	}
	//生成新页码
	$html = "";
	if ($from > 1){
		$html .= '<li class="page-item"><div aria-label="First Page" class="page-link" href="' . $urls[1] . '"><i class="fa fa-angle-double-left" aria-hidden="true"></i></div></li>';
	}
	if ($current > 1){
		$html .= '<li class="page-item"><div aria-label="Previous Page" class="page-link" href="' . $urls[$current - 1] . '"><i class="fa fa-angle-left" aria-hidden="true"></i></div></li>';
	}
	for ($i = $from; $i <= $to; $i++){
		if ($current == $i){
			$html .= '<li class="page-item active"><span class="page-link" style="cursor: default;">' . $i . '</span></li>';
		}else{
			$html .= '<li class="page-item"><div class="page-link" href="' . $urls[$i] . '">' . $i . '</div></li>';
		}
	}
	if ($current < $total){
		$html .= '<li class="page-item"><div aria-label="Next Page" class="page-link" href="' . $urls[$current + 1] . '"><i class="fa fa-angle-right" aria-hidden="true"></i></div></li>';
	}
	if ($to < $total){
		$html .= '<li class="page-item"><div aria-label="Last Page" class="page-link" href="' . $urls[$total] . '"><i class="fa fa-angle-double-right" aria-hidden="true"></i></div></li>';
	}
	return '<nav id="comments_navigation" class="comments-navigation"><ul class="pagination' . $extraClasses . '">' . $html . '</ul></nav>';
}
function get_argon_formatted_comment_paginate_links_for_all_platforms(){
	return get_argon_formatted_comment_paginate_links(7) . get_argon_formatted_comment_paginate_links(5, " pagination-mobile");
}
function get_argon_comment_paginate_links_prev_url(){
	$args = array(
		'prev_text' => '',
		'next_text' => '',
		'before_page_number' => '',
		'after_page_number' => '',
		'show_all' => True,
		'echo' => False
	);
	$str = paginate_comments_links($args);
	//单引号转双引号
	$str = preg_replace(
		'/\'/',
		'"',
		$str
	);
	//获取上一页地址
	$url = "";
	preg_match(
		'/<a class="prev page-numbers" href="(.*?)">(.*?)<\/a>/',
		$str,
		$url
	);
	if (!isset($url[1])){
		return NULL;
	}
	
	if (isset($_GET['fill_first_page']) || strpos(parse_url($_SERVER['REQUEST_URI'])['path'], 'comment-page-') === false){
		$parsed_url = parse_url($url[1]);
		if (!isset($parsed_url['query'])){
			$parsed_url['query'] = 'fill_first_page=true';
		}else
			if (strpos($parsed_url['query'], 'fill_first_page=true') === false){
			$parsed_url['query'] .= '&fill_first_page=true';
		}
		return $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'] . '?' . $parsed_url['query'];
	}
	return $url[1];
}
//评论重排序（置顶优先）
$GLOBALS['comment_order'] = get_option('comment_order');
function argon_comment_cmp($a, $b){
	$a_pinned = get_comment_meta($a -> comment_ID, 'pinned', true);
	$b_pinned = get_comment_meta($b -> comment_ID, 'pinned', true);
	if ($a_pinned != "true"){
		$a_pinned = "false";
	}
	if ($b_pinned != "true"){
		$b_pinned = "false";
	}
	if ($a_pinned == $b_pinned){
		return ($a -> comment_date_gmt) > ($b -> comment_date_gmt);
	}else{
		if ($a_pinned == "true"){
			return ($GLOBALS['comment_order'] == 'desc');
		}else{
			return ($GLOBALS['comment_order'] != 'desc');
		}
	}
}
function argon_get_comments(){
	global $wp_query;
	/*$cpage = get_query_var('cpage') ?? 1;
	$maxiumPages = $wp_query -> max_num_pages;*/
	$args = array(
		'post__in'		 => array(get_the_ID()),
		'type'           => 'comment',
		'order'          => 'DESC',
		'orderby'        => 'comment_date_gmt',
		'status'         => 'approve'
	);
	if (is_user_logged_in()){
		$args['include_unapproved'] = array(get_current_user_id());
	} else {
		$unapproved_email = wp_get_unapproved_comment_author_email();
		if ($unapproved_email) {
			$args['include_unapproved'] = array($unapproved_email);
		}
	}

	$comment_query = new WP_Comment_Query;
	$comments = $comment_query -> query($args);
	
	if (get_option("argon_enable_comment_pinning", "false") == "true"){
		usort($comments, "argon_comment_cmp");
	}else{
		$comments = array_reverse($comments);
	}
	
	//向评论数组中填充 placeholder comments 以填满第一页
	if (get_option("argon_comment_pagination_type", "feed") == "page"){
		return $comments;
	}
	if (!isset($_GET['fill_first_page']) && strpos(parse_url($_SERVER['REQUEST_URI'])['path'], 'comment-page-') !== false){
		return null;
	}
	$comments_per_page = get_option('comments_per_page');
	$comments_count = 0; 
	foreach ($comments as $comment){
		if ($comment -> comment_parent == 0){
			$comments_count++;
		}
	}
	$comments_pages = ceil($comments_count / $comments_per_page);
	if ($comments_pages > 1){
		$placeholders_count = $comments_pages * $comments_per_page - $comments_count;
		while ($placeholders_count--){
			array_unshift($comments, new WP_Comment((object) array(
				"placeholder" => true
			)));
		}
	}
	return $comments;
}
//QQ Avatar 获取
function get_avatar_by_qqnumber($avatar){
	global $comment;
	if (!isset($comment) || !isset($comment -> comment_ID)){
		return $avatar;
	}
	$qqnumber = get_comment_meta($comment -> comment_ID, 'qq_number', true);
	if (!empty($qqnumber)){
		preg_match_all('/width=\'(.*?)\'/', $avatar, $preg_res);
		$size = $preg_res[1][0];
		return "<img src='https://q1.qlogo.cn/g?b=qq&s=640&nk=" . $qqnumber ."' class='avatar avatar-" . $size . " photo' width='" . $size . "' height='" . $size . "'>";
	}
	return $avatar;
}
add_filter('get_avatar', 'get_avatar_by_qqnumber');
//判断 QQ 号合法性
if (!function_exists('check_qqnumber')){
	function check_qqnumber($qqnumber){
		if (preg_match("/^[1-9][0-9]{4,10}$/", $qqnumber)){
			return true;
		} else {
			return false;
		}
	}
}
//获取顶部 Banner 背景图（用户指定或必应日图）
function get_banner_background_url(){
	$url = get_option("argon_banner_background_url");
	if ($url == "--bing--"){
		$lastUpdated = get_option("argon_bing_banner_background_last_updated_time");
		if ($lastUpdated == ""){
			$lastUpdated = 0;
		}
		$now = time();
		if ($now - $lastUpdated < 3600){
			return get_option("argon_bing_banner_background_last_updated_url");
		}else{
			$data = json_decode(@file_get_contents('https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1') , true);
			$url = "//bing.com" . $data['images'][0]['url'];
			update_option("argon_bing_banner_background_last_updated_time" , $now);
			update_option("argon_bing_banner_background_last_updated_url" , $url);
			return $url;
		}
	}else{
		return $url;
	}
}
//Lazyload 对 <img> 标签预处理以加载 Lazyload
function argon_lazyload($content){
	$lazyload_loading_style = get_option('argon_lazyload_loading_style');
	if ($lazyload_loading_style == ''){
		$lazyload_loading_style = 'none';
	}
	$lazyload_loading_style = "lazyload-style-" . $lazyload_loading_style;

	if(!is_feed() && !is_robots() && !is_home()){
		$content = preg_replace('/<img(.*?)src=[\'"](.*?)[\'"](.*?)((\/>)|(<\/img>))/i',"<img class=\"lazyload " . $lazyload_loading_style . "\" src=\"data:image/svg+xml;base64,PCEtLUFyZ29uTG9hZGluZy0tPgo8c3ZnIHdpZHRoPSIxIiBoZWlnaHQ9IjEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgc3Ryb2tlPSIjZmZmZmZmMDAiPjxnPjwvZz4KPC9zdmc+\" \$1data-original=\"\$2\" src=\"data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAAAANSURBVBhXYzh8+PB/AAffA0nNPuCLAAAAAElFTkSuQmCC\"\$3$4" , $content);
		$content = preg_replace('/<img(.*?)data-full-url=[\'"]([^\'"]+)[\'"](.*)>/i',"<img$1data-full-url=\"$2\" data-original=\"$2\"$3>" , $content);
		$content = preg_replace('/<img(.*?)srcset=[\'"](.*?)[\'"](.*?)>/i',"<img$1$3>" , $content);
	}
	return $content;
}
function argon_fancybox($content){
	if(!is_feed() && !is_robots() && !is_home()){
		if (get_option('argon_enable_lazyload') != 'false'){
			$content = preg_replace('/<img(.*?)data-original=[\'"](.*?)[\'"](.*?)((\/>)|>|(<\/img>))/i',"<div class='fancybox-wrapper lazyload-container-unload' data-fancybox='post-images' href='$2'>$0</div>" , $content);
		}else{
			$content = preg_replace('/<img(.*?)src=[\'"](.*?)[\'"](.*?)((\/>)|>|(<\/img>))/i',"<div class='fancybox-wrapper' data-fancybox='post-images' href='$2'>$0</div>" , $content);
		}
	}
	return $content;
}
function the_content_filter($content){
	if (get_option('argon_enable_lazyload') != 'false'){
		$content = argon_lazyload($content);
	}
	if (get_option('argon_enable_fancybox') != 'false' && get_option('argon_enable_zoomify') == 'false'){
		$content = argon_fancybox($content);
	}
	global $post;
	$custom_css = get_post_meta($post -> ID, 'argon_custom_css', true);
	if (!empty($custom_css)){
		$content .= "<style>" . $custom_css . "</style>";
	}

	return $content;
}
add_filter('the_content' , 'the_content_filter',20);
//使用 CDN 加速 gravatar
function gravatar_cdn($url){
	$cdn = get_option('argon_gravatar_cdn', 'gravatar.pho.ink/avatar/');
	$cdn = str_replace("http://", "", $cdn);
	$cdn = str_replace("https://", "", $cdn);
	if (substr($cdn, -1) != '/'){
		$cdn .= "/";
	}
	$url = preg_replace("/\/\/(.*?).gravatar.com\/avatar\//", "//" . $cdn, $url);
	return $url;
}
if (get_option('argon_gravatar_cdn' , '') != ''){
	add_filter('get_avatar_url', 'gravatar_cdn');
}
function text_gravatar($url){
	$url = preg_replace("/[?&]d[^&]+/i", "" , $url);
	$url .= '&d=404';
	return $url;
}
if (get_option('argon_text_gravatar', 'false') == 'true' && !is_admin()){
	add_filter('get_avatar_url', 'text_gravatar');
}
//说说点赞
function get_shuoshuo_upvotes($ID){
	$count_key = 'upvotes';
	$count = get_post_meta($ID, $count_key, true);
	if ($count==''){
		delete_post_meta($ID, $count_key);
		add_post_meta($ID, $count_key, '0');
		$count = '0';
	}
	return number_format_i18n($count);
}
function set_shuoshuo_upvotes($ID){
	if (get_post_type($ID) != 'shuoshuo'){
		return;
	}
	$count_key = 'upvotes';
	$count = get_post_meta($ID, $count_key, true);
	if ($count==''){
		delete_post_meta($ID, $count_key);
		add_post_meta($ID, $count_key, '1');
	} else {
		update_post_meta($ID, $count_key, $count + 1);
	}
}
function upvote_shuoshuo(){
	header('Content-Type:application/json; charset=utf-8');
	$ID = $_POST["shuoshuo_id"];
	$upvotedList = isset( $_COOKIE['argon_shuoshuo_upvoted'] ) ? $_COOKIE['argon_shuoshuo_upvoted'] : '';
	if (in_array($ID, explode(',', $upvotedList))){
		exit(json_encode(array(
			'status' => 'failed',
			'msg' => __('该说说已被赞过', 'argon'),
			'total_upvote' => get_shuoshuo_upvotes($ID)
		)));
	}
	set_shuoshuo_upvotes($ID);
	setcookie('argon_shuoshuo_upvoted', $upvotedList . $ID . "," , time() + 3153600000 , '/');
	exit(json_encode(array(
		'ID' => $ID,
		'status' => 'success',
		'msg' => __('点赞成功', 'argon'),
		'total_upvote' => get_shuoshuo_upvotes($ID)
	)));
}
add_action('wp_ajax_upvote_shuoshuo' , 'upvote_shuoshuo');
add_action('wp_ajax_nopriv_upvote_shuoshuo' , 'upvote_shuoshuo');
//检测页面底部版权是否被修改
function alert_footer_copyright_changed(){ ?>
	<div class='notice notice-warning is-dismissible'>
		<p><?php _e("警告：你可能修改了 Argon 主题页脚的版权声明，Argon 主题要求你至少保留主题的 Github 链接或主题的发布文章链接。", 'argon');?></p>
	</div>
<?php }
function check_footer_copyright(){
	$footer = file_get_contents(get_theme_root() . "/" . wp_get_theme() -> template . "/footer.php");
	if ((strpos($footer, "github.com/solstice23/argon-theme") === false) && (strpos($footer, "solstice23.top") === false)){
		add_action('admin_notices', 'alert_footer_copyright_changed');
	}
}
check_footer_copyright();
//颜色计算
function rgb2hsl($R,$G,$B){
	$r = $R / 255;
	$g = $G / 255;
	$b = $B / 255;

	$var_Min = min($r, $g, $b);
	$var_Max = max($r, $g, $b);
	$del_Max = $var_Max - $var_Min;

	$L = ($var_Max + $var_Min) / 2;

	if ($del_Max == 0){
		$H = 0;
		$S = 0;
	}else{
		if ($L < 0.5){
			$S = $del_Max / ($var_Max + $var_Min);
		}else{
			$S = $del_Max / (2 - $var_Max - $var_Min);
		}

		$del_R = ((($var_Max - $r) / 6) + ($del_Max / 2)) / $del_Max;
		$del_G = ((($var_Max - $g) / 6) + ($del_Max / 2)) / $del_Max;
		$del_B = ((($var_Max - $b) / 6) + ($del_Max / 2)) / $del_Max;

		if ($r == $var_Max){
			$H = $del_B - $del_G;
		}
		else if ($g == $var_Max){
			$H = (1 / 3) + $del_R - $del_B;
		}
		else if ($b == $var_Max){
			$H = (2 / 3) + $del_G - $del_R;
		}
		if ($H < 0) $H += 1;
		if ($H > 1) $H -= 1;
	}
	return array(
		'h' => $H,//0~1
		's' => $S,
		'l' => $L,
		'H' => round($H * 360),//0~360
		'S' => round($S * 100),//0~100
		'L' => round($L * 100),//0~100
	);
}
function Hue_2_RGB($v1,$v2,$vH){
	if ($vH < 0) $vH += 1;
	if ($vH > 1) $vH -= 1;
	if ((6 * $vH) < 1) return ($v1 + ($v2 - $v1) * 6 * $vH);
	if ((2 * $vH) < 1) return $v2;
	if ((3 * $vH) < 2) return ($v1 + ($v2 - $v1) * ((2 / 3) - $vH) * 6);
	return $v1;
}
function hsl2rgb($h,$s,$l){
	if ($s == 0){
		$r = $l;
		$g = $l;
		$b = $l;
	}
	else{
		if ($l < 0.5){
			$var_2 = $l * (1 + $s);
		}
		else{
			$var_2 = ($l + $s) - ($s * $l);
		}
		$var_1 = 2 * $l - $var_2;
		$r = Hue_2_RGB($var_1, $var_2, $h + (1 / 3));
		$g = Hue_2_RGB($var_1, $var_2, $h);
		$b = Hue_2_RGB($var_1, $var_2, $h - (1 / 3));
	}
	return array(
		'R' => round($r * 255),//0~255
		'G' => round($g * 255),
		'B' => round($b * 255),
		'r' => $r,//0~1
		'g' => $g,
		'b' => $b
	);
}
function rgb2hex($r,$g,$b){
	$hex = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'A', 'B', 'C', 'D', 'E', 'F');
	$rh = "";
	$gh = "";
	$bh = "";
	while (strlen($rh) < 2){
		$rh = $hex[$r%16] . $rh;
		$r = floor($r / 16);
	}
	while (strlen($gh) < 2){
		$gh = $hex[$g%16] . $gh;
		$g = floor($g / 16);
	}
	while (strlen($bh) < 2){
		$bh = $hex[$b%16] . $bh;
		$b = floor($b / 16);
	}
	return "#".$rh.$gh.$bh;
}
function hexstr2rgb($hex){
	//$hex: #XXXXXX
	return array(
		'R' => hexdec(substr($hex,1,2)),//0~255
		'G' => hexdec(substr($hex,3,2)),
		'B' => hexdec(substr($hex,5,2)),
		'r' => hexdec(substr($hex,1,2)) / 255,//0~1
		'g' => hexdec(substr($hex,3,2)) / 255,
		'b' => hexdec(substr($hex,5,2)) / 255
	);
}
function rgb2str($rgb){
	return $rgb['R']. "," .$rgb['G']. "," .$rgb['B'];
}
function hex2str($hex){
	return rgb2str(hexstr2rgb($hex));
}
function rgb2gray($R,$G,$B){
	return round($R * 0.299 + $G * 0.587 + $B * 0.114);
}
function hex2gray($hex){
	$rgb_array = hexstr2rgb($hex);
	return rgb2gray($rgb_array['R'], $rgb_array['G'], $rgb_array['B']);
}
function checkHEX($hex){
	if (strlen($hex) != 7){
		return False;
	}
	if (substr($hex,0,1) != "#"){
		return False;
	}
	return True;
}
//编辑文章界面新增 Meta 编辑模块
function argon_meta_box_1(){
	wp_nonce_field("argon_meta_box_nonce_action", "argon_meta_box_nonce");
	global $post;
	?>
		<h4><?php _e("显示字数和预计阅读时间", 'argon');?></h4>
		<?php $argon_meta_hide_readingtime = get_post_meta($post->ID, "argon_hide_readingtime", true);?>
		<select name="argon_meta_hide_readingtime" id="argon_meta_hide_readingtime">
			<option value="false" <?php if ($argon_meta_hide_readingtime=='false'){echo 'selected';} ?>><?php _e("跟随全局设置", 'argon');?></option>
			<option value="true" <?php if ($argon_meta_hide_readingtime=='true'){echo 'selected';} ?>><?php _e("不显示", 'argon');?></option>
		</select>
		<p style="margin-top: 15px;"><?php _e("是否显示字数和预计阅读时间 Meta 信息", 'argon');?></p>
		<h4><?php _e("Meta 中隐藏发布时间和分类", 'argon');?></h4>
		<?php $argon_meta_simple = get_post_meta($post->ID, "argon_meta_simple", true);?>
		<select name="argon_meta_simple" id="argon_meta_simple">
			<option value="false" <?php if ($argon_meta_simple=='false'){echo 'selected';} ?>><?php _e("不隐藏", 'argon');?></option>
			<option value="true" <?php if ($argon_meta_simple=='true'){echo 'selected';} ?>><?php _e("隐藏", 'argon');?></option>
		</select>
		<p style="margin-top: 15px;"><?php _e("适合特定的页面，例如友链页面。开启后文章 Meta 的第一行只显示阅读数和评论数。", 'argon');?></p>
		<h4><?php _e("使用文章中第一张图作为头图", 'argon');?></h4>
		<?php $argon_first_image_as_thumbnail = get_post_meta($post->ID, "argon_first_image_as_thumbnail", true);?>
		<select name="argon_first_image_as_thumbnail" id="argon_first_image_as_thumbnail">
			<option value="default" <?php if ($argon_first_image_as_thumbnail=='default'){echo 'selected';} ?>><?php _e("跟随全局设置", 'argon');?></option>
			<option value="true" <?php if ($argon_first_image_as_thumbnail=='true'){echo 'selected';} ?>><?php _e("使用", 'argon');?></option>
			<option value="false" <?php if ($argon_first_image_as_thumbnail=='false'){echo 'selected';} ?>><?php _e("不使用", 'argon');?></option>
		</select>
		<h4><?php _e("显示文章过时信息", 'argon');?></h4>
		<?php $argon_show_post_outdated_info = get_post_meta($post->ID, "argon_show_post_outdated_info", true);?>
		<div style="display: flex;">
			<select name="argon_show_post_outdated_info" id="argon_show_post_outdated_info">
				<option value="default" <?php if ($argon_show_post_outdated_info=='default'){echo 'selected';} ?>><?php _e("跟随全局设置", 'argon');?></option>
				<option value="always" <?php if ($argon_show_post_outdated_info=='always'){echo 'selected';} ?>><?php _e("一直显示", 'argon');?></option>
				<option value="never" <?php if ($argon_show_post_outdated_info=='never'){echo 'selected';} ?>><?php _e("永不显示", 'argon');?></option>
			</select>
			<button id="apply_show_post_outdated_info" type="button" class="components-button is-primary" style="height: 22px; display: none;"><?php _e("应用", 'argon');?></button>
		</div>
		<p style="margin-top: 15px;"><?php _e("单独控制该文章的过时信息显示。", 'argon');?></p>
		<?php if ($post -> post_type === 'post') { ?>
			<h4>首页展示摘要</h4>
			<?php $argon_home_preview = get_post_meta($post->ID, "argon_home_preview", true);?>
			<textarea name="argon_home_preview" id="argon_home_preview" rows="4" cols="30" style="width:100%;"><?php if (!empty($argon_home_preview)){echo esc_textarea($argon_home_preview);} ?></textarea>
			<p style="margin-top: 15px;">只显示在首页文章卡片中。留空则继续使用主题默认摘要或 WordPress 摘要。</p>
			<h4>首页摘要字数上限</h4>
			<?php $argon_home_preview_limit = get_post_meta($post->ID, "argon_home_preview_limit", true);?>
			<input type="number" name="argon_home_preview_limit" id="argon_home_preview_limit" min="0" max="2000" step="1" value="<?php echo esc_attr($argon_home_preview_limit); ?>" style="width:100%;" />
			<p style="margin-top: 15px;">填 0 或留空表示不额外截断；这里按字符数截断。</p>
		<?php } ?>
		<h4>文末附加内容</h4>
		<?php $argon_after_post = get_post_meta($post->ID, "argon_after_post", true);?>
		<textarea name="argon_after_post" id="argon_after_post" rows="3" cols="30" style="width:100%;"><?php if (!empty($argon_after_post)){echo $argon_after_post;} ?></textarea>
		<p style="margin-top: 15px;"><?php _e("给该文章设置单独的文末附加内容，留空则跟随全局，设为 <code>--none--</code> 则不显示。", 'argon');?></p>
		<h4><?php _e("自定义 CSS", 'argon');?></h4>
		<?php $argon_custom_css = get_post_meta($post->ID, "argon_custom_css", true);?>
		<textarea name="argon_custom_css" id="argon_custom_css" rows="5" cols="30" style="width:100%;"><?php if (!empty($argon_custom_css)){echo $argon_custom_css;} ?></textarea>
		<p style="margin-top: 15px;"><?php _e("给该文章添加单独的 CSS", 'argon');?></p>

		<script>$ = window.jQuery;</script>
		<script>
			function showAlert(type, message){
				if (!wp.data){
					alert(message);
					return;
				}
				wp.data.dispatch('core/notices').createNotice(
					type,
					message,
					{ type: "snackbar", isDismissible: true, }
				);
			}
			$("select[name=argon_show_post_outdated_info").change(function(){
				$("#apply_show_post_outdated_info").css("display", "");
			});
			$("#apply_show_post_outdated_info").click(function(){
				$("#apply_show_post_outdated_info").addClass("is-busy").attr("disabled", "disabled").css("opacity", "0.5");
				$("#argon_show_post_outdated_info").attr("disabled", "disabled");
				var data = {
					action: 'update_post_meta_ajax',
					argon_meta_box_nonce: $("#argon_meta_box_nonce").val(),
					post_id: <?php echo $post->ID; ?>,
					meta_key: 'argon_show_post_outdated_info',
					meta_value: $("select[name=argon_show_post_outdated_info]").val()
				};
				$.ajax({
					url: ajaxurl,
					type: 'post',
					data: data,
					success: function(response) {
						$("#apply_show_post_outdated_info").removeClass("is-busy").removeAttr("disabled").css("opacity", "1");
						$("#argon_show_post_outdated_info").removeAttr("disabled");
						if (response.status == "failed"){
							showAlert("failed", "<?php _e("应用失败", 'argon');?>");
							return;
						}
						$("#apply_show_post_outdated_info").css("display", "none");
						showAlert("success", "<?php _e("应用成功", 'argon');?>");
					},
					error: function(response) {
						$("#apply_show_post_outdated_info").removeClass("is-busy").removeAttr("disabled").css("opacity", "1");
						$("#argon_show_post_outdated_info").removeAttr("disabled");
						showAlert("failed", "<?php _e("应用失败", 'argon');?>");
					}
				});
			});
		</script>
	<?php
}
function argon_add_meta_boxes(){
	add_meta_box('argon_meta_box_1', __("文章设置", 'argon'), 'argon_meta_box_1', array('post', 'page'), 'side', 'low');
	add_meta_box('argon_parent_composite_page_box', '归属页面', 'argon_parent_composite_page_meta_box', 'post', 'side', 'core');
	add_meta_box('argon_post_privacy_box', '文章开放程度', 'argon_post_privacy_meta_box', 'post', 'side', 'core');
}
add_action('admin_menu', 'argon_add_meta_boxes');

function argon_parent_composite_page_meta_box($post){
	wp_nonce_field('argon_parent_composite_page_nonce_action', 'argon_parent_composite_page_nonce');
	$composite_pages = function_exists('argon_get_composite_pages_for_settings') ? argon_get_composite_pages_for_settings() : array();
	$assigned_page = intval(get_post_meta($post -> ID, 'argon_parent_composite_page', true));
	?>
		<select name="argon_parent_composite_page" id="argon_parent_composite_page" style="width:100%;">
			<option value="0">不归属复合页面</option>
			<?php foreach ($composite_pages as $composite_page) { ?>
				<option value="<?php echo esc_attr($composite_page -> ID); ?>" <?php selected($assigned_page, intval($composite_page -> ID)); ?>><?php echo esc_html(get_the_title($composite_page -> ID)); ?></option>
			<?php } ?>
		</select>
		<p style="margin-top: 10px; color:#646970;">这里只决定文章显示在哪个复合页面里，不会写入分类目录。</p>
	<?php
}

function argon_get_privacy_visibility_values(){
	return array('public', 'private', 'logged_in', 'ip_allowlist', 'password');
}

function argon_normalize_privacy_visibility($visibility){
	return in_array($visibility, argon_get_privacy_visibility_values(), true) ? $visibility : 'public';
}

function argon_post_privacy_meta_box($post){
	wp_nonce_field('argon_post_privacy_nonce_action', 'argon_post_privacy_nonce');
	$visibility = argon_normalize_privacy_visibility(get_post_meta($post -> ID, 'argon_post_visibility', true));
	$allowed_ips = get_post_meta($post -> ID, 'argon_post_allowed_ips', true);
	$password = get_post_meta($post -> ID, 'argon_post_password', true);
	?>
		<p>
			<label for="argon_post_visibility"><strong>开放程度</strong></label>
			<select name="argon_post_visibility" id="argon_post_visibility" class="widefat" style="margin-top:6px;">
				<option value="public" <?php selected($visibility, 'public'); ?>>公开</option>
				<option value="private" <?php selected($visibility, 'private'); ?>>私密，仅管理员/编辑可看</option>
				<option value="logged_in" <?php selected($visibility, 'logged_in'); ?>>登录后才能查看</option>
				<option value="ip_allowlist" <?php selected($visibility, 'ip_allowlist'); ?>>指定 IP 才能查看</option>
				<option value="password" <?php selected($visibility, 'password'); ?>>指定密码才能查看</option>
			</select>
		</p>
		<p id="argon_post_allowed_ips_wrap" style="<?php echo $visibility === 'ip_allowlist' ? '' : 'display:none;'; ?>">
			<label for="argon_post_allowed_ips"><strong>允许访问的 IP</strong></label>
			<textarea name="argon_post_allowed_ips" id="argon_post_allowed_ips" rows="3" class="widefat" placeholder="每行一个 IP"><?php echo esc_textarea($allowed_ips); ?></textarea>
		</p>
		<p id="argon_post_password_wrap" style="<?php echo $visibility === 'password' ? '' : 'display:none;'; ?>">
			<label for="argon_post_password"><strong>访问密码</strong></label>
			<input type="text" name="argon_post_password" id="argon_post_password" value="<?php echo esc_attr($password); ?>" class="widefat" autocomplete="off" placeholder="由你设置访问密码">
		</p>
		<script>
			(function(){
				var visibility = document.getElementById('argon_post_visibility');
				var ipWrap = document.getElementById('argon_post_allowed_ips_wrap');
				var passwordWrap = document.getElementById('argon_post_password_wrap');
				if (!visibility) {
					return;
				}
				function syncPostPrivacyFields(){
					if (ipWrap) {
						ipWrap.style.display = visibility.value === 'ip_allowlist' ? '' : 'none';
					}
					if (passwordWrap) {
						passwordWrap.style.display = visibility.value === 'password' ? '' : 'none';
					}
				}
				visibility.addEventListener('change', syncPostPrivacyFields);
				syncPostPrivacyFields();
			})();
		</script>
	<?php
}

function argon_save_meta_data($post_id){
	if (!isset($_POST['argon_meta_box_nonce'])){
		return $post_id;
	}
	$nonce = $_POST['argon_meta_box_nonce'];
	if (!wp_verify_nonce($nonce, 'argon_meta_box_nonce_action')){
		return $post_id;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return $post_id;
	}
	if ($_POST['post_type'] == 'post'){
		if (!current_user_can('edit_post', $post_id)){
			return $post_id;
		}
	}
	if ($_POST['post_type'] == 'page'){
		if (!current_user_can('edit_page', $post_id)){
			return $post_id;
		}
	}
	update_post_meta($post_id, 'argon_hide_readingtime', $_POST['argon_meta_hide_readingtime']);
	update_post_meta($post_id, 'argon_meta_simple', $_POST['argon_meta_simple']);
	update_post_meta($post_id, 'argon_first_image_as_thumbnail', $_POST['argon_first_image_as_thumbnail']);
	update_post_meta($post_id, 'argon_show_post_outdated_info', $_POST['argon_show_post_outdated_info']);
	update_post_meta($post_id, 'argon_home_preview', isset($_POST['argon_home_preview']) ? wp_kses_post($_POST['argon_home_preview']) : '');
	update_post_meta($post_id, 'argon_home_preview_limit', isset($_POST['argon_home_preview_limit']) ? max(0, intval($_POST['argon_home_preview_limit'])) : 0);
	update_post_meta($post_id, 'argon_after_post', $_POST['argon_after_post']);
	update_post_meta($post_id, 'argon_custom_css', $_POST['argon_custom_css']);
}
add_action('save_post', 'argon_save_meta_data');

function argon_save_parent_composite_page_meta($post_id){
	if (!isset($_POST['argon_parent_composite_page_nonce']) || !wp_verify_nonce($_POST['argon_parent_composite_page_nonce'], 'argon_parent_composite_page_nonce_action')){
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;
	}
	if (get_post_type($post_id) !== 'post' || !current_user_can('edit_post', $post_id)){
		return;
	}
	if (function_exists('argon_save_post_composite_page_assignment')){
		argon_save_post_composite_page_assignment($post_id, isset($_POST['argon_parent_composite_page']) ? intval($_POST['argon_parent_composite_page']) : 0);
	}
}
add_action('save_post_post', 'argon_save_parent_composite_page_meta');

function argon_save_post_privacy_meta($post_id){
	if (!isset($_POST['argon_post_privacy_nonce']) || !wp_verify_nonce($_POST['argon_post_privacy_nonce'], 'argon_post_privacy_nonce_action')){
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE){
		return;
	}
	if (get_post_type($post_id) !== 'post' || !current_user_can('edit_post', $post_id)){
		return;
	}
	update_post_meta($post_id, 'argon_post_visibility', isset($_POST['argon_post_visibility']) ? argon_normalize_privacy_visibility(sanitize_text_field(wp_unslash($_POST['argon_post_visibility']))) : 'public');
	update_post_meta($post_id, 'argon_post_allowed_ips', isset($_POST['argon_post_allowed_ips']) ? sanitize_textarea_field(wp_unslash($_POST['argon_post_allowed_ips'])) : '');
	update_post_meta($post_id, 'argon_post_password', isset($_POST['argon_post_password']) ? sanitize_text_field(wp_unslash($_POST['argon_post_password'])) : '');
}
add_action('save_post_post', 'argon_save_post_privacy_meta');

function argon_move_parent_composite_page_box_below_categories(){
	if (!function_exists('get_current_screen')){
		return;
	}
	$screen = get_current_screen();
	if (empty($screen -> post_type) || $screen -> post_type !== 'post'){
		return;
	}
	?>
	<script>
		jQuery(function($){
			var pageBox = $('#argon_parent_composite_page_box');
			var categoryBox = $('#categorydiv');
			if (pageBox.length && categoryBox.length) {
				pageBox.insertAfter(categoryBox);
			}
		});
	</script>
	<?php
}
add_action('admin_footer-post.php', 'argon_move_parent_composite_page_box_below_categories');
add_action('admin_footer-post-new.php', 'argon_move_parent_composite_page_box_below_categories');

function update_post_meta_ajax(){
	if (!isset($_POST['argon_meta_box_nonce'])){
		return;
	}
	$nonce = $_POST['argon_meta_box_nonce'];
	if (!wp_verify_nonce($nonce, 'argon_meta_box_nonce_action')){
		return;
	}
	header('Content-Type:application/json; charset=utf-8');
	if (!isset($_POST["post_id"]) || !isset($_POST["meta_key"]) || !isset($_POST["meta_value"])){
		status_header(400);
		exit(json_encode(array(
			'status' => 'failed',
			'message' => 'invalid_request'
		)));
	}
	$post_id = intval($_POST["post_id"]);
	$meta_key = $_POST["meta_key"];
	$meta_value = $_POST["meta_value"];
	$allowed_meta_keys = array(
		'argon_hide_readingtime',
		'argon_meta_simple',
		'argon_first_image_as_thumbnail',
		'argon_show_post_outdated_info',
		'argon_after_post',
		'argon_custom_css'
	);

	if (!current_user_can('edit_post', $post_id) || !in_array($meta_key, $allowed_meta_keys, true)){
		status_header(403);
		exit(json_encode(array(
			'status' => 'failed',
			'message' => 'forbidden'
		)));
	}

	if (get_post_meta($post_id, $meta_key, true) == $meta_value){
		exit(json_encode(array(
			'status' => 'success'
		)));
	}

	$result = update_post_meta($post_id, $meta_key, $meta_value);

	if ($result){
		exit(json_encode(array(
			'status' => 'success'
		)));
	}else{
		exit(json_encode(array(
			'status' => 'failed'
		)));
	}
}
add_action('wp_ajax_update_post_meta_ajax' , 'update_post_meta_ajax');
//首页显示说说
function argon_home_add_post_type_shuoshuo($query){
	if (is_home() && $query -> is_main_query()){
		$query -> set('post_type', array('post', 'shuoshuo'));
	}
	return $query;
}
if (get_option("argon_home_show_shuoshuo") == "true"){
	add_action('pre_get_posts', 'argon_home_add_post_type_shuoshuo');
}
//首页隐藏特定分类文章
function argon_home_hide_categories($query){
	if (is_home() && $query -> is_main_query()){
		$excludeCategories = explode(",", get_option("argon_hide_categories"));
		$excludeCategories = array_map(function($cat) { return -$cat; }, $excludeCategories);
		$query -> set('category__not_in', $excludeCategories);
		$query -> set('tag__not_in', $excludeCategories);
	}
	return $query;
}
if (get_option("argon_hide_categories") != ""){
	add_action('pre_get_posts', 'argon_home_hide_categories');
}

function argon_home_exclude_composite_assigned_posts($query){
	if (is_admin() || !$query -> is_main_query() || !is_home()){
		return $query;
	}
	$meta_query = $query -> get('meta_query');
	if (!is_array($meta_query)){
		$meta_query = array();
	}
	$meta_query[] = array(
		'relation' => 'OR',
		array(
			'key' => 'argon_parent_composite_page',
			'compare' => 'NOT EXISTS'
		),
		array(
			'key' => 'argon_parent_composite_page',
			'value' => '',
			'compare' => '='
		),
		array(
			'key' => 'argon_parent_composite_page',
			'value' => '0',
			'compare' => '='
		)
	);
	$query -> set('meta_query', $meta_query);
	return $query;
}
add_action('pre_get_posts', 'argon_home_exclude_composite_assigned_posts', 20);
//文章过时信息显示
function argon_get_post_outdated_info(){
	global $post;
	$post_show_outdated_info_status = strval(get_post_meta($post -> ID, 'argon_show_post_outdated_info', true));
	if (get_option("argon_outdated_info_tip_type") == "toast"){
		$before = "<div id='post_outdate_toast' style='display:none;' data-text='";
		$after = "'></div>";
	}else{
		$before = "<div class='post-outdated-info'><i class='fa fa-info-circle' aria-hidden='true'></i>";
		$after = "</div>";
	}
	$content = get_option('argon_outdated_info_tip_content') == '' ? '本文最后更新于 %date_delta% 天前，其中的信息可能已经有所发展或是发生改变。' : get_option('argon_outdated_info_tip_content');
	$delta = get_option('argon_outdated_info_days') == '' ? (-1) : get_option('argon_outdated_info_days');
	if ($delta == -1){
		$delta = 2147483647;
	}
	$post_date_delta = floor((current_time('timestamp') - get_the_time("U")) / (60 * 60 * 24));
	$modify_date_delta = floor((current_time('timestamp') - get_the_modified_time("U")) / (60 * 60 * 24));
	if (get_option("argon_outdated_info_time_type") == "createdtime"){
		$date_delta = $post_date_delta;
	}else{
		$date_delta = $modify_date_delta;
	}
	if (($date_delta <= $delta && $post_show_outdated_info_status != 'always') || $post_show_outdated_info_status == 'never'){
		return "";
	}
	$content = str_replace("%date_delta%", $date_delta, $content);
	$content = str_replace("%modify_date_delta%", $modify_date_delta, $content);
	$content = str_replace("%post_date_delta%", $post_date_delta, $content);
	return $before . $content . $after;
}
//Gutenberg 编辑器区块
function argon_init_gutenberg_blocks() {
	wp_register_script(
		'argon-gutenberg-block-js',
		$GLOBALS['assets_path'].'/gutenberg/dist/blocks.build.js',
		array( 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'),
		null,
		true
	);
	wp_register_style(
		'argon-gutenberg-block-backend-css',
		$GLOBALS['assets_path'].'/gutenberg/dist/blocks.editor.build.css',
		array('wp-edit-blocks'),
		filemtime(get_template_directory() . '/gutenberg/dist/blocks.editor.build.css')
	);
	register_block_type(
		'argon/argon-gutenberg-block', array(
			//'style'         => 'argon-gutenberg-block-frontend-css',
			'editor_script' => 'argon-gutenberg-block-js',
			'editor_style'  => 'argon-gutenberg-block-backend-css',
		)
	);
}
add_action('init', 'argon_init_gutenberg_blocks');
function argon_add_gutenberg_category($block_categories, $editor_context) {
	if (!empty($editor_context->post)){
		array_push(
			$block_categories,
			array(
				'slug'  => 'argon',
				'title' => 'Argon',
				'icon'  => null,
			)
		);
	}
	return $block_categories;
}
add_filter('block_categories_all', 'argon_add_gutenberg_category', 10, 2);
function argon_admin_i18n_info(){
	echo "<script>var argon_language = '" . argon_get_locate() . "';</script>";
}
add_filter('admin_head', 'argon_admin_i18n_info');
//主题文章短代码解析
function shortcode_content_preprocess($attr, $content = ""){
	if ( isset( $attr['nested'] ) ? $attr['nested'] : 'true' != 'false' ){
		return do_shortcode($content);
	}else{
		return $content;
	}	
}
add_shortcode('br','shortcode_br');
function shortcode_br($attr,$content=""){
	return "</br>";
}
add_shortcode('label','shortcode_label');
function shortcode_label($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$out = "<span class='badge";
	$color = isset( $attr['color'] ) ? $attr['color'] : 'indigo';
	switch ($color){
		case 'green':
			$out .= " badge-success";
			break;
		case 'red':
			$out .= " badge-danger";
			break;
		case 'orange':
			$out .= " badge-warning";
			break;
		case 'blue':
			$out .= " badge-info";
			break;
		case 'indigo':
		default:
			$out .= " badge-primary";
			break;
	}
	$shape = isset( $attr['shape'] ) ? $attr['shape'] : 'square';
	if ($shape=="round"){
		$out .= " badge-pill";
	}
	$out .= "'>" . $content . "</span>";
	return $out;
}
add_shortcode('progressbar','shortcode_progressbar');
function shortcode_progressbar($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$out = "<div class='progress-wrapper'><div class='progress-info'>";
	if ($content != ""){
		$out .= "<div class='progress-label'><span>" . $content . "</span></div>";
	}
	$progress = isset( $attr['progress'] ) ? $attr['progress'] : 100;
	$out .= "<div class='progress-percentage'><span>" . $progress . "%</span></div>";
	$out .= "</div><div class='progress'><div class='progress-bar";
	$color = isset( $attr['color'] ) ? $attr['color'] : 'indigo';
	switch ($color){
		case 'indigo':
			$out .= " bg-primary";
			break;
		case 'green':
			$out .= " bg-success";
			break;
		case 'red':
			$out .= " bg-danger";
			break;
		case 'orange':
			$out .= " bg-warning";
			break;
		case 'blue':
			$out .= " bg-info";
			break;
		default:
			$out .= " bg-primary";
			break;
	}
	$out .= "' style='width: " . $progress . "%;'></div></div></div>";
	return $out;
}
add_shortcode('checkbox','shortcode_checkbox');
function shortcode_checkbox($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$checked = isset( $attr['checked'] ) ? $attr['checked'] : 'false';
	$inline = isset($attr['inline']) ? $attr['checked'] : 'false';
	$out = "<div class='shortcode-todo custom-control custom-checkbox";
	if ($inline == 'true'){
		$out .= " inline";
	}
	$out .= "'>
				<input class='custom-control-input' type='checkbox'" . ($checked == 'true' ? ' checked' : '') . ">
				<label class='custom-control-label'>
					<span>" . $content . "</span>
				</label>
			</div>";
	return $out;
}
add_shortcode('alert','shortcode_alert');
function shortcode_alert($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$out = "<div class='alert";
	$color = isset( $attr['color'] ) ? $attr['color'] : 'indigo';
	switch ($color){
		case 'indigo':
			$out .= " alert-primary";
			break;
		case 'green':
			$out .= " alert-success";
			break;
		case 'red':
			$out .= " alert-danger";
			break;
		case 'orange':
			$out .= " alert-warning";
			break;
		case 'blue':
			$out .= " alert-info";
			break;
		case 'black':
			$out .= " alert-default";
			break;
		default:
			$out .= " alert-primary";
			break;
	}
	$out .= "'>";
	if (isset($attr['icon'])){
		$out .= "<span class='alert-inner--icon'><i class='fa fa-" . $attr['icon'] . "'></i></span>";
	}
	$out .= "<span class='alert-inner--text'>";
	if (isset($attr['title'])){
		$out .= "<strong>" . $attr['title'] . "</strong> ";
	}
	$out .= $content . "</span></div>";
	return $out;
}
add_shortcode('admonition','shortcode_admonition');
function shortcode_admonition($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$out = "<div class='admonition shadow-sm";
	$color = isset( $attr['color'] ) ? $attr['color'] : 'indigo';
	switch ($color){
		case 'indigo':
			$out .= " admonition-primary";
			break;
		case 'green':
			$out .= " admonition-success";
			break;
		case 'red':
			$out .= " admonition-danger";
			break;
		case 'orange':
			$out .= " admonition-warning";
			break;
		case 'blue':
			$out .= " admonition-info";
			break;
		case 'black':
			$out .= " admonition-default";
			break;
		case 'grey':
			$out .= " admonition-grey";
			break;
		default:
			$out .= " admonition-primary";
			break;
	}
	$out .= "'>";
	if (isset($attr['title'])){
		$out .= "<div class='admonition-title'>";
		if (isset($attr['icon'])){
			$out .= "<i class='fa fa-" . $attr['icon'] . "'></i> ";
		}
		$out .= $attr['title'] . "</div>";
	}
	if ($content != ''){
		$out .= "<div class='admonition-body'>" . $content . "</div>";
	}
	$out .= "</div>";
	return $out;
}
add_shortcode('collapse','shortcode_collapse_block');
add_shortcode('fold','shortcode_collapse_block');
function shortcode_collapse_block($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$collapsed = isset( $attr['collapsed'] ) ? $attr['collapsed'] : 'true';
	$show_border_left = isset( $attr['showleftborder'] ) ? $attr['showleftborder'] : 'false';
	$out = "<div " ;
	$out .= " class='collapse-block shadow-sm";
	$color = isset( $attr['color'] ) ? $attr['color'] : 'none';
	$title = isset( $attr['title'] ) ? $attr['title'] : '';
	switch ($color){
		case 'indigo':
			$out .= " collapse-block-primary";
			break;
		case 'green':
			$out .= " collapse-block-success";
			break;
		case 'red':
			$out .= " collapse-block-danger";
			break;
		case 'orange':
			$out .= " collapse-block-warning";
			break;
		case 'blue':
			$out .= " collapse-block-info";
			break;
		case 'black':
			$out .= " collapse-block-default";
			break;
		case 'grey':
			$out .= " collapse-block-grey";
			break;
		case 'none':
		default:
			$out .= " collapse-block-transparent";
			break;
	}
	if ($collapsed == 'true'){
		$out .= " collapsed";
	}
	if ($show_border_left != 'true'){
		$out .= " hide-border-left";
	}
	$out .= "'>";

	$out .= "<div class='collapse-block-title'>";
	if (isset($attr['icon'])){
		$out .= "<i class='fa fa-" . $attr['icon'] . "'></i> ";
	}
	$out .= "<span class='collapse-block-title-inner'>" . $title . "</span><i class='collapse-icon fa fa-angle-down'></i></div>";

	$out .= "<div class='collapse-block-body'";
	if ($collapsed != 'false'){
		$out .= " style='display:none;'";
	}
	$out .= ">" . $content . "</div>";
	$out .= "</div>";
	return $out;
}
add_shortcode('friendlinks','shortcode_friend_link');
function shortcode_friend_link($attr,$content=""){
	$sort = isset( $attr['sort'] ) ? $attr['sort'] : 'name';
	$order = isset( $attr['order'] ) ? $attr['order'] : 'ASC';
	$friendlinks = get_bookmarks( array(
		'orderby' => $sort ,
		'order'   => $order
	));
	$style = isset( $attr['style'] ) ? $attr['style'] : '1';
	switch ($style) {
		case '1':
			$class = "friend-links-style1";
			break;
		case '1-square':
			$class = "friend-links-style1 friend-links-style1-square";
			break;
		case '2':
			$class = "friend-links-style2";
			break;
		case '2-big':
			$class = "friend-links-style2 friend-links-style2-big";
			break;
		default:
			$class = "friend-links-style1";
			break;
	}
	$out = "<div class='friend-links " . $class . "'><div class='row'>";
	foreach ($friendlinks as $friendlink){
		$out .= "
			<div class='link mb-2 col-lg-6 col-md-6'>
				<div class='card shadow-sm friend-link-container" . ($friendlink -> link_image == "" ? " no-avatar" : "") . "'>";
		if ($friendlink -> link_image != ''){
			$out .= "
					<img src='" . $friendlink -> link_image . "' class='friend-link-avatar bg-gradient-secondary'>";
		}
		$out .= "	<div class='friend-link-content'>
						<div class='friend-link-title title text-primary'>
							<a target='_blank' href='" . esc_url($friendlink -> link_url) . "'>" . esc_html($friendlink -> link_name) . "</a>
						</div>
						<div class='friend-link-description'>" . esc_html($friendlink -> link_description) . "</div>";
		$out .= "		<div class='friend-link-links'>";
		foreach (explode("\n", $friendlink -> link_notes) as $line){
			$item = explode("|", trim($line));
			if(stripos($item[0], "fa-") !== 0){
				continue;
			}
			$out .= "<a href='" . esc_url($item[1]) . "' target='_blank'><i class='fa " . sanitize_html_class($item[0]) . "'></i></a>";
		}
		$out .= "<a href='" . esc_url($friendlink -> link_url) . "' target='_blank' style='float:right; margin-right: 10px;'><i class='fa fa-angle-right' style='font-weight: bold;'></i></a>";
		$out .= "
						</div>
					</div>
				</div>
			</div>";
	}
	$out .= "</div></div>";
	return $out;
}
add_shortcode('sfriendlinks','shortcode_friend_link_simple');
function shortcode_friend_link_simple($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$content = trim(strip_tags($content));
	$entries = explode("\n" , $content);

	$shuffle = isset( $attr['shuffle'] ) ? $attr['shuffle'] : 'false';
	if ($shuffle == "true"){
		mt_srand();
		$group_start = 0;
		foreach ($entries as $index => $value){
			$now = explode("|" , $value);
			if ($now[0] == 'category'){
				echo ($index-1).",".$group_start." | ";
				for ($i = $index - 1; $i >= $group_start; $i--){
					echo $i."#";
					$tar = mt_rand($group_start , $i);
					$tmp = $entries[$tar];
					$entries[$tar] = $entries[$i];
					$entries[$i] = $tmp;
				}
				$group_start = $index + 1;
			}
		}
		for ($i = count($entries) - 1; $i >= $group_start; $i--){
			$tar = mt_rand($group_start , $i);
			$tmp = $entries[$tar];
			$entries[$tar] = $entries[$i];
			$entries[$i] = $tmp;
		}
	}

	$row_tag_open = False;
	$out = "<div class='friend-links-simple'>";
	foreach($entries as $index => $value){
		$now = explode("|" , $value);
		if ($now[0] == 'category'){
			if ($row_tag_open == True){
				$row_tag_open = False;
				$out .= "</div>";
			}
			$out .= "<div class='friend-category-title text-black'>" . $now[1] . "</div>";
		}
		if ($now[0] == 'link'){
			if ($row_tag_open == False){
				$row_tag_open = True;
				$out .= "<div class='row'>";
			}
			$out .= "
			<div class='link mb-2 col-lg-4 col-md-6'>
				<div class='card shadow-sm'>
					<div class='d-flex'>
						<div class='friend-link-avatar'>
							<a target='_blank' href='" . $now[1] . "'>";
			if (!ctype_space($now[4]) && $now[4] != '' && isset($now[4])){
				$out .= "<img src='" . $now[4] . "' class='icon bg-gradient-secondary rounded-circle text-white' style='pointer-events: none;'>
						</img>";
			}else{
				$out .= "<div class='icon icon-shape bg-gradient-primary rounded-circle text-white'>" . mb_substr($now[2], 0, 1) . "
						</div>";
			}

			$out .= "		</a>
						</div>
						<div class='pl-3'>
							<div class='friend-link-title title text-primary'><a target='_blank' href='" . $now[1] . "'>" . $now[2] . "</a>
						</div>";
			if (!ctype_space($now[3]) && $now[3] != ''  && isset($now[3])){
				$out .= "<p class='friend-link-description'>" . $now[3] . "</p>";
			}else{
				/*$out .= "<p class='friend-link-description'>&nbsp;</p>";*/
			}
			$out .= "		<a target='_blank' href='" . $now[1] . "' class='text-primary opacity-8'>前往</a>
						</div>
					</div>
				</div>
			</div>";
		}
	}
	if ($row_tag_open == True){
		$row_tag_open = False;
		$out .= "</div>";
	}
	$out .= "</div>";
	return $out;
}
add_shortcode('timeline','shortcode_timeline');
function shortcode_timeline($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$content = trim(strip_tags($content));
	$entries = explode("\n" , $content);
	$out = "<div class='argon-timeline'>";
	foreach($entries as $index => $value){
		$now = explode("|" , $value);
		$now[0] = str_replace("/" , "</br>" , $now[0]);
		$out .= "<div class='argon-timeline-node'>
					<div class='argon-timeline-time'>" . $now[0] . "</div>
					<div class='argon-timeline-card card bg-gradient-secondary shadow-sm'>";
		if ($now[1] != ''){
			$out .= "	<div class='argon-timeline-title'>" . $now[1] . "</div>";
		}
		$out .= "		<div class='argon-timeline-content'>";
		foreach($now as $index => $value){
			if ($index < 2){
				continue;
			}
			if ($index > 2){
				$out .= "</br>";
			}
			$out .= $value;
		}
		$out .= "		</div>
					</div>
				</div>";
	}
	$out .= "</div>";
	return $out;
}
add_shortcode('hidden','shortcode_hidden');
add_shortcode('spoiler','shortcode_hidden');
function shortcode_hidden($attr,$content=""){
	$content = shortcode_content_preprocess($attr, $content);
	$out = "<span class='argon-hidden-text";
	$tip = isset( $attr['tip'] ) ? $attr['tip'] : '';
	$type = isset( $attr['type'] ) ? $attr['type'] : 'blur';
	if ($type == "background"){
		$out .= " argon-hidden-text-background";
	}else{
		$out .= " argon-hidden-text-blur";
	}
	$out .= "'";
	if ($tip != ''){
		$out .= " title='" . $tip ."'";
	}
	$out .= ">" . $content . "</span>";
	return $out;
}
add_shortcode('github','shortcode_github');
function shortcode_github($attr,$content=""){
	$github_info_card_id = mt_rand(1000000000 , 9999999999);
	$author = isset( $attr['author'] ) ? $attr['author'] : '';
	$project = isset( $attr['project'] ) ? $attr['project'] : '';
	$getdata = isset( $attr['getdata'] ) ? $attr['getdata'] : 'frontend';
	$size = isset( $attr['size'] ) ? $attr['size'] : 'full';

	$description = "";
	$stars = "";
	$forks = "";

	if ($getdata == "backend"){
		set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
			if (error_reporting() === 0) {
				return false;
			}
			throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
		});
		try{
			$contexts = stream_context_create(
				array(
					'http' => array(
						'method'=>"GET",
						'header'=>"User-Agent: ArgonTheme\r\n"
					)
				)
			);
			$json = file_get_contents("https://api.github.com/repos/" . $author . "/" . $project, false, $contexts);
			if (empty($json)){
				throw new Exception("");
			}
			$json = json_decode($json);
			$description = esc_html($json -> description);
			if (!empty($json -> homepage)){
				$description .= esc_html(" <a href='" . $json -> homepage . "' target='_blank' no-pjax>" . $json -> homepage . "</a>");
			}
			$stars = $json -> stargazers_count;
			$forks = $json -> forks_count;
		}catch (Exception $e){
			$getdata = "frontend";
		}
		restore_error_handler();
	}

	$out = "<div class='github-info-card github-info-card-" . $size . " card shadow-sm' data-author='" . $author . "' data-project='" . $project . "' githubinfo-card-id='" . $github_info_card_id . "' data-getdata='" . $getdata . "' data-description='" . $description . "' data-stars='" . $stars . "' data-forks='" . $forks . "'>";
	$out .= "<div class='github-info-card-header'><a href='https://github.com/' ref='nofollow' target='_blank' title='Github' no-pjax><span><i class='fa fa-github'></i>";
	if ($size != "mini"){
		$out .= " GitHub";
	}
	$out .= "</span></a></div>";
	$out .= "<div class='github-info-card-body'>
			<div class='github-info-card-name-a'>
				<a href='https://github.com/" . $author . "/" . $project . "' target='_blank' no-pjax>
					<span class='github-info-card-name'>" . $author . "/" . $project . "</span>
				</a>
				</div>
			<div class='github-info-card-description'></div>
		</div>";
	$out .= "<div class='github-info-card-bottom'>
				<span class='github-info-card-meta github-info-card-meta-stars'>
					<i class='fa fa-star'></i> <span class='github-info-card-stars'></span>
				</span>
				<span class='github-info-card-meta github-info-card-meta-forks'>
					<i class='fa fa-code-fork'></i> <span class='github-info-card-forks'></span>
				</span>
			</div>";
	$out .= "</div>";
	return $out;
}
add_shortcode('video','shortcode_video');
function shortcode_video($attr,$content=""){
	$url = isset( $attr['mp4'] ) ? $attr['mp4'] : '';
	$url = isset( $attr['url'] ) ? $attr['url'] : $url;
	$width = isset( $attr['width'] ) ? $attr['width'] : '';
	$height = isset( $attr['height'] ) ? $attr['height'] : '';
	$autoplay = isset( $attr['autoplay'] ) ? $attr['autoplay'] : 'false';
	$out = "<video";
	if ($width != ''){
		$out .= " width='" . $width . "'";
	}
	if ($height != ''){
		$out .= " height='" . $height . "'";
	}
	if ($autoplay == 'true'){
		$out .= " autoplay";
	}
	$out .= " controls>";
	$out .= "<source src='" . $url . "'>";
	$out .= "</video>";
	return $out;
}
add_shortcode('hide_reading_time','shortcode_hide_reading_time');
function shortcode_hide_reading_time($attr,$content=""){
	return "";
}
add_shortcode('post_time','shortcode_post_time');
function shortcode_post_time($attr,$content=""){
	$format = isset( $attr['format'] ) ? $attr['format'] : 'Y-n-d G:i:s';
	return get_the_time($format);
}
add_shortcode('post_modified_time','shortcode_post_modified_time');
function shortcode_post_modified_time($attr,$content=""){
	$format = isset( $attr['format'] ) ? $attr['format'] : 'Y-n-d G:i:s';
	return get_the_modified_time($format);
}
function argon_get_profile_page_url(){
	$match_titles = array('新世纪传说W.Y.Z.', '新世纪传说W.Y.Z');
	$locations = get_nav_menu_locations();
	foreach (array('toolbar_menu', 'leftbar_menu') as $location){
		if (empty($locations[$location])){
			continue;
		}
		$items = wp_get_nav_menu_items($locations[$location]);
		if (empty($items) || is_wp_error($items)){
			continue;
		}
		foreach ($items as $item){
			$title = trim(wp_strip_all_tags(html_entity_decode($item -> title, ENT_QUOTES, get_bloginfo('charset'))));
			foreach ($match_titles as $match_title){
				if ($title == $match_title || strpos($title, 'W.Y.Z') !== false){
					return $item -> url;
				}
			}
		}
	}
	if (function_exists('get_page_by_title')){
		foreach ($match_titles as $match_title){
			$page = get_page_by_title($match_title);
			if (!empty($page)){
				return get_permalink($page -> ID);
			}
		}
	}
	return home_url('/');
}

function argon_get_homepage_link_pages(){
	$exclude_ids = array();
	$front_page_id = intval(get_option('page_on_front'));
	if ($front_page_id > 0){
		$exclude_ids[] = $front_page_id;
	}
	$pages = get_pages(array(
		'sort_column' => 'menu_order,post_title',
		'sort_order' => 'ASC',
		'post_status' => 'publish',
		'exclude' => implode(',', $exclude_ids)
	));
	return array_values(array_filter($pages, function($page) {
		return !function_exists('argon_content_is_restricted_for_navigation') || !argon_content_is_restricted_for_navigation($page -> ID);
	}));
}

function argon_render_homepage_page_links($class_name = 'home-page-links'){
	$pages = argon_get_homepage_link_pages();
	if (empty($pages)){
		return;
	}
	?>
	<nav class="<?php echo esc_attr($class_name); ?>" aria-label="页面入口">
		<?php foreach ($pages as $page){ ?>
			<a href="<?php echo esc_url(get_permalink($page -> ID)); ?>">
				<span><?php echo esc_html(get_the_title($page -> ID)); ?></span>
				<i class="fa fa-angle-right" aria-hidden="true"></i>
			</a>
		<?php } ?>
	</nav>
	<?php
}

function argon_render_mobile_home_profile_card(){
	$avatar = get_option('argon_sidebar_auther_image');
	if ($avatar == ''){
		$avatar = 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz48c3ZnIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMDAgMTAwIiB4bWw6c3BhY2U9InByZXNlcnZlIj48cmVjdCBmaWxsPSIjNUU3MkU0MjIiIHdpZHRoPSIxMDAiIGhlaWdodD0iMTAwIi8+PGc+PGcgb3BhY2l0eT0iMC4zIj48cGF0aCBmaWxsPSIjNUU3MkU0IiBkPSJNNzQuMzksMzIuODZjLTAuOTgtMS43LTMuMzktMy4wOS01LjM1LTMuMDlINDUuNjJjLTEuOTYsMC00LjM3LDEuMzktNS4zNSwzLjA5TDI4LjU3LDUzLjE1Yy0wLjk4LDEuNy0wLjk4LDQuNDgsMCw2LjE3bDExLjcxLDIwLjI5YzAuOTgsMS43LDMuMzksMy4wOSw1LjM1LDMuMDloMjMuNDNjMS45NiwwLDQuMzctMS4zOSw1LjM1LTMuMDlMODYuMSw1OS4zMmMwLjk4LTEuNywwLjk4LTQuNDgsMC02LjE3TDc0LjM5LDMyLjg2eiIvPjwvZz48ZyBvcGFjaXR5PSIwLjgiPjxwYXRoIGZpbGw9IiM1RTcyRTQiIGQ9Ik02Mi4wNCwyMC4zOWMtMC45OC0xLjctMy4zOS0zLjA5LTUuMzUtMy4wOUgzMS43M2MtMS45NiwwLTQuMzcsMS4zOS01LjM1LDMuMDlMMTMuOSw0Mi4wMWMtMC45OCwxLjctMC45OCw0LjQ4LDAsNi4xN2wxMi40OSwyMS42MmMwLjk4LDEuNywzLjM5LDMuMDksNS4zNSwzLjA5aDI0Ljk3YzEuOTYsMCw0LjM3LTEuMzksNS4zNS0zLjA5bDEyLjQ5LTIxLjYyYzAuOTgtMS43LDAuOTgtNC40OCwwLTYuMTdMNjIuMDQsMjAuMzl6Ii8+PC9nPjwvZz48L3N2Zz4=';
	}
	$name = get_option('argon_sidebar_auther_name');
	if ($name == ''){
		$name = get_bloginfo('name');
	}
	$description = get_option('argon_sidebar_author_description');
	$author_links = '';
	if (has_nav_menu('leftbar_author_links')){
		$author_links = wp_nav_menu(array(
			'container' => '',
			'theme_location' => 'leftbar_author_links',
			'items_wrap' => '<ul class="mobile-home-profile-links">%3$s</ul>',
			'depth' => 1,
			'echo' => false
		));
	}
	?>
	<section class="mobile-home-profile-card card shadow-sm border-0" aria-label="个人信息">
		<div class="mobile-home-profile-avatar" style="background-image: url('<?php echo esc_attr($avatar); ?>');"></div>
		<div class="mobile-home-profile-body">
			<h2 class="mobile-home-profile-name"><?php echo esc_html($name); ?></h2>
			<?php if (!empty($description)){ ?>
				<div class="mobile-home-profile-description"><?php echo wp_kses_post($description); ?></div>
			<?php } ?>
			<div class="mobile-home-profile-motto">桂棹兰桨，溯流远上，不惧劲风勇搏浪</div>
			<a class="mobile-home-profile-about-link" href="<?php echo esc_url(argon_get_profile_page_url()); ?>">关于我 <span aria-hidden="true">→</span></a>
			<?php echo $author_links; ?>
		</div>
	</section>
	<?php
}
add_shortcode('noshortcode','shortcode_noshortcode');
function shortcode_noshortcode($attr,$content=""){
	return $content;
}
//Reference Footnote
add_shortcode('ref','shortcode_ref');
$post_references = array();
$post_reference_keys_first_index = array();
$post_reference_contents_first_index = array();
function argon_get_ref_html($content, $index, $subIndex){
	$index++;
	return "<sup class='reference' id='ref_" . $index . "_" . $subIndex . "' data-content='" . esc_attr($content) . "' tabindex='0'><a class='reference-link' href='#ref_" . $index . "'>[" . $index . "]</a></sup>";
}
function shortcode_ref($attr,$content=""){
	global $post_references;
	global $post_reference_keys_first_index;
	global $post_reference_contents_first_index;
	$content = preg_replace(
		'/<p>(.*?)<\/p>/is',
		'</br>$1',
		$content
	);
	$content = wp_kses($content, array(
		'a' => array(
			'href' => array(),
			'title' => array(),
			'target' => array()
		),
		'br' => array(),
		'em' => array(),
		'strong' => array(),
		'b' => array(),
		'sup' => array(),
		'sub' => array(),
		'small' => array()
	));
	if (isset($attr['id'])){
		if (isset($post_reference_keys_first_index[$attr['id']])){
			$post_references[$post_reference_keys_first_index[$attr['id']]]['count']++;
		}else{
			array_push($post_references, array('content' => $content, 'count' => 1));
			$post_reference_keys_first_index[$attr['id']] = count($post_references) - 1;
		}
		$index = $post_reference_keys_first_index[$attr['id']];
		return argon_get_ref_html($post_references[$index]['content'], $index, $post_references[$index]['count']);
	}else{
		if (isset($post_reference_contents_first_index[$content])){
			$post_references[$post_reference_contents_first_index[$content]]['count']++;
			$index = $post_reference_contents_first_index[$content];
			return argon_get_ref_html($post_references[$index]['content'], $index, $post_references[$index]['count']);
		}else{
			array_push($post_references, array('content' => $content, 'count' => 1));
			$post_reference_contents_first_index[$content] = count($post_references) - 1;
			$index = count($post_references) - 1;
			return argon_get_ref_html($post_references[$index]['content'], $index, $post_references[$index]['count']);
		}
	}
}
function get_reference_list(){
	global $post_references;
	if (count($post_references) == 0){
		return "";
	}
	$res = "<div class='reference-list-container'>";
	$res .= "<h3>" . (get_option('argon_reference_list_title') == "" ? __('参考', 'argon') : get_option('argon_reference_list_title')) . "</h3>";
	$res .= "<ol class='reference-list'>";
		foreach ($post_references as $index => $ref) {
			$res .= "<li id='ref_" . ($index + 1)  . "'><div>";
			if ($ref['count'] == 1){
				$res .= "<a class='reference-list-backlink' href='#ref_" . ($index + 1) . "_1' aria-label='back'>^</a>";
			}else{
				$res .= "<span class='reference-list-backlink'>^</span>";
				for ($i = 1, $j = 'a'; $i <= $ref['count']; $i++, $j++){
					$res .= "<sup><a class='reference-list-backlink' href='#ref_" . ($index + 1) . "_" . $i . "' aria-label='back'>" . $j . "</a></sup>";
				}
			}
			$res .= "<span>" . $ref['content'] . "</span>";
			$res .= "<div class='space' tabindex='-1'></div>";
			$res .= "</div></li>";
		}
	$res .= "</ol>";
	$res .= "</div>";
	return $res;
}
//TinyMce 按钮
function argon_tinymce_extra_buttons(){
	if(!current_user_can('edit_posts') && !current_user_can('edit_pages')){
		return;
	}
	if(get_user_option('rich_editing') == 'true'){
		add_filter('mce_external_plugins', 'argon_tinymce_add_plugin');
		add_filter('mce_buttons', 'argon_tinymce_register_button');
		add_editor_style($GLOBALS['assets_path'] . "/assets/tinymce_assets/tinymce_editor_codeblock.css");
	}
}
add_action('init', 'argon_tinymce_extra_buttons');
function argon_tinymce_register_button($buttons){
	array_push($buttons, "|", "codeblock");
	array_push($buttons, "|", "label");
	array_push($buttons, "", "checkbox");
	array_push($buttons, "", "progressbar");
	array_push($buttons, "", "alert");
	array_push($buttons, "", "admonition");
	array_push($buttons, "", "collapse");
	array_push($buttons, "", "timeline");
	array_push($buttons, "", "github");
	array_push($buttons, "", "video");
	array_push($buttons, "", "hiddentext");
	return $buttons;
}
function argon_tinymce_add_plugin($plugins){
	$plugins['codeblock'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['label'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['checkbox'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['progressbar'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['alert'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['admonition'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['collapse'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['timeline'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['github'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['video'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	$plugins['hiddentext'] = get_bloginfo('template_url') . '/assets/tinymce_assets/tinymce_btns.js';
	return $plugins;
}
//主题选项页面
function themeoptions_admin_menu(){
	/*后台管理面板侧栏添加选项*/
	add_menu_page(__("Argon 主题设置", 'argon'), __("Argon 主题选项", 'argon'), 'edit_theme_options', basename(__FILE__), 'themeoptions_page');
}
include_once(get_template_directory() . '/settings.php');
	
/*主题菜单*/
add_action('init', 'init_nav_menus');
function init_nav_menus(){
	register_nav_menus( array(
		'toolbar_menu' => __('顶部导航', 'argon'),
		'leftbar_menu' => __('左侧栏菜单', 'argon'),
		'leftbar_author_links' => __('左侧栏作者个人链接', 'argon'),
		'leftbar_friend_links' => __('左侧栏友情链接', 'argon')
	));
}

//隐藏 admin 管理条
//show_admin_bar(false);

/*说说*/
add_action('init', 'init_shuoshuo');
function init_shuoshuo(){
	$labels = array(
		'name' => __('说说', 'argon'),
		'singular_name' => __('说说', 'argon'),
		'add_new' => __('发表说说', 'argon'),
		'add_new_item' => __('发表说说', 'argon'),
		'edit_item' => __('编辑说说', 'argon'),
		'new_item' => __('新说说', 'argon'),
		'view_item' => __('查看说说', 'argon'),
		'search_items' => __('搜索说说', 'argon'),
		'not_found' => __('暂无说说', 'argon'),
		'not_found_in_trash' => __('没有已遗弃的说说', 'argon'),
		'parent_item_colon' => '',
		'menu_name' => __('说说', 'argon')
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'exclude_from_search' => true,
		'query_var' => true,
		'rewrite' => array(
			'slug' => 'shuoshuo',
			'with_front' => false
		),
		'capability_type' => 'post',
		'has_archive' => false,
		'hierarchical' => false,
		'menu_position' => null,
		'menu_icon' => 'dashicons-format-quote',
		'supports' => array('editor', 'author', 'title', 'custom-fields', 'comments')
	);
	register_post_type('shuoshuo', $args);
}

function argon_get_search_post_type_array(){
	$search_filters_type = get_option("argon_search_filters_type", "*post,*page,shuoshuo");
	$search_filters_type = explode(',', $search_filters_type);
	if (!isset($_GET['post_type'])) {
		$default = array_filter($search_filters_type, function ($str) {	return $str[0] == '*'; });
		$default = array_map(function ($str) { return substr($str, 1) ;}, $default);
		return $default;
	}
	$search_filters_type = array_map(function ($str) { return $str[0] == '*' ? substr($str, 1) : $str; }, $search_filters_type);
	$post_type = explode(',', $_GET['post_type']);
	$arr = array();
	foreach ($search_filters_type as $type) {
		if (in_array($type, $post_type)) {
			array_push($arr, $type);
		}
	}
	if (count($arr) == 0) {
		array_push($arr, 'none');
	}
	return $arr;
}
function search_filter($query) {
	if (!$query -> is_search || is_admin()) {
		return $query;
	}
	if (get_option('argon_enable_search_filters', 'true') == 'false'){
		return $query;
	}
	$query -> set('post_type', argon_get_search_post_type_array());
	return $query;
}
add_filter('pre_get_posts', 'search_filter');

/*恢复链接管理器*/
add_filter('pre_option_link_manager_enabled', '__return_true');

/*登录界面 CSS*/
function argon_login_page_style() {
	wp_enqueue_style("argon_login_css", $GLOBALS['assets_path'] . "/login.css", null, $GLOBALS['theme_version']);
}
if (get_option('argon_enable_login_css') == 'true'){
	add_action('login_head', 'argon_login_page_style');
}
// ======================
// 1. 加载 Markdown 解析库 Parsedown（需下载文件）
// 下载地址：https://github.com/erusev/parsedown
// 将 Parsedown.php 放到主题根目录
require_once get_template_directory() . '/parsedown-master/Parsedown.php';

// 2. 将文章内容通过 Markdown 解析
function convert_markdown_content($content) {
    if (is_singular() && !is_admin()) {
        $parsedown = new Parsedown();
        // 可选：启用安全模式（禁用HTML标签）
        // $parsedown->setSafeMode(true);
        $content = $parsedown->text($content);
    }
    return $content;
}
add_filter('the_content', 'convert_markdown_content', 9);

// 3. 加载 MathJax 和 自定义 CSS
function add_markdown_latex_assets() {
    if (is_singular()) {
        // 加载自定义 CSS
        wp_enqueue_style(
            'markdown-latex-style',
            get_template_directory_uri() . '/markdown-latex.css',
            array(),
            '1.0'
        );
        
        // 加载 MathJax（渲染 LaTeX 公式）
        wp_enqueue_script(
            'mathjax',
            'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml.js',
            array(),
            null,
            true
        );
        
        // 添加 MathJax 配置
wp_enqueue_script(
    'mathjax',
    'https://cdn.jsdelivr.net/npm/mathjax@2/MathJax.js?config=TeX-AMS_CHTML',
    array(),
    null,
    false
);
wp_add_inline_script('mathjax', '
    MathJax.Hub.Config({
        tex2jax: {
            inlineMath: [["$","$"],["\\(","\\)"]],
            displayMath: [["$$","$$"],["\\[","\\]"]],
            processEscapes: true
        },
        TeX: {
            extensions: ["AMSmath.js", "AMSsymbols.js"]
        }
    });
', 'before');
    }
}
add_action('wp_enqueue_scripts', 'add_markdown_latex_assets');

// 4. 为文章容器添加 markdown-content 类（便于 CSS 和 MathJax 定位）
function add_markdown_wrapper_class($classes) {
    if (is_singular()) {
        $classes[] = 'markdown-content';
    }
    return $classes;
}
add_filter('post_class', 'add_markdown_wrapper_class');

remove_filter('the_content', 'convert_markdown_content', 9);
remove_action('wp_enqueue_scripts', 'add_markdown_latex_assets');
remove_filter('post_class', 'add_markdown_wrapper_class');

function argon_markdown_editor_post_types(){
	return array('post', 'page');
}

function argon_markdown_editor_handles_post_type($post_type){
	return in_array($post_type, argon_markdown_editor_post_types(), true);
}

function argon_composite_body_class($classes){
	if (function_exists('argon_is_composite_page') && argon_is_composite_page()){
		$classes[] = 'argon-is-composite-page';
	}
	return $classes;
}
add_filter('body_class', 'argon_composite_body_class');

function argon_disable_block_editor_for_markdown($use_block_editor, $post_type){
	if (argon_markdown_editor_handles_post_type($post_type)){
		return false;
	}
	return $use_block_editor;
}
add_filter('use_block_editor_for_post_type', 'argon_disable_block_editor_for_markdown', 20, 2);

function argon_disable_block_editor_for_markdown_post($use_block_editor, $post){
	if (!empty($post) && argon_markdown_editor_handles_post_type($post -> post_type)){
		return false;
	}
	return $use_block_editor;
}
add_filter('use_block_editor_for_post', 'argon_disable_block_editor_for_markdown_post', 20, 2);

function argon_markdown_default_editor($editor){
	return 'html';
}
add_filter('wp_default_editor', 'argon_markdown_default_editor');

function argon_markdown_disable_visual_editor($can_richedit){
	if (!is_admin() || !function_exists('get_current_screen')){
		return $can_richedit;
	}
	$screen = get_current_screen();
	if (!empty($screen -> post_type) && argon_markdown_editor_handles_post_type($screen -> post_type)){
		return false;
	}
	return $can_richedit;
}
add_filter('user_can_richedit', 'argon_markdown_disable_visual_editor', 50);

function argon_markdown_mathjax_config(){
	return 'window.MathJax = window.MathJax || {tex:{inlineMath:[[\'$\',\'$\'],[\'\\\\(\',\'\\\\)\']],displayMath:[[\'$$\',\'$$\'],[\'\\\\[\',\'\\\\]\']],processEscapes:true,processEnvironments:true},options:{skipHtmlTags:[\'script\',\'noscript\',\'style\',\'textarea\',\'pre\',\'code\'],processHtmlClass:\'markdown-content|post-content\'}};';
}

function argon_markdown_admin_assets($hook){
	if (!in_array($hook, array('post.php', 'post-new.php'), true) || !function_exists('get_current_screen')){
		return;
	}
	$screen = get_current_screen();
	if (empty($screen -> post_type) || !argon_markdown_editor_handles_post_type($screen -> post_type)){
		return;
	}
	$markdown_editor_css = get_template_directory() . '/assets/css/argon-markdown-editor.css';
	$markdown_editor_js = get_template_directory() . '/assets/js/argon-markdown-editor.js';
	$markdown_editor_css_version = file_exists($markdown_editor_css) ? filemtime($markdown_editor_css) : $GLOBALS['theme_version'];
	$markdown_editor_js_version = file_exists($markdown_editor_js) ? filemtime($markdown_editor_js) : $GLOBALS['theme_version'];
	wp_enqueue_media();
	wp_enqueue_style('argon-easymde', 'https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.css', array(), '2.18.0');
	wp_enqueue_style('argon-markdown-editor-admin', get_template_directory_uri() . '/assets/css/argon-markdown-editor.css', array('argon-easymde'), $markdown_editor_css_version);
	wp_enqueue_script('argon-easymde', 'https://cdn.jsdelivr.net/npm/easymde@2.18.0/dist/easymde.min.js', array(), '2.18.0', true);
	wp_register_script('argon-markdown-editor-mathjax', 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml-full.js', array(), '3.2.2', true);
	wp_add_inline_script('argon-markdown-editor-mathjax', argon_markdown_mathjax_config(), 'before');
	wp_enqueue_script('argon-markdown-editor-mathjax');
	wp_enqueue_script('argon-markdown-editor-admin', get_template_directory_uri() . '/assets/js/argon-markdown-editor.js', array('jquery', 'media-editor', 'media-views'), $markdown_editor_js_version, true);
}
add_action('admin_enqueue_scripts', 'argon_markdown_admin_assets');

function argon_markdown_is_escaped($content, $position){
	$slashes = 0;
	for ($i = $position - 1; $i >= 0 && isset($content[$i]) && $content[$i] === '\\'; $i--){
		$slashes++;
	}
	return $slashes % 2 === 1;
}

function argon_markdown_store_math_block(&$math_blocks, $block){
	$key = 'ARGONMARKDOWNMATH' . count($math_blocks) . 'TOKEN';
	$math_blocks[$key] = $block;
	return $key;
}

function argon_markdown_find_unescaped_delimiter($content, $delimiter, $offset, $single_dollar = false){
	$length = strlen($content);
	$delimiter_length = strlen($delimiter);
	while ($offset < $length){
		$position = strpos($content, $delimiter, $offset);
		if ($position === false){
			return false;
		}
		if (!argon_markdown_is_escaped($content, $position)){
			if (!$single_dollar){
				return $position;
			}
			$previous = $position > 0 ? $content[$position - 1] : '';
			$next = ($position + 1) < $length ? $content[$position + 1] : '';
			if ($previous !== '$' && $next !== '$'){
				return $position;
			}
		}
		$offset = $position + $delimiter_length;
	}
	return false;
}

function argon_markdown_extract_math($content, &$math_blocks){
	$math_blocks = array();
	$result = '';
	$length = strlen($content);
	$i = 0;
	while ($i < $length){
		if (substr($content, $i, 2) === '$$' && !argon_markdown_is_escaped($content, $i)){
			$end = argon_markdown_find_unescaped_delimiter($content, '$$', $i + 2);
			if ($end !== false){
				$result .= argon_markdown_store_math_block($math_blocks, substr($content, $i, $end - $i + 2));
				$i = $end + 2;
				continue;
			}
		}
		if (substr($content, $i, 2) === '\\['){
			$end = strpos($content, '\\]', $i + 2);
			if ($end !== false){
				$result .= argon_markdown_store_math_block($math_blocks, substr($content, $i, $end - $i + 2));
				$i = $end + 2;
				continue;
			}
		}
		if (substr($content, $i, 2) === '\\('){
			$end = strpos($content, '\\)', $i + 2);
			if ($end !== false){
				$result .= argon_markdown_store_math_block($math_blocks, substr($content, $i, $end - $i + 2));
				$i = $end + 2;
				continue;
			}
		}
		if ($content[$i] === '$' && !argon_markdown_is_escaped($content, $i) && substr($content, $i, 2) !== '$$'){
			$end = argon_markdown_find_unescaped_delimiter($content, '$', $i + 1, true);
			if ($end !== false){
				$inside = substr($content, $i + 1, $end - $i - 1);
				if (trim($inside) !== ''){
					$result .= argon_markdown_store_math_block($math_blocks, substr($content, $i, $end - $i + 1));
					$i = $end + 1;
					continue;
				}
			}
		}
		$result .= $content[$i];
		$i++;
	}
	return $result;
}

function argon_markdown_restore_math($content, $math_blocks){
	if (empty($math_blocks)){
		return $content;
	}
	return str_replace(array_keys($math_blocks), array_values($math_blocks), $content);
}

function argon_convert_markdown_content($content) {
	if (is_admin() || !is_singular() || !class_exists('Parsedown')){
		return $content;
	}
	$math_blocks = array();
	$content = argon_markdown_extract_math($content, $math_blocks);
	$parsedown = new Parsedown();
	$parsedown -> setBreaksEnabled(false);
	$content = $parsedown -> text($content);
	return argon_markdown_restore_math($content, $math_blocks);
}
add_filter('the_content', 'argon_convert_markdown_content', 9);

function argon_markdown_latex_assets() {
	if (!is_singular()){
		return;
	}
	wp_enqueue_style(
		'markdown-latex-style',
		get_template_directory_uri() . '/markdown-latex.css',
		array(),
		$GLOBALS['theme_version']
	);
	if (get_option('argon_math_render', 'none') == 'none'){
		wp_register_script('argon-markdown-mathjax', 'https://cdn.jsdelivr.net/npm/mathjax@3/es5/tex-chtml-full.js', array(), '3.2.2', true);
		wp_add_inline_script('argon-markdown-mathjax', argon_markdown_mathjax_config(), 'before');
		wp_enqueue_script('argon-markdown-mathjax');
	}
}
add_action('wp_enqueue_scripts', 'argon_markdown_latex_assets');

function argon_add_markdown_wrapper_class($classes) {
	if (is_singular()) {
		$classes[] = 'markdown-content';
		$classes[] = 'tex2jax_process';
	}
	return $classes;
}
add_filter('post_class', 'argon_add_markdown_wrapper_class');

function argon_get_post_home_custom_preview($post_id = null) {
	$post_id = $post_id ? intval($post_id) : get_the_ID();
	if (!$post_id) {
		return "";
	}
	$custom_preview = get_post_meta($post_id, 'argon_home_preview', true);
	if (trim($custom_preview) === "") {
		return "";
	}
	return wpautop(wp_kses_post($custom_preview));
}

function argon_is_composite_page($post_id = null) {
	$post_id = $post_id ? intval($post_id) : get_queried_object_id();
	if (!$post_id || get_post_type($post_id) !== 'page') {
		return false;
	}
	return get_post_meta($post_id, 'argon_page_mode', true) === 'composite';
}

function argon_composite_page_meta_box($post) {
	wp_nonce_field('argon_composite_page_nonce_action', 'argon_composite_page_nonce');
	$page_mode = get_post_meta($post -> ID, 'argon_page_mode', true);
	$page_mode = $page_mode === 'composite' ? 'composite' : 'single';
	$visibility = get_post_meta($post -> ID, 'argon_composite_visibility', true);
	$visibility = argon_normalize_privacy_visibility($visibility);
	$allowed_ips = get_post_meta($post -> ID, 'argon_composite_allowed_ips', true);
	$password = get_post_meta($post -> ID, 'argon_composite_password', true);
	?>
		<div class="argon-composite-page-settings">
			<p>
				<label for="argon_page_mode"><strong>页面类型</strong></label>
				<select name="argon_page_mode" id="argon_page_mode" style="width:100%; margin-top:6px;">
					<option value="single" <?php selected($page_mode, 'single'); ?>>单页面</option>
					<option value="composite" <?php selected($page_mode, 'composite'); ?>>复合页面</option>
				</select>
			</p>
			<div id="argon_composite_page_options" style="<?php echo $page_mode === 'composite' ? '' : 'display:none;'; ?>">
				<p>
					<label for="argon_composite_visibility"><strong>页面开放程度</strong></label>
					<select name="argon_composite_visibility" id="argon_composite_visibility" class="widefat" style="margin-top:6px;">
						<option value="public" <?php selected($visibility, 'public'); ?>>公开</option>
						<option value="private" <?php selected($visibility, 'private'); ?>>私密，仅管理员/编辑可看</option>
						<option value="logged_in" <?php selected($visibility, 'logged_in'); ?>>登录后才能查看</option>
						<option value="ip_allowlist" <?php selected($visibility, 'ip_allowlist'); ?>>指定 IP 才能查看</option>
						<option value="password" <?php selected($visibility, 'password'); ?>>指定密码才能查看</option>
					</select>
				</p>
				<p id="argon_composite_allowed_ips_wrap" style="<?php echo $visibility === 'ip_allowlist' ? '' : 'display:none;'; ?>">
					<label for="argon_composite_allowed_ips"><strong>允许访问的 IP</strong></label>
					<textarea name="argon_composite_allowed_ips" id="argon_composite_allowed_ips" rows="3" class="widefat" placeholder="每行一个 IP"><?php echo esc_textarea($allowed_ips); ?></textarea>
				</p>
				<p id="argon_composite_password_wrap" style="<?php echo $visibility === 'password' ? '' : 'display:none;'; ?>">
					<label for="argon_composite_password"><strong>访问密码</strong></label>
					<input type="text" name="argon_composite_password" id="argon_composite_password" value="<?php echo esc_attr($password); ?>" class="widefat" autocomplete="off" placeholder="由你设置访问密码">
				</p>
			</div>
		</div>
		<script>
			(function(){
				var mode = document.getElementById('argon_page_mode');
				var options = document.getElementById('argon_composite_page_options');
				var bannerBox = document.getElementById('argon_composite_banner_meta_box');
				var visibility = document.getElementById('argon_composite_visibility');
				var ipWrap = document.getElementById('argon_composite_allowed_ips_wrap');
				var passwordWrap = document.getElementById('argon_composite_password_wrap');
				if (!mode || !options) {
					return;
				}
				function syncCompositeFields(){
					var isComposite = mode.value === 'composite';
					options.style.display = isComposite ? '' : 'none';
					if (bannerBox) {
						bannerBox.style.display = isComposite ? '' : 'none';
					}
				}
				mode.addEventListener('change', function(){
					syncCompositeFields();
				});
				syncCompositeFields();
				if (visibility) {
					function syncCompositePrivacyFields(){
						if (ipWrap) {
							ipWrap.style.display = visibility.value === 'ip_allowlist' ? '' : 'none';
						}
						if (passwordWrap) {
							passwordWrap.style.display = visibility.value === 'password' ? '' : 'none';
						}
					}
					visibility.addEventListener('change', syncCompositePrivacyFields);
					syncCompositePrivacyFields();
				}
			})();
		</script>
	<?php
}

function argon_composite_page_banner_meta_box($post) {
	$banner_background = get_post_meta($post -> ID, 'argon_composite_banner_background', true);
	$banner_attachment_id = intval(get_post_meta($post -> ID, 'argon_composite_banner_attachment_id', true));
	$banner_summary = get_post_meta($post -> ID, 'argon_composite_banner_summary', true);
	?>
		<div class="argon-composite-banner-fields">
			<div class="argon-composite-banner-field">
				<label for="argon_composite_banner_background"><strong>顶部 Banner 图片</strong></label>
				<div class="argon-composite-banner-image-row">
					<input type="hidden" name="argon_composite_banner_attachment_id" id="argon_composite_banner_attachment_id" value="<?php echo esc_attr($banner_attachment_id); ?>">
					<input type="url" name="argon_composite_banner_background" id="argon_composite_banner_background" value="<?php echo esc_attr($banner_background); ?>" class="widefat" placeholder="图片 URL，留空则使用页面特色图或全局背景">
					<button type="button" class="button argon-composite-page-image-select">上传/选择图片</button>
					<button type="button" class="button argon-composite-page-image-clear">清除</button>
				</div>
				<div class="argon-composite-banner-preview" id="argon_composite_banner_preview" style="<?php echo $banner_background !== '' ? '' : 'display:none;'; ?>">
					<img src="<?php echo esc_url($banner_background); ?>" alt="">
				</div>
			</div>
			<div class="argon-composite-banner-field">
				<label for="argon_composite_banner_summary"><strong>页面介绍 / 摘要</strong></label>
				<textarea name="argon_composite_banner_summary" id="argon_composite_banner_summary" rows="3" class="widefat" placeholder="显示在页面标题下方，留空则不显示"><?php echo esc_textarea($banner_summary); ?></textarea>
			</div>
		</div>
		<script>
			(function(){
				jQuery(function($){
					var box = $('#argon_composite_banner_meta_box');
					var title = $('#titlediv');
					if (box.length && title.length) {
						box.insertAfter(title);
					}
					var mode = $('#argon_page_mode');
					if (box.length && mode.length) {
						box.toggle(mode.val() === 'composite');
						mode.on('change', function(){
							box.toggle(mode.val() === 'composite');
						});
					}
				});
			})();
		</script>
	<?php
}

function argon_add_composite_page_meta_box() {
	add_meta_box('argon_composite_page_meta_box', '页面模式', 'argon_composite_page_meta_box', 'page', 'side', 'high');
	add_meta_box('argon_composite_banner_meta_box', '复合页面顶部设置', 'argon_composite_page_banner_meta_box', 'page', 'normal', 'high');
}
add_action('add_meta_boxes', 'argon_add_composite_page_meta_box');

function argon_save_composite_page_meta($post_id) {
	if (!isset($_POST['argon_composite_page_nonce']) || !wp_verify_nonce($_POST['argon_composite_page_nonce'], 'argon_composite_page_nonce_action')) {
		return;
	}
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	if (get_post_type($post_id) !== 'page' || !current_user_can('edit_page', $post_id)) {
		return;
	}

	$page_mode = isset($_POST['argon_page_mode']) && $_POST['argon_page_mode'] === 'composite' ? 'composite' : 'single';
	update_post_meta($post_id, 'argon_page_mode', $page_mode);

	if ($page_mode === 'composite') {
		update_post_meta($post_id, 'argon_composite_banner_background', isset($_POST['argon_composite_banner_background']) ? esc_url_raw($_POST['argon_composite_banner_background']) : '');
		update_post_meta($post_id, 'argon_composite_banner_attachment_id', isset($_POST['argon_composite_banner_attachment_id']) ? intval($_POST['argon_composite_banner_attachment_id']) : 0);
		update_post_meta($post_id, 'argon_composite_banner_summary', isset($_POST['argon_composite_banner_summary']) ? sanitize_textarea_field($_POST['argon_composite_banner_summary']) : '');
		$visibility = isset($_POST['argon_composite_visibility']) ? argon_normalize_privacy_visibility(sanitize_text_field(wp_unslash($_POST['argon_composite_visibility']))) : 'public';
		update_post_meta($post_id, 'argon_composite_visibility', $visibility);
		update_post_meta($post_id, 'argon_composite_allowed_ips', isset($_POST['argon_composite_allowed_ips']) ? sanitize_textarea_field(wp_unslash($_POST['argon_composite_allowed_ips'])) : '');
		update_post_meta($post_id, 'argon_composite_password', isset($_POST['argon_composite_password']) ? sanitize_text_field(wp_unslash($_POST['argon_composite_password'])) : '');
	}
}
add_action('save_post_page', 'argon_save_composite_page_meta');

function argon_save_post_composite_page_assignment($post_id, $page_id) {
	$page_id = intval($page_id);
	if ($page_id > 0 && argon_is_composite_page($page_id)) {
		update_post_meta($post_id, 'argon_parent_composite_page', $page_id);
	} else {
		delete_post_meta($post_id, 'argon_parent_composite_page');
	}
}

function argon_get_request_ip() {
	foreach (array('HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR') as $key) {
		if (empty($_SERVER[$key])) {
			continue;
		}
		$value = explode(',', sanitize_text_field(wp_unslash($_SERVER[$key])));
		$ip = trim($value[0]);
		if (filter_var($ip, FILTER_VALIDATE_IP)) {
			return $ip;
		}
	}
	return '';
}

function argon_ip_is_allowed_for_list($allowed_ips) {
	$allowed_ips = preg_split('/[\s,;]+/', $allowed_ips);
	$allowed_ips = array_filter(array_map('trim', $allowed_ips));
	$current_ip = argon_get_request_ip();
	return $current_ip !== '' && in_array($current_ip, $allowed_ips, true);
}

function argon_get_privacy_settings_for_content($post_id) {
	$post_id = intval($post_id);
	if (!$post_id) {
		return array('enabled' => false, 'visibility' => 'public', 'allowed_ips' => '', 'password' => '');
	}
	if (get_post_type($post_id) === 'post') {
		return array(
			'enabled' => true,
			'visibility' => argon_normalize_privacy_visibility(get_post_meta($post_id, 'argon_post_visibility', true)),
			'allowed_ips' => get_post_meta($post_id, 'argon_post_allowed_ips', true),
			'password' => get_post_meta($post_id, 'argon_post_password', true)
		);
	}
	if (get_post_type($post_id) === 'page' && (argon_is_composite_page($post_id) || get_post_meta($post_id, 'argon_composite_visibility', true) !== '')) {
		return array(
			'enabled' => true,
			'visibility' => argon_normalize_privacy_visibility(get_post_meta($post_id, 'argon_composite_visibility', true)),
			'allowed_ips' => get_post_meta($post_id, 'argon_composite_allowed_ips', true),
			'password' => get_post_meta($post_id, 'argon_composite_password', true)
		);
	}
	return array('enabled' => false, 'visibility' => 'public', 'allowed_ips' => '', 'password' => '');
}

function argon_privacy_password_cookie_name($post_id) {
	return 'argon_privacy_pass_' . intval($post_id);
}

function argon_privacy_password_token($post_id, $password) {
	return wp_hash(intval($post_id) . '|' . (string)$password . '|argon_privacy_password');
}

function argon_privacy_password_is_unlocked($post_id, $password) {
	if ($password === '') {
		return true;
	}
	$cookie_name = argon_privacy_password_cookie_name($post_id);
	if (!empty($_COOKIE[$cookie_name])) {
		setcookie($cookie_name, '', time() - HOUR_IN_SECONDS, COOKIEPATH ? COOKIEPATH : '/', COOKIE_DOMAIN, is_ssl(), true);
		unset($_COOKIE[$cookie_name]);
	}
	return false;
}

function argon_privacy_password_form($post_id, $has_error = false) {
	$title = get_the_title($post_id);
	$password_field_name = 'argon_privacy_password_' . wp_generate_password(12, false, false);
	ob_start();
	?>
		<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#eef2ff,#fff7ed);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#32325d;">
			<form method="post" autocomplete="off" data-form-type="other" style="width:min(420px,100%);background:rgba(255,255,255,.92);border:1px solid rgba(94,114,228,.18);border-radius:18px;padding:28px;box-shadow:0 24px 60px rgba(50,50,93,.16);">
				<h1 style="margin:0 0 10px;font-size:26px;line-height:1.3;">请输入访问密码</h1>
				<p style="margin:0 0 20px;color:#667085;">访问「<?php echo esc_html($title); ?>」需要密码。</p>
				<?php if ($has_error) { ?>
					<div style="margin-bottom:14px;padding:10px 12px;border-radius:10px;background:#fff1f2;color:#e11d48;font-weight:600;">密码不正确，请再试一次。</div>
				<?php } ?>
				<?php wp_nonce_field('argon_privacy_password_' . intval($post_id), 'argon_privacy_password_nonce'); ?>
				<input type="hidden" name="argon_privacy_post_id" value="<?php echo esc_attr($post_id); ?>">
				<input type="password" name="<?php echo esc_attr($password_field_name); ?>" autofocus autocomplete="new-password" autocapitalize="off" autocorrect="off" spellcheck="false" data-lpignore="true" data-1p-ignore="true" data-form-type="other" placeholder="访问密码" style="width:100%;height:46px;border:1px solid #d6dcff;border-radius:12px;padding:0 14px;font-size:16px;box-sizing:border-box;">
				<button type="submit" style="width:100%;margin-top:16px;height:46px;border:0;border-radius:12px;background:#5e72e4;color:#fff;font-size:16px;font-weight:700;cursor:pointer;">进入</button>
				<a href="<?php echo esc_url(home_url('/')); ?>" style="display:block;margin-top:14px;text-align:center;color:#667085;text-decoration:none;">返回首页</a>
			</form>
		</div>
	<?php
	return ob_get_clean();
}

function argon_oauth_login_required_page($post_id) {
	$title = get_the_title($post_id);
	$github_ready = function_exists('argon_comment_oauth_is_configured') && argon_comment_oauth_is_configured('github');
	$ur_ready = function_exists('argon_comment_oauth_is_configured') && argon_comment_oauth_is_configured('clogin');
	ob_start();
	?>
		<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#eef2ff,#fff7ed);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#32325d;">
			<div style="width:min(460px,100%);background:rgba(255,255,255,.94);border:1px solid rgba(94,114,228,.18);border-radius:20px;padding:30px;box-shadow:0 24px 60px rgba(50,50,93,.16);">
				<h1 style="margin:0 0 10px;font-size:26px;line-height:1.3;">需要登录后查看</h1>
				<p style="margin:0 0 22px;color:#667085;line-height:1.7;">访问“<?php echo esc_html($title); ?>”需要使用 GitHub 或 UR互联（微信）登录。</p>
				<?php if ($github_ready) { ?>
					<a href="<?php echo esc_url(argon_comment_oauth_login_url('github')); ?>" style="display:flex;align-items:center;justify-content:center;gap:10px;height:48px;border-radius:13px;background:#5e72e4;color:#fff;text-decoration:none;font-weight:800;margin-bottom:12px;">GitHub 登录</a>
				<?php } ?>
				<?php if ($ur_ready) { ?>
					<a href="<?php echo esc_url(argon_comment_oauth_login_url('clogin')); ?>" style="display:flex;align-items:center;justify-content:center;gap:10px;height:48px;border-radius:13px;border:1px solid #6d7ee8;color:#5e72e4;background:#fff;text-decoration:none;font-weight:800;margin-bottom:12px;">UR互联（微信）登录</a>
				<?php } ?>
				<?php if (!$github_ready && !$ur_ready) { ?>
					<div style="padding:12px 14px;border-radius:12px;background:#fff7ed;color:#c2410c;font-weight:700;">第三方登录尚未配置，请联系站点管理员。</div>
				<?php } ?>
				<a href="<?php echo esc_url(home_url('/')); ?>" style="display:block;margin-top:14px;text-align:center;color:#667085;text-decoration:none;">返回首页</a>
			</div>
		</div>
	<?php
	return ob_get_clean();
}

function argon_handle_privacy_password_submit($post_id, $password) {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['argon_privacy_post_id'])) {
		return false;
	}
	if (intval($_POST['argon_privacy_post_id']) !== intval($post_id)) {
		return false;
	}
	if (empty($_POST['argon_privacy_password_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['argon_privacy_password_nonce'])), 'argon_privacy_password_' . intval($post_id))) {
		wp_die(argon_privacy_password_form($post_id, true), '请输入访问密码', array('response' => 403));
	}
	$submitted_password = '';
	foreach ($_POST as $field_name => $field_value) {
		if (strpos((string)$field_name, 'argon_privacy_password_') === 0 && $field_name !== 'argon_privacy_password_nonce') {
			$submitted_password = sanitize_text_field(wp_unslash($field_value));
			break;
		}
	}
	if (hash_equals((string)$password, (string)$submitted_password)) {
		return true;
	}
	wp_die(argon_privacy_password_form($post_id, true), '请输入访问密码', array('response' => 403));
}

function argon_get_current_privacy_content_id() {
	if (is_admin() || is_feed() || is_robots() || is_trackback()) {
		return 0;
	}
	$post_id = intval(get_queried_object_id());
	if ($post_id && in_array(get_post_type($post_id), array('post', 'page'), true)) {
		return $post_id;
	}
	if (!empty($_SERVER['REQUEST_URI'])) {
		$scheme = is_ssl() ? 'https://' : 'http://';
		$host = !empty($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : wp_parse_url(home_url(), PHP_URL_HOST);
		$request_uri = strtok(sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])), '?');
		$url_post_id = url_to_postid($scheme . $host . $request_uri);
		if ($url_post_id && in_array(get_post_type($url_post_id), array('post', 'page'), true)) {
			return intval($url_post_id);
		}
	}
	return 0;
}

function argon_protect_private_content_access() {
	$post_id = argon_get_current_privacy_content_id();
	if (!$post_id) {
		return;
	}
	$settings = argon_get_privacy_settings_for_content($post_id);
	if (empty($settings['enabled']) || $settings['visibility'] === 'public') {
		return;
	}
	nocache_headers();
	if ($settings['visibility'] === 'logged_in') {
		$oauth_identity = function_exists('argon_get_comment_oauth_identity') ? argon_get_comment_oauth_identity() : false;
		if (!$oauth_identity) {
			wp_die(argon_oauth_login_required_page($post_id), '需要登录后查看', array('response' => 403));
		}
		return;
	}
	if ($settings['visibility'] === 'private') {
		if (!is_user_logged_in()) {
			auth_redirect();
		}
		if (!current_user_can('edit_post', $post_id)) {
			wp_die('这是私密内容，当前账号没有访问权限。', '访问受限', array('response' => 403));
		}
	}
	if ($settings['visibility'] === 'ip_allowlist' && !argon_ip_is_allowed_for_list($settings['allowed_ips'])) {
		wp_die('当前 IP 不在允许访问范围内。', '访问受限', array('response' => 403));
	}
	if ($settings['visibility'] === 'password') {
		$password = (string)$settings['password'];
		if ($password === '') {
			return;
		}
		$password_accepted_for_current_request = argon_handle_privacy_password_submit($post_id, $password);
		if (!$password_accepted_for_current_request && !argon_privacy_password_is_unlocked($post_id, $password)) {
			wp_die(argon_privacy_password_form($post_id), '请输入访问密码', array('response' => 403));
		}
	}
}
add_action('template_redirect', 'argon_protect_private_content_access');

function argon_content_is_restricted_for_navigation($post_id) {
	$post_id = intval($post_id);
	if (!$post_id) {
		return false;
	}
	$post = get_post($post_id);
	if (!$post) {
		return false;
	}
	if ($post -> post_status === 'private' || get_post_field('post_password', $post_id) !== '') {
		return true;
	}
	return false;
}

function argon_hide_restricted_nav_menu_items($items, $args) {
	if (is_admin() || empty($items) || !is_array($items)) {
		return $items;
	}
	$hidden_item_ids = array();
	foreach ($items as $item) {
		$post_id = 0;
		if (!empty($item -> object_id) && in_array($item -> object, array('page', 'post'), true)) {
			$post_id = intval($item -> object_id);
		} elseif (!empty($item -> url)) {
			$post_id = intval(url_to_postid($item -> url));
		}
		if ($post_id && argon_content_is_restricted_for_navigation($post_id)) {
			$hidden_item_ids[] = intval($item -> ID);
		}
	}
	if (empty($hidden_item_ids)) {
		return $items;
	}
	$hidden_item_ids = array_unique($hidden_item_ids);
	$changed = true;
	while ($changed) {
		$changed = false;
		foreach ($items as $item) {
			if (!in_array(intval($item -> ID), $hidden_item_ids, true) && in_array(intval($item -> menu_item_parent), $hidden_item_ids, true)) {
				$hidden_item_ids[] = intval($item -> ID);
				$changed = true;
			}
		}
		$hidden_item_ids = array_unique($hidden_item_ids);
	}
	return array_values(array_filter($items, function($item) use ($hidden_item_ids) {
		return !in_array(intval($item -> ID), $hidden_item_ids, true);
	}));
}
add_filter('wp_nav_menu_objects', 'argon_hide_restricted_nav_menu_items', 20, 2);

function argon_get_composite_pages_for_settings() {
	return get_posts(array(
		'post_type' => 'page',
		'post_status' => array('publish', 'draft', 'pending', 'private'),
		'numberposts' => -1,
		'orderby' => 'menu_order title',
		'order' => 'ASC',
		'meta_key' => 'argon_page_mode',
		'meta_value' => 'composite'
	));
}

function argon_add_composite_page_settings_page() {
	add_theme_page('复合页面设置', '复合页面设置', 'edit_pages', 'argon-composite-pages', 'argon_render_composite_page_settings');
}
add_action('admin_menu', 'argon_add_composite_page_settings_page');

function argon_composite_page_settings_assets($hook) {
	if ($hook !== 'appearance_page_argon-composite-pages') {
		return;
	}
	wp_enqueue_media();
}
add_action('admin_enqueue_scripts', 'argon_composite_page_settings_assets');

function argon_render_composite_page_settings() {
	if (!current_user_can('edit_pages')) {
		return;
	}

	$composite_pages = argon_get_composite_pages_for_settings();
	if (!empty($_POST['argon_composite_page_settings_nonce']) && wp_verify_nonce($_POST['argon_composite_page_settings_nonce'], 'argon_composite_page_settings_action')) {
		$posted_pages = isset($_POST['argon_composite_pages']) && is_array($_POST['argon_composite_pages']) ? $_POST['argon_composite_pages'] : array();
		foreach ($composite_pages as $composite_page) {
			$page_id = intval($composite_page -> ID);
			$page_settings = isset($posted_pages[$page_id]) && is_array($posted_pages[$page_id]) ? $posted_pages[$page_id] : array();
			update_post_meta($page_id, 'argon_composite_banner_background', isset($page_settings['banner_background']) ? esc_url_raw($page_settings['banner_background']) : '');
			update_post_meta($page_id, 'argon_composite_banner_attachment_id', isset($page_settings['banner_attachment_id']) ? intval($page_settings['banner_attachment_id']) : 0);
			update_post_meta($page_id, 'argon_composite_banner_summary', isset($page_settings['banner_summary']) ? sanitize_textarea_field($page_settings['banner_summary']) : '');
		}
		echo '<div class="notice notice-success is-dismissible"><p>复合页面设置已保存。</p></div>';
		$composite_pages = argon_get_composite_pages_for_settings();
	}
	?>
		<div class="wrap argon-composite-admin">
			<h1>复合页面设置</h1>
			<p>这里专门管理所有复合页面的 Banner 背景图和 Banner 摘要。页面标题直接使用页面自己的标题。</p>
			<?php if (empty($composite_pages)) : ?>
				<div class="notice notice-info"><p>当前还没有复合页面。请先新建或编辑一个页面，在「页面模式」里选择「复合页面」并保存。</p></div>
			<?php else : ?>
				<form method="post">
					<?php wp_nonce_field('argon_composite_page_settings_action', 'argon_composite_page_settings_nonce'); ?>
					<div class="argon-composite-admin-list">
						<?php foreach ($composite_pages as $composite_page) : ?>
							<?php
								$page_id = intval($composite_page -> ID);
								$banner_background = get_post_meta($page_id, 'argon_composite_banner_background', true);
								$banner_attachment_id = intval(get_post_meta($page_id, 'argon_composite_banner_attachment_id', true));
								$banner_summary = get_post_meta($page_id, 'argon_composite_banner_summary', true);
							?>
							<section class="argon-composite-admin-card">
								<div class="argon-composite-admin-head">
									<h2><?php echo esc_html(get_the_title($page_id)); ?></h2>
									<a href="<?php echo esc_url(get_edit_post_link($page_id)); ?>">编辑页面</a>
								</div>
								<p class="description">文章归属方式：写文章时在右侧「归属页面」中选择「<?php echo esc_html(get_the_title($page_id)); ?>」。</p>
								<table class="form-table" role="presentation">
									<tr>
										<th scope="row"><label for="argon_composite_banner_background_<?php echo esc_attr($page_id); ?>">Banner 背景图</label></th>
										<td>
											<input type="hidden" class="argon-composite-banner-attachment-id" name="argon_composite_pages[<?php echo esc_attr($page_id); ?>][banner_attachment_id]" value="<?php echo esc_attr($banner_attachment_id); ?>">
											<input type="url" class="regular-text argon-composite-banner-input" id="argon_composite_banner_background_<?php echo esc_attr($page_id); ?>" name="argon_composite_pages[<?php echo esc_attr($page_id); ?>][banner_background]" value="<?php echo esc_attr($banner_background); ?>" placeholder="图片 URL，留空则使用页面特色图或全局背景">
											<button type="button" class="button argon-composite-banner-select">选择图片</button>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="argon_composite_banner_summary_<?php echo esc_attr($page_id); ?>">Banner 摘要</label></th>
										<td><textarea class="large-text" rows="3" id="argon_composite_banner_summary_<?php echo esc_attr($page_id); ?>" name="argon_composite_pages[<?php echo esc_attr($page_id); ?>][banner_summary]" placeholder="显示在页面标题下方，留空则不显示"><?php echo esc_textarea($banner_summary); ?></textarea></td>
									</tr>
								</table>
							</section>
						<?php endforeach; ?>
					</div>
					<?php submit_button('保存复合页面设置'); ?>
				</form>
			<?php endif; ?>
		</div>
		<style>
			.argon-composite-admin-list { max-width: 980px; }
			.argon-composite-admin-card { margin: 18px 0; padding: 18px 20px; background: #fff; border: 1px solid #dcdcde; border-radius: 8px; box-shadow: 0 2px 8px rgba(15, 23, 42, .04); }
			.argon-composite-admin-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
			.argon-composite-admin-head h2 { margin: 0; }
			.argon-composite-admin-card .form-table { margin-top: 8px; }
			.argon-composite-admin-card .regular-text { max-width: 520px; width: 100%; }
		</style>
		<script>
			(function($){
				$('.argon-composite-banner-select').on('click', function(){
					var button = $(this);
					var input = button.siblings('.argon-composite-banner-input');
					var attachmentInput = button.siblings('.argon-composite-banner-attachment-id');
					var frame = wp.media({
						title: '选择 Banner 背景图',
						button: { text: '使用这张图片' },
						library: { type: 'image' },
						multiple: false
					});
					frame.on('select', function(){
						var attachment = frame.state().get('selection').first().toJSON();
						input.val(attachment.url).trigger('change');
						attachmentInput.val(attachment.id || '');
					});
					frame.open();
				});
			})(jQuery);
		</script>
	<?php
}

function argon_composite_page_banner_title($title) {
	$page_id = get_queried_object_id();
	if (!argon_is_composite_page($page_id)) {
		return $title;
	}
	return get_the_title($page_id);
}
add_filter('argon_banner_title', 'argon_composite_page_banner_title');

function argon_composite_page_banner_subtitle($subtitle) {
	$page_id = get_queried_object_id();
	if (!argon_is_composite_page($page_id)) {
		return $subtitle;
	}
	$summary = get_post_meta($page_id, 'argon_composite_banner_summary', true);
	return $summary !== '' ? $summary : '';
}
add_filter('argon_banner_subtitle', 'argon_composite_page_banner_subtitle');

function argon_get_composite_banner_background_url($page_id) {
	$page_id = intval($page_id);
	if (!$page_id) {
		return '';
	}
	$custom_background = trim((string)get_post_meta($page_id, 'argon_composite_banner_background', true));
	if ($custom_background !== '') {
		if (is_numeric($custom_background)) {
			$attachment_url = wp_get_attachment_image_url(intval($custom_background), 'full');
			if ($attachment_url) {
				return $attachment_url;
			}
		}
		return esc_url_raw($custom_background);
	}
	$attachment_id = intval(get_post_meta($page_id, 'argon_composite_banner_attachment_id', true));
	if ($attachment_id > 0) {
		$attachment_url = wp_get_attachment_image_url($attachment_id, 'full');
		if ($attachment_url) {
			return $attachment_url;
		}
	}
	$thumbnail = get_the_post_thumbnail_url($page_id, 'full');
	return $thumbnail ? $thumbnail : '';
}

function argon_composite_page_banner_background($url) {
	$page_id = get_queried_object_id();
	if (!argon_is_composite_page($page_id)) {
		return $url;
	}
	$composite_background = argon_get_composite_banner_background_url($page_id);
	return $composite_background !== '' ? $composite_background : $url;
}
add_filter('argon_banner_background_url', 'argon_composite_page_banner_background');
