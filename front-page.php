<?php
/**
 * Front page template.
 *
 * Renders the Eternal Labs homepage via Gutenberg blocks.
 * Add the Homepage Hero block (and any other sections) directly
 * in Pages → Home using the block editor.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#front-page-display
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

get_header();
?>

<main id="primary" class="site-main">
	<?php
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	?>
</main>

<?php
get_footer();
