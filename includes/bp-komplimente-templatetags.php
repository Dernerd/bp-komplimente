<?php
/**
 * Functions related to kompliment buttons and template tags.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */

/**
 * Output a kompliment button for a given user.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function bp_komplimente_add_kompliment_button( $args = '' ) {
    echo bp_komplimente_get_add_kompliment_button( $args );
}

/**
 * Returns a kompliment button for a given user.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @global object $members_template Members template object.
 * @param array|string $args {
 *    Attributes of the $args.
 *
 *    @type int $receiver_id Kompliment receiver ID.
 *    @type int $sender_id Kompliment sender ID.
 *    @type string $link_text Link text.
 *    @type string $link_title Link title.
 *    @type string $wrapper_class Link wrapper class.
 *    @type string $link_class Link class. Default "komplimente-popup".
 *    @type string $wrapper Link wrapper. Default "div".
 *
 * }
 * @return string Button HTML.
 */
function bp_komplimente_get_add_kompliment_button( $args = '' ) {
    global $bp, $members_template;

    $r = wp_parse_args( $args, array(
        'receiver_id'     => bp_displayed_user_id(),
        'sender_id'   => bp_loggedin_user_id(),
        'link_text'     => '',
        'link_title'    => '',
        'wrapper_class' => '',
        'link_class'    => 'komplimente-popup',
        'wrapper'       => 'div'
    ) );

    if ( ! $r['receiver_id'] || ! $r['sender_id'] )
        return false;


    // if the logged-in user is the receiver, use already-queried variables
    if ( bp_loggedin_user_id() && $r['receiver_id'] == bp_loggedin_user_id() ) {
        $receiver_domain   = bp_loggedin_user_domain();
        $receiver_fullname = bp_get_loggedin_user_fullname();

        // else we do a lookup for the user domain and display name of the receiver
    } else {
        $receiver_domain   = bp_core_get_user_domain( $r['receiver_id'] );
        $receiver_fullname = bp_core_get_user_displayname( $r['receiver_id'] );
    }

    // setup some variables

    $id        = 'komplimente';
    $action    = 'start';
    $class     = 'komplimente';
    /**
     * Filters the kompliment receiver name.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param string $receiver_fullname Receiver full name.
     * @param int $r['receiver_id'] Receiver ID.
     */
    $link_text = sprintf( sprintf( __( 'Sende %s', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME ), apply_filters( 'bp_komplimente_receiver_name', bp_get_user_firstname( $receiver_fullname ), $r['receiver_id'] ) );

    if ( empty( $r['link_text'] ) ) {
        $r['link_text'] = $link_text;
    }


    $wrapper_class = 'komplimente-button ' . $id;

    if ( ! empty( $r['wrapper_class'] ) ) {
        $wrapper_class .= ' '  . esc_attr( $r['wrapper_class'] );
    }

    $link_class = $class;

    if ( ! empty( $r['link_class'] ) ) {
        $link_class .= ' '  . esc_attr( $r['link_class'] );
    }

    // make sure we can view the button if a user is on their own page
    $block_self = empty( $members_template->member ) ? true : false;

    // if we're using AJAX and a user is on their own profile, we need to set
    // block_self to false so the button shows up
    if ( ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) && bp_is_my_profile() ) {
        $block_self = false;
    }

    // setup the button arguments
    $button = array(
        'id'                => $id,
        'component'         => 'komplimente',
        'must_be_logged_in' => true,
        'block_self'        => $block_self,
        'wrapper_class'     => $wrapper_class,
        'wrapper_id'        => 'komplimente-button-' . (int) $r['receiver_id'],
        'link_href'         => wp_nonce_url( $receiver_domain . $bp->komplimente->komplimente->slug . '/' . $action .'/', $action . '_komplimente' ),
        'link_text'         => esc_attr( $r['link_text'] ),
        'link_title'        => esc_attr( $r['link_title'] ),
        'link_id'           => $class . '-' . (int) $r['receiver_id'],
        'link_class'        => $link_class,
        'wrapper'           => ! empty( $r['wrapper'] ) ? esc_attr( $r['wrapper'] ) : false
    );

    // Filter and return the HTML button

    /**
     * Filters the kompliment button.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param string $button Button HTML.
     * @param int $r['receiver_id'] Receiver ID.
     * @param int $r['sender_id'] Sender ID.
     */
    return bp_get_button( apply_filters( 'bp_komplimente_get_add_kompliment_button', $button, $r['receiver_id'], $r['sender_id'] ) );
}

/**
 * Add kompliment button to the profile page.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function bp_komplimente_add_profile_kompliment_button() {
    bp_komplimente_add_kompliment_button();
}
add_action( 'bp_member_header_actions', 'bp_komplimente_add_profile_kompliment_button', 31 );

/**
 * Get komplimente for a given user.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @param array|string $args {
 *    Attributes of the $args.
 *
 *    @type int $user_id User ID.
 *    @type int $offset Query results offset.
 *    @type int $limit Query results limit.
 *    @type int $c_id Kompliment ID.
 *
 * }
 * @return mixed|void
 */
function bp_komplimente_get_komplimente( $args = '' ) {
    $r = wp_parse_args( $args, array(
        'user_id' => bp_displayed_user_id(),
        'offset' => 0,
        'limit' => 100,
        'c_id' => false
    ) );

    /**
     * Filters the kompliment query results.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param int $r['user_id'] The user ID.
     * @param int $r['offset'] Query results offset.
     * @param int $r['limit'] Query results limit.
     * @param bool|int $r['c_id'] The kompliment ID.
     */
    return apply_filters( 'bp_komplimente_get_komplimente', BP_Komplimente::get_komplimente( $r['user_id'], $r['offset'], $r['limit'], $r['c_id'] ) );
}

/**
 * Add kompliment button to the members page.
 *
 * @since 0.0.7
 * @package BuddyPress_Komplimente
 */
function bp_komplimente_add_members_kompliment_button() {
    $bp_kompliment_member_dir_btn_value = esc_attr( get_option('bp_kompliment_member_dir_btn'));
    $bp_kompliment_member_dir_btn = $bp_kompliment_member_dir_btn_value ? $bp_kompliment_member_dir_btn_value : 'no';

    if ($bp_kompliment_member_dir_btn == 'yes') {
        $args = array(
            'receiver_id' => bp_get_member_user_id(),
            'sender_id'   => bp_loggedin_user_id()
        );
        bp_komplimente_add_kompliment_button($args);
    }
}
add_action( 'bp_directory_members_actions', 'bp_komplimente_add_members_kompliment_button' );