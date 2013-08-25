YUI.add('treeview-node', function (Y) {
	"use strict";

	var node_count = 0,
	// TreeNode Model
	TreeviewNode = Y.Base.create('treeviewNode', Y.Model, [], {//} [Y.ModelSync.Local], {
			initializer: function(){
				this.set('children', new Y.TreeviewMVC.TreeviewNodeList());
			},
			// Toggle the collapsed state.
			toggle: function () {
				this.set('collapsed', !this.get('collapsed') );
			},

			// Destroy this node and remove it from localStorage.
			clear: function () {
				this.destroy( { remove: true } );
			},

			// add a child to the node's children modellist
			addChild: function(treeviewNode){

				var children = this.get('children');
                if ( Y.Lang.isObject(treeviewNode) ){
                    if (Y.Lang.isUndefined(treeviewNode.name) || treeviewNode.name !== 'treeviewNode' ){
                        treeviewNode = new Y.TreeviewMVC.TreeviewNode(treeviewNode);
                    }
    				children.add( treeviewNode );

                    if ( !Y.Lang.isUndefined( treeviewNode.children) ){

                        if ( !treeviewNode.children.isEmpty() ){

                            Y.Array.each(treeviewNode.children, function(child){
                                model.addChild(child);
                            });

                        }

                    }
                }
			},

			//
			removeChild: function(id){
				console.log('node removechild');
				var children = this.get('children');

				Y.Array.each( children, function( node ){
					if ( node.get('id') == id ){
						node.destroy({remove:true})
					}
				});
			}
		}, {

		// Default attributes.
		ATTRS: {
			name: {
				value: 'invalid-node'
			},
			children: {
				value: null 
			},
			collapsed: {
				value: false
			}
		}
	});

	Y.namespace('TreeviewMVC').TreeviewNode = TreeviewNode;

}, '@VERSION@', {
	requires: [
		'model',
		'treeview-node-list'
	]
});
