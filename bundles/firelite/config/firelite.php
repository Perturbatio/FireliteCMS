<?php
return array(
	////////////// ADMIN ///////////////
	'admin' => array(
		'default_plugin' => 'dashboard',
		'base_url' => 'admin/',
		'route' => 'admin/(:all?)',//all URLs that match "admin", "admin/" or "admin/anything"
		'view_dir' => 'firelite::admin.',
		'master_view' => 'firelite::admin.master',
		
		'plugins_dir' => Bundle::path('firelite') . 'plugins',
		'editors_dir' => Bundle::path('firelite') . 'editors',
		
		'plugins' => array(
			'dashboard' => array(
				'name' => 'dashboard',
				'class' => 'FireliteDashboardPlugin'
			),
			'nodes' => array(
				'name' => 'nodes',
				'class' => 'FireliteNodesPlugin'
			),
			/*'pages' => array(
				'name' => 'pages',
				'class' => 'FirelitePagesPlugin'
			),*/
			'users '=> array(
				'name' => 'users',
				'class' => 'FireliteUsersPlugin'
			),
			'login' => array(
				'name' => 'login',
				'class' => 'FireliteLoginPlugin'
			),
		),
		
		'datatype_editors' => array(
			'simpletext' => array( 
				'name' => 'Simpletext Editor',
				'class' => 'FireliteSimpletextEditor',
				'handles' => 'Simpletext'
			),
			'richtext' => array( 
				'name' => 'Richtext Editor', 
				'class' => 'FireliteRichtextEditor',
				'handles' => 'Largetext'
			), //'Largetext',
			'largetext' => array( 
				'name' => 'Largetext Editor', 
				'class' => 'FireliteLargetextEditor',
				'handles' => 'Largetext'
			), //'Largetext',
			'datetime' => array( 
				'name' => 'Datetime Editor', 
				'class' => 'FireliteDatetimeEditor',
				'handles' => 'Datetime'
			), //'Datetime',
			'simpleimage' => array( 
				'name' => 'Simpleimage Editor', 
				'class' => 'FireliteSimpleimageEditor',
				'handles' => 'Simpleimage'
			), //'Simpleimage',
			'Integer' => 'Integer',
		),
		
		'nodetype_editors' => array(
			'alias' => array( 
				'name' => 'Alias Editor', 
				'class' => 'FireliteAliasPlugin',
				'handles' => 'alias'
			),
			'page' => array( 
				'name' => 'Page Editor', 
				'class' => 'FirelitePagesPlugin',
				'handles' => 'page'
			),
		),
	),
	
	'blog' => array(
		'default_template_id' => 2
	),
	
	'auth' => array(
		'driver' => 'firelite',
		'username' => 'username',
		'password' => 'password',
		'model' => 'FireliteUser', //the model to use for firelite users
	),
	
	'tinymce' => array(
		'setups' => array(
			'simple' => array(),
			'full'	=> array(),
			'basic' => array(
					'plugins' => "style,iespell,paste,fullscreen,inlinepopups,xhtmlxtras,template,autosave",
					'paste_text_use_dialog' => 'true',
					'theme_advanced_buttons1' => "undo,redo,cut,copy,paste,|,pastetext,|,bold,italic,underline,link,unlink,anchor,|,bullist,numlist,|,cleanup,removeformat",
					'theme_advanced_buttons2' => "image,|,styleselect,formatselect,|,restoredraft,fullscreen,code",
					'theme_advanced_resizing' => 'true',
					//'content_css' => "http://fof.local/css/global.css",
					'skin' => 'cirkuit',
					'style_formats' => array(
						array(
							'title'=>'Intro Text',
							'selector' => 'p',
							'classes'	=> 'intro_text'
						)
					),
					'width' => 545,
			),
		),
		'default_setup' => 'basic',
	),
	
	'default_site_tree' => 1,
	
	'images' => array(
		'path' => path('base').path('public') . 'img' . DS . 'public' . DS,
	),
	/////////// TEMPLATES //////////
	'templates' => array(
		//the path to the templates, offset from the base view directory.
		'path' => 'firelite/templates',//this will resolve to /var/www/vhosts/mysite.local/application/views/firelite/templates
	),
	
	///////////// CLASS ALIASES /////////////
	//this array will only be used if Firelite is enabled
	'aliases' => array(
		
		'FireliteError' => 'Firelite\\Error',
		
		//System
		'Firelite'	=> 'Firelite\\System\\Firelite',
		'FireliteMessage'	=> 'Firelite\\System\\Message',
		'FireliteAsset' => 'Firelite\\Asset',
		//Auth
		'FireliteAuth' => 'Firelite\\System\\Auth',
			'FireliteAuthDriver' => 'Firelite\\System\\Authdriver',
				'FireliteUser' => 'Firelite\\Models\\User',
			
		
		//Core
		'FireliteSchema' => 'Firelite\\Database\\Schema',
		'FireliteModel'	=> 'Firelite\\Models\\Model',
		'FireliteNested'	=> 'Firelite\\Models\\Nested',
		'FireliteNodetype'	=> 'Firelite\\Models\\Nodetype',
		'FireliteNode'	=> 'Firelite\\Models\\Node',
		'FireliteTree'	=> 'Firelite\\Models\\Tree',
		'FireliteDatatype'	=> 'Firelite\\Models\\Datatype',
		'FireliteEditor' => 'Firelite\\Models\\Editor',
		
		//Node Types
		'FireliteAlias'	=> 'Firelite\\Models\\Nodetypes\\Alias',
		'FirelitePage'	=> 'Firelite\\Models\\Nodetypes\\Page',
			'FirelitePageField'	=> 'Firelite\\Models\\Nodetypes\\Page\\PageField',
		
		//Page extras
		'FireliteTemplate'	=> 'Firelite\\Models\\Template',
			'FireliteTemplateField' => 'Firelite\\Models\\TemplateField',
		
		//datatypes
		'FireliteBasedatatype'	=> 'Firelite\\Models\\Datatypes\\Basedatatype',
		'FireliteDatatypeSimpletext' => 'Firelite\\Models\\Datatypes\\Simpletext',
			'FireliteStorageSimpletext' => 'Firelite\\Models\\Storage\\Simpletext',
		
		'FireliteDatatypeLargetext' => 'Firelite\\Models\\Datatypes\\Largetext',
			'FireliteStorageLargetext' => 'Firelite\\Models\\Storage\\Largetext',
		
		'FireliteDatatypeDatetime' => 'Firelite\\Models\\Datatypes\\Datetime',
			'FireliteStorageDatetime' => 'Firelite\\Models\\Storage\\Datetime',
		
		'FireliteDatatypeSimpleimage' => 'Firelite\\Models\\Datatypes\\Simpleimage',
			'FireliteStorageSimpleimage' => 'Firelite\\Models\\Storage\\Simpleimage',
		
		'FireliteDatatypeInteger' => 'Firelite\\Models\\Datatypes\\Integer',
			'FireliteStorageInteger' => 'Firelite\\Models\\Storage\\Integer',
		
		//Plugins
		'FireliteBasePlugin' => 'Firelite\\Plugins\\BasePlugin',//base must always be registered
		'FirelitePagesPlugin' => 'Firelite\\Plugins\\PagesPlugin',
		'FireliteDashboardPlugin' => 'Firelite\\Plugins\\DashboardPlugin',
		'FireliteLoginPlugin' => 'Firelite\\Plugins\\LoginPlugin',
		'FireliteUsersPlugin' => 'Firelite\\Plugins\\UsersPlugin',
		'FireliteBlogPlugin' => 'Firelite\\Plugins\\BlogPlugin',
		'FireliteNodesPlugin' => 'Firelite\\Plugins\\NodesPlugin',
		
		//editors
		'FireliteBaseEditor' => 'Firelite\\Editors\\BaseEditor',
			'FireliteSimpletextEditor' => 'Firelite\\Editors\\Simpletext',
			'FireliteLargetextEditor' => 'Firelite\\Editors\\Largetext',
			'FireliteRichtextEditor' => 'Firelite\\Editors\\Richtext',
			'FireliteDatetimeEditor' => 'Firelite\\Editors\\Datetime',
			'FireliteSimpleimageEditor' => 'Firelite\\Editors\\Simpleimage',
			'FireliteIntegerEditor' => 'Firelite\\Editors\\Integer',
		
		//Blog
		'FireliteBlog' => 'Firelite\\Controllers\\Blog',
			'FirelitePost' => 'Firelite\\Models\\Blog\\Post',
			'FirelitePostField' => 'Firelite\\Models\\Blog\\Postfield',
	),
	
);
