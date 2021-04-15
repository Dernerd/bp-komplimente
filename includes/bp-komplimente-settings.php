<?php
add_action('admin_menu', 'register_komplimente_menu_page');

/**
 * Register Komplimente menu below Settings menu.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function register_komplimente_menu_page() {
	add_menu_page(
		BP_COMP_PLURAL_NAME,
		BP_COMP_PLURAL_NAME,
		'manage_options',
		'bp-komplimente-settings',
		'bp_komplimente_settings_page',
		plugins_url( 'bp-komplimente/images/smiley-icon.png' ),
		85
	);
}

add_action( 'admin_init', 'bp_komplimente_register_settings' );
function bp_komplimente_register_settings() {
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_singular_name' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_plural_name' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_slug' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_can_see_others_comp' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_member_dir_btn' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_can_delete' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_enable_activity' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_enable_notifications' );
	register_setting( 'bp-komplimente-settings', 'bp_comp_per_page' );
	register_setting( 'bp-komplimente-settings', 'bp_comp_custom_css' );
	register_setting( 'bp-komplimente-settings', 'bp_kompliment_uninstall_delete');
}

function bp_komplimente_settings_page() {
	?>
	<div class="wrap">
		<h2><?php echo sprintf( __( 'BuddyPress %s - Einstellungen', 'bp-komplimente' ), BP_COMP_PLURAL_NAME ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'bp-komplimente-settings' ); ?>
			<?php do_settings_sections( 'bp-komplimente-settings' );

			$bp_kompliment_can_see_others_comp_value = esc_attr( get_option('bp_kompliment_can_see_others_comp'));
			$bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_others_comp_value ? $bp_kompliment_can_see_others_comp_value : 'yes';

			$bp_kompliment_member_dir_btn_value = esc_attr( get_option('bp_kompliment_member_dir_btn'));
			$bp_kompliment_member_dir_btn = $bp_kompliment_member_dir_btn_value ? $bp_kompliment_member_dir_btn_value : 'no';

			$bp_kompliment_can_delete_value = esc_attr( get_option('bp_kompliment_can_delete'));
			$bp_kompliment_can_delete = $bp_kompliment_can_delete_value ? $bp_kompliment_can_delete_value : 'yes';

			$bp_kompliment_enable_activity_value = esc_attr( get_option('bp_kompliment_enable_activity'));
			$bp_kompliment_enable_activity = $bp_kompliment_enable_activity_value ? $bp_kompliment_enable_activity_value : 'yes';

			$bp_kompliment_enable_notifications_value = esc_attr( get_option('bp_kompliment_enable_notifications'));
			$bp_kompliment_enable_notifications = $bp_kompliment_enable_notifications_value ? $bp_kompliment_enable_notifications_value : 'yes';

			$comp_per_page_value = esc_attr( get_option('bp_comp_per_page'));
			$comp_per_page = $comp_per_page_value ? (int) $comp_per_page_value : 5;

			$comp_custom_css_value = esc_attr( get_option('bp_comp_custom_css'));
			$comp_custom_css = $comp_custom_css_value ? $comp_custom_css_value : '';

			$bp_kompliment_uninstall_delete_value = esc_attr( get_option('bp_kompliment_uninstall_delete'));
			$bp_kompliment_uninstall_delete = $bp_kompliment_uninstall_delete_value ? $bp_kompliment_uninstall_delete_value : 'no';
			?>
			<table class="widefat fixed" style="padding:10px;margin-top: 10px;">
				<tr valign="top">
					<th scope="row"><?php echo __( 'Singular Name (z.B. Geschenk. Standard: Kompliment)', 'bp-komplimente' ); ?></th>
					<td><input type="text" class="widefat" name="bp_kompliment_singular_name" value="<?php echo BP_COMP_SINGULAR_NAME; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo __( 'Pluralname (z.B. Geschenke. Standard: Komplimente)', 'bp-komplimente' ); ?></th>
					<td><input type="text" class="widefat" name="bp_kompliment_plural_name" value="<?php echo BP_COMP_PLURAL_NAME; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo __( 'Slug (z.B. geschenke. Standard: komplimente. Muss Kleinbuchstaben sein)', 'bp-komplimente' ); ?></th>
					<td><input type="text" class="widefat" name="bp_kompliment_slug" value="<?php echo BP_KOMPLIMENTE_SLUG; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo sprintf( __( 'Wer kann die %s Seite anderer Mitglieder sehen?', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME ); ?></th>
					<td>
						<select id="bp_kompliment_can_see_others_comp" name="bp_kompliment_can_see_others_comp">
							<option value="yes" <?php selected( $bp_kompliment_can_see_others_comp, 'yes' ); ?>><?php echo __( 'Jeder', 'bp-komplimente' ); ?></option>
							<option value="no" <?php selected( $bp_kompliment_can_see_others_comp, 'no' ); ?>><?php echo __( 'Niemand', 'bp-komplimente' ); ?></option>
							<option value="members_only" <?php selected( $bp_kompliment_can_see_others_comp, 'members_only' ); ?>><?php echo __( 'Nur Mitglieder', 'bp-komplimente' ); ?></option>
							<option value="members_choice" <?php selected( $bp_kompliment_can_see_others_comp, 'members_choice' ); ?>><?php echo __( 'Lasse die Mitglieder sich um diese Einstellung kümmern', 'bp-komplimente' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo sprintf( __( 'Schaltfläche "%s senden" auf der Seite/Mitglieder anzeigen?', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME ); ?></th>
					<td>
						<select id="bp_kompliment_can_delete" name="bp_kompliment_member_dir_btn">
							<option value="yes" <?php selected( $bp_kompliment_member_dir_btn, 'yes' ); ?>><?php echo __( 'Ja', 'bp-komplimente' ); ?></option>
							<option value="no" <?php selected( $bp_kompliment_member_dir_btn, 'no' ); ?>><?php echo __( 'Nein', 'bp-komplimente' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo sprintf( __( 'Mitglieder können erhaltene %s löschen?', 'bp-komplimente' ), BP_COMP_PLURAL_NAME ); ?></th>
					<td>
						<select id="bp_kompliment_can_delete" name="bp_kompliment_can_delete">
							<option value="yes" <?php selected( $bp_kompliment_can_delete, 'yes' ); ?>><?php echo __( 'Ja', 'bp-komplimente' ); ?></option>
							<option value="no" <?php selected( $bp_kompliment_can_delete, 'no' ); ?>><?php echo __( 'Nein', 'bp-komplimente' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo sprintf( __( 'Aktivitätskomponente für %s aktivieren?', 'bp-komplimente' ), BP_COMP_PLURAL_NAME ); ?></th>
					<td>
						<select id="bp_kompliment_enable_activity" name="bp_kompliment_enable_activity">
							<option value="yes" <?php selected( $bp_kompliment_enable_activity, 'yes' ); ?>><?php echo __( 'Ja', 'bp-komplimente' ); ?></option>
							<option value="no" <?php selected( $bp_kompliment_enable_activity, 'no' ); ?>><?php echo __( 'Nein', 'bp-komplimente' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo sprintf( __( 'Benachrichtigungskomponente für %s aktivieren?', 'bp-komplimente' ), BP_COMP_PLURAL_NAME ); ?></th>
					<td>
						<select id="bp_kompliment_enable_notifications" name="bp_kompliment_enable_notifications">
							<option value="yes" <?php selected( $bp_kompliment_enable_notifications, 'yes' ); ?>><?php echo __( 'Ja', 'bp-komplimente' ); ?></option>
							<option value="no" <?php selected( $bp_kompliment_enable_notifications, 'no' ); ?>><?php echo __( 'Nein', 'bp-komplimente' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo sprintf( __( 'Anzahl der %s, die pro Seite angezeigt werden sollen?', 'bp-komplimente' ), BP_COMP_PLURAL_NAME ); ?></th>
					<td><input type="number" class="widefat" name="bp_comp_per_page" value="<?php echo $comp_per_page; ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo __( 'Benutzerdefinierte CSS-Stile', 'bp-komplimente' ); ?></th>
					<td><textarea class="widefat" rows="5" name="bp_comp_custom_css"><?php echo $comp_custom_css; ?></textarea></td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php echo sprintf( __( 'Plugin-Daten löschen, wenn Du BP Komplimente deinstallieren?', 'bp-komplimente' ), BP_COMP_PLURAL_NAME ); ?></th>
					<td>
						<select id="bp_kompliment_uninstall_delete" name="bp_kompliment_uninstall_delete">
							<option value="yes" <?php selected( $bp_kompliment_uninstall_delete, 'yes' ); ?>><?php echo __( 'Ja', 'bp-komplimente' ); ?></option>
							<option value="no" <?php selected( $bp_kompliment_uninstall_delete, 'no' ); ?>><?php echo __( 'Nein', 'bp-komplimente' ); ?></option>
						</select>
					</td>
				</tr>
				<tr valign="top">
					<th></th>
					<td><?php submit_button(null, 'primary','submit',false); ?></td>
				</tr>
			</table>

		</form>
	</div>
<?php }

//BuddyPress user settings
function bp_comp_settings_submenu() {
	global $bp;

	$bp_kompliment_can_see_others_comp_value = esc_attr( get_option('bp_kompliment_can_see_others_comp'));
	$bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_others_comp_value ? $bp_kompliment_can_see_others_comp_value : 'yes';

	if ($bp_kompliment_can_see_others_comp == 'members_choice') {
		if (!bp_is_active('settings')) {
			return;
		}

		if (bp_displayed_user_domain()) {
			$user_domain = bp_displayed_user_domain();
		} elseif (bp_loggedin_user_domain()) {
			$user_domain = bp_loggedin_user_domain();
		} else {
			$user_domain = null;
		}

		// Get the settings slug
		$settings_slug = bp_get_settings_slug();

		bp_core_new_subnav_item(array(
				'name' => __('Komplimente', 'bp-komplimente'),
				'slug' => 'komplimente',
				'parent_url' => trailingslashit($user_domain . $settings_slug),
				'parent_slug' => $settings_slug,
				'screen_function' => 'bp_comp_settings_submenu_page_content',
				'position' => 20,
				'user_has_access' => bp_core_can_edit_settings()
		));
	}

}
add_action('bp_setup_nav', 'bp_comp_settings_submenu', 16);

function bp_comp_settings_submenu_page_content() {
	//add title and content here - last is to call the members plugin.php template
	//add_action( 'bp_template_title', 'bp_comp_settings_submenu_page_show_screen_title' );
	add_action( 'bp_template_content', 'bp_comp_settings_submenu_page_show_screen_content' );
	bp_core_load_template( apply_filters( 'bp_core_template_plugin', 'members/single/plugins' ) );
}

//function bp_comp_settings_submenu_page_show_screen_title() {
//	echo __('Komplimente Settings');
//}

function bp_comp_settings_submenu_page_show_screen_content() {
	if (bp_displayed_user_id()) {
		$user_id = bp_displayed_user_id();
	} elseif (bp_loggedin_user_id()) {
		$user_id = bp_loggedin_user_id();
	} else {
		$user_id = null;
	}

	if (isset( $_POST['bp-comp-settings-submit'] )) {
		if (! isset( $_POST['bp-compl-settings-field'] ) || ! wp_verify_nonce( $_POST['bp-compl-settings-field'], 'bp_compl_settings_action' )) {
			?>
			<div id="message" class="error">
				<p>
					<?php echo __('Bei Deinem Formular ist ein Fehler aufgetreten. Bitte wende Dich an den Administrator.', 'bp-komplimente'); ?>
				</p>
			</div>
			<?php
		} else {
			// process form data
			$bp_kompliment_can_see_your_comp_form_value = $_POST['bp_kompliment_can_see_your_comp'];
			update_user_meta($user_id, 'bp_kompliment_can_see_your_comp', $bp_kompliment_can_see_your_comp_form_value);
			?>
			<div id="message" class="updated">
				<p>
					<?php echo __('Einstellung erfolgreich aktualisiert.', 'bp-komplimente'); ?>
				</p>
			</div>
			<?php
		}
	}

	$bp_kompliment_can_see_your_comp_value = esc_attr( get_user_meta($user_id, 'bp_kompliment_can_see_your_comp', true));
	$bp_kompliment_can_see_your_comp = $bp_kompliment_can_see_your_comp_value ? $bp_kompliment_can_see_your_comp_value : 'yes';
	?>
	<form method="post" class="standard-form">
		<table class="profile-settings">
			<thead>
			<tr>
				<th class="title" colspan="2"><?php echo __('Komplimente Einstellungen', 'bp-komplimente'); ?></th>
			</tr>
			</thead>
			<tbody>
			<tr>
				<td class="field-name"><label><?php echo __('Wer kann Deine Kompliment-Seite sehen?', 'bp-komplimente'); ?></label></td>
				<td class="field-visibility">
					<select id="bp_kompliment_can_see_your_comp" class="bp-xprofile-visibility" name="bp_kompliment_can_see_your_comp">
						<option value="yes" <?php selected( $bp_kompliment_can_see_your_comp, 'yes' ); ?>><?php echo __( 'Jeder', 'bp-komplimente' ); ?></option>
						<option value="no" <?php selected( $bp_kompliment_can_see_your_comp, 'no' ); ?>><?php echo __( 'Niemand', 'bp-komplimente' ); ?></option>
						<option value="members_only" <?php selected( $bp_kompliment_can_see_your_comp, 'members_only' ); ?>><?php echo __( 'Nur Mitglieder', 'bp-komplimente' ); ?></option>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<div class="submit">
			<?php wp_nonce_field( 'bp_compl_settings_action', 'bp-compl-settings-field' ); ?>
			<input type="submit" name="bp-comp-settings-submit" value="Save Settings" class="auto">
		</div>
	</form>
<?php
}