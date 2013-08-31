<div class="pure-g-r">
	<div class="pure-u-1-3"></div>
	<div class="pure-u-1-3">
		<div class="unit-content">
			<div class="panel" id="panel-login">
				<div class="panel-header">
					<h2>Login</h2>
				</div>
				<div class="panel-content">
					<?php
					echo Form::open(null, 'POST', array('class' => 'pure-form pure-form-aligned'));
					?>
					<fieldset>
						<div class="pure-control-group">
							<?php
							echo Form::label('username', 'Username');
							echo Form::input('text', 'username', null, array('class' => 'inp-text'));
							?>
						</div>
						<div class="pure-control-group">
							<?php
							echo Form::label('password', 'Password');
							echo Form::input('password', 'password', null, array('class' => 'inp-text'));
							?>
						</div>

						<div class="pure-controls">
							<?php
							echo Form::submit('Submit', array('class' => 'pure-button button-positive'));

							echo Form::token();
							?>
						</div>
					</fieldset>
					<?php
					echo Form::close();
					?>
				</div>
			</div>

		</div>
	</div>
	<div class="pure-u-1-3"></div>
</div>
