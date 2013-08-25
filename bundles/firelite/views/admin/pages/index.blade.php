		@section('document_title')
			@parent
			Pages
		@endsection
<?php
FireliteAsset::container('plugin.header')->bundle('firelite');
FireliteAsset::container('plugin.header')->script('pages.node_browser', 'js/pages/node_browser.js');
FireliteAsset::container('plugin.header.handlebars')->handlebar('treenode-template', 'firelite::admin.pages.handlebars.treenode-template');
FireliteAsset::container('plugin.header')->style('pages.node_browser', 'css/pages/pages.css');
?>
	<div class="panel">
		<div class="panel-header">
			<h2>Pages</h2>
		</div>
		<div class="panel-content">
<?php if (!empty($nodes)){ ?>
<script>
var treeview_nodes = <?=json_encode($treeview_nodes);?>;
</script>
	<section id="treeview-app">
		<header id="treeview-header">
			<div id="treeview-messages">
				
			</div>
			<div id="tree-controls">
				<div id="tree-controls-edit-structure" style="display: none;">
					<button id="btn-edit-structure" class="yui3-button btn-action-neutral">Edit Structure</button>
				</div>
				<div id="tree-controls-editing-structure" style="display: none;">
					<button id="btn-save-structure" class="yui3-button btn-action-positive">Save</button>
					<button id="btn-cancel-edit-structure" class="yui3-button btn-action-neutral">Cancel</button>
				</div>
			</div>
		</header>
		<section id="main">
			<ul id="treeview-node-list"></ul>
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
	<p>No pages in default site tree</p>
<?php 
	}
}
?>
		</div>
	</div>
