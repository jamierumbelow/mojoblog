<div>
	<?=form_open('admin/addons/blog/create')?>
		<p>
			<label for="entry[title]">Title</label>
			<?=form_input('entry[title]', $entry['title'], 'id="entry_title" class="mojo_textbox"')?>
		</p>
		
		<p>
			<label for="entry[status]">Status</label>
			<?=form_dropdown('entry[status]', $statuses, $entry['status'], 'id="entry_status" class="mojo_textbox"')?>
		</p>
		
		<p>
			<label for="entry[content]">Content</label>
			<?=form_textarea('entry[content]', $entry['content'], 'id="entry_content" class="mojo_textbox"')?>
		</p>

		<p class="mojo_shift_right">
			<?=form_submit('', 'Create Entry', 'class="button"')?>
		</p>

	<?=form_close()?>
<div>
	
<script type="text/javascript" charset="utf-8">
	$(function(){
		// CKEditorise content field...
	});
</script>