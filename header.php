<?php
/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package wp_rig
 */

namespace WP_Rig\WP_Rig;

?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1">
	<link rel="profile" href="http://gmpg.org/xfn/11">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div id="page" class="site">
	<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'wp-rig' ); ?></a>

	<header id="masthead" class="site-header">

		<?php /* Desktop left — Primary navigation (hidden on mobile) */ ?>
		<?php get_template_part( 'template-parts/header/nav-primary' ); ?>

		<?php /* Mobile left — Hamburger + Search (hidden on desktop) */ ?>
		<div class="header-left">
			<button
				class="header-hamburger"
				id="header-hamburger"
				aria-label="<?php esc_attr_e( 'Open menu', 'wp-rig' ); ?>"
				aria-expanded="false"
				aria-controls="site-sidebar"
				type="button"
			>
				<svg width="25" height="24" viewBox="0 0 25 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M1.66602 6H23.671Z" fill="#3E3E47"/>
					<path d="M1.66602 6H23.671" stroke="currentColor"/>
					<path d="M1.66602 18H23.671Z" fill="#3E3E47"/>
					<path d="M1.66602 18H23.671" stroke="currentColor"/>
					<path d="M1.66602 12H23.671Z" fill="#3E3E47"/>
					<path d="M1.66602 12H23.671" stroke="currentColor"/>
				</svg>
			</button>
			<button
				class="header-utility__btn header-search-trigger header-search--mobile"
				aria-label="<?php esc_attr_e( 'Open search', 'wp-rig' ); ?>"
				aria-expanded="false"
				type="button"
			>
				<svg class="header-utility__icon" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true" focusable="false">
					<circle cx="8.5" cy="8.5" r="5.5" stroke="currentColor" stroke-width="1.5"/>
					<path d="M13 13L17.5 17.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
				</svg>
			</button>
		</div>

		<?php /* Centre — Logo / branding */ ?>
		<?php get_template_part( 'template-parts/header/branding' ); ?>

		<?php /* Right — User icon + Cart icon */ ?>
		<?php get_template_part( 'template-parts/header/nav-utility' ); ?>

	</header><!-- #masthead -->

	<?php /* Search megamenu — full-width overlay, slides below header */ ?>
	<?php get_template_part( 'template-parts/header/search-megamenu' ); ?>

	<?php /* Full-screen sidebar overlay */ ?>
	<div class="site-sidebar__overlay" id="site-sidebar-overlay" aria-hidden="true"></div>

	<?php /* Sidebar panel */ ?>
	<aside
		id="site-sidebar"
		class="site-sidebar"
		aria-hidden="true"
		aria-label="<?php esc_attr_e( 'Main menu', 'wp-rig' ); ?>"
	>
		<div class="site-sidebar__header">
			<span class="site-sidebar__title"><?php esc_html_e( 'Menu', 'wp-rig' ); ?></span>
			<button
				class="site-sidebar__close"
				id="site-sidebar-close"
				aria-label="<?php esc_attr_e( 'Close menu', 'wp-rig' ); ?>"
				type="button"
			>
				<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false">
					<path d="M14.1405 13.6099C14.2109 13.6803 14.2504 13.7757 14.2504 13.8752C14.2504 13.9747 14.2109 14.0702 14.1405 14.1405C14.0702 14.2109 13.9747 14.2504 13.8752 14.2504C13.7757 14.2504 13.6803 14.2109 13.6099 14.1405L7.12521 7.65583L0.640521 14.1405C0.570156 14.2109 0.47472 14.2504 0.375208 14.2504C0.275697 14.2504 0.180261 14.2109 0.109896 14.1405C0.0395309 14.0702 1.96161e-09 13.9747 0 13.8752C-1.96161e-09 13.7757 0.0395306 13.6803 0.109896 13.6099L6.59458 7.12521L0.109896 0.640521C0.0395306 0.570156 0 0.47472 0 0.375208C0 0.275697 0.0395306 0.180261 0.109896 0.109896C0.180261 0.0395306 0.275697 0 0.375208 0C0.47472 0 0.570156 0.0395306 0.640521 0.109896L7.12521 6.59458L13.6099 0.109896C13.6447 0.0750545 13.6861 0.0474169 13.7316 0.0285609C13.7771 0.00970488 13.8259 9.7129e-10 13.8752 0C13.9245 -9.71289e-10 13.9733 0.00970488 14.0188 0.0285609C14.0643 0.0474169 14.1057 0.0750545 14.1405 0.109896C14.1754 0.144737 14.203 0.1861 14.2219 0.231622C14.2407 0.277145 14.2504 0.325935 14.2504 0.375208C14.2504 0.424482 14.2407 0.473272 14.2219 0.518795C14.203 0.564317 14.1754 0.60568 14.1405 0.640521L7.65583 7.12521L14.1405 13.6099Z" fill="currentColor"/>
				</svg>
			</button>
		</div>
		<nav class="site-sidebar__nav" aria-label="<?php esc_attr_e( 'Sidebar menu', 'wp-rig' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'menu_id'        => 'sidebar-menu',
					'menu_class'     => 'site-sidebar__menu',
					'container'      => false,
					'depth'          => 2,
					'fallback_cb'    => false,
				)
			);
			?>
		</nav>
	</aside>
