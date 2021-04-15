<?php
/**
 * Plugin Name: BP Komplimente
 * Plugin URI: https://n3rds.work/piestingtal_source/bp-komplimente/
 * Description: Komplimente Modul für BuddyPress.
 * Version: 1.1.2
 * Author: WMS N@W
 * Text Domain: bp-komplimente
 * Domain Path: /languages
 * Requires at least: 3.1
 * Tested up to: 5.6
 */
require 'psource-plugin-update/plugin-update-checker.php';
$MyUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://n3rds.work//wp-update-server/?action=get_metadata&slug=bp-komplimente', 
	__FILE__, 
	'bp-komplimente' 
);

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Define the plugin version.
define( 'BP_KOMPLIMENTE_VER', '1.1.2' );

/**
 * BuddyPress komplimente text domain.
 */
define( 'BP_COMP_TEXTDOMAIN', 'bp-komplimente' );
/**
 * BuddyPress komplimente names.
 */
define( 'BP_COMP_SINGULAR_NAME', trim(esc_attr( get_option('bp_kompliment_singular_name', __( 'Kompliment', 'bp-komplimente' )))) );
define( 'BP_COMP_PLURAL_NAME', trim(esc_attr( get_option('bp_kompliment_plural_name', __( 'Komplimente', 'bp-komplimente' )))) );
define( 'BP_KOMPLIMENTE_SLUG', strtolower(trim(esc_attr( get_option('bp_kompliment_slug', __( 'komplimente', 'bp-komplimente' ))))) );


/**
 * Only load the plugin code if BuddyPress is activated.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @global object $wpdb WordPress db object.
 */
function bp_komplimente_init() {
    global $wpdb, $bp;

    //define the plugin path.
    define( 'BP_KOMPLIMENTE_DIR', dirname( __FILE__ ) );
    //define the plugin url.
    define( 'BP_KOMPLIMENTE_URL', plugin_dir_url( __FILE__ ) );
    if ( !$table_prefix = $bp->table_prefix )
        /**
         * Filters the value of BuddyPress table prefix.
         *
         * @since 0.0.1
         * @package BuddyPress_Komplimente
         *
         * @param string $wpdb->base_prefix WordPress table prefix.
         */
        $table_prefix = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );
    ////define the plugin table.
    define( 'BP_KOMPLIMENTE_TABLE', $table_prefix . 'bp_komplimente' );
	if( file_exists(BP_KOMPLIMENTE_DIR . 'vendor/autoload.php' ) ){
		require_once( BP_KOMPLIMENTE_DIR . 'vendor/autoload.php' );
	}
    // only supported in BP 1.5+
    if ( version_compare( BP_VERSION, '1.3', '>' ) ) {
        require( constant( 'BP_KOMPLIMENTE_DIR' ) . '/bp-komplimente-core.php' );
    // show admin notice for users on BP 1.2.x
    } else {
        add_action( 'admin_notices', 'bp_komplimente_older_version_notice' );
        return;
    }
}
add_action( 'bp_include', 'bp_komplimente_init' );
add_action( 'init', 'bp_komplimente_plugin_init' );
/**
 * Hook into actions and filters on site init.
 */
function bp_komplimente_plugin_init(){
	add_action( 'tgmpa_register', 'bp_komplimente_require_plugins');
}

/**
 * Add required plugin check.
 */
function bp_komplimente_require_plugins(){
	$plugins = array( /* The array to install plugins */ );
	$plugins = array(
		array(
			'name'      => 'BuddPress',
			'slug'      => 'buddypress',
			'required'  => true, // this plugin is recommended
			'version'   => '1.5'
		)
	);
	$config = array( /* The array to configure TGM Plugin Activation */ );
	tgmpa( $plugins, $config );
}

/**
 * Creates Custom table for BuddyPress komplimente.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 * @global object $wpdb WordPress db object.
 */
function bp_komplimente_activate() {
    global $bp, $wpdb;
    $version = get_option( 'bp_komplimente_version');

    if (!$version) {
        $charset_collate = !empty( $wpdb->charset ) ? "DEFAULT CHARACTER SET $wpdb->charset" : '';
        if ( !$table_prefix = $bp->table_prefix )
            /**
             * Filters the value of BuddyPress table prefix.
             *
             * @since 0.0.1
             * @package BuddyPress_Komplimente
             *
             * @param string $wpdb->base_prefix WordPress table prefix.
             */
            $table_prefix = apply_filters( 'bp_core_get_table_prefix', $wpdb->base_prefix );

        $sql = "CREATE TABLE {$table_prefix}bp_komplimente (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			term_id int(10) NOT NULL,
			post_id int(10) NULL DEFAULT NULL,
			receiver_id bigint(20) NOT NULL,
			sender_id bigint(20) NOT NULL,
			message varchar(1000) NULL DEFAULT NULL,
			created_at datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		        KEY komplimente (receiver_id, sender_id)
		) {$charset_collate};";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
        update_option( 'bp_komplimente_version', BP_KOMPLIMENTE_VER );
    }
    
    add_option( 'bp_komplimente_activation_redirect', 1 );
}

if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    register_activation_hook( __FILE__, 'bp_komplimente_activate' );
    register_deactivation_hook( __FILE__, 'bp_komplimente_deactivate' );
    add_action( 'admin_init', 'bp_komplimente_activation_redirect' );
}

/**
 * Plugin deactivation hook.
 *
 * @since 1.0.7
 */
function bp_komplimente_deactivate() {
    // Plugin deactivation stuff.
}

/**
 * Redirects user to BuddyPress Komplimente settings page after plugin activation.
 *
 * @since 1.0.7
 */
function bp_komplimente_activation_redirect() {
    if ( get_option( 'bp_komplimente_activation_redirect', false ) ) {
        delete_option( 'bp_komplimente_activation_redirect' );
        if(class_exists('BuddyPress')){
            wp_redirect( admin_url( 'admin.php?page=bp-komplimente-settings' ) );
            exit;
        }
    }
}

/**
 * Custom text domain loader.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function bp_komplimente_localization() {
    /**
     * Filters the value of plugin locale.
     *
     * @since 0.0.1
     * @package BuddyPress_Komplimente
     */
    $locale = apply_filters('plugin_locale', get_locale(), 'bp-komplimente');

    load_textdomain('bp-komplimente', WP_LANG_DIR . '/' . 'bp-komplimente' . '/' . 'bp-komplimente' . '-' . $locale . '.mo');
    load_plugin_textdomain('bp-komplimente', false, dirname( plugin_basename( __FILE__ ) ) . '/languages');

}
add_action( 'plugins_loaded', 'bp_komplimente_localization' );

function bp_komplimente_older_version_notice() {
    $older_version_notice = __( "Hallo! BP Komplimente benötigt BuddyPress 1.5 oder höher.", 'bp-komplimente' );

    echo '<div class="error"><p>' . $older_version_notice . '</p></div>';
}