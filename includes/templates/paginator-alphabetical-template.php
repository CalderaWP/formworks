<?php
$var = 'A';
?><ul style="float: left; margin: 0 0 0 5px; padding: 5px 0px;">
	<li style="display: inline-block; margin: 0px 0px 0px 0px;"><a style="text-decoration: none; display: inline-block; padding: 3px;" href="#0"><?php _e('All', 'formworks'); ?></a>		
	<?php 
	for( $i = 0; $i < 26; $i++ ){
		if( $i === 3 ){
		?>
			<li style="display: inline-block; margin: 0px 0px 0px 0px;"><a style="text-decoration: none; display: inline-block; padding: 3px; background: rgb(159, 159, 159) none repeat scroll 0% 0%; color: rgb(255, 255, 255); border-radius: 3px;" class="current" href="#0"><?php echo $var++; ?></a>
		<?php }else{ ?>
			<li style="display: inline-block; margin: 0px 0px 0px 0px;"><a style="text-decoration: none; display: inline-block; padding: 3px;" href="#0"><?php echo $var++; ?></a>		
		<?php } ?>
	<?php } ?>
</ul>