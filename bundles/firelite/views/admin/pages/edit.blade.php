		@section('document_title')
			@parent
			Pages/Edit - <?=$page->name;?>
		@endsection
		
<div class="panel">
	<div class="panel-header">
		<h2>Pages - Edit (<?=$page->title; ?>)</h2>
	</div>
	<div class="panel-content">
		<?php
		if ($page->hasErrors()){
			
		?>
		<div class="errors">
			<?php foreach($page->getErrors() as $name=>$error){ ?>
			<?=ucfirst($name), ':', $error;?>
			<?php } ?>
		</div>
		<?php
		}
		
		echo Form::open();
		echo 'Template: ', $page->template->name, '<br /><br />';

		
		echo Form::label( 'page_parent_node_id', 'Parent:' );
		echo Form::select('page_parent_node_id', $parent_pages, Input::get('page_parent_node_id', $page->node->parent_node_id)), '<br /><br />';
		
		//echo Form::input('hidden', 'page_parent_node_id', $page->node->parent_node_id);
		echo Form::input('hidden', 'page_template_id', $page->template->id);
		
		$props = array(
			'name' => 'URL Segment',
			'title' => 'Page Title',
			'link_text' => 'Link Text',
			'link_title' => 'Link Title',
		);
		
		foreach($props as $prop_name=>$label){
			echo Form::label($prop_name, $label);
			echo Form::input('text', 'page_' . $prop_name, $page->{$prop_name}, array('class'=>'inp-text'));
		}
		echo '<br />';
		echo Form::label( 'page_published', 'Published:' );
		echo Form::checkbox('page_published', 1, Input::get('page_published', $page->isPublished())), '<br /><br />';
		
		foreach ( $page->template->fields()->order_by('sort_order', 'ASC')->get() as $field ){
			echo Form::label( 'page_field_' . $field->name, $field->label ), '<br />';
			echo $field->editor( 'page_field_' . $field->name, $page->getField( $field->name ), array('field_unique_id' => $page->node_id) ), '<br />';
		}
		
		?>
			<input type="submit" class="yui3-button button-positive" value="Update Page" />
		<?=Form::token();?>
		<?=Form::close();?>
	</div>
</div>
