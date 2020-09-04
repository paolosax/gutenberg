<?php

/**
 * Print a wpInjectStylesheet function in <head>.
 * This will be used by blocks to print their styles on render.
 */
function gutenberg_print_inject_stylesheet_script() {
	if ( defined ( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		?>
		<script>
		function wpEnqueueStyle( handle, src, deps, ver, media ) {
			var style = document.createElement( 'link' );
			style.id = handle + '-css';
			style.rel = 'stylesheet';
			style.href = src;
			if ( ver ) {
				style.href += 0 < style.href.indexOf( '?' ) ? '&ver=' + ver : '?ver=' + ver;
			}
			style.media = media ? media : 'all';
			document.getElementsByTagName( 'head' )[ 0 ].appendChild( style );
		}
		</script>
		<?php
	} else {
		?>
		<script>function wpEnqueueStyle(e,n,t,l,u){var c=document.createElement("link");c.id=e+"-css";c.rel="stylesheet";c.href=n;if(l){c.href+=0<c.href.indexOf("?")?"&ver="+l:"?ver="+l}c.media=u?u:"all";document.getElementsByTagName("head")[0].appendChild(c)}</script>
		<?php
	}
}
add_action( 'wp_head', 'gutenberg_print_inject_stylesheet_script', 1 );

/**
 * Injects a JS call to print the stylesheet for a block.
 *
 * @param string $block_content The block content about to be appended.
 * @param array  $block         The full block, including name and attributes.
 *
 * @return string
 */
function gutenberg_inject_block_stylesheet_loading_script( $block_content, $block ) {
	// We're using a global var to avoid adding the same thing multiple times.
	global $block_styles_injected_scripts;
	if ( ! $block_styles_injected_scripts ) {
		$block_styles_injected_scripts = array();
	}

	// Check if we've processed this block before or not.
	if ( isset( $block['blockName'] ) && ! in_array( $block['blockName'], $block_styles_injected_scripts ) ) {

		// Add the script.
		gutenberg_the_block_stylesheet_loading_script( $block['blockName'] );

		// Add the block-name to our global.
		$block_styles_injected_scripts[] = $block['blockName'];
	}
	return $block_content;
}
add_filter( 'render_block', 'gutenberg_inject_block_stylesheet_loading_script', 10, 2 );

/**
 * Prints the stylesheet injection script for a single block.
 *
 * @param string $block_name The block-name.
 *
 * @return void
 */
function gutenberg_the_block_stylesheet_loading_script( $block_name ) {

	// Get an array of stylesheets for this block.
	$styles = gutenberg_get_block_stylesheet_urls( $block_name );

	// Loop styles and inject them in <head>.
	foreach ( $styles as $style ) {
		$style = wp_parse_args(
			$style,
			[
				'handle' => '',
				'src'    => '',
				'ver'    => false,
				'media'  => 'all',
			]
		);
		echo "<script>wpEnqueueStyle('{$style['handle']}', '{$style['src']}', [], '{$style['ver']}', '{$style['media']}')</script>";
	}
}

/**
 * Get an array of stylesheet URLs for a specific block.
 *
 * @param string $block_name The block-name.
 *
 * @return array
 */
function gutenberg_get_block_stylesheet_urls( $block_name ) {

	// An array of stylesheets per block-type.
	$stylesheets = array(
		'core/paragraph' => array(
			array(
				'handle' => 'core-paragraph-block-styles',
				'src'    => '/test.css',
				'ver'    => time(),
				'media'  => 'all',
			),
		),
	);

	/**
	 * Filter collection of stylesheets per block-type.
	 *
	 * @since 5.5.0
	 *
	 * @param array $stylesheets An array of stylesheets per block-type.
	 */
	$stylesheets = apply_filters( 'block_styles_urls', $stylesheets );

	return isset( $stylesheets[ $block_name ] ) ? $stylesheets[ $block_name ] : array();
}
