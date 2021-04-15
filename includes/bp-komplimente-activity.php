<?php
/**
 * Functions related to activity component.
 *
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Records activity when a kompliment get posted.
 *
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 * 
 * @param array|string $args {
 *    Attributes of the $args.
 *
 *    @type int $user_id The user to record the activity for, can be false if this activity is not for a user.
 *    @type string $action The activity action - e.g. "Jon Doe posted an update".
 *    @type string $content Optional: The content of the activity item e.g. "BuddyPress is awesome guys!".
 *    @type string $primary_link Optional: The primary URL for this item in RSS feeds (defaults to activity permalink).
 *    @type string $component The name/ID of the component e.g. groups, profile, mycomponent.
 *    @type bool $type The activity type e.g. activity_update, profile_updated.
 *    @type bool $item_id Optional: The ID of the specific item being recorded, e.g. a blog_id.
 *    @type bool $secondary_item_id Optional: A second ID used to further filter e.g. a comment_id.
 *    @type string $recorded_time The GMT time that this activity was recorded.
 *    @type bool $hide_sitewide Should this be hidden on the sitewide activity stream?.
 *
 * }
 * @return bool|int The ID of the activity on success. False on error.
 */
function komplimente_record_activity( $args = '' ) {

    if ( ! bp_is_active( 'activity' ) ) {
        return false;
    }

    $r = wp_parse_args( $args, array(
        'user_id'           => bp_loggedin_user_id(),
        'action'            => '',
        'content'           => '',
        'primary_link'      => '',
        'component'         => buddypress()->komplimente->id,
        'type'              => false,
        'item_id'           => false,
        'secondary_item_id' => false,
        'recorded_time'     => bp_core_current_time(),
        'hide_sitewide'     => false
    ) );

    return bp_activity_add( $r );
}

/**
 * Records activity in two ways when a kompliment get posted.
 *
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 * 
 * @param BP_Komplimente $kompliment The kompliment object.
 */
function komplimente_record_sent_received_activity( BP_Komplimente $kompliment ) {
    if ( ! bp_is_active( 'activity' ) ) {
        return;
    }

    // Record in activity streams for the sender
    komplimente_record_activity( array(
        'user_id'           => $kompliment->sender_id,
        'type'              => 'kompliment_sent',
        'item_id'           => $kompliment->id,
        'secondary_item_id' => $kompliment->receiver_id
    ) );

    // Record in activity streams for the receiver
    komplimente_record_activity( array(
        'user_id'           => $kompliment->receiver_id,
        'type'              => 'kompliment_received',
        'item_id'           => $kompliment->id,
        'secondary_item_id' => $kompliment->sender_id
    ) );
}
add_action( 'bp_komplimente_start_kompliment', 'komplimente_record_sent_received_activity' );


/**
 * Register the activity actions for komplimente.
 * 
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 */
function komplimente_register_activity_actions() {

    if ( !bp_is_active( 'activity' ) ) {
        return false;
    }

    $bp = buddypress();

    bp_activity_set_action(
        $bp->komplimente->id,
        'kompliment_received',
        sprintf( __( '%s erhalten', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME ),
        'komplimente_format_activity_action_kompliment_received',
        BP_COMP_PLURAL_NAME,
        array( 'activity' )
    );

    bp_activity_set_action(
        $bp->komplimente->id,
        'kompliment_sent',
        sprintf( __( '%s gesendet', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME ),
        'komplimente_format_activity_action_kompliment_sent',
        BP_COMP_PLURAL_NAME,
        array( 'activity' )
    );

    /**
     * Use this hook to register additional activity actions.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     */
    do_action( 'komplimente_register_activity_actions' );
}
add_action( 'bp_register_activity_actions', 'komplimente_register_activity_actions' );

/**
 * Format activity actions for 'kompliment_received'.
 *
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @param object $activity Activity data.
 * @return string $action Formatted activity action.
 */
function komplimente_format_activity_action_kompliment_received( $action, $activity ) {
    global $bp;

    $receiver_link = bp_core_get_userlink( $activity->user_id );
    $sender_link    = bp_core_get_userlink( $activity->secondary_item_id );
    $receiver_url    = bp_core_get_userlink( $activity->user_id, false, true );
    $kompliment_url = $receiver_url . BP_KOMPLIMENTE_SLUG . '/?c_id='.$activity->item_id;
    $kompliment_link = '<a href="'.$kompliment_url.'">'.strtolower(BP_COMP_SINGULAR_NAME).'</a>';

    $bp_kompliment_can_see_others_comp_value = esc_attr( get_option('bp_kompliment_can_see_others_comp'));
    $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_others_comp_value ? $bp_kompliment_can_see_others_comp_value : 'yes';

    if (current_user_can('manage_options')) {
        $bp_kompliment_can_see_others_comp = 'yes';
    } else {
        if ($bp_kompliment_can_see_others_comp == 'members_choice') {
            $bp_kompliment_can_see_your_comp_value = esc_attr( get_user_meta($bp->displayed_user->id, 'bp_kompliment_can_see_your_comp', true));
            $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_your_comp_value ? $bp_kompliment_can_see_your_comp_value : 'yes';
        }

        if (bp_is_user() && ($bp->loggedin_user->id == $bp->displayed_user->id)) {
            $bp_kompliment_can_see_others_comp = 'yes';
        }
    }


    if ($bp_kompliment_can_see_others_comp == 'yes') {
        $action = sprintf( __( '%1$s hat von %2$s %3$s erhalten', 'bp-komplimente' ), $receiver_link, $sender_link, $kompliment_link );
    } elseif ($bp_kompliment_can_see_others_comp == 'members_only') {
        if (is_user_logged_in()) {
            $action = sprintf( __( '%1$s hat von %2$s %3$s erhalten', 'bp-komplimente' ), $receiver_link, $sender_link, $kompliment_link );
        } else {
            $action = sprintf( __( '%1$s hat von %3$s %2$s erhalten', 'bp-komplimente' ), $receiver_link, strtolower(BP_COMP_SINGULAR_NAME),  $sender_link );
        }
    } else {
        $action = sprintf( __( '%1$s hat von %3$s %2$s erhalten', 'bp-komplimente' ), $receiver_link, strtolower(BP_COMP_SINGULAR_NAME),  $sender_link );
    }

    /**
     * Filters the 'kompliment_received' activity action format.
     *
     * @since 0.0.2
     *
     * @param string $action String text for the 'kompliment_received' action.
     * @param object $activity Activity data.
     */
    return apply_filters( 'komplimente_format_activity_action_kompliment_received', $action, $activity );
}

/**
 * Format activity actions for 'kompliment_sent'.
 *
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @param string $action Static activity action.
 * @param object $activity Activity data.
 * @return string $action Formatted activity action.
 */
function komplimente_format_activity_action_kompliment_sent( $action, $activity ) {
    global $bp;
    $sender_link = bp_core_get_userlink( $activity->user_id );
    $receiver_link    = bp_core_get_userlink( $activity->secondary_item_id );
    $receiver_url    = bp_core_get_userlink( $activity->secondary_item_id, false, true );
    $kompliment_url = $receiver_url . BP_KOMPLIMENTE_SLUG . '/?c_id='.$activity->item_id;
    $kompliment_link = '<a href="'.$kompliment_url.'">'.strtolower(BP_COMP_SINGULAR_NAME).'</a>';

    $bp_kompliment_can_see_others_comp_value = esc_attr( get_option('bp_kompliment_can_see_others_comp'));
    $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_others_comp_value ? $bp_kompliment_can_see_others_comp_value : 'yes';


    if (current_user_can('manage_options')) {
        $bp_kompliment_can_see_others_comp = 'yes';
    } else {
        if ($bp_kompliment_can_see_others_comp == 'members_choice') {
            $bp_kompliment_can_see_your_comp_value = esc_attr( get_user_meta($bp->displayed_user->id, 'bp_kompliment_can_see_your_comp', true));
            $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_your_comp_value ? $bp_kompliment_can_see_your_comp_value : 'yes';
        }

        if (bp_is_user() && ($bp->loggedin_user->id == $bp->displayed_user->id)) {
            $bp_kompliment_can_see_others_comp = 'yes';
        }
    }


    if ($bp_kompliment_can_see_others_comp == 'yes') {
        $action = sprintf( __( '%1$s hat %2$s an %3$s gesendet', 'bp-komplimente' ), $sender_link, $kompliment_link, $receiver_link );
    } elseif ($bp_kompliment_can_see_others_comp == 'members_only') {
        if (is_user_logged_in()) {
            $action = sprintf( __( '%1$s hat %2$s an %3$s gesendet', 'bp-komplimente' ), $sender_link, $kompliment_link, $receiver_link );
        } else {
            $action = sprintf( __( '%1$s hat %2$s an %3$s gesendet', 'bp-komplimente' ), $sender_link, strtolower(BP_COMP_SINGULAR_NAME), $receiver_link );
        }
    } else {
        $action = sprintf( __( '%1$s hat %2$s an %3$s gesendet', 'bp-komplimente' ), $sender_link, strtolower(BP_COMP_SINGULAR_NAME), $receiver_link );
    }

    /**
     * Filters the 'kompliment_sent' activity action format.
     *
     * @since 0.0.2
     *
     * @param string $action String text for the 'kompliment_sent' action.
     * @param object $activity Activity data.
     */
    return apply_filters( 'komplimente_format_activity_action_kompliment_sent', $action, $activity );
}


/**
 * Delete a kompliment activity using kompliment ID.
 *
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 * 
 * @param int $c_id Kompliment ID.
 */
function komplimente_delete_activity( $c_id ) {
    if ( ! bp_is_active( 'activity' ) ) {
        return;
    }

    bp_activity_delete( array(
        'component' => buddypress()->komplimente->id,
        'item_id'   => $c_id
    ) );
}
add_action('bp_komplimente_after_remove_kompliment', 'komplimente_delete_activity');

/**
 * Delete all kompliment activities for user using user ID.
 *
 * @since 0.0.2
 * @package BuddyPress_Komplimente
 * 
 * @param int $user_id User ID.
 */
function komplimente_delete_activity_for_user( $user_id ) {
    if ( ! bp_is_active( 'activity' ) ) {
        return;
    }

    bp_activity_delete( array(
        'component' => buddypress()->komplimente->id,
        'user_id'   => $user_id
    ) );

    bp_activity_delete( array(
        'component' => buddypress()->komplimente->id,
        'secondary_item_id'   => $user_id
    ) );
}
add_action('bp_komplimente_after_remove_data', 'komplimente_delete_activity_for_user');

/**
 * Kompliment activity collapses two filters into one
 *
 * @since 0.0.8
 * @package BuddyPress_Komplimente
 *
 * @param array $filters Array of filter options for the given context, in the following format: $option_value => $option_name.
 * @param string $context Context for the filter. 'activity', 'member', 'member_groups', 'group'.
 *
 * @return array
 */
function komplimente_merge_filter( $filters, $context ){
    if (array_key_exists('kompliment_sent', $filters) && array_key_exists('kompliment_received', $filters)) {
        $label = $filters['kompliment_sent'];
        unset($filters['kompliment_sent']);
        unset($filters['kompliment_received']);
        $filters['kompliment_sent,kompliment_received'] = $label;
    }
    return $filters;
}
add_filter('bp_get_activity_show_filters_options', 'komplimente_merge_filter', 10, 2);

add_filter('komplimente_format_activity_action_kompliment_sent', 'bp_comp_add_kompliment_received_content', 10, 2);
add_filter('komplimente_format_activity_action_kompliment_received', 'bp_comp_add_kompliment_received_content', 10, 2);
function bp_comp_add_kompliment_received_content($action, $activity) {
    
    $display_comp_content = apply_filters('bp_comp_display_comp_content_in_activity', false);
    if (!$display_comp_content) {
        return $action;
    }

    global $wpdb;
    $comp = $wpdb->get_row($wpdb->prepare("select * from " . BP_KOMPLIMENTE_TABLE . " where id= %d", array($activity->item_id)));
    $t_id = $comp->term_id;
    $term = get_term_by('id', $t_id, 'kompliment');
    $term_meta = get_option("taxonomy_$t_id");
    $komplimente_icon = esc_attr($term_meta['komplimente_icon']) ? esc_attr($term_meta['komplimente_icon']) : '';
    if (is_ssl()) {
        $komplimente_icon = str_replace('http://', 'https://', $komplimente_icon);
    }
    $image = "<div class=\"comp-user-header\">";
    $image .= '<img style="height: 20px; width: 20px; vertical-align:middle"
              src="'.$komplimente_icon.'"
              />';
    $image .= $term->name;
    $image .= '</div>';
    $image .= '<br/>';

    $message = '<div class="comp-user-message">';
    $message .= $comp->message;
    $message .= '</div>';
    $activity->content = $message;
    return $action;
}