		@section('document_title')
			@parent
			Structure
		@endsection
<?php
FireliteAsset::container('plugin.header')->bundle('firelite');
//FireliteAsset::container('plugin.header')->script('pages.node_browser', 'js/pages/node_browser.js');
//FireliteAsset::container('plugin.header.handlebars')->handlebar('treenode-template', 'firelite::admin.pages.handlebars.treenode-template');
FireliteAsset::container('plugin.header')->style('nodes.node_browser', 'css/nodes/nodes.css');

?>		@section('head')
			@parent
		@endsection
	<div class="panel">
		<div class="panel-header">
			<h2>Structure</h2>
		</div>
		<div class="panel-content">
<?php if (!empty($nodes)){ ?>
<script type="application/javascript">
var treeview_nodes = <?=json_encode($treeview_nodes);?>;
//TODO: get the nodes via XHR
</script>
<script>
"use strict";

YUI().use('dd','anim', 'transition', 'io', 'json-parse', 'json-stringify', 'oop', 'tree', 'widget', 'tree-labelable', 'tree-openable', 'tree-selectable', 'tree-sortable', function(Y){
	var dds = []
		,lastPos
		,startPos
		,messageTypes = ['success','error','info', 'no_message']
		,tree
		,messageBox = Y.one('#treeview-messages')
		,treeContainer = Y.one('#firelite-tree')
		,treeControlsNode = Y.one('#tree-controls')
		,treeStructureControls = Y.one('#tree-controls-edit-structure')
		,treeStructureEditingcontrols = Y.one('#tree-controls-editing-structure'),
		isUndef = Y.Lang.isUndefined,
		FireliteTree = Y.Base.create('FireliteTree', Y.Widget, [
			Y.Tree, 
			Y.Tree.Labelable, 
			Y.Tree.Openable, 
			Y.Tree.Selectable, 
			Y.Tree.Sortable
		],
	{
		//private vars
		TRAVERSAL_SKIP_BRANCH: {}
		,openClass: 'tree-node-expanded'
		,closedClass: 'tree-node-collapsed'
		,hiddenClass: 'hidden'
		,expandableClass: 'expandable'
		,branchNodeClass: 'tree-node-branch'
		,leafNodeClass: 'tree-node-leaf'
		,treeNodeEditClass: 'tree-node-edit-link'
		,treeNodeAddChildClass: 'tree-node-add-child-link'
		,treeNodeControlsClass: 'tree-node-controls'
		,treeNodeIconClass: 'tree-node-icon'
		,treeNodeStateControlClass: 'tree-node-state-control'
		,tree:null
		/*
		,initialize: function(config){
			var widget = this;
					
			if ( !isUndef( config.treeNodes ) ){
				this.tree = {};
			}
		}*/
		
		/**
		 * 
		 * @returns {undefined}
		 */
		,renderUI : function(){
			var widget = this;
			widget.renderTree();
			
		}//end renderUI
		
		/**
		 * 
		 * @returns {undefined}
		 */
		,renderTree : function(){
			var widget = this,
				cb = widget.get('contentBox'),
				root = widget.rootNode || null;
			
			
			if ( root !== null ){
				
				tree.rootNode.children[0];
				root.data.depth = 0;
				
				var build = function(treeNodes, parentElement){
					//handler_class
					var hasChildren,
						i,
						elementStateClass, nodeElement, depth = -1, 
						treeNode,
						nodeChildrenContainer,
						parent, ancestry, nodeCounter, nodeCounterMax, nodeTemplate;
					
					for ( nodeCounter = 0, nodeCounterMax = treeNodes.length; nodeCounter < nodeCounterMax; nodeCounter++ ){
						treeNode = treeNodes[nodeCounter];
						depth = -1;
						hasChildren = (treeNode.children.length > 0);
						elementStateClass = (treeNode.isOpen()) ? widget.openClass : widget.closedClass;
						ancestry = [];
						
						parent = treeNode.parent;
						
						if ( !isUndef(parent) && Y.Lang.isNumber(parent.id)){
							ancestry.push(parent.id);
						} else {
							ancestry.push(0);
						}

						while( parent ){
							parent = parent.parent;
							if (!isUndef(parent) && Y.Lang.isNumber(parent.id)){
								ancestry.push(parent.id);
							}
							depth++;
						}

						treeNode.data.depth = depth;
						
						if ( isUndef( treeNode.YUINode ) ){
							nodeTemplate = '<li class="tree-node">'
									+ '<div class="tree-node-inner">'
										+ '<span class="drag-handle"></span>'
										+ '<span class="' + widget.treeNodeStateControlClass +' yui3-button"></span>'
										+ '<span class="' + widget.treeNodeIconClass + '"></span>'
										+ '<span class="tree-node-label"><a href="' + treeNode.data.edit_url + '" title="Edit ' + treeNode.label + ' (' + treeNode.data.link_title + ')" class="' + widget.treeNodeEditClass + '">' + treeNode.label + '</a></span>'
										+ '<span class="' + widget.treeNodeControlsClass + '">'
										+ '<a title="Edit this node" href="' + treeNode.data.edit_url + '" class="yui3-button btn-small node-control ' + widget.treeNodeEditClass + '">Edit</a>'
										+ '<a title="Add a child node" href="' + treeNode.data.add_child_url + '" class="yui3-button btn-small node-control ' + widget.treeNodeEditClass + '">Add Child</a>'
										+ '</span>' 
								
									+ '<div class="tree-node-dropzones">'
										+ ((treeNode.data.depth > 0)? '<span class="dropzone-sibling dropzone" title="Drag here to add as sibling">Add as sibling</span>':'')
										+ '<span class="dropzone-child dropzone" title="Drag here to add as child">Add as child</span>'
									+ '</div>'
									+ '</div>'
									+ '</li>';
							
							nodeElement = Y.Node.create( nodeTemplate );
							nodeElement.appendChild( Y.Node.create('<ul class="tree-node-children"></ul>') );
							nodeElement.setData('tree-node-id',  treeNode.id);
							nodeElement.generateID();
							nodeElement.setAttribute('title', treeNode.data.link_title);
						} else {
							nodeElement = treeNode.YUINode;
						}
						
						nodeElement.setData('ancestry', ancestry);
						//remove old depth (horrible hack but nobody should have more than 20 levels deep)
						for (i = 0; i < 20; i++){
							nodeElement.removeClass('node_depth_' + i);
						}
						nodeElement.addClass('node_depth_' + treeNode.data.depth);

						nodeChildrenContainer = nodeElement.one('.tree-node-children');
						if ( hasChildren ){
							//nodeChildrenContainer.empty();
							//Y.Node.create('<ul class="tree-node-children"></ul>');
							build(treeNode.children, nodeChildrenContainer);
							nodeElement.appendChild(nodeChildrenContainer);
							nodeElement.removeClass(widget.closedClass);
							nodeElement.removeClass(widget.openClass);
							nodeElement.addClass(elementStateClass);
							nodeElement.addClass(widget.expandableClass);
							nodeElement.replaceClass(widget.leafNodeClass, widget.branchNodeClass);
							
						} else {
							nodeElement.replaceClass(widget.branchNodeClass, widget.leafNodeClass);
							//nodeChildrenContainer.empty();
						}

						if ( !isUndef( treeNode.data.node_type ) ){
							nodeElement.addClass('node-type-' + treeNode.data.node_type.name.toLowerCase());
						}
						
						treeNode.YUINode = nodeElement;
						parentElement.appendChild(nodeElement);
						
					}//end treeNodes for loop
					
				};
				
				build(widget.children, cb);
			} else {
				//console.log('root is null');
			}
		}
		
		/**
		 * 
		 * @param {type} treeNode
		 * @returns {undefined}		 */
		,openTo : function( treeNode ){
			var widget, parent, searchString;

			if ( Y.Lang.isString( treeNode ) ){
				treeNode = widget.findNode(treeNode);
			}
			
			if ( isUndef( treeNode ) ){
				return false;
			}
			
			
			parent = treeNode.parent;
			
			while ( parent ){
				parent.open();
				parent = parent.parent;
			}

		}
		/**
		 * 
		 * @param {type} search
		 * @returns {@exp;tree@call;findNode}
		 */
		,findNode: function(search){
			return widget.findNode(tree.rootNode, {}, function(node){
				
				if ( node.label.indexOf(search) > -1 ){
					return true;
				}
				
				if ( node.data.link_title.indexOf(search) > -1 ){
					return true;
				}
				
				if ( node.data.link_text.indexOf(search) > -1 ){
					return true;
				}
				
			});
		}
		
		/**
		 * 
		 * @param {type} search
		 * @returns {undefined}
		 */
		,findNodes: function(search){
			var result = [], widget = this;
			
			widget.walk( widget.rootNode, {}, function(node){
				var hit = false;
				
				if ( isUndef(node.data.link_title) ){
					return;
				}
				search = search.toLowerCase();
				if ( node.label.toLowerCase().indexOf( search ) > -1 ){
					hit = true;
				} else if ( node.data.link_title.toLowerCase().indexOf( search ) > -1 ){
					hit = true;
				} else if ( node.data.link_text.toLowerCase().indexOf( search ) > -1 ){
					hit = true;
				}
				
				if (hit){
					result.push(node);
				}
			}, widget );
			
			return result;
		}
		
		/**
		* 
		* 
		* @returns {undefined}
		*/
		,bindUI : function(){
			
			var widget = this,
				cb = widget.get('contentBox');
			
			/**
			 * node click
			 * 
			 * @param e
			 */
			cb.delegate('click', function(e){
				
				if ( !e.target.hasClass( widget.treeNodeStateControlClass) ){
					return;
				}
				e.preventDefault();
				e.stopPropagation();
				
				var el = e.currentTarget,
					node_id = el.getData('tree-node-id'),
					treeNode = widget.getNodeById(node_id);
					
				treeNode.toggleOpen();
				widget.syncUI();

			}, '.tree-node:not(' + widget.hiddenClass + ')');
			
		}//end bindUI

		/**
		*
		* @returns {undefined}
		*/
		,syncUI : function(){
			var widget = this,
				root = widget.rootNode || null;			

			if ( root !== null ){
				widget.walk(widget.children[0], function(treeNode){
					//console.log( treeNode.YUINode );
					var	nodeOpen = treeNode.isOpen(),
						nodeElement = treeNode.YUINode;
					
					if (nodeElement){
						if (nodeOpen){
							nodeElement.replaceClass(widget.closedClass, widget.openClass);
						} else {
							nodeElement.replaceClass(widget.openClass, widget.closedClass);
						}
					}
					if ( !treeNode.isOpen() ){//node.label === 'about-us'){
						return widget.TRAVERSAL_SKIP_BRANCH;
					}
				});
			}
			widget.renderTree();
			return;
		}//end syncUI
		
		/**
		 * @param {treeNode} node
		 * @param {object|function} options
		 * @param {function} callback
		 * @param {object} thisObj
		 * @returns {unresolved}
		 */
		,walk: function (node, options, callback, thisObj) {//modified copy of the traverseNode method
			var widget = this,
				stop,
				skip,
				unlimited,
				children,
				childOptions,
				i,
				len,
				traverseResult;
			
			// Allow callback as second argument.
			if (typeof options === 'function') {
				thisObj = callback;
				callback = options;
				options = {};
			}

			options || (options = {});

			stop = Y.Tree.STOP_TRAVERSAL;
			skip = widget.TRAVERSAL_SKIP_BRANCH;
			unlimited = typeof options.depth === 'undefined';
			
			traverseResult = callback.call(thisObj, node);
			
			if ( traverseResult === stop ) {
				return stop;
			} else if ( traverseResult === skip ){
				return;
			}
			
			children = node.children;
			
			if ( unlimited || options.depth > 0 ) {
				childOptions = unlimited ? options : {depth: options.depth - 1};
				
				for ( i = 0, len = children.length; i < len; i++ ) {
					traverseResult = widget.walk(children[i], childOptions, callback, thisObj);
					
					if ( traverseResult === stop) {
						return stop;
					} else if ( traverseResult === skip ){
						return;
					}
					
				}
			}
			return;
		}//end walk
		
		/**
		 * 
		 * @returns {undefined}
		 */
		,enableDrag: function(){
			var widget = this;
			return widget.set('dragEnabled', true);
		}

		/**
		 * 
		 * @returns {undefined}
		 */
		,disableDrag: function(){
			var widget = this;
			return widget.set('dragEnabled', false);
		}
		
		/**
		 * 
		 * @returns boolean
		 */		
		,dragEnabled: function(){
			return this.get('dragEnabled');
		}
		
		,ATTRS: {
			dragEnabled : {
				value : false
			}
		}
	});//END FireliteTree class declaration
	
	Y.FireliteTree = FireliteTree;

	
	tree = new FireliteTree({
		contentBox: treeContainer,
		nodes:treeview_nodes[0]
	});
	
	if (tree.rootNode.children.length > 0){
		tree.rootNode.children[0].open();
	}
	
	tree.render();
	
	window.tree = tree;
	
	Y.one('#btn-cancel-edit-structure').on('click', function(){});
	
	/**
	 * 
	 * @returns {undefined}
	 */
	function performSearch(){
		var nodes, i, searchTerm, treeNode;
		
		searchTerm = Y.one('#inp-tree-search').get('value');
		if (searchTerm.length < 1){
			return;
		}
		nodes = tree.findNodes(searchTerm);

		for ( i = 0; i < nodes.length; i++ ){
			treeNode = nodes[i];
			tree.openTo( treeNode );
			isUndef( treeNode.YUINode );
			flashNode( treeNode.YUINode.one('.tree-node-inner'), 5 );
		}
		
		tree.syncUI();
		Y.DD.DDM.syncActiveShims();
	}
	
	/**
	 * @param e
	 */
	treeControlsNode.delegate('keypress', function(e){
		switch(e.currentTarget.get('id')){
			case 'inp-tree-search':
				switch(e.charCode){
					case 13:
						performSearch();
					break;
				}
			break;
		}
	}, 'input');
	
	/**
	 * @param e
	 */
	treeControlsNode.delegate('click', function(e){
		
		var data;
		
		switch(e.currentTarget.get('id')){
			case 'btn-edit-structure':
				tree.enableDrag();
				treeStructureControls.hide();
				treeStructureEditingcontrols.show();
				treeContainer.addClass('editing-structure');
				enableDragDrop();
			break;
			
			case 'btn-save-structure':
				data = { structure: tree.rootNode.children[0].toJSON() };
				sendNewStructure( Y.JSON.stringify(data) );
				disableDragDrop();
			break;
			
			case 'btn-cancel-edit-structure':
				disableDragDrop();
			break;
			
			case 'btn-search':
				performSearch();
			break;
		}
		
	}, 'button');

	
	/**
	 * 
	 * @returns {undefined}
	 */
	function disableDragDrop(){
		treeStructureControls.show();
		treeStructureEditingcontrols.hide();
		treeContainer.removeClass('editing-structure');
		tree.disableDrag();
	}
	
	/**
	 * 
	 * @returns {undefined}
	 */
	function enableDragDrop(){
		
		treeStructureControls.hide();
		treeStructureEditingcontrols.show();
		treeContainer.addClass('editing-structure');
		
		var lis = treeContainer.all('li');
		
		if ( lis.size() > 0 ){
			lis.each(function(v, k) {
				if (!isUndef(dds[v.get('id')])){
					return;
				}
				if (!v.hasClass('node_depth_0')){
					var dd = new Y.DD.Drag({
						node: v,
						handles: ['.drag-handle']
					}).plug(Y.Plugin.DDProxy, {
						//Don't move the node at the end of the drag
						moveOnEnd: false,
						//don't resize the proxy to the same as the dragged node
						resizeFrame: false
					});
					dds[v.get('id')] = {drag:dd, drops:[]};
					v.all('.tree-node-dropzones span.dropzone').each(function(node, index){
						dds[v.get('id')].drops[index] = new Y.DD.Drop({node:node});
					});
				} else {
					dds[v.get('id')] = {drag:null, drops:[]};

					v.all('.tree-node-dropzones span.dropzone-child').each(function(node, index){
						dds[v.get('id')].drops[index] = new Y.DD.Drop({node:node});
					});
				}
			});
			
			//treeDD = new Y.DD.Drop({node: treeNode});
		}
	}
	
	
	Y.DD.DDM.on('drag:start', function(e) {
		//Get our drag object
		startPos = e.target.startXY;
		
		var drag = e.target
			,dragNode = drag.get('dragNode')
			,draggingNode = drag.get("node");
			
		//Set some styles here
		drag.get('node').setStyle('opacity', '.5');
		dragNode.set('innerHTML', draggingNode.get('innerHTML'));
		dragNode.setStyles({
			height: draggingNode.getComputedStyle('height'),
			opacity: '.5'
			//borderColor: draggingNode.getStyle('borderColor'),
			//backgroundColor: draggingNode.getStyle('backgroundColor')
		});
		Y.DD.DDM.syncActiveShims();
	});
	
	/**
	 * @param e
	 */
	Y.DD.DDM.on('drag:end', function(e) {
		var drag = e.target;
		//Put our styles back
		drag.get('node').setStyles({
			visibility: '',
			opacity: '1'
		});

	});
	
	/**
	 * @param e
	 */
	Y.DD.DDM.on('drag:drag', function(e) {
		//Get the last y point
		lastPos = e.target.lastXY;
		
	});
	
	/**
	 * @param e
	 */
	Y.DD.DDM.on('drag:drophit', function(e) {
		 //TODO: move this function into the tree widget and then change this reference
		var widget = tree
			,drop = e.drop.get('node')
			,dropNode
			,drag = e.drag.get('node')
			,dragTreeNode
			,dropTreeNode
			,dropInfo = {
				type: '',
				drag_id: 0,
				drop_id: 0
			};
		e.preventDefault();
		e.stopPropagation();
		//get drop node
		//normalize the drop node so that it's the first tree-node parent of the drop zone
		dropNode = drop.ancestor('.tree-node');
		
		if ( dropNode !== null ){//in the unlikely event that this fails (i.e. someone has added a drop zone outside the tree)
			
			
			dropInfo.drag_id = parseInt(drag.getData('tree-node-id'), 10);
			dropInfo.drop_id = parseInt(dropNode.getData('tree-node-id'), 10);
			
			if ( drop.hasClass('dropzone-sibling') ){
				dropInfo.type = 'sibling';
			} else if ( drop.hasClass('dropzone-child') ){
				dropInfo.type = 'child';
			} else {
				//return;
			}
			dragTreeNode = widget.getNodeById(dropInfo.drag_id);
			dropTreeNode = widget.getNodeById(dropInfo.drop_id);
			
			switch(dropInfo.type){
				case 'sibling'://if we drop as sibling, we want to make it the direct sibling
					dropTreeNode.parent.insert(dragTreeNode, {index:dropTreeNode.index()+1});
					
				break;
				case 'child':
					dropTreeNode.append(dragTreeNode);
					dropTreeNode.open();
				break;
				default:
					console.log('invalid drop type', dropInfo);
				break;
			}
			
			flashNode(dragTreeNode.YUINode.one('.tree-node-inner'), 2);
			
		}
		
		widget.syncUI();
		Y.DD.DDM.syncActiveShims();
	});
	
	/**
	 * 
	 * @param {type} animNode
	 * @param {type} animDuration
	 * @returns {undefined}
	 */
	function flashNode(animNode, animDuration){
		animDuration = animDuration || 1;
		
		var anim = new Y.Anim({
			node: animNode,
			from: {
				backgroundColor: '#F2E691'
			},

			to: {
				backgroundColor:or(animNode.getStyle('backgroundColor'), '#EEEEEE')
			},
			on: {
				end: function(e){
					this.get('node').setStyle('backgroundColor', '');
				}
			},
			duration:animDuration
		});
		anim.run();
	}
	
	/**
	* 

	 * @param {type} val1
	 * @param {type} val2
	 * @returns {@exp;@call;isUndef}	 */
	function or(val1, val2){
		return (!isUndef(val1))?val1:val2;
	}
	
	/**
	 * 
	 * @param {type} message
	 * @param {type} type
	 * @param {type} autoclear
	 * @returns {undefined}
	 */
	function setTreeviewMessage(message, type, autoclear){
		autoclear = (!isUndef(autoclear))?autoclear:true;
		messageBox.setStyles({
			'opacity': 0,
			'display': 'none'
		});
		messageBox.show('fadeIn', { duration: 0.5 });
		messageBox.setContent(Y.Node.create('<p/>').setContent(message));
		Y.Array.each(messageTypes, function(t){
			messageBox.removeClass(t);
		});
		messageBox.addClass(type);
		
		if (autoclear !== false){
			Y.later(4000,messageBox, function(){
				messageBox.hide('fadeOut', { duration: 0.5 });
			});
		}
	}
	
	
	/**
	 * 
	 * @param {type} data
	 * @returns {undefined}
	 */
	function sendNewStructure(data){
		var uri = "update_structure",
			cfg = {
				method: 'POST',
				data: data,
				headers: {
					'Content-Type':'application/json'
				}
			};
			
		Y.on('io:complete', function(id, o, args){
			//console.log(id, o, args);
			var response = Y.JSON.parse(o.responseText);
			if (response.result){
				setTreeviewMessage('Structure updated', 'success', true);
			} else {
				setTreeviewMessage(response.message, 'error', false);
			}
		}, Y);
		
		var request = Y.io(uri, cfg);
		
	}

});
</script>
	<section id="treeview-app">
		<header id="treeview-header">
			<div id="treeview-messages" class="no_message"></div>
			<div id="tree-controls">
				<div class="yui3-g">
					<div class="yui3-u">
						<div id="tree-controls-edit-structure">
							<button id="btn-edit-structure" class="yui3-button btn-action-neutral">Edit Tree Structure</button>
						</div>
					</div>
					<div class="yui3-u">
						<div id="tree-controls-editing-structure" style="display: none;">
							<button id="btn-save-structure" class="yui3-button btn-action-positive">Save</button>
							<a id="btn-cancel-edit-structure" href="" class="yui3-button btn-action-neutral fake-button">Cancel</a>
						</div>
					</div>
					<div class="yui3-u">
						<div id="tree-search-wrapper">
							<input id="inp-tree-search" type="text" placeholder="Search" />
							<button class="yui3-button btn-action-neutral" id="btn-search">Search</button>
						</div>
					</div>
				</div>
			</div>
		</header>
		<section id="main">
			<ul id="firelite-tree"></ul>
		</section>
	</section>
<?php
} else {
	if (empty($tree)){
?>
	<p>You need to define a site tree, if one exists, check your firelite config and ensure the default_site_tree config item is set</p>
<?php
	} else {
?>
	<p>The requested tree is empty</p><br />
	<a href="<?=Firelite::getPluginURL('nodes', 'add', array($tree->id));?>" class="yui3-button">Add a node</a>
<?php 
	}
}
?>
		</div>
	</div>
