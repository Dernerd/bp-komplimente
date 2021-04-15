<?php
/**
 * Functions related to kompliment component.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */

/**
 * Start kompliment.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @param string|array $args {
 *    Attributes of the $args.
 *
 *    @type int $receiver_id Received ID.
 *    @type int $sender_id Sender ID.
 *    @type int $term_id Kompliment Icon Term ID.
 *    @type int $post_id Post ID.
 *    @type string $message The kompliment Message.
 *
 * }
 * @return bool
 */
function bp_komplimente_start_kompliment( $args = '' ) {
    global $bp;

    $r = wp_parse_args( $args, array(
        'receiver_id'   => bp_displayed_user_id(),
        'sender_id' => bp_loggedin_user_id(),
        'term_id' => 0,
        'post_id' => 0,
        'message' => null
    ) );

    $kompliment = new BP_Komplimente( $r['receiver_id'], $r['sender_id'], $r['term_id'], $r['post_id'], $r['message'] );

    $insert_id = $kompliment->save();
    if ( ! $insert_id ) {
        return false;
    }

    // Add kompliment ID
    $kompliment->id = $insert_id;
    /**
     * Functions hooked to this action will be processed after komplimente data stored into the db.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param object $kompliment The kompliment data object.
     */
    do_action_ref_array( 'bp_komplimente_start_kompliment', array( &$kompliment ) );

    return $insert_id;
}

/**
 * Get the total kompliment counts for a user.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @param string|array $args {
 *    Attributes of the $args.
 *
 *    @type int $user_id User ID.
 *
 * }
 * @return mixed|void
 */
function bp_komplimente_total_counts( $args = '' ) {

    $r = wp_parse_args( $args, array(
        'user_id' => bp_loggedin_user_id()
    ) );

    $count = false;

    /* try to get locally-cached values first */

    // logged-in user
    if ( $r['user_id'] == bp_loggedin_user_id() && is_user_logged_in() ) {
        global $bp;

        if ( ! empty( $bp->loggedin_user->total_kompliment_counts ) ) {
            $count = $bp->loggedin_user->total_kompliment_counts;
        }

        // displayed user
    } elseif ( $r['user_id'] == bp_displayed_user_id() && bp_is_user() ) {
        global $bp;

        if ( ! empty( $bp->displayed_user->total_kompliment_counts ) ) {
            $count = $bp->displayed_user->total_kompliment_counts;
        }
    }

    // no cached value, so query for it
    if ( $count === false ) {
        $count = BP_Komplimente::get_counts( $r['user_id'] );
    }

    /**
     * Filters the kompliment count array.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param array $count {
     *    Attributes of the $count.
     *
     *    @type int $received Count of total komplimente received.
     *    @type int $sent Count of total komplimente sent.
     *
     * }
     * @param int $r['user_id'] User ID.
     */
    return apply_filters( 'bp_komplimente_total_counts', $count, $r['user_id'] );
}

/**
 * Remove komplimente data for the given user id.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @param int $user_id The user ID.
 */
function bp_komplimente_remove_data( $user_id ) {
    /**
     * Functions hooked to this action will be processed before deleting the user's complement data.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param int $user_id The User ID.
     */
    do_action( 'bp_komplimente_before_remove_data', $user_id );

    BP_Komplimente::delete_all_for_user( $user_id );

    /**
     * Functions hooked to this action will be processed after deleting the user's complement data.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param int $user_id The User ID.
     */
    do_action( 'bp_komplimente_after_remove_data', $user_id );
}

/**
 * Inject buddypress komplimente content into BP.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function bp_komplimente_single_komplimente_content() {
    bp_get_template_part( 'members/single/komplimente' ); // note the new template name for our template part.
}

add_action( 'wpmu_delete_user',	'bp_komplimente_remove_data' );
add_action( 'delete_user',	'bp_komplimente_remove_data' );
add_action( 'make_spam_user',	'bp_komplimente_remove_data' );