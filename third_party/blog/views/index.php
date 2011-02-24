<table class="mojo_table" id="mojoblog_entries">
	<thead>
		<tr>
			<th>Title</th>
			<th>Date</th>
			<th>Status</th>
			<th></th>
			<th></th>
		</tr>
	</thead>

	<tbody>
		<?php foreach($entries as $entry): ?>
			<tr class="<?=alternator('even','odd')?>">
				<td><?=$entry->title?></td>
				<td><?=date('Y/m/d', strtotime($entry->date))?></td>
				<td><?=ucwords($entry->status)?></td>
				<td><?=anchor('admin/addons/blog/edit/' . $entry->id, 'Edit', 'class="mojo_sub_page" title="Edit Entry"')?></td>
				<td><?=anchor('admin/addons/blog/delete_confirm/' . $entry->id, 'Delete', 'class="mojo_sub_page" title="Delete Entry"')?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<div class="shrinkwrap">
	<p><?=anchor('admin/addons/blog/create', 'Add Post', 'class="mojo_sub_page shrinkwrap_submit" title="Add Post"')?></p>
</div>