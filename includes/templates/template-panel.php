
	<textarea id="template-code-editor" class="formworks-code-editor" data-mode="mustache" name="template[code]">{{template/code}}</textarea>
	
	{{#is _current_tab value="#formworks-panel-template"}}
		{{#script}}
		frmwks_init_editor('template-code-editor');
		{{/script}}
	{{/is}}

	<span class="formworks-autocomplete-in-entry-mustache" data-parent="entry" data-slug="_date" data-label="_date"></span>
	<span class="formworks-autocomplete-in-entry-mustache" data-parent="entry" data-slug="_entry_id" data-label="_entry_id"></span>
	<span class="formworks-autocomplete-out-entry-mustache" data-slug="active" data-label="active"></span>
	<span class="formworks-autocomplete-out-entry-mustache" data-slug="total" data-label="total"></span>
	<span class="formworks-autocomplete-out-entry-mustache" data-slug="pages" data-label="pages"></span>
	<span class="formworks-autocomplete-out-entry-mustache" data-slug="current_page" data-label="current_page"></span>
	<span class="formworks-autocomplete-out-entry-mustache" data-slug="label" data-label="label"></span>
	<span class="formworks-autocomplete-out-entry-mustache" data-parent="label" data-slug="this" data-label="this"></span>
	<span class="formworks-autocomplete-out-entry-mustache" data-slug="entry" data-label="entry"></span>
	
	<?php
	$form = Caldera_Forms::get_form( $formworks['form'] );
	foreach( $form['fields'] as $field ){		
		?>
		<span class="formworks-autocomplete-in-entry-mustache" data-parent="entry" data-slug="data.<?php echo $field['slug']; ?>" data-label="data.<?php echo $field['slug']; ?>"></span>
		<span class="formworks-autocomplete-out-entry-mustache" data-slug="label.<?php echo $field['slug']; ?>" data-label="label.<?php echo $field['slug']; ?>"></span>
		<span class="formworks-autocomplete-in-entry-mustache" data-parent="data.<?php echo $field['slug']; ?>" data-slug="value" data-label="value"></span>
		<span class="formworks-autocomplete-in-entry-mustache" data-parent="data.<?php echo $field['slug']; ?>" data-slug="label" data-label="label"></span>
		<?php
	}
	?>
	
	
