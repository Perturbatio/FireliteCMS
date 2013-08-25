YUI.add('treeview-app', function(Y){
	"use strict";

	var TreeviewNodeList = Y.TreeviewMVC.TreeviewNodeList,
		TreeviewNodeView = Y.TreeviewMVC.TreeviewNodeView,
		TreeviewApp;

		// -- Main Application --------------
	TreeviewApp = Y.Base.create('treeviewApp', Y.App, [], {
		containerTemplate: '#treeview-app',
		sortable: null,
		locked: false,
		debug: true,
		events: {
			
		},

		// Initialize our treeviewNodeList, and bind any events that occur
		// when new Nodes are added, changed, or removed within it.
		initializer: function (cfg) {
			var app = this;
			cfg = cfg || {};

			//create the root level node list
			app.set('treeviewNodeList', new TreeviewNodeList());

			var list = app.get('treeviewNodeList');

			//create a helper function for handlebars
			Y.Handlebars.registerHelper('pluralize', function (context, word) {
				return (context === 1) ? word : word + 's';
			});


			list.after(['add', 'remove', 'reset', 'treeviewNode:change'],
				app.render, app);

			list.load();

			// Keep our filters on refresh by immediately dispatching route.
			app.once('ready', function (e) {
				if (app.hasRoute(app.getPath())) {
					app.dispatch();
				}
			});
			app.publish("beforeRender", {preventable:true});
			app.publish("afterRender", {preventable:false});
			
			app.sortable = new Y.Sortable({
				container: '#treeview-node-list',
				nodes:'li',
				opacity: 1
			});
			
			app.sortable.delegate.dd.addHandle('.drag-handle');
			
			app.on('beforeRender', app._pre_render, null,  app);
			app.on('afterRender', app._post_render, null, app);
			
			app.sortable.delegate.on( 'drag:end', app._handleDragEnd, null, app );
			app.sortable.delegate.on( 'drag:mouseDown', app._handleDragStart, null, app );
			
		},
		_pre_render: function(e, app){
			return !app.locked;
		},
		lock: function(){
			this.locked = true;
		},
		unlock: function(){
			this.locked = false;
		},
		_handleDragStart: function(e, app){
			if (!app.get('dragEnabled')){
				e.preventDefault();
				e.stopPropagation();
				return false;
			}
			
		},
		_model_from_view: function(li){
			var id = 0,div,app=this;
			if (li){
				div = li.one('div.node-content');
				if (div){
					id = parseInt(div.getAttribute('data-node_id'), 10);
				}
				return app.findNode(id);
			}
			return null;
		},
		_handleDragEnd: function(e, app){
			var node = app.sortable.delegate.get('currentNode'),
				movedModel = app._model_from_view(node), previousModel,
				prev = node.previous(), next = node.next(),
				nextModel, msg, targetList;
				
			if (!movedModel){
				return;
			}
			if (prev){
				previousModel = app._model_from_view(prev);
			}
			if (next){
				nextModel = app._model_from_view(next);
			}

			if (app.debug){	
				msg = 'Moved ';

				msg += movedModel.get('name');
				// Customize the log message based on where the `node` moved to.
				if (prev && next) {
					msg += ' between ' + previousModel.get('name') + ':' + (previousModel.get('sort_order')) + ' and ' + nextModel.get('name') + ':' + (nextModel.get('sort_order'));
				} else if (prev) {
					msg += ' to the end, after ' + previousModel.get('name');
				} else if (next) {
					msg += ' to the beginning, before ' + nextModel.get('name');
				}
				Y.log(msg);
			}

			//move the nodes
			app.lock();//prevent rendering until we're done messing around with nodes

			if ( prev ) {
				targetList = previousModel.lists[0];
				movedModel.set('sort_order', previousModel.get('sort_order') + 1 );
			} else if (next) {
				targetList = nextModel.lists[0];
				movedModel.set('sort_order', nextModel.get('sort_order') - 1 );
			} else {
				movedModel.set('sort_order', 1 );
			}

			if ( prev || next ) {
				//remove the model from it's current list
				Y.Array.each(movedModel.lists, function(list){
					if (app.debug){
						Y.log('removing from list');
					}
					list.remove(movedModel);
					list.normalizeSort();
					//list.sort();
				});
				targetList.shiftRight( movedModel.get('sort_order') - 1 );
				targetList.add(movedModel);
				targetList.normalizeSort();
				//targetList.sort();
			}


			app.unlock();
			app.render();
				
		},
		toggleSortable: function(val){
			var app = this;
			if (Y.Lang.isUndefined(val) || val === null){
				val = !app.get('dragEnabled');
			}
			app.set('dragEnabled', val);
			return val;
		},
		/**
		 * Enhance the view once it's fully rendered
		 ********************************/
		_post_render: function(){
			
			var app = this;
			if (app.sortable){
				app.sortable.sync();
			}
		},

		// Render our application with the statistics from our treeviewNodeList,
		// and various other stylistic elements.
		render: function () {
			
			if ( !this.fire("beforeRender") ){
				return;
			}
			
			var app = this,
				treeviewNodeList = app.get('treeviewNodeList'),
				container = app.get('container');
				//main = app.get('main');
			

			// Set the checkbox only if all TreeviewNodes have been collapsed.
			//this.get('allCheckbox').set('checked', !remaining);
			app.addViews();
			
			this.fire("afterRender");
		},


		// Add TreeviewNode views to the DOM simultaneously, triggered when
		// the application initially loads, or we switch filters.
		addViews: function () {
			var app = this,
				expandByDefault = app.get('expandByDefault'),
				renderCollapsed = app.get('renderCollapsed'),
				fragment = Y.one(Y.config.doc.createDocumentFragment()),
				treeviewNodeList = this.get('treeviewNodeList'),
				models;

			// An Array of models is passed through when the 'reset'
			// event is triggered through syncing through load().
			switch (app.get('filter')) {
				case 'active':
					models = treeviewNodeList.remaining();
					break;
				case 'collapsed':
					models = treeviewNodeList.collapsed();
					break;
				default:
					models = treeviewNodeList;
				break;
			}

			/**
			 * render the node and all of it's children
			 */
			function addNodeView(container, model, renderChildren){
				
				var view = new TreeviewNodeView({
						model: model, 
						dragEnabled:app.get('dragEnabled'),
						nodeContainerTemplate: app.get('templates.nodeContainer'),
						nodeContentTemplate: app.get('templates.nodeContent')
					}),
					children = model.get('children'),
					childContainer = null,
					renderChildren = ( renderChildren !== false ) ? true : false, //default to true
					nodeContainer = view.render().get('container');

				if ( model.get('collapsed') && !renderCollapsed ) {
					renderChildren = false;
				}
				if ( !children.isEmpty() ){
 					childContainer = Y.Node.create( app.get( 'templates.nodeListContainer' ) );

					children.each( function(child){
						addNodeView( childContainer, child);
					} );

				}

				if (childContainer !== null){
					nodeContainer.append(childContainer);
				}

				container.append(nodeContainer);
			}//end addNodeView

			// Iterate through the (filtered) ModelList.
			models.each(function(node){
				addNodeView(fragment, node);
			});

			this.get('container').one('#treeview-node-list').setContent(fragment);
			
		},
		/**
		 * 
		 */
		getNodeList: function(){
			return this.get('treeviewNodeList');
		},
		
		clearNodeList: function(){
			
			var list = this.getNodeList().clear();
			
		},

		/**
		 * find the first node that matches the supplied condition
		 * this is a recursive function
		 * 
		 * @param condition integer|function
		 * @return TreeviewNode|null
		 */
		findNode: function(condition){
			var app = this,
				findFn = null, 
				result = null,
				list = app.getNodeList();
			if (!Y.Lang.isUndefined(condition) && condition !== null){
				switch(true){
					case Y.Lang.isFunction(condition):
						findFn = condition;
					break;
					case Y.Lang.isNumber(condition):
						findFn = function(model, index, list){
							return parseInt(model.get('id'), 10) === condition;
						};
					break;
					default:
						Y.log('findNode: unsupported condition', condition);
					break;
				}

				if (findFn !== null){
					result = list.findNode(findFn);
				}
			} else if (app.debug){
				console.log('error', condition);
			}
			return result;
		},
		/**
		 * 
		 */
		addNode: function(node){

			var app = this,
				expandByDefault = app.get('expandByDefault'),
				treeviewNodeList  = app.get('treeviewNodeList'),
				last_sort = 1;

			function makeNode(data, parentList){
				var model,
					modelData = {};
					
				modelData.id = data.id;
				modelData.name = data.name;
				modelData.data = data;
				modelData.sort_order = last_sort++;

				model = parentList.create(modelData);
				
				if ( !Y.Lang.isUndefined( data.children) ){

					if ( data.children.length > 0 ){
						
						Y.Array.each(data.children, function(child){
							var list = model.get('children');
							makeNode(child, list);
							list.after(['add', 'remove', 'reset', 'treeviewNode:collapsedChange'], app.render, app);
						});

						//data.children = true;
					} else {
						modelData.data.children = false;
					}
				} else {
					modelData.data.children = false;
				}
				
				
				model.set('collapsed', !expandByDefault);
				parentList.normalizeSort();
				return model;
			}
			if (!Y.Lang.isArray(node)){
				makeNode(node, treeviewNodeList);
			} else {
				Y.Array.each(node, function(item, index){
					makeNode(item, treeviewNodeList);
				}, app);
			}
		}
	}, {
		ATTRS: {
			// Significant DOM elements that relate to our application that
			// we would like to keep as attributes.
			container: {
				valueFn: function () {
					return Y.one('#treeview-app');
				}
			},
			inputNode: {
				valueFn: function () {
					return Y.one('#new-node');
				}
			},
			allCheckbox: {
				valueFn: function () {
					return Y.one('#toggle-all');
				}
			},
			main: {
				valueFn: function () {
					return Y.one('#main');
				}
			},
			footer: {
				valueFn: function () {
					return Y.one('#footer');
				}
			},

			expandByDefault: {
				value: true
			},

			dragEnabled: {
				value: false
			},

			renderCollapsed: {
				value: true
			},
			// Routing for the application, to determine the filter.
			// The callback takes a request object, Express-style.
			routes: {
				value: [
					{path: '/:filter', callback: 'handleFilter'}
				]
			},
			templates: {
				value: {
					nodeListContainer: '<ul/>',
					nodeListContainerSelector: 'ul',
					nodeContainer: '<li>',
					nodeContainerSelector: 'li',
					nodeContent: '#treenode-template'
				}
			}
		}
	});

	// Namespace this application under our custom Y.MVC namespace.
	Y.namespace('TreeviewMVC').TreeviewApp = TreeviewApp;

}, '@VERSION@', {
	requires: [
		'app',
		'treeview-node-list',
		'treeview-node-view',
		'node',
		'event-focus',
		'sortable',
		'node-midpoint'
	]
});
