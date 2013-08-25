		@section('document_title')
			@parent
			Posts/Edit - <?=$post->name;?>
		@endsection
		
<div class="panel">
	<div class="panel-header">
		<h2>Post - Edit (<?=$post->title; ?>)</h2>
	</div>
	<div class="panel-content">
		<?php
		if ($post->hasErrors()){
			
		?>
		<div class="errors">
			<?php foreach($post->getErrors() as $name=>$error){ ?>
			<?=ucfirst($name), ':', $error;?>
			<?php } ?>
		</div>
		<?php
		}
		
		echo Form::open();
		echo 'Template: ', $post->template->name, '<br /><br />';
		
		
		//echo Form::input('hidden', 'page_parent_node_id', $post->node->parent_node_id);
		echo Form::input('hidden', 'post_template_id', $post->template->id);
		
		$props = array(
			'name' => 'Name',
			'title' => 'Title',
		);
		
		foreach($props as $prop_name=>$label){
			echo Form::label($prop_name, $label);
			echo Form::input('text', 'post_' . $prop_name, $post->{$prop_name}, array('class'=>'inp-text'));
		}
		
		echo Form::label( 'post_published', 'Published:' );
		echo Form::checkbox('post_published', 1, Input::get('post_published', $post->isPublished())), '<br /><br />';
		
		
		foreach ( $post->template->fields()->order_by('id')->get() as $field ){
			echo Form::label( 'post_field_' . $field->name, $field->label ), '<br />';
			echo $field->editor( 'post_field_' . $field->name, $post->getField( $field->name ) ), '<br />';
		}
		
		?>
		<input type="submit" class="yui3-button button-positive" value="Update Post" />
		<?=Form::token();?>
		<?=Form::close();?>
	</div>
</div>
