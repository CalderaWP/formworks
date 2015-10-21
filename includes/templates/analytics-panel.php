

	<p>Follow these instructions to setup goals in Google Analytics to make tracking easier.</p>
	<ol>
	    <li>Login to Google Analytics and click on <strong>Admin</strong> in the main navigation.</li>
	    <li>Select the <em>Account</em> and <em>Property</em> where you want to create the goal. Under the <em>View</em> list, click on <strong>Goals</strong></li>
	    <li>Click on the <strong>New Goal</strong> button, click on the <strong>Custom</strong> radio button and then click on the <strong>Next step</strong> button.</li>
	    <li>Name the goal and select the <strong>Event</strong> radio button.</li>
	    <li>Populate all of the relevant goal details (in bold) Case sensitive:
	        <ul>
	            <li>Category | that matches | <strong>Form</strong></li>
	            <li>Action | that matches | <strong>load</strong> or <strong>view</strong> or <strong>engage</strong> or <strong>submit</strong> <em>( Use a single one for a goal. Create multiple goals for each event type you want to track)</em></li>
	            <li>Label | that matches | <strong><em>form name you want to track</em></strong></li>
	            <li>Value | that matches | <em>Leave this blank</em></li>
	        </ul>
	    </li>
	    <li>Click the <strong>Create Goal</strong> button.</li>
	</ol>

	<br>
	<h4>
		<?php _e( 'Google Analytics ID', 'formworks' ); ?>
	</h4>
	<p class="description"><?php _e( "If you're site is already using Google Analytics, this can be ignored as it will automatically be detected and events pushed to the existing implementation.", 'formworks' ); ?></p>
	<div class="formworks-config-group">
		<label for="formworks-template-ga">
			<?php _e( 'Number of page links', 'formworks' ); ?>
		</label>
		<input id="formworks-template-ga" type="text" name="external[ga]" value="{{external/ga}}">		
	</div>