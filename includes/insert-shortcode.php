<?php
/**
 *The shortcode insert modal.
 *
 * @package   Formworks
 * @author    David Cramer
 * @license   GPL-2.0+
 * @link
 * @copyright 2015 David Cramer
 */
?>

<div class="formworks-backdrop formworks-insert-modal" style="display: none;"></div>
<div id="formworks_shortcode_modal" class="formworks-modal-wrap formworks-insert-modal" style="display: none; width: 600px; max-height: 500px; margin-left: -300px;">
	<div class="formworks-modal-title" id="formworks_shortcode_modalTitle" style="display: block;">
		<a href="#close" class="formworks-modal-closer" data-dismiss="modal" aria-hidden="true" id="formworks_shortcode_modalCloser">×</a>
		<h3 class="modal-label" id="formworks_shortcode_modalLable"><?php echo __('Insert Form View', 'formworks'); ?></h3>
	</div>
	<div class="formworks-modal-body none" id="formworks_shortcode_modalBody">
		<div class="modal-body">

		<?php

			$formworks = \calderawp\frmwks\options::get_registry();
			

			if(!empty($formworks)){
				foreach( $formworks as $formworks_id => $formwork ){
					if( false === strpos( $formwork['type'], 'front_' ) ){
						continue;
					}
					echo '<div class="modal-list-item-frmwks"><label><input name="insert_formworks_id" autocomplete="off" class="selected-formworks-shortcode" value="' . $formwork['slug'] . '" type="radio">' . $formwork['name'];
					echo ' </label></div>';
					$has = true;
				}
			}
			if( empty( $has ) ){
				echo '<p>' . esc_html__('You don\'t have any Formworks Frontend Views to insert.', 'formworks') .'</p>';
			}

		?>


		</div>
	</div>
	<div class="formworks-modal-footer" id="formworks_shortcode_modalFooter" style="display: block;">
		<p class="modal-label-subtitle" style="text-align: right;">
			<button class="button formworks-shortcode-insert" style="margin:5px 25px 0 15px;">
				<?php echo esc_html__('Insert Selected', 'formworks'); ?>
			</button>
		</p>
	</div>
</div>
