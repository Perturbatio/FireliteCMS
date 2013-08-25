@layout('firelite.templates.site-master')

@section('head.css')
	@parent
<style type="text/css">
	#panel-main_nav ul li {
		margin-left: 1em;
	}
</style>
@endsection

@section('head.js')
	<!-- parent js before this view's -->
	@parent
	<!-- insert head javascript here  -->
@endsection


@section('main')
	<div class="yui3-g">
		
		<div class="yui3-u-1-3">
			<div id="panel-main_nav" class="panel  ">
				<div class="panel-content">
					<?php
					$structure = $tree->getStructure(true, function($node){
						return $node->isPublished();
					});
					echo Firelite::buildNav($structure);
					?>
				</div>
			</div>
		</div>
		
		<div class="yui3-u-2-3">

			<div id="panel-main_content" class="panel  ">
				<div class="panel-content">
					<h1><?=$page->getField('heading'); ?></h1>
					<?=$page->getField('main_content'); ?>
					
				</div>
			</div>
		</div>
	
	</div>
@endsection
