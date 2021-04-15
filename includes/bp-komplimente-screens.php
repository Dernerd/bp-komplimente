<?php
/**
 * Functions related to frontend content display.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Loads template for the user komplimente tab.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 */
function bp_komplimente_screen_komplimente() {
    global $bp;

    /**
     * Functions hooked to this action will be processed before loading komplimente page screen .
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     */
    do_action( 'bp_komplimente_screen_komplimente' );
    bp_core_load_template( 'members/single/komplimente' );
}

/**
 * Filters template for the user komplimente tab.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @param string $found_template Located template file.
 * @param array $templates The template array.
 * @return string Template file.
 */
function bp_komplimente_load_template_filter( $found_template, $templates ) {
    global $bp;

    // Only filter the template location when we're on the komplimente component pages.
    if ( ! bp_is_current_component( $bp->komplimente->komplimente->slug ) )
        return $found_template;

    if ( empty( $found_template ) ) {

        bp_register_template_stack( 'bp_komplimente_get_template_directory', 14 );

        // locate_template() will attempt to find the plugins.php template in the
        // child and parent theme and return the located template when found
        //
        // plugins.php is the preferred template to use, since all we'd need to do is
        // inject our content into BP
        //
        // note: this is only really relevant for bp-default themes as theme compat
        // will kick in on its own when this template isn't found
        $found_template = locate_template( 'members/single/plugins.php', false, false );

        // add our hook to inject content into BP
        add_action( 'bp_template_content', 'bp_komplimente_single_komplimente_content' );
    }

    /**
     * Filters the kompliment page template.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param string $found_template Located template file.
     */
    return apply_filters( 'bp_komplimente_load_template_filter', $found_template );
}
add_filter( 'bp_located_template', 'bp_komplimente_load_template_filter', 10, 2 );

/**
 * Get template directory for kompliment page templates.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @return string Template directory.
 */
function bp_komplimente_get_template_directory() {
    /**
     * Filters the kompliment template directory.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     */
    return apply_filters( 'bp_komplimente_get_template_directory', constant( 'BP_KOMPLIMENTE_DIR' ) . '/includes/templates' );
}