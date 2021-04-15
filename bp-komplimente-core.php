<?php
/**
 * Main Komplimente component class.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class BP_Komplimente_Component
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
class BP_Komplimente_Component extends BP_Component {

    /**
     * Initialize BP_Komplimente_Component class.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @global object $bp BuddyPress instance.
     */
    public function __construct() {
        global $bp;


        /**
         * Filters the value of kompliment nav position.
         *
         * @since 0.0.1
         * @package BuddyPress_Komplimente
         */
        $this->params = array(
            'adminbar_myaccount_order' => apply_filters( 'bp_komplimente_nav_position', 71 )
        );

        parent::start(
            'komplimente',
            __( 'Komplimente', 'bp-komplimente' ),
            constant( 'BP_KOMPLIMENTE_DIR' ) . '/includes',
            $this->params
        );

        // include our files
        $this->includes();

        // setup hooks
        $this->setup_hooks();

        // register our component as an active component in BP
        $bp->active_components[$this->id] = '1';
    }

    /**
     * Include required files.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     */
    public function includes( $includes = array() ) {
        $bp_kompliment_enable_activity_value = esc_attr( get_option('bp_kompliment_enable_activity'));
        $bp_kompliment_enable_activity = $bp_kompliment_enable_activity_value ? $bp_kompliment_enable_activity_value : 'yes';

        $bp_kompliment_enable_notifications_value = esc_attr( get_option('bp_kompliment_enable_notifications'));
        $bp_kompliment_enable_notifications = $bp_kompliment_enable_notifications_value ? $bp_kompliment_enable_notifications_value : 'yes';

        // Include the Class that interact with the custom db table.
        require( $this->path . '/bp-komplimente-classes.php' );
        // Functions related to kompliment component.
        require( $this->path . '/bp-komplimente-functions.php' );
        // Functions related to frontend content display.
        require( $this->path . '/bp-komplimente-screens.php' );
        // Functions related to kompliment buttons and template tags.
        require( $this->path . '/bp-komplimente-templatetags.php' );
        // Functions related to handling user submitted data and actions.
        require( $this->path . '/bp-komplimente-actions.php' );
        // Functions related to notification component.
        if ($bp_kompliment_enable_notifications == 'yes') {
            require( $this->path . '/bp-komplimente-notifications.php' );
        }
        // Functions related to activity component.
        if ($bp_kompliment_enable_activity == 'yes') {
            require( $this->path . '/bp-komplimente-activity.php' );
        }
        // Functions related to kompliment forms.
        require( $this->path . '/bp-komplimente-forms.php' );
        // Functions related to kompliment settings.
        require( $this->path . '/bp-komplimente-settings.php' );
        // Functions related to kompliment types and icons.
        require( $this->path . '/bp-komplimente-taxonomies.php' );
    }

    /**
     * Setup globals.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @global object $bp BuddyPress instance.
     * @param array $args Not being used.
     */
    public function setup_globals( $args = array() ) {
        global $bp;

        // Set up the $globals array
        $globals = array(
            'notification_callback' => 'bp_komplimente_format_notifications',
            'global_tables'         => array(
                'table_name' => BP_KOMPLIMENTE_TABLE,
            )
        );

        // Let BP_Component::setup_globals() do its work.
        parent::setup_globals( $globals );

        // register other globals since BP isn't really flexible enough to add it
        // in the setup_globals() method
        //
        // would rather do away with this, but keeping it for backpat
        $bp->komplimente->komplimente = new stdClass;
        $bp->komplimente->komplimente->slug = constant( 'BP_KOMPLIMENTE_SLUG' );

        // locally cache total count values for logged-in user
        if ( is_user_logged_in() ) {
            $bp->loggedin_user->total_kompliment_counts = bp_komplimente_total_counts( array(
                'user_id' => bp_loggedin_user_id()
            ) );
        }

        // locally cache total count values for displayed user
        if ( bp_is_user() && ( bp_loggedin_user_id() != bp_displayed_user_id() ) ) {
            $bp->displayed_user->total_kompliment_counts = bp_komplimente_total_counts( array(
                'user_id' => bp_displayed_user_id()
            ) );
        }

    }

    /**
     * Setup hooks.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     */
    public function setup_hooks() {
        // javascript hook
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 11 );
    }

    /**
     * Setup profile navigation.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     *
     * @global object $bp BuddyPress instance.
     * @param array $main_nav Not being used.
     * @param array $sub_nav Not being used.
     */
    public function setup_nav( $main_nav = array(), $sub_nav = array() ) {
        global $bp;

        /**
         * Functions hooked to this action will be processed before komplimente navigation setup.
         *
         * @since 0.0.1
         * @package BuddyPress_Komplimente
         */
        do_action( 'bp_komplimente_before_setup_nav' );
        // Need to change the user ID, so if we're not on a member page, $counts variable is still calculated
        $user_id = bp_is_user() ? bp_displayed_user_id() : bp_loggedin_user_id();
        $counts  = bp_komplimente_total_counts( array( 'user_id' => $user_id ) );

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

        bp_core_new_nav_item( array(
            'name'                => BP_COMP_PLURAL_NAME." "."<span>".$counts['received']."</span>",
            'slug'                => $bp->komplimente->komplimente->slug,
            'position'            => $this->params['adminbar_myaccount_order'],
            'screen_function'     => 'bp_komplimente_screen_komplimente',
            'show_for_displayed_user' => $show_for_displayed_user,
            'default_subnav_slug' => 'komplimente',
            'item_css_id'         => 'members-komplimente'
        ) );

        /**
         * Functions hooked to this action will be processed after komplimente navigation setup.
         *
         * @since 0.0.1
         * @package BuddyPress_Komplimente
         */
        do_action( 'bp_komplimente_after_setup_nav' );

    }


    /**
     * Enqueues the javascript.
     *
     * The JS is used to add AJAX functionality when clicking on the komplimente button.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 'bp-komplimente-js', constant( 'BP_KOMPLIMENTE_URL' ) . 'js/bp-komplimente.js', array( 'jquery' ) );
        wp_register_style( 'bp-komplimente-css', constant( 'BP_KOMPLIMENTE_URL' ) . 'css/bp-komplimente.css' );
        wp_enqueue_style( 'bp-komplimente-css' );
	    wp_enqueue_style( 'dashicons' );
    }

}

/**
 * Adds the Komplimente component to BuddyPress.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 */
function bp_komplimente_setup_component() {
    global $bp;

    $bp->komplimente = new BP_Komplimente_Component;
}
add_action( 'bp_loaded', 'bp_komplimente_setup_component' );