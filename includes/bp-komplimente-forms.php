<?php
/**
 * Functions related to kompliment forms.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */

/**
 * Front end modal form.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @param int $pid The post ID.
 * @param int $receiver_id Kompliment receiver ID.
 */
function bp_komplimente_modal_form($pid = 0, $receiver_id = 0 ) {
    if (!$receiver_id) {
	    $receiver_id = bp_displayed_user_id();
    }
    ?>
    <div class="comp-modal">
        <div class="comp-modal-content-wrap">
            <span class="comp-close-x dashicons dashicons-no" title="<?php _e( 'Lightbox schließen', 'bp-komplimente' ); ?>" aria-action="close"></span>
            <div class="comp-modal-title">
                <h2><?php echo sprintf( __( 'Wähle Deinen %s-Typ:', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME ); ?></h2>
            </div>
            <div class="comp-modal-content">
               <form action="" method="post">
                    <?php
                    $args = array(
                        'hide_empty' => false,
                        'orderby'  => 'id'
                    );
                    $terms = get_terms( 'kompliment', $args );
                    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
                        echo '<ul class="comp-form-ul">';
                        $count = 0;
                        foreach ( $terms as $term ) {
                            $count++;
                            $t_id = $term->term_id;
                            $term_meta = get_option( "taxonomy_$t_id" );
                            ?>
                            <li>
                                <label>
                                    <input type="radio" name="term_id" value="<?php echo $term->term_id; ?>" <?php if ($count == 1) { echo 'checked="checked"'; } ?>>
                                <span>
                                    <img style="height: 20px; width: 20px; vertical-align:middle" src='<?php echo esc_attr( $term_meta['komplimente_icon'] ) ? esc_attr( $term_meta['komplimente_icon'] ) : ''; ?>' class='preview-upload'/>
                                    <?php echo $term->name; ?>
                                </span>
                                </label>
                            </li>
                        <?php
                        }
                        echo '</ul>';
                        ?>
                        <textarea placeholder="<?php echo __( 'Schreibe Deine Nachricht hier', 'bp-komplimente' ); ?>" name="message" maxchar="1000"></textarea>
                        <input type="hidden" name="post_id" value="<?php echo $pid; ?>"/>
                        <input type="hidden" name="receiver_id" value="<?php echo absint( $receiver_id ); ?>"/>
                        <?php wp_nonce_field( 'handle_komplimente_form_data','handle_komplimente_nonce' ); ?>
                        <div class="bp-comp-pop-buttons">
                            <button type="submit" class="comp-submit-btn" name="comp-modal-form" value="submit"><?php echo __( 'Senden', 'bp-komplimente' ); ?></button>
                            <a class="bp-comp-cancel" href="#"><?php echo __( 'Abbrechen', 'bp-komplimente' ); ?></a>
                        </div>
                    <?php
                    } else {
                        echo __( 'Keine Komplimente gefunden.', 'bp-komplimente' );
                    }
                    ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function() {
                            jQuery('a.bp-comp-cancel, .comp-close-x').click(function (e) {
                                e.preventDefault();
                                var mod_shadow = jQuery('#bp_komplimente_modal_shadow');
                                var container = jQuery('.comp-modal');
                                container.hide();
                                container.replaceWith("<div class='comp-modal' style='display: none;'><div class='comp-modal-content-wrap'><div class='comp-modal-title comp-loading-icon'><div class='bp-comp-loading-icon'></div></div></div></div>");
                                 mod_shadow.hide();
                            });
                        });
                    </script>
                </form>
            </div>
        </div>
    </div>
<?php
}

/**
 * Komplimente ajax modal form.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function bp_komplimente_modal_ajax()
{
    check_ajax_referer('bp-komplimente-nonce', 'bp_komplimente_nonce');

    //Get the receiver id
    $btn_id = strip_tags($_POST["btn_id"]);
    if ($btn_id && (strpos($btn_id, '-') !== false)) {
        $btn_id = explode("-", $btn_id);
        $btn_id = (int) $btn_id[1];
    }

    if (empty($btn_id)) {
        $btn_id = 0;
    }
    bp_komplimente_modal_form(0, $btn_id);

    wp_die();
}

//Ajax functions
add_action('wp_ajax_bp_komplimente_modal_ajax', 'bp_komplimente_modal_ajax');

//Javascript
add_action('wp_footer', 'bp_komplimente_modal_init');
/**
 * Initialize modal form.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
function bp_komplimente_modal_init() {
    if (!is_user_logged_in()){
        return;
    }

    if (!bp_is_user() && !bp_is_directory()){
        return;
    }

    $ajax_nonce = wp_create_nonce("bp-komplimente-nonce");
    ?>
    <div id="bp_komplimente_modal_shadow" style="display: none;"></div>
    <div class="comp-modal" style="display: none;">
        <div class="comp-modal-content-wrap">
            <div class="comp-modal-title comp-loading-icon">
                <div class="bp-comp-loading-icon"></div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        jQuery(document).ready(function() {
            jQuery('a.komplimente-popup').click(function (e) {
                e.preventDefault();
                var mod_shadow = jQuery('#bp_komplimente_modal_shadow');
                var container = jQuery('.comp-modal');
                var btn_id = jQuery(this).attr('id');
                mod_shadow.show();
                container.show();
                var data = {
                    'action': 'bp_komplimente_modal_ajax',
                    'bp_komplimente_nonce': '<?php echo $ajax_nonce; ?>',
                    'btn_id': btn_id
                };

                jQuery.post('<?php echo admin_url('admin-ajax.php'); ?>', data, function (response) {
                    container.replaceWith(response);
                });
            });
        });
    </script>
<?php
}