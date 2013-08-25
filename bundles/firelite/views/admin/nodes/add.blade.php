		@section('document_title')
			@parent
			Pages/Add
		@endsection
		
<div class="panel">
	<div class="panel-header">
		<h2>Pages - Add</h2>
	</div>
	<div class="panel-content">
		<?=Form::open();?>
		
		<?php
		echo $parent_node_id;
		echo Form::label( 'page_template_id', 'Template:' ),'<br />', Form::select( 'page_template_id', $templates, Input::get('page_template_id') ), '<br /><br />';
		echo Form::label( 'page_parent_node_id', 'Parent:' ),'<br />',  Form::select('page_parent_node_id', $parent_pages, Input::get('page_parent_node_id', $parent_node_id)), '<br /><br />';
		
		$props = array(
			'name' => 'URL Segment',
			'title' => 'Title',
			'link_text' => 'Link Text',
			'link_title' => 'Link Title',
		);

		foreach ( $props as $prop_name => $label ){
			$input_id = 'page_' . $prop_name;
			echo Form::label( $input_id, $label ),
				Form::input( 'text',  $input_id, Input::get( $input_id, '' ), array('class' => 'inp-text') );
		}
		/*
		//until a template is chosen, we can't know what the page fields will be
		foreach ( $page->template->fields as $field ){
			echo Form::label( 'page_field_' . $field->name, $field->label ), '<br />';
			echo $field->editor( 'page_field_' . $field->name, $page->getField( $field->name ) ), '<br />';
		}
		*/
		?>
		<br /><br />
		<p>Once you have added the page, you will be able to edit its fields</p><br />
		<input type="submit" class="yui3-button button-positive" value="Add Page" />
		<?=Form::token();?>
		<?=Form::close();?>
	</div>
</div>
