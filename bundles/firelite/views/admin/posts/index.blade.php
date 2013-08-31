		@section('document_title')
			@parent
			Posts
		@endsection

	<div class="panel">
		<div class="panel-header">
			<h2>Posts</h2>
		</div>
		<div class="panel-content">
<?php if (!empty($posts)){ ?>
			<table id="node-tree">
				<tbody>
<?php foreach ( $posts as $post ){ ?>
				<?php
				//$page_to_edit = FirelitePage::from_node( $post->id );
				?>
				<tr class="node-row">
					<td><?=$post->title; ?></td>
					<td><a href="<?=Firelite::getPluginURL('blog', 'edit', array($post->id)); ?>" class="pure-button">Edit</a></td>
				</tr>
<?php } ?>
				</tbody>
				<thead>
					<th>
						Name
					</th>
					<th>
						Action
					</th>
				</thead>
			</table>
<?php
	} else {
?>
	<p>No posts to display</p>
<?php
}
?>
		</div>
	</div>
