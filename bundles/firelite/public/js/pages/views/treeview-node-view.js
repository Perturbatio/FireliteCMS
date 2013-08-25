YUI.add('treeview-node-view', function (Y) {
	"use strict";

	// -- Tree View -------------------
	var TreeviewNodeView = Y.Base.create('TreeviewNodeView', Y.View, [], {
		dragEnabled: false,
		// The container element that the View is rendered under.
		containerTemplate: '<li>',

		// Compile our template using Handlebars.
		nodeContentTemplate: Y.Handlebars.compile(Y.one('#treenode-template').getHTML()),

		// Bind DOM events for handling changes to a specific node
		events: {
			'.node-branch-icon': {
				click: 'toggleCollapsed'
			},
			'.destroy': {
				click: 'clear'
			}
			
		},
		// Initialize this view by setting event handlers when the Model
		// is updated or destroyed.
		initializer: function (cfg) {
			this.dragEnabled = cfg.dragEnabled;
			if ( !Y.Lang.isUndefined( cfg.nodeContainerTemplate ) ){
				this.containerTemplate = cfg.nodeContainerTemplate;
			}

			if ( !Y.Lang.isUndefined( cfg.nodeContentTemplate ) ){
				this.nodeContentTemplate = Y.Handlebars.compile( Y.one( cfg.nodeContentTemplate ).getHTML() );
			}
		},

		// Render this view in it's container
		render: function () {
			var container = this.get('container'),
				model = this.get('model'),
				viewData = model.toJSON();

			viewData.has_children = !model.get('children').isEmpty();
			viewData.can_drag = this.dragEnabled;
			
			container.addClass( 'treeview-node' );
			container.toggleClass( 'collapsed', model.get('collapsed') );
			
			container.setHTML( this.nodeContentTemplate( viewData ) );
			/*
			if (!viewData.has_children){
				container.append(Y.Node.create('<ul class="node-list"><li style="height: 5px;"></li></ul>'));
			}
			console.log(container.getHTML());
			*/
			return this;
		},

		// Toggle the model's collapsed state
		toggleCollapsed: function (e) {
			e.preventDefault();
			var container = this.get('container');
			//ensure that we only toggle this node and not parent or child nodes
			if (e.currentTarget.get('id') === container.one('.node-branch-icon').get('id')){

				this.get('model').toggle();
			}
		},

		// Destroy the model when the delete button is clicked.
		clear: function (e) {
			this.get('model').clear();
			e.preventDefault();
			e.stopPropagation();
		}
	});

	// Set this View under our custom Y.TodoMVC namespace.
	Y.namespace('TreeviewMVC').TreeviewNodeView = TreeviewNodeView;
}, '@VERSION@', {
	requires: [
		'view',
		'handlebars'
	]
});
