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
});