

		<div class="formworks-config-group">
			<label for="formworks-rebuild">
				<?php esc_html_e( 'Rebuild Database', 'formworks' ); ?>
			</label>
			<button class="button wp-baldrick"
			 data-modal="warning"
			 data-modal-title="<?php echo esc_attr( __('Rebuild Database', 'formworks') ); ?>"
			 data-request="frmwks_prepdb_rebuild"
			 data-type="json"
			 data-template="#rebuild-db-tml"
			 data-modal-width="400"
			 data-modal-height="190" type="button"><?php esc_html_e('Rebuild Database', 'formworks'); ?></button>			
		</div>

