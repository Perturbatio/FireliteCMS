<div class="node-content {{#if has_children}}node-branch-content{{else}}node-leaf-content{{/if}}" data-node_id="{{id}}">
	{{#if can_drag}}<div class="drag-handle">::</div>{{/if}}
	{{#if has_children}}<a class="node-branch-icon"></a>{{else}}<a class="node-leaf-icon"></a>{{/if}}
	<label>{{name}}</label>
	{{#unless can_drag}}<a class="yui3-button btn-action-neutral small" href="{{data.edit_url}}">Edit</a>{{/unless}}
	<!--<button class="destroy">Delete</button>-->
</div>