<?php
/**
 * Functions hooked to this action will be processed before displaying komplimente page content.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 *
 * @global object $bp BuddyPress instance.
 */
do_action('bp_before_member_' . bp_current_action() . '_content'); ?>

<div class="bp-komplimente-wrap">
    <?php
    $c_id = false;
    $count_args = array(
        'user_id' => bp_displayed_user_id()
    );
    $count_array = bp_komplimente_total_counts($count_args);
    $total = (int)$count_array['received'];

    $comp_per_page_value = esc_attr( get_option('bp_comp_per_page'));
    $items_per_page = $comp_per_page_value ? (int) $comp_per_page_value : 5;

    $bp_kompliment_can_see_others_comp_value = esc_attr( get_option('bp_kompliment_can_see_others_comp'));
    $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_others_comp_value ? $bp_kompliment_can_see_others_comp_value : 'yes';

    if ($bp_kompliment_can_see_others_comp == 'members_choice') {
        $bp_kompliment_can_see_your_comp_value = esc_attr( get_user_meta(bp_displayed_user_id(), 'bp_kompliment_can_see_your_comp', true));
        $bp_kompliment_can_see_others_comp = $bp_kompliment_can_see_your_comp_value ? $bp_kompliment_can_see_your_comp_value : 'yes';
    }

    if (bp_displayed_user_id() == bp_loggedin_user_id()) {
        $bp_kompliment_can_see_others_comp = 'yes';
    } elseif (current_user_can( 'manage_options' )) {
        $bp_kompliment_can_see_others_comp = 'yes';
    }

    $page = isset($_GET['cpage']) ? abs((int)$_GET['cpage']) : 1;
    $offset = ($page * $items_per_page) - $items_per_page;
    $args = array(
        'offset' => $offset,
        'limit' => $items_per_page
    );
    if (isset($_GET['c_id'])) {
        $c_id = (int) strip_tags(esc_sql($_GET['c_id']));
        if ($c_id) {
            $args['c_id'] = $c_id;
        }

    }
    $komplimente = bp_komplimente_get_komplimente($args);
    $start = $offset ? $offset : 1;
    $end = $offset + $items_per_page;
    $end = ($end > $total) ? $total : $end;

    if (isset($_GET['c_id'])) {
        foreach ($komplimente as $comp) {
            $author_id = $comp->sender_id;
            if ($author_id == bp_loggedin_user_id()) {
                $bp_kompliment_can_see_others_comp = 'yes';
            }
        }
    }

    if ( ($komplimente && $bp_kompliment_can_see_others_comp == 'yes') || ( $komplimente && is_user_logged_in() && $bp_kompliment_can_see_others_comp == 'members_only' )) {
        ?>
        <div class="comp-user-content">
            <ul class="comp-user-ul">
                <?php
                foreach ($komplimente as $comp) {
                    $t_id = $comp->term_id;
                    $term = get_term_by('id', $t_id, 'kompliment');
                    $term_meta = get_option("taxonomy_$t_id");
                    ?>
                    <li>
                        <div class="comp-user-header">
                          <span>
                            <?php
                            $komplimente_icon = esc_attr($term_meta['komplimente_icon']) ? esc_attr($term_meta['komplimente_icon']) : '';
                            if (is_ssl()) {
                                $komplimente_icon = str_replace('http://', 'https://', $komplimente_icon);
                            }
                            ?>
                              <img style="height: 20px; width: 20px; vertical-align:middle"
                                   src='<?php echo $komplimente_icon; ?>'
                                   class='preview-upload'/>
                              <?php echo $term->name; ?>
                           </span>
                            <em>
                                <?php echo date_i18n(get_option('date_format'), strtotime($comp->created_at)); ?>
                            </em>
                            <?php
                            global $bp;
                            $bp_kompliment_can_delete_value = esc_attr( get_option('bp_kompliment_can_delete'));
                            $bp_kompliment_can_delete = $bp_kompliment_can_delete_value ? $bp_kompliment_can_delete_value : 'yes';

                            if (current_user_can( 'manage_options' )) {
                                $bp_kompliment_can_delete = 'yes';
                            }

                            //Admins can delete any complement
                            $current_user_can_delete = current_user_can( 'manage_options' );

                            //Complement sender and receiver can delete kompliment if admin has allowed it
                            if( is_user_logged_in() ){
                                //Is this the receiver
                                if( $bp->loggedin_user->id == $bp->displayed_user->id ){
                                    $current_user_can_delete = true;
                                }
                                //Is this the sender
                                if( $bp->loggedin_user->id == $comp->sender_id ){
                                    $current_user_can_delete = true;
                                }
                            }
                            if ( $current_user_can_delete && ($bp_kompliment_can_delete == 'yes')) {
                                $receiver_url    = bp_core_get_userlink( $comp->receiver_id, false, true );
                                $kompliment_url = $receiver_url . BP_KOMPLIMENTE_SLUG . '/?c_id='.$comp->id.'&action=delete';
                                ?>
                                <a href="<?php echo $kompliment_url; ?>" class="button item-button confirm" style="float: right;"><?php echo __('Löschen', 'bp-komplimente'); ?></a>
                            <?php } ?>
                        </div>
                        <div class="comp-user-msg-wrap">
                            <div class="comp-user-message">
                                <?php $author_id = $comp->sender_id; ?>
                                <div class="comp-user">
                                    <div class="comp-user-avatar">
                                        <?php
                                        $user = get_user_by('id', $author_id);
                                        $name = apply_filters('bp_komplimente_item_user_name', $user->display_name, $user);
                                        $user_link = apply_filters('bp_komplimente_item_user_link', bp_core_get_user_domain($author_id), $author_id);
                                        $avatar_size = apply_filters('bp_komplimente_item_user_avatar_size', 60);
                                        ?>
                                        <?php echo get_avatar($author_id, $avatar_size); ?>
                                        <div class="comp-username">
                                            <a href="<?php echo $user_link; ?>" class="url"><?php echo $name; ?></a>
                                            <?php do_action('bp_komplimente_after_user_name', $author_id); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                $comp_message = apply_filters('bp_comp_message', $comp->message);
                                echo $comp_message; ?>
                            </div>
                        </div>
                    </li>
                <?php
                } ?>
            </ul>
        </div>
        <?php
        if (($total > $items_per_page) && !$c_id) { ?>
            <div id="pag-top" class="pagination">
                <div class="pag-count" id="member-dir-count-top">
                    <?php echo sprintf(_n('1 of 1', '%1$s to %2$s of %3$s', $total, 'bp-komplimente'), $start, $end, $total); ?>
                </div>
                <div class="pagination-links">
                    <span class="bp-comp-pagination-text"><?php echo __('Gehe zur Seite', 'bp-komplimente') ?></span>
                    <?php
                    echo paginate_links(array(
                        'base' => esc_url(add_query_arg('cpage', '%#%')),
                        'format' => '',
                        'prev_next' => false,
                        'total' => ceil($total / $items_per_page),
                        'current' => $page
                    ));
                    ?>
                </div>
            </div>
        <?php }
    } elseif($bp_kompliment_can_see_others_comp == 'no') {
        ?>
        <div id="message" class="bp-no-komplimente info">
            <p><?php echo __('Du hast keine Berechtigung, auf diese Seite zuzugreifen.', 'bp-komplimente'); ?></p>
        </div>
    <?php
    } else {
        if (bp_displayed_user_id() == bp_loggedin_user_id()) {
            ?>
            <div id="message" class="bp-no-komplimente info">
                <p><?php echo sprintf( __( 'Schade, du hast noch keine %1$s erhalten. Um einige zu erhalten, sende doch %1$s an andere.', 'bp-komplimente' ), BP_COMP_SINGULAR_NAME ); ?></p>
            </div>
        <?php
        } else {
            ?>
            <div id="message" class="bp-no-komplimente info">
                <p><?php echo sprintf( __( 'Entschuldigung, noch keine %1$s.', 'bp-komplimente' ), BP_COMP_PLURAL_NAME ); ?></p>
            </div>
        <?php
        }
    }
    ?>
</div>

<?php
/**
 * Functions hooked to this action will be processed after displaying komplimente page content.
 *
 * @since 0.0.1
 * @package BuddyPress_Komplimente
 */
do_action('bp_after_member_' . bp_current_action() . '_content'); ?>