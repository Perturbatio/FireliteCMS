<!doctype html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" >
		@section('head')
			@section('head.css')
<?php
Asset::container('head-core')->style('fonts', 'http://fonts.googleapis.com/css?family=Amatic+SC');
Asset::container('head')->add('buttons', 'http://yui.yahooapis.com/combo?3.5.1/build/cssbutton/cssbutton-min.css');

echo Asset::container('head-core')->styles();
echo Asset::container('head')->styles();
?>

				<!--[if gte IE 9]>
				<style type="text/css">
					.gradient {
					filter: none;
					}
				</style>
				<![endif]-->
			@yield_section
			@section('head.js')
<?php
Asset::container('head-core')->add('yui', 'http://yui.yahooapis.com/3.7.2/build/yui/yui-min.js');
echo Asset::container('head-core')->scripts();
echo Asset::container('head')->scripts();
?>
			@yield_section
			<title>
				@section('document_title')
					<?php if(isset($page)){ ?>
						<?=$page->title; ?>
					<?php } ?>
				@yield_section
			</title>
		@yield_section
	</head>
	<body class="yui3-skin-sam">
		
		<div id="header" class="gradient">
		@section('header')
			<?php if(isset($page)){ ?>
			<h1 id="site-title" title="<?=$page->title; ?>"><?=$page->title; ?></h1>
			<?php } ?>
		@yield_section
		
		</div><!-- /header -->
		<div id="outer-wrap">
			<div id="banner">
				<div></div>
			</div>
			<div id="wrap">
				<?php
					if(Session::has('flash_message')){
						PageAlert::flash(Session::get('flash_message'));
					}
				?>
				<div id="main">
					@section('main')
						<div class="content">
							@section('main.content')
							<p class="intro_text"><?=__( 'site.welcome', array( 'site_title' => __( 'site.title' ) ) ); ?></p>
							@yield_section
						</div>
					@yield_section
				</div><!-- /main -->

			</div>
		</div>
		<div id="footer">
			@section('footer')
			
			@yield_section
		</div><!-- /footer -->
		@section('footer.js')
		@yield_section
	</body>
</html>
