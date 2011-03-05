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
			<tr class="<?=alternator('even','odd')?>" id="mojoblog_entry_<?=$entry->id?>">
				<td><?=$entry->title?></td>
				<td><?=date('Y/m/d', strtotime($entry->date))?></td>
				<td><?=ucwords($entry->status)?></td>
				<td><?=anchor('admin/addons/blog/edit/' . $entry->id, 'Edit', 'class="mojo_sub_page" title="Edit Entry"')?></td>
				<td><?=anchor('admin/addons/blog/delete/' . $entry->id, 'Delete', 'class="mojoblog_delete" title="Are you sure you want to delete `'.$entry->title.'`?"')?></td>
			</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<div class="shrinkwrap">
	<p>
		<?=anchor('admin/addons/blog/create', 'Create Entry', 'class="mojo_sub_page shrinkwrap_submit" title="Create Entry"')?>
		
		&bull;
		
		<?=anchor('admin/addons/blog/categories_all', 'Categories', 'class="mojo_sub_page" title="Categories"')?>
	</p>
	
	<div id="mojoblog_pagination">
		<?=$this->pagination->create_links()?>
	</div>
</div>

