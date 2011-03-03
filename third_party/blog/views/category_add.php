<div>
	<?=form_open('admin/addons/blog/category_add')?>
		<p>
			<label for="category[name]">Name</label>
			<?=form_input('category[name]', $category['name'], 'id="entry_title" class="mojo_textbox to_be_slugged" data-slugging-target="category_url_name"')?>
		</p>
		
		<p>
			<label for="category[url_name]">URL Name</label>
			<?=form_input('category[url_name]', $category['url_name'], 'id="category_url_name" class="mojo_textbox to_hold_the_slug"')?>
		</p>

		<p class="mojo_shift_right">
			<?=form_submit('', 'Add Category', 'class="button"')?>
		</p>

	<?=form_close()?>
<div>