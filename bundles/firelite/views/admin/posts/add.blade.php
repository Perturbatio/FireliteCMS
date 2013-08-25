		@section('document_title')
			@parent
			Posts/Add
		@endsection
		
<div class="panel">
	<div class="panel-header">
		<h2>Posts - Add</h2>
	</div>
	<div class="panel-content">
		<?=Form::open();?>
		
		<?php
		echo Form::label( 'post_template_id', 'Template:' ),'<br />', Form::select( 'post_template_id', $templates, Input::get('post_template_id', Firelite::config('blog.default_template_id')) ), '<br /><br />';
		
		$props = array(
			'name' => 'Name',
			'title' => 'Title',
		);

		foreach ( $props as $prop_name => $label ){
			$input_id = 'post_' . $prop_name;
			echo Form::label( $input_id, $label ),
				Form::input( 'text',  $input_id, Input::get( $input_id, '' ), array('class' => 'inp-text') );
		}
		?>
		<br /><br />
		<p>Once you have added the page, you will be able to edit its fields</p><br />
		<input type="submit" class="yui3-button button-positive" value="Add Post" />
		<?=Form::token();?>
		<?=Form::close();?>
	</div>
</div>
