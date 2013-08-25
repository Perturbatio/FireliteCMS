YUI({
	filter: 'raw',
	allowRollup: 'false',
	groups: {
		'kris-plugins': {
			base: '/bundles/firelite/js/pages/plugins/',
			modules: {
				'node-midpoint' : {
					path: 'node-midpoint.js',
					requires: ['node']
				}
			}
		},
		'treeview-mvc': {
			base: '/bundles/firelite/js/pages/',
			modules: {
				'treeview-node': {
					path: 'models/treeview-node.js',
					requires: [ 'model', 'treeview-node-list']// 'treeview-node-list'
				},
				'treeview-node-list': {
					path: 'models/treeview-node-list.js',
					requires: [ 'model-list', 'treeview-node']
				},
				'treeview-node-view': {
					path: 'views/treeview-node-view.js',
					requires: ['view', 'handlebars']
				},
				'treeview-app': {
					path: 'treeview-app.js',
					requires: [
						'app', 
						'treeview-node-list', 
						'treeview-node-view',
						'sortable',
						'node-midpoint'
					]
				}
			}
		}
	}
}).use('treeview-app', 'node-midpoint', 'node', 'event', 'io', 'json-parse', 'json-stringify', function (Y) {
	var tree = new Y.TreeviewMVC.TreeviewApp({
			expandByDefault: false,
			dragEnabled: false,
			renderCollapsed: false,
			templates: {
				nodeListContainer: '<ul class="node-list"/>',
				nodeListContainerSelector: 'ul.node-list',
				nodeContainer: '<li>',
				nodeContent: '#treenode-template'
			}
		}),
		defaultControls = Y.one('#tree-controls-edit-structure'),
		editControls = Y.one('#tree-controls-editing-structure'),
		btnEdit = Y.one('#btn-edit-structure'),
		btnSave = Y.one('#btn-save-structure'),
		btnCancel = Y.one('#btn-cancel-edit-structure'),
		messageBox = Y.one('#treeview-messages'),
		messageTypes = ['success','error','info']
	;//end vars
	
	editControls.hide();
	
	tree.lock();//prevent render
	tree.addNode(treeview_nodes);
	
	tree.unlock();//allow render again
	tree.render();//and render
	defaultControls.show();

	//exposing app for debugging via console
	//TODO: remove this once debugging done
	//window.treeApp = tree;
	
	//window.treenodes = tree.getNodeList();
	//helper functions
	function enableEditControls(){
		setTreeviewMessage('Drag the nodes by their handle to re-order', 'info');
		tree.set('dragEnabled', true);
		editControls.show();
		defaultControls.hide();
		tree.render();
	}
	
	function disableEditControls(){
		tree.set('dragEnabled', false);
		editControls.hide();
		defaultControls.show();
		tree.render();
	}
	
	function setTreeviewMessage(message, type){
		messageBox.setContent(Y.Node.create('<p/>').setContent(message));
		Y.Array.each(messageTypes, function(t){
			messageBox.removeClass(t);
		});
		messageBox.addClass(type);
	}
	
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
				setTreeviewMessage('Structure updated', 'success');
			} else {
				setTreeviewMessage('Error updating structure', 'error');
			}
		}, Y);
		
		var request = Y.io(uri, cfg);
	}
	
	//Attach event handlers
	btnEdit.on('click', function(e){
		enableEditControls();
	});
	
	btnCancel.on('click', function(e){
		//need to revert the nodes
		tree.lock();//prevent render
		tree.clearNodeList();
		tree.addNode(treeview_nodes);
		tree.unlock();//allow render again
		disableEditControls();
		setTreeviewMessage('Cancelled', 'info');
	});
	
	btnSave.on('click', function(e){
		disableEditControls();
		var data = {structure:tree.getNodeList()};
		sendNewStructure(Y.JSON.stringify(data));
	});
	
	
});