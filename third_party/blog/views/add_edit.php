<div>
	<?=form_open('admin/addons/blog/create')?>
		<p>
			<label for="entry[title]">Entry Title</label>
			<?=form_input('entry[title]', $entry['title'], 'id="entry_title" class="mojo_textbox"')?>
		</p>

		<p class="mojo_shift_right">
			<?=form_submit('', 'Create Entry', 'class="button"')?>
		</p>

	<?=form_close()?>
<div>