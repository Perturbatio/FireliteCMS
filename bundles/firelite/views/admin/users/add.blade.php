@section('document_title')
	@parent
	Firelite User Management
@endsection

<div class="panel">
	<div class="panel-header">
		<h2>Firelite User Management - Add User</h2>
	</div>
	<div class="panel-content">
		<?php

		if (!isset($validation)){
			$validation = null;
		}
		?>
		<div class="pure-g">
			<div class="pure-u-1">

			<?php
			echo Form::open( Firelite::getPluginURL( 'users', 'add' ));

			echo Form::label('username', 'Username:');
			?>
			<br />
			<?php
			echo Form::input('text', 'username', Input::get( 'username', '' ), array('autocomplete'=>'off') );
			//echo PageAlert::error('username', $validation, 'error', $field_error_template); 
			?>
			<br />
			<?php
			echo Form::label('password', 'Password:');
			?>
			<br />
			<?php
			echo Form::input('password', 'password', Input::get( 'password', '' ), array('autocomplete'=>'off') );
			//echo PageAlert::error('password', $validation, 'error', $field_error_template); 
			?>
			<br />
			<br />
			<?php
			echo Form::submit('Add', array('class' => 'pure-button btn-action-positive'));
			echo HTML::link(Firelite::getPluginURL('users', 'index'), 'Cancel', array('class'=>'pure-button btn-action-neutral'));
			echo Form::token();
			echo Form::close();
			?>
			</div>
			
		</div><!-- end grid -->
	</div>
</div>
