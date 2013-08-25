<!doctype html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>
		@section('document_title')
		Firelite:
		@yield_section
	</title>
	<?php
	FireliteAsset::container('firelite.header')->script('yui', 'http://yui.yahooapis.com/3.10.3/build/yui/yui-min.js');
	FireliteAsset::container('firelite.header')->bundle('firelite');
	FireliteAsset::container('firelite.header.after')->bundle('firelite');
	FireliteAsset::container('firelite.header')->add('core', 'css/core.css');
	FireliteAsset::container('firelite.header')->add('global', 'css/global.css');
	?>
	@section('head')
	@section('head.handlebars')
	<?php
	echo FireliteAsset::container( 'firelite.header.handlebars' )->handlebars();
	echo FireliteAsset::container( 'firelite.header.after' )->handlebars();
	echo FireliteAsset::container( 'plugin.header.handlebars' )->handlebars();
	?>
	@yield_section
	@section('head.css')
	<?= FireliteAsset::container( 'firelite.header' )->styles(); ?>
	<?= FireliteAsset::container( 'firelite.header.after' )->styles(); ?>
	<?= FireliteAsset::container( 'plugin.header' )->styles(); ?>
	@yield_section
	@section('head.js')
	<?= FireliteAsset::container( 'firelite.header' )->scripts(); ?>
	<?= FireliteAsset::container( 'firelite.header.after' )->scripts(); ?>
	<?= FireliteAsset::container( 'plugin.header' )->scripts(); ?>
	@yield_section
	@yield_section
</head>
<body class="yui3-skin-sam">
<div id="wrap">
	<div class="yui3-g">
		<?php
		//if logged in
		if ( ( class_exists( 'FireliteLoginPlugin' ) && FireliteAuth::check() ) || !class_exists( 'FireliteLoginPlugin' ) ) {
			?>
			<div class="yui3-u" id="unit-navigation">
				<div class="panel" id="navigation">
					<div class="panel-header">
						<h2>Navigation</h2>
					</div>
					<div class="panel-content">
						<?php if ( !empty( $nav_items ) ) { ?>
							<ul id="main_nav">
								<?php
								$current_plugin_action = $requested_plugin_details[ 'action' ];
								foreach ( $nav_items as $plugin => $nav ) {
									$li_classes = array( 'nav-item' );

									if ( $current_plugin == $plugin ) {
										$li_classes[ ] = 'nav-item-selected';
									}

									$a_classes = array( 'yui3-button' );

									if ( !empty( $nav[ 'sub_nav' ] ) ) {
										$a_classes[ ] = 'has-children';
									}
									?>
									<li class="<?= implode( ' ', $li_classes ); ?>">
										<div class="nav-item-wrapper">
											<a class="<?= implode( ' ', $a_classes ); ?>" href="<?= $nav[ 'main_nav' ][ 'url' ]; ?>"><span class="nav-item-inner"><?= $nav[ 'main_nav' ][ 'link_text' ]; ?></span></a>
										</div>
										<?php
										if ( !empty( $nav[ 'sub_nav' ] ) ) {
											?>
											<div class="sub-nav">
												<ul>
													<?php
													foreach ( $nav[ 'sub_nav' ] as $sub_nav ) {

														$sub_li_classes = array( 'nav-item' );
														if ( $current_plugin == $plugin && 'action_' . $current_plugin_action == $sub_nav[ 'action' ] ) {
															$sub_li_classes[ ] = 'nav-item-selected';
														}
														$sub_a_classes = array();

														if ( !empty( $nav[ 'sub_nav' ] ) ) {
															$sub_a_classes[ ] = 'has-children';
														}
														?>
														<li class="<?= implode( ' ', $sub_li_classes ); ?>">
															<div class="sub-nav-item-wrapper">
																<a class="<?= implode( ' ', $sub_a_classes ); ?>" href="<?= $sub_nav[ 'url' ]; ?>"><span class="sub-nav-item-inner"><?= $sub_nav[ 'link_text' ]; ?></span></a>
															</div>
														</li>
													<?php
													}
													?>
												</ul>
											</div>
										<?php
										}
										?>
									</li>
								<?php
								}
								?>
							</ul>
						<?php } //end !empty ?>
					</div>
				</div>
			</div>
		<?php } //end if logged in ?>
		<div class="yui3-u" id="unit-main">
			@section('main')
			<div class="content">
				<p>This is the main admin content area</p>
			</div>
			@yield_section
		</div>
		<!-- end main -->
	</div>
</div>
<div id="footer">
	@section('footer')
	<p>Firelite CMS <?= Firelite::version(); ?></p>
	@yield_section
	@section('footer.js')
	<?php echo Asset::container( 'firelite.footer' )->scripts(); ?>
	<?php echo Asset::container( 'plugin.footer' )->scripts(); ?>
	@yield_section
</div>
<!-- /footer -->
</body>
</html>