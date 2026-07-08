<?php
$composite_page_id = get_the_ID();
$composite_category = intval(get_post_meta($composite_page_id, 'argon_composite_category', true));
$composite_posts_per_page = intval(get_post_meta($composite_page_id, 'argon_composite_posts_per_page', true));
if ($composite_posts_per_page <= 0) {
	$composite_posts_per_page = intval(get_option('posts_per_page', 10));
}
$composite_paged = max(1, intval(get_query_var('paged')), intval(get_query_var('page')));

$composite_args = array(
	'post_type' => 'post',
	'post_status' => 'publish',
	'posts_per_page' => $composite_posts_per_page,
	'paged' => $composite_paged,
	'ignore_sticky_posts' => false
);
if ($composite_category > 0) {
	$composite_args['cat'] = $composite_category;
}

$page_intro = trim(get_the_content());
if ($page_intro !== '') {
?>
	<article class="post post-full card bg-white shadow-sm border-0 argon-composite-page-intro" id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<div class="post-content" id="post_content">
			<?php the_content(); ?>
		</div>
	</article>
<?php
}

$composite_query = new WP_Query($composite_args);
if ($composite_query -> have_posts()) :
	while ($composite_query -> have_posts()) :
		$composite_query -> the_post();
		get_template_part('template-parts/content-preview', get_option('argon_article_list_layout', '1'));
	endwhile;

	$big = 999999999;
	$pagination_links = paginate_links(array(
		'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
		'format' => '?paged=%#%',
		'current' => $composite_paged,
		'total' => $composite_query -> max_num_pages,
		'type' => 'array',
		'prev_text' => '<i class="fa fa-angle-left" aria-hidden="true"></i>',
		'next_text' => '<i class="fa fa-angle-right" aria-hidden="true"></i>'
	));
	if (!empty($pagination_links) && is_array($pagination_links)) {
		echo '<nav class="argon-composite-pagination"><ul class="pagination">';
		foreach ($pagination_links as $pagination_link) {
			$is_current = strpos($pagination_link, 'current') !== false;
			$pagination_link = str_replace('page-numbers', 'page-link', $pagination_link);
			echo '<li class="page-item' . ($is_current ? ' active' : '') . '">' . $pagination_link . '</li>';
		}
		echo '</ul></nav>';
	}
	wp_reset_postdata();
else :
	wp_reset_postdata();
?>
	<article class="post card bg-white shadow-sm border-0">
		<div class="post-content">
			<p>这个复合页面暂时没有可展示的文章。</p>
		</div>
	</article>
<?php endif; ?>
