YUI.add('treeview-node-list', function (Y) {
    "use strict";

    // Dependencies from Y.MVC.
    var TreeviewNode = Y.TreeviewMVC.TreeviewNode,
        TreeviewNodeList;

    // TreeviewNodeList Model list
    TreeviewNodeList = Y.Base.create('treeviewNodeList', Y.ModelList, [], {

        // The related Model for our Model List.
        model: TreeviewNode,

        // The root used for our localStorage key.
        root: 'treenode-lists-yui',
        // Return a ModelList of our collapsed Models.
        collapsed: function () {
            return this.filter({ asList: true }, function (treeviewNode) {
                return treeviewNode.get('collapsed');
            });
        },
        // Return a ModelList of our expanded Models.
        expanded: function () {
            return this.filter({ asList: true }, function (treeviewNode) {
                return !treeviewNode.get('collapsed');
            });
        },
		/**
		 * returns the max sort order of nodes in this list
		 **/
		getMaxSort: function(){

            var 
                items   = this._items,
                i, item, len,
				current_sort = 0,
				max_Sort = 0;

			for ( i = 0, len = items.length; i < len; ++i ) {
				item = items[i];
				current_sort = item.get('sort_order');
				if (current_sort  > max_Sort){
					max_Sort = current_sort;
				}
			}
			return max_Sort;
		},
		
		shiftRight: function(after, size){

            var items = this._items,
                i, item, len,
				current_sort = 0;
				
			for ( i = 0, len = items.length; i < len; ++i ) {
				item = items[i];
				current_sort = item.get('sort_order');
				if (current_sort  > after){
					item.set('sort_order', current_sort+size);
				}
			}
		},
		
		shiftLeft: function(after, size){

            var items = this._items,
                i, item, len,
				current_sort = 0;
				
			for ( i = 0, len = items.length; i < len; ++i ) {
				item = items[i];
				current_sort = item.get('sort_order');
				if (current_sort  > after){
					item.set('sort_order', current_sort-size);
				}
			}
		},
		
		normalizeSort: function(){
            var items = this._items,
                i, len;
				
			for ( i = 0, len = items.length; i < len; ++i ) {
				items[i].set('sort_order', i+1);
				
			}
			this.sort();
		},
		clear: function(){
			return this._clear();
		},
		comparator: function(model){
			return parseInt( model.get('sort_order'), 10);
		},
        /**
         * recursively search for a node in this list or any child node lists
         * 
         * @param callback function
         * @return TreeviewNode | null
         */
        findNode: function(callback){

            var children,
                found = null,
                items   = this._items,
                i, item, len;

            // Allow options as first arg.
            if (typeof callback === 'function') {

                for (i = 0, len = items.length; i < len; ++i) {
                    item = items[i];

                    if (callback.call(this, item, i, this)) {
                        found = item;
                    } else {
                        children = item.get('children');
                        if (!children.isEmpty()){
                            found = children.findNode(callback);
                        }
                    }

                    if (found !== null){
                        break;
                    }
                }

            }//end typeof

            return found;
        }//end findNode

    });

    // Set this Model List under our custom Y.MVC namespace.
    Y.namespace('TreeviewMVC').TreeviewNodeList = TreeviewNodeList;

}, '@VERSION@', {
    requires: [
        //'gallery-model-sync-local',
        'model-list',
        'treeview-node'
    ]
});
