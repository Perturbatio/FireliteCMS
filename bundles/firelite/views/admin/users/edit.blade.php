@section('document_title')
	@parent
	Firelite User Management
@endsection

<div class="panel">
	<div class="panel-header">
		<h2>Firelite User Management - <?=$user->username;?></h2>
	</div>
	<div class="panel-content">
		<?php

		if (!isset($validation)){
			$validation = null;
		}
		?>
		<div class="yui3-g">
			<div class="yui3-u-1">

			<?php
			echo Form::open( Firelite::getPluginURL( 'users', 'edit' ) . '/' . $user->id );

			echo Form::label('username', 'Username:');
			?>
			<br />
			<?php
			echo Form::input('text', 'username', Input::get( 'username', $user->username ) );
			//echo PageAlert::error('username', $validation, 'error', $field_error_template); 
			?>
			<br />
			<?php
			echo Form::label('password', 'Password:');
			?>
			<br />
			<?php
			echo Form::input('password', 'password', Input::get( 'password', '' ) );
			//echo PageAlert::error('password', $validation, 'error', $field_error_template); 
			?>
			<br />
			<br />
			<?php
			echo Form::submit('Update', array('class' => 'yui3-button btn-action-positive'));
			echo HTML::link(Firelite::getPluginURL('users', 'index'), 'Cancel', array('class'=>'yui3-button btn-action-neutral'));
			echo Form::token();
			echo Form::close();
			?>
			</div>
			
		</div><!-- end grid -->
	</div>
</div>
