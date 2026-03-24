<?php
/**
 * Front page template.
 *
 * Renders the Eternal Labs homepage, starting with the hero section.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#front-page-display
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

get_header();
?>

<?php get_template_part( 'template-parts/homepage-hero' ); ?>

<main id="primary" class="site-main">
	<?php
	while ( have_posts() ) {
		the_post();
		get_template_part( 'template-parts/content/entry', get_post_type() );
	}
	?>
</main>

<?php
get_footer();
