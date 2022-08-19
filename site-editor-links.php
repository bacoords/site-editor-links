<?php
/**
 * Site Editor Admin Links
 *
 * @package SiteEditorLinks
 * @author  Brian Coords
 *
 * @wordpress-plugin
 * Plugin Name:       Site Editor Admin Links
 * Description:       Show links to edit currently visible block templates in the admin bar.
 * Version:           0.0.1
 * Requires at least: 6.0
 * Author:            Brian Coords
 * Author URI:        https://www.briancoords.com
 */

/**
 * Renders our admin bar nodes.
 *
 * @since 0.0.1
 *
 * @return void
 */
function bc_site_editor_admin_bar_render() {
	if ( ! current_theme_supports( 'block-templates' ) || is_admin() ) {
		return;
	}
	global $wp_admin_bar;
	global $_wp_current_template_content;

	$base_url      = admin_url( 'site-editor.php' );
	$main_template = bc_false_template_loader();
	if ( $main_template ) {
		$url = add_query_arg( 'postType', 'wp_template', $base_url );
		$url = add_query_arg( 'postId', rawurlencode( $main_template->theme . '//' . $main_template->slug ), $url );
		$wp_admin_bar->add_node(
			array(
				'parent' => 'site-editor',
				'id'     => 'bc_site_editor_links_template',
				'title'  => $main_template->theme . '/' . $main_template->slug,
				'href'   => $url,
			)
		);
	}

	$blocks = parse_blocks( $_wp_current_template_content );
	if ( $blocks ) {
		foreach ( $blocks as $i => $block ) {
			if ( 'core/template-part' === $block['blockName'] ) {
				$url = add_query_arg( 'postType', 'wp_template_part', $base_url );
				$url = add_query_arg( 'postId', rawurlencode( $block['attrs']['theme'] . '//' . $block['attrs']['slug'] ), $url );
				$wp_admin_bar->add_node(
					array(
						'parent' => 'site-editor',
						'id'     => 'bc_site_editor_links_' . $i,
						'title'  => $block['attrs']['theme'] . '/' . $block['attrs']['slug'],
						'href'   => $url,
					)
				);
			}
		}
	}
}
add_action( 'admin_bar_menu', 'bc_site_editor_admin_bar_render', 99 );




/**
 * A fake function to mimic what happens in wp-includes/template-loader.php
 *
 * @since 0.0.1
 *
 * @return object
 */
function bc_false_template_loader() {
	$tag_templates = array(
		'is_embed'             => 'get_embed_template',
		'is_404'               => 'get_404_template',
		'is_search'            => 'get_search_template',
		'is_front_page'        => 'get_front_page_template',
		'is_home'              => 'get_home_template',
		'is_privacy_policy'    => 'get_privacy_policy_template',
		'is_post_type_archive' => 'get_post_type_archive_template',
		'is_tax'               => 'get_taxonomy_template',
		'is_attachment'        => 'get_attachment_template',
		'is_singular'          => 'get_singular_template',
		'is_single'            => 'get_single_template',
		'is_page'              => 'get_page_template',
		'is_category'          => 'get_category_template',
		'is_tag'               => 'get_tag_template',
		'is_author'            => 'get_author_template',
		'is_date'              => 'get_date_template',
		'is_archive'           => 'get_archive_template',
	);
	$template      = false;

	// Loop through each of the template conditionals, and find the appropriate template file.
	foreach ( $tag_templates as $tag => $template_getter ) {
		if ( call_user_func( $tag ) ) {
			$template = call_user_func( $template_getter );
			$type     = str_replace( 'is_', '', $tag );
		}
	}

	if ( ! $template ) {
		$template = get_index_template();
	}

	$templates = array( "{$type}.php" );
	$templates = apply_filters( "{$type}_template_hierarchy", $templates );
	$template  = locate_template( $templates );
	return resolve_block_template( $type, $templates, $template );
}
