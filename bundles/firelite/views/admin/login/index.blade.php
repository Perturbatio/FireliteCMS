	<div class="panel" id="panel-login">
		<div class="panel-header">
			<h2>Login</h2>
		</div>
		<div class="panel-content">
			<?php
				echo Form::open();

				echo Form::label('username', 'Username') . "<br />";
				echo Form::input('text', 'username', null, array('class'=>'inp-text'));
				echo Form::label('password', 'Password') . "<br />";
				echo Form::input('password', 'password', null, array('class'=>'inp-text'));

				echo Form::submit('Submit', array('class'=>'yui3-button button-positive'));

				echo Form::token();
				echo Form::close();
			?>
		</div>
	</div>
