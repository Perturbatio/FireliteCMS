@layout('firelite.templates.site-master')

@section('main')
	<div class="yui3-g">
					
	<div class="yui3-g">
		<div class="yui3-u" id="col_full">
			<div class="yui3-g" id="col_full_content">
					<?php
					
				
					$content = '';
					
					foreach($posts as $post){
						$content .= '<h1><a href="/blog/' . $post->name . '">' . $post->title . '</a></h1>';
						$content .=  '<div>' . $post->getField('summary') . '</div>';
					}
					
					echo $content;
					?>
				</div>
			</div>
	</div>
@endsection
