<div>
	<?=form_open('admin/addons/blog/edit/'.$entry['id'])?>
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
			
			<div id="mojoblog_content_entry">
				<?=form_textarea('entry[content]', $entry['content'], 'id="entry_content" class="mojo_textbox"')?>
			</div>
		</p>

		<p class="mojo_shift_right">
			<?=form_submit('', 'Update Entry', 'class="button"')?>
		</p>

	<?=form_close()?>
<div>
	
<?=$this->load->view('_js', array(), TRUE)?>