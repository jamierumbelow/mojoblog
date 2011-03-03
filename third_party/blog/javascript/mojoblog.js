/**
 * MojoBlog
 *
 * A small, quick, and painfully simple 
 * blogging system for MojoMotor 
 *
 * @package mojoblog
 * @author Jamie Rumbelow <http://jamierumbelow.net>
 * @copyright (c)2010 Jamie Rumbelow
 */

// Our main function
jQuery(function(){

	/**
	 * After the Pages navigation bar, we want to insert the
	 * MojoBlog Blogs toolbar button.
	 */
	var toolbar_button = $("<li>").attr('id', 'mojo_admin_blog')
								  .html($("<a>").attr('href', Mojo.URL.site_path + '/admin/addons/blog')
												.attr('title', 'Blog')
												.text('Blog'));
	$("#mojo_admin_pages").after(toolbar_button);
	
	/**
	 * Let's be sneaky here and hack into the mojoEditor object;
	 * whenever a CP page is changed, check if we've got an open
	 * MojoBlog CKEditor. If we do, get rid of it so that we can 
	 * return to a new one without refreshing the page!
	 */
	var old_reveal_page = mojoEditor.reveal_page;
	
	mojoEditor.reveal_page = function (a, b) {
		if (CKEDITOR.instances['mojoblog_entry_content']) {
			CKEDITOR.instances['mojoblog_entry_content'].destroy();
		}
		
		old_reveal_page(a, b);
	}
	
	/**
	 * When the user clicks on a delete link, bring up the 
	 * MojoBlog delete confirmation modal window.
	 */
	$(".mojoblog_delete").live('click', function(event) {
		var anchor = this;
		event.preventDefault();
		
		// Create the new modal window
		$("<div class='mojoblog_delete_dialog'><p>" + $(anchor).attr("title") + "</p></div>").dialog({
			resizable: false,
			title: 'Delete Entry',
			modal: true,
			width: "400px",
			buttons: {
				
				/**
				 * When the user confirms, send a HTTP GET request
				 * to the appropriate URL and parse response
				 */
				"Delete Entry": function() {
					$.get($(anchor).attr('href'), {}, function(data){
						if (data.result == "success") {
							// Remove the entry from the list
							jQuery("#mojoblog_entry_" + data.id).slideUp(function() {
								jQuery(this).remove();
							});
						}
						
						// Close the modal and display the message
						jQuery(".mojoblog_delete_dialog").dialog("close");
						mojoEditor.add_notice(data.message, data.result)
						
						// Reload the page
						jQuery('#mojo_reveal_page_content').load(mojoEditor.revealed_page);
					}, 'json');
				},
				
				/**
				 * If the user clicks close, dismiss the modal
				 */
				"Close": function() {
					$('.mojoblog_delete_dialog').dialog('close');
				}
			}
		});
		
		return false;
	});
	
	/**
	 * Automatic URL slugging
	 */
	$('.to_be_slugged').live('keyup', function(){
		mojoEditor.liveUrlTitle(this, $(this).attr('data-slugging-target'));
	});
});