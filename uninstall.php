<?php
/**
 * Uninstall BuddyPress Komplimente
 *
 * Uninstalling BuddyPress Komplimente deletes tables and plugin options.
 *
 * @package BuddyPress_Komplimente
 * @since 1.0.7
 */

// Exit if accessed directly.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb, $bp;

if ( get_option( 'bp_kompliment_uninstall_delete' ) ) {    
    $wpdb->hide_errors();

    if ( !defined( 'BP_KOMPLIMENTE_VER' ) ) {
        // Load plugin file.
        include_once( 'bp-komplimente.php' );
    }

    if ( !( !empty( $bp ) && !empty( $bp->table_prefix ) ) ) {
        /** This action is documented in bp-komplimente.php */
        $table_prefix = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );
    } else {
        $table_prefix = $bp->table_prefix;
    }

    /* Delete all terms & taxonomies. */
    $taxonomies = array( 'kompliment' );
    
    foreach ( array_unique( array_filter( $taxonomies ) ) as $taxonomy ) {
        $terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

        // Delete terms.
        if ( $terms ) {
            foreach ( $terms as $term ) {
                $wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
                $wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
                
                delete_option( 'taxonomy_' . $term->term_id );
            }
        }

        // Delete taxonomy.
        $wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
        
        delete_option( $taxonomy . '_children' );
    }

    // Delete options
    delete_option( 'bp_kompliment_singular_name' );
    delete_option( 'bp_kompliment_plural_name' );
    delete_option( 'bp_kompliment_slug' );
    delete_option( 'bp_kompliment_can_see_others_comp' );
    delete_option( 'bp_kompliment_member_dir_btn' );
    delete_option( 'bp_kompliment_can_delete' );
    delete_option( 'bp_kompliment_enable_activity' );
    delete_option( 'bp_kompliment_enable_notifications' );
    delete_option( 'bp_comp_per_page' );
    delete_option( 'bp_comp_custom_css' );
    delete_option( 'bp_komplimente_version' );

    // Drop table.
    $wpdb->query( "DROP TABLE IF EXISTS " . $table_prefix . "bp_komplimente" );
}