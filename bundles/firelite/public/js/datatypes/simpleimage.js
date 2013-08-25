YUI().use('node', 'event', function(Y){
	
	if ( !Y.Lang.isUndefined( mcImageManager ) ){//if tinyMCE file manager is available
		Y.all('.editor-simpleimage').each(function(node){
			var 
				N = Y.Node, 
				wrapper;
			//javascript:mcFileManager.open('form_add','article_file_path');
			//wrap input in div, stick browse link next to it
			node.wrap('<div class="editor-simpleimage-wrapper"></div>');
			wrapper = node.ancestor('.editor-simpleimage-wrapper');
			wrapper.append(N.create('<a class="yui3-button btn-editor-simpleimage-browse" data-input-id="'+ node.get('id') +'">Browse</a>'));
			
		});
		
		Y.one('#unit-main').delegate(
			'click', 
			function(e){
				var node = e.currentTarget,
					form = node.ancestor('form');
				mcImageManager.open(form.get('id'),node.getAttribute('data-input-id'));
			},
			'.btn-editor-simpleimage-browse'
		);
	} else {
		Y.log('can\'t find mcImageManager');
	}
	
});