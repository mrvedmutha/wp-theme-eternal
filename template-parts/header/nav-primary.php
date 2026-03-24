<?php
/**
 * Template part: Primary navigation (left side of header).
 *
 * Outputs the primary WordPress registered menu.
 * Menu location: 'primary' (registered by Nav_Menus\Component).
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

if ( ! has_nav_menu( 'primary' ) ) {
	return;
}
?>
<nav
	id="site-navigation"
	class="header-nav header-nav--primary"
	aria-label="<?php esc_attr_e( 'Primary menu', 'wp-rig' ); ?>"
>
	<?php
	wp_nav_menu(
		array(
			'theme_location'  => 'primary',
			'menu_id'         => 'primary-menu',
			'menu_class'      => 'header-nav__list',
			'container'       => false,
			'depth'           => 2,
			'link_before'     => '<span class="header-nav__link-text">',
			'link_after'      => '</span>',
			'fallback_cb'     => false,
		)
	);
	?>
</nav>
