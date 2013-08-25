@layout('firelite.templates.site-master')

@section('main')
	<div class="yui3-g">
					
	<div class="yui3-g">
		<div class="yui3-u" id="col_full">
			<div class="yui3-g" id="col_full_content">
					<?php
										
					$content =  '<h1>' . $posts[0]->title . '</h1>';
					$content .=  '<div>' . $posts[0]->getField('main_content') . '</div>';
			
					echo $content;
					?>
				</div>
			</div>
	</div>
@endsection
