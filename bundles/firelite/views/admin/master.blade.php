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
	FireliteAsset::container('firelite.header')->script('yui', 'http://yui.yahooapis.com/3.11.0/build/yui/yui-min.js');
	FireliteAsset::container('firelite.header')->bundle('firelite');
	FireliteAsset::container('firelite.header.after')->bundle('firelite');
	FireliteAsset::container('firelite.header')->add('core', 'http://yui.yahooapis.com/pure/0.3.0-rc-2/pure-min.css');
	//FireliteAsset::container('firelite.header')->add('core', 'css/core.css');
	FireliteAsset::container('firelite.header')->add('admin.skin', 'css/pure-skin.css');
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
<body class="pure-skin-firelite yui3-skin-firelite">
<div id="wrap">
	<div class="pure-g-r">
		<?php
		//if logged in
		if ( ( class_exists( 'FireliteLoginPlugin' ) && FireliteAuth::check() ) || !class_exists( 'FireliteLoginPlugin' ) ) {
			?>
			<div class="pure-u" id="unit-navigation">
				<div class="panel" id="navigation">
					<div class="panel-header">
						<h2>Navigation</h2>
					</div>
					<div class="panel-content pure-menu pure-menu-open">
						<?php if ( !empty( $nav_items ) ) { ?>
							<ul id="main_nav">
								<?php
								$current_plugin_action = $requested_plugin_details[ 'action' ];
								foreach ( $nav_items as $plugin_name => $nav ) {
									$li_classes = array( 'nav-item' );

									if ( $current_plugin == $plugin_name ) {
										$li_classes[ ] = 'pure-menu-selected';
									}

									$a_classes = array( 'nav-item-link' );

									if ( !empty( $nav[ 'sub_nav' ] ) ) {
										$a_classes[ ] = 'has-children';
									}
									?>
									<li class="<?= implode( ' ', $li_classes ); ?>" id="nav-<?=$plugin_name;?>">
										<a class="<?= implode( ' ', $a_classes ); ?>" href="<?= $nav[ 'main_nav' ][ 'url' ]; ?>"><span class="nav-item-inner"><?= $nav[ 'main_nav' ][ 'link_text' ]; ?></span></a>
										<?php
										if ( !empty( $nav[ 'sub_nav' ] ) ) {
										?>
										<div class="sub-nav pure-menu pure-menu-open">
											<ul class="pure-menu-children">
												<?php
												foreach ( $nav[ 'sub_nav' ] as $sub_nav ) {

													$sub_li_classes = array( 'nav-item', 'sub-nav-item' );

													if ( $current_plugin == $plugin_name && 'action_' . $current_plugin_action == $sub_nav[ 'action' ] ) {
														$sub_li_classes[ ] = 'pure-menu-selected';
													}

													$sub_a_classes = array();

													if ( !empty( $nav[ 'sub_nav' ] ) ) {
														$sub_a_classes[ ] = 'has-children';
													}
													?>
													<li class="<?= implode( ' ', $sub_li_classes ); ?>" id="nav-<?=$plugin_name, '-', $sub_nav['action'];?>">
														<a class="<?= implode( ' ', $sub_a_classes ); ?>" href="<?= $sub_nav[ 'url' ]; ?>"><span class="sub-nav-item-inner"><?= $sub_nav[ 'link_text' ]; ?></span></a>
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
		<div class="pure-u" id="unit-main">
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