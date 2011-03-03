<?php if($categories): ?>
	<table class="mojo_table" id="mojoblog_categories">
		<thead>
			<tr>
				<th>Name</th>
				<th>URL Name</th>
				<th>Entries</th>
				<th></th>
				<th></th>
			</tr>
		</thead>

		<tbody>
			<?php foreach($categories as $category): ?>
				<tr class="<?=alternator('even','odd')?>" id="mojoblog_category_<?=$category->id?>">
					<td><?=$category->name?></td>
					<td><?=$category->url_name?></td>
					<td><?=$category->entries?></td>
					<td><?=anchor('admin/addons/blog/category_edit/' . $category->id, 'Edit', 'class="mojo_sub_page" title="Edit Category"')?></td>
					<td><?=anchor('admin/addons/blog/category_delete/' . $category->id, 'Delete', 'class="mojoblog_delete" title="Are you sure you want to delete '.$category->name.'`?"')?></td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<div class="shrinkwrap">
	<?php if(!$categories): ?>
		<p>Currently, there are no categories! Why don't you <?=anchor('admin/addons/blog/category_add/', 'add one', 'class="mojo_sub_page" title="Add Category"')?>?</p>
	<?php else: ?>
		<p>
			<?=anchor('admin/addons/blog/category_add/', 'Add Category', 'class="mojo_sub_page shrinkwrap_submit" title="Add Category"')?>
		</p>
	<?php endif; ?>
</div>

