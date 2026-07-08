<?php get_header(); ?>

<div class="page-information-card-container"></div>

<?php get_sidebar(); ?>

<?php $argon_is_composite_page = function_exists('argon_is_composite_page') && argon_is_composite_page(get_queried_object_id()); ?>
<div id="primary" class="content-area">
	<main id="main" class="site-main<?php echo $argon_is_composite_page ? ' article-list article-list-composite' : ''; ?>" role="main">
		<?php
		while ( have_posts() ) :
			the_post();

			if ($argon_is_composite_page) {
				get_template_part( 'template-parts/content', 'composite-page' );
				continue;
			}

			get_template_part( 'template-parts/content', 'page' );

			if (get_option("argon_show_sharebtn") != 'false') {
				get_template_part( 'template-parts/share' );
			}

			if (comments_open() || get_comments_number()) {
				comments_template();
			}

		endwhile;
		?>

<?php get_footer(); ?>
