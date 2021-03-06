<?php
/**
 * @file form-buttons.php
 * @global GravityView_Edit_Entry_Render $object
 */

if ( current_filter() === 'gform_previous_button' ) {
	if ( $object->show_previous_button || $object->show_update_button ) {
		return; // Will be called later once more
	}
}

if ( current_filter() === 'gform_next_button' ) {
	if ( $object->show_update_button ) {
		return; // Will be called later once more
	}
}

?>
<div id="publishing-action">
	<?php

    /**
     * @filter `gravityview/edit_entry/cancel_link` Modify the cancel button link URL
     * @since 1.11.1
     * @param string $back_link Existing URL of the Cancel link
     * @param array $form The Gravity Forms form
     * @param array $entry The Gravity Forms entry
     * @param int $view_id The current View ID
     */
    $back_link = apply_filters( 'gravityview/edit_entry/cancel_link', remove_query_arg( array( 'page', 'view', 'edit' ) ), $object->form, $object->entry, $object->view_id );

	/**
	 * @action `gravityview/edit-entry/publishing-action/before` Triggered before the submit buttons in the Edit Entry screen, inside the `<div id="publishing-action">` container.
	 * @since 1.5.1
	 * @param array $form The Gravity Forms form
	 * @param array $entry The Gravity Forms entry
	 * @param int $view_id The current View ID
	 */
	do_action( 'gravityview/edit-entry/publishing-action/before', $object->form, $object->entry, $object->view_id );


	$labels = array(
		'cancel'   => __( 'Cancel', 'gravityview' ),
		'submit'   => __( 'Update', 'gravityview' ),
		'next'     => __( 'Next', 'gravityview' ),
		'previous' => __( 'Previous', 'gravityview' ),
	);

	/**
	 * @filter `gravityview/edit_entry/button_labels` Modify the cancel/submit buttons' labels
	 * @since 1.16.3
	 * @param array $labels Default button labels associative array
	 * @param array $form The Gravity Forms form
	 * @param array $entry The Gravity Forms entry
	 * @param int $view_id The current View ID
	 */
	$labels = apply_filters( 'gravityview/edit_entry/button_labels', $labels, $object->form, $object->entry, $object->view_id );

	if ( $object->show_previous_button ) {
		$previous_tabindex = GFCommon::get_tabindex();
		?>
		<input id="gform_previous_button_<?php echo esc_attr( $object->form['id'] ); ?>" class="btn btn-lg button button-large gform_button button-primary gv-button-previous" type="submit" <?php echo $previous_tabindex; ?> value="<?php echo esc_attr( $labels['previous'] ); ?>" name="save" />
		<?php
	}

	if ( $object->show_next_button ) {
		$next_tabindex    = GFCommon::get_tabindex();
		?>
		<input id="gform_next_button_<?php echo esc_attr( $object->form['id'] ); ?>" class="btn btn-lg button button-large gform_button button-primary gv-button-next" type="submit" <?php echo $next_tabindex; ?> value="<?php echo esc_attr( $labels['next'] ); ?>" name="save" />
		<?php
	}

	if ( $object->show_update_button ) {
		$update_tabindex  = GFCommon::get_tabindex();
		?>
		<input id="gform_submit_button_<?php echo esc_attr( $object->form['id'] ); ?>" class="btn btn-lg button button-large gform_button button-primary gv-button-update" type="submit" <?php echo $update_tabindex; ?> value="<?php echo esc_attr( $labels['submit'] ); ?>" name="save" />
		<?php
	}

	$cancel_tabindex   = GFCommon::get_tabindex();

	?>
	<a class="btn btn-sm button button-small gv-button-cancel" <?php echo $cancel_tabindex; ?> href="<?php echo esc_url( $back_link ); ?>"><?php echo esc_attr( $labels['cancel'] ); ?></a>
	<?php

	/**
	 * @action `gravityview/edit-entry/publishing-action/after` Triggered after the submit buttons in the Edit Entry screen, inside the `<div id="publishing-action">` container.
	 * @since 1.5.1
     * @since 2.0.13 Added $post_id
	 * @param array $form The Gravity Forms form
	 * @param array $entry The Gravity Forms entry
	 * @param int $view_id The current View ID
     * @param int $post_id The current Post ID
	 */
	do_action( 'gravityview/edit-entry/publishing-action/after', $object->form, $object->entry, $object->view_id, $object->post_id );

	?>
	<input type="hidden" name="action" value="update" />
	<input type="hidden" name="lid" value="<?php echo esc_attr( $object->entry['id'] ); ?>" />
</div>
