<div>
	<?=form_open('admin/addons/blog/create')?>
		<p>
			<label for="entry[title]">Title</label>
			<?=form_input('entry[title]', $entry['title'], 'id="entry_title" class="mojo_textbox to_be_slugged" data-slugging-target="entry_url_title"')?>
		</p>
		
		<p>
			<label for="entry[url_title]">URL Title</label>
			<?=form_input('entry[url_title]', $entry['url_title'], 'id="entry_url_title" class="mojo_textbox"')?>
		</p>
		
		<p>
			<label for="entry[status]">Status</label>
			<?=form_dropdown('entry[status]', $statuses, $entry['status'], 'id="entry_status" class="mojo_textbox"')?>
		</p>
		
		<?php if($categories): ?>
			<p>
				<label for="entry[category_id]">Category</label>
				<?=form_dropdown('entry[category_id]', $categories, $entry['category_id'], 'id="entry_category_id" class="mojo_textbox"')?>
			</p>
		<?php endif; ?>
		
		<p>
			<label for="entry[content]">Content</label>
			
			<div id="mojoblog_content_entry">
				<?=form_textarea('entry[content]', $entry['content'], 'id="mojoblog_entry_content" class="mojo_textbox"')?>
			</div>
		</p>

		<p class="mojo_shift_right">
			<?=form_submit('', 'Create Entry', 'class="button"')?>
		</p>

	<?=form_close()?>
<div>
	
<?=$this->load->view('_js', array(), TRUE)?>