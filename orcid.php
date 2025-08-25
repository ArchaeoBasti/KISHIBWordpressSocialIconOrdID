<?php
/**
 * Plugin Name: KIŠIB Social Icons – ORCID
 * Description: Fügt ORCID als neuen Dienst im Social-Icons-Block hinzu.
 * Version: 1.3.0
 * Author: Dr. Sebastian Hageneuer
 */

if ( ! defined( 'ABSPATH' ) ) exit;

add_action( 'enqueue_block_editor_assets', function() {
	wp_enqueue_script(
		'social-icons-orcid-variation',
		plugins_url( 'orcid-variation.js', __FILE__ ),
		[ 'wp-blocks', 'wp-dom-ready', 'wp-element' ],
		filemtime( __DIR__ . '/orcid-variation.js' )
	);
});

add_action( 'enqueue_block_editor_assets', function() {
	wp_enqueue_style(
		'social-icons-orcid-editor-style',
		plugins_url( 'orcid-editor.css', __FILE__ ),
		[],
		filemtime( __DIR__ . '/orcid-editor.css' )
	);
});

add_action( 'wp_enqueue_scripts', function() {
	wp_enqueue_style(
		'social-icons-orcid-frontend-style',
		plugins_url( 'orcid.css', __FILE__ ),
		[],
		filemtime( __DIR__ . '/orcid.css' )
	);
});

add_filter( 'render_block', function( $content, $block ) {
	if ( 'core/social-link' !== ( $block['blockName'] ?? '' ) ) return $content;

	// Trigger über URL, damit es auch greift, wenn attrs.service mal fehlt
	if ( false === stripos( $content, 'orcid.org' ) ) return $content;

	$svg_path = __DIR__ . '/orcid.svg';
	if ( ! file_exists( $svg_path ) ) return $content;

	$svg = file_get_contents( $svg_path );
	if ( ! $svg ) return $content;

	// XML-Header entfernen, falls vorhanden
	$svg = preg_replace( '/^\xEF\xBB\xBF|<\?xml[^>]*\?>/i', '', $svg );

	// Ersetze das erste <svg> im Link (das ist das Core-Icon)
	return preg_replace( '#<svg\b[^>]*>.*?</svg>#si', $svg, $content, 1 );
}, 10, 2 );

add_action( 'enqueue_block_editor_assets', function() {
    $icon_url = plugins_url( 'orcid.svg', __FILE__ );

    wp_register_style( 'social-icons-orcid-editor-style', false, [], null );

    $css = <<<CSS
/* --- Gutenberg Editor: doppeltes Icon verhindern & ORCID korrekt zeigen --- */

/* Core-Icon im Editor ausblenden (das nackte <svg> direkt im Anchor/Button) */
.editor-styles-wrapper li.wp-social-link-orcid .wp-block-social-link-anchor > svg,
.block-editor-writing-flow li.wp-social-link-orcid .wp-block-social-link-anchor > svg,
li.wp-social-link-orcid .wp-block-social-link-anchor > svg {
  display: none !important;
}

.editor-styles-wrapper li.wp-social-link-orcid .wp-block-social-link-anchor::before,
.block-editor-writing-flow li.wp-social-link-orcid .wp-block-social-link-anchor::before,
li.wp-social-link-orcid .wp-block-social-link-anchor::before {
  content: "";
  display: inline-block;
	width: 1em;
	height: 1em;
  background: currentColor;
  -webkit-mask: url("{$icon_url}") no-repeat center / contain;
          mask: url("{$icon_url}") no-repeat center / contain;
}

CSS;

    wp_add_inline_style( 'social-icons-orcid-editor-style', $css );
    wp_enqueue_style( 'social-icons-orcid-editor-style' );
});
