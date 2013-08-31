<div class="panel">
	<div class="panel-header">
		<h2>Error</h2>
	</div>
	<div class="panel-content">
		<div class="pure-g">
			<div class="pure-u-1">
				<?php
				if (isset($errors)){
					foreach($errors as $error){
						echo '<p>', $error, '</p>';
					}
				} else {
				?>
				<h1>Oops!</h1>
				<p>An error occurred.</p>
				<?php } ?>
			</div>
		</div>
	</div>
</div>