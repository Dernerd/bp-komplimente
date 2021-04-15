<?php
/**
 * Functions related to handling user submitted data and actions.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Handle submitted modal form data.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function handle_komplimente_form_data() {

    if (isset($_POST['comp-modal-form'])) {
        $nonce = wp_verify_nonce( $_POST['handle_komplimente_nonce'], 'handle_komplimente_form_data' );
        if (!$nonce) {
            return;
        }

        if ( bp_displayed_user_id() == bp_loggedin_user_id() ) {
            return;
        }

        $insert_id = false;
        $term_id = strip_tags($_POST['term_id']);
        // post id is required for geodirectory's whoop theme.
        $post_id = strip_tags($_POST['post_id']);
        $receiver_id = strip_tags($_POST['receiver_id']);
        $message = strip_tags($_POST['message']);
        $args = array(
            'term_id' => (int) $term_id,
            'post_id' => (int) $post_id,
            'message' => $message,
            'sender_id' => get_current_user_id()
        );
        if ($receiver_id) {
            $args['receiver_id'] = $receiver_id;
        }

        $receiver_name = bp_core_get_user_displayname($receiver_id);

        $redirect_url = bp_core_get_user_domain($receiver_id);

        //check duplicate
        $check_duplicate = apply_filters('bp_comp_check_duplicate', false);

        if ($check_duplicate) {
            $count = bp_comp_check_duplicate_comp($args);
            if ($count != 0) {
                bp_core_add_message( __( 'Doppelte Komplimente erkannt.', 'bp-komplimente' ), 'error' );
            } else {
                $insert_id = bp_komplimente_start_kompliment($args);
                if ( ! $insert_id) {
                    bp_core_add_message( sprintf( __( 'Beim Versuch, %s an %s zu senden, ist ein Problem aufgetreten. Wende Dich an den Administrator.', 'bp-komplimente' ), strtolower(BP_COMP_SINGULAR_NAME), $receiver_name ), 'error' );
                } else {
                    bp_core_add_message( sprintf( __( 'Dein %s wurde an %s gesendet.', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME, $receiver_name ) );
                }
            }
        } else {
            $insert_id = bp_komplimente_start_kompliment($args);
            if ( ! $insert_id) {
                bp_core_add_message( sprintf( __( 'Beim Versuch, %s an %s zu senden, ist ein Problem aufgetreten. Wende Dich an den Administrator.', 'bp-komplimente' ), strtolower(BP_COMP_SINGULAR_NAME), $receiver_name ), 'error' );
            } else {
                bp_core_add_message( sprintf( __( 'Dein %s wurde an %s gesendet.', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME, $receiver_name ) );
            }
        }

	    $bp_kompliment_can_see_others_comp_value = esc_attr( get_option('bp_kompliment_can_see_others_comp'));
	    $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_others_comp_value ? $bp_kompliment_can_see_others_comp_value : 'yes';

        if ($bp_kompliment_can_see_others_comp == 'members_choice') {
            $bp_kompliment_can_see_your_comp_value = esc_attr( get_user_meta(bp_displayed_user_id(), 'bp_kompliment_can_see_your_comp', true));
            $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_your_comp_value ? $bp_kompliment_can_see_your_comp_value : 'yes';
        }

        if ($bp_kompliment_can_see_others_comp == 'yes') {
		    $show_for_displayed_user = true;
	    } elseif ($bp_kompliment_can_see_others_comp == 'members_only') {
            if (is_user_logged_in()) {
                $show_for_displayed_user = true;
            } else {
                $show_for_displayed_user = false;
            }
        } else {
            $show_for_displayed_user = false;
        }

	    if (current_user_can( 'manage_options' )) {
		    $show_for_displayed_user = true;
	    }

	    if ($show_for_displayed_user) {
            $redirect_to_single_comp = apply_filters('redirect_to_single_comp', false);
            if ($insert_id && $redirect_to_single_comp) {
                $redirect = $redirect_url.BP_KOMPLIMENTE_SLUG.'/?c_id='.$insert_id;
            } else {
                $redirect = $redirect_url.BP_KOMPLIMENTE_SLUG.'/';
            }

	    } else {
		    $redirect = $redirect_url;
	    }

        bp_core_redirect( $redirect );
    }
}
add_action( 'bp_actions', 'handle_komplimente_form_data', 99 );

/**
 * Delete a single complement using kompliment ID.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function delete_single_complement() {
    global $wpdb;

    if (!bp_is_user()) {
        return;
    }

    if (!isset($_GET['c_id']) OR !isset($_GET['action']) ) {
        return;
    }

    $bp_kompliment_can_delete_value = esc_attr( get_option('bp_kompliment_can_delete'));
    $bp_kompliment_can_delete = $bp_kompliment_can_delete_value ? $bp_kompliment_can_delete_value : 'yes';

    if (current_user_can( 'manage_options' )) {
        $bp_kompliment_can_delete = 'yes';
    }

    if ($bp_kompliment_can_delete == 'no') {
        return;
    }

    $c_id = (int) strip_tags(esc_sql($_GET['c_id']));

    if (!$c_id) {
        return;
    }

    $table_name = BP_KOMPLIMENTE_TABLE;
    $sender_id  = $wpdb->get_var( $wpdb->prepare( "SELECT sender_id FROM {$table_name} WHERE id = %d", $c_id ) );

    //Admins can delete any complement
    $current_user_can_delete = current_user_can( 'manage_options' );

    //Complement sender and receiver can delete kompliment if admin has allowed it
    if( is_user_logged_in() ){

        //Is this the receiver
        if( bp_loggedin_user_id() == bp_displayed_user_id() ){
            $current_user_can_delete = true;
        }

        //Is this the sender
        if( bp_loggedin_user_id() == $sender_id ){
            $current_user_can_delete = true;
        }
    }

    if ( false == $current_user_can_delete ) {
        return;
    }


    /**
     * Functions hooked to this action will be processed before deleting the complement.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param int $c_id The kompliment ID.
     */
    do_action( 'bp_komplimente_before_remove_kompliment', $c_id );

    BP_Komplimente::delete( $c_id );

    /**
     * Functions hooked to this action will be processed after deleting the complement.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @param int $c_id The kompliment ID.
     */
    do_action( 'bp_komplimente_after_remove_kompliment', $c_id );

    $redirect = bp_displayed_user_domain().BP_KOMPLIMENTE_SLUG.'/';
    bp_core_redirect( $redirect );
}
add_action( 'bp_actions', 'delete_single_complement');

function bp_comp_check_duplicate_comp($args) {

    global $wpdb;

    $r = wp_parse_args( $args, array(
        'receiver_id'   => bp_displayed_user_id(),
        'sender_id' => bp_loggedin_user_id(),
        'term_id' => 0,
        'post_id' => 0,
        'message' => null
    ) );

    $term_id = (int) $r['term_id'];
    $sender_id = (int) $r['sender_id'];
    $receiver_id = (int) $r['receiver_id'];

    $count = $wpdb->get_var($wpdb->prepare("select COUNT(id) from " . BP_KOMPLIMENTE_TABLE . " where term_id= %d AND sender_id= %d AND receiver_id= %d", array($term_id, $sender_id, $receiver_id)));
    return $count;
}

