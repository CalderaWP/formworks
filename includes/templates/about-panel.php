<div id="learn-more">
	<?php echo
		sprintf( esc_html__( 'Learn more about how to use FormWorks %s', 'formworks' ),
			sprintf(
				'<a href="https://calderawp.com/?post_type=doc&p=10400" title="%s" target="_blank">%s</a>',
				esc_html__( 'FormWorks Documentation', 'formworks' ) ,
				esc_html__( 'here.', 'formworks' )
			)
		); ?>
	<p>
		<?php
			esc_html_e( 'Support is available for those with a valid FormWorks license.', 'formworks' );
			echo ' ';
			echo sprintf( '<a href="https://CalderaWP.com/support" target="_blank">%s</a>', esc_html( 'Click here for support.', 'formworks' ) );
		?>
	</p>

</div>

<div id="licensing">
	<h4>
		<?php esc_html_e( 'Licensing', 'formworks' );?>
	</h4>
	<div>
		<?php echo frmwks_license_display(); ?>
	</div>
</div>


<div id="featured">
	<h4><?php esc_html_e( 'More Cool Plugins From CalderaWP', 'formworks' ); ?></h4>
	<?php echo frmwks_cwp_featured(); ?>
</div>
