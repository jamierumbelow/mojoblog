<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
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

class Blog {
	private $mojo;
	
	public function __construct() {
		$this->mojo =& get_instance();
		
		$this->mojo->load->database();
		
		$this->mojo->load->model('member_model');
		$this->mojo->load->model('site_model');
		// $this->mojo->load->model('blog_model', 'blog');
		
		$this->mojo->load->library('auth');
		
		// Check that we're setup and the DB table exists
		$this->_install();
	}
	
	/**
	 * Loops through a blog's entries and displays them
	 *
	 * {mojo:blog:entries blog="blog" editable="no" page="about|home" global="yes" limit="5" orderby="date" sort="desc" date_format="Y-m-d" no_posts="No posts!"}
	 *	   	{posts}
	 *     		<h1>{title}</h1>
	 *     		<p>{content}</p>
	 * 		{/posts}
	 * {/mojo:blog:entries}
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function entries($template_data) {
		$this->template_data = $template_data;
		$blog = $this->_param('blog');
		$page = $this->_param('page');
		$global = $this->_param('global');
		$editable = $this->_param('editable');
		$limit = $this->_param('limit');
		$orderby = $this->_param('orderby');
		$sort = $this->_param('sort');
		$date_format = $this->_param('date_format');
		$no_posts = $this->_param('no_posts');
				
		// Limit access by page
		if (!$this->_limited_access_by_page($page)) {
			return '';
		}
			
		// Blog time!
		$this->mojo->db->where('blog', $blog);
		
		// Limit
		if ($limit) {
			$this->mojo->db->limit($limit);
		}
		
		// Orderby and sort
		$orderby = ($orderby) ? $orderby : 'date';
		$sort = ($sort) ? strtoupper($sort) : 'DESC';
		
		$this->mojo->db->order_by("$orderby $sort");
		
		// Get the posts
		$posts = $this->mojo->db->get('blog_entries')->result();
		
		// Any posts?
		if (!$posts) {
			return ($no_posts) ? $no_posts : '';
		} else {
			$parsed = "";
			
			// Do we have the {posts} tag at all?
			if (preg_match("/{posts}/", $this->template_data['template'])) {			
				// Strip the template tags and replace with nothing
				$divs = '';
				$tags = array('{mojo::blog:entries}', '{/mojo::blog:entries}');		
				$parsed = str_replace($tags, $divs, $this->template_data['template']);
			
				// Get the contents of the {posts}{/posts} tag
				preg_match("/\{posts\}(.*)\{\/posts\}/is", $this->template_data['template'], $internal_template);
				$internal_template = $internal_template[1];
				
				// The replace it
				$parsed = preg_replace("/\{posts\}(.*)\{\/posts\}/is", "", $parsed);
				
				// Loop through and parse
				foreach ($posts as $post) {
					$tmp = $internal_template;
				
					// First, check that we're editable
					if ($editable !== "no") { 
						// ...and add the MojoBlog divs if we are
						$tmp = "<div class=\"mojo_blog_entry_region\" data-active=\"false\" data-post-id=\"{$post->id}\">\n$tmp\n</div>";
					}
				
					// Start off with the basic variables
					$tmp = preg_replace("/{id}/", $post->id, $tmp);
					$tmp = preg_replace("/{title}/", $post->title, $tmp);
					$tmp = preg_replace("/{content}/", $post->content, $tmp);
				
					// Then to the date!
					if ($date_format) {
						$tmp = preg_replace("/{date}/", date($date_format, strtotime($post->date)), $tmp);
					} else {
						$tmp = preg_replace("/{date}/", date('d/m/Y', strtotime($post->date)), $tmp);
					}
				
					// Finally, add it to the buffer
					$parsed .= $tmp;
				}
			}
			
			// Return the parsed string!
			return $parsed;
		}
	}
	
	/**
	 * Shows the entry form at the location specified.
	 *
	 * {mojo:blog:entry_form blog="blog" page="about|contact"}
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function entry_form($template_data) {
		// Only display the entry form if we're logged in
		if (!$this->mojo->auth->is_editor()) {
			return '';
		}

		// Set up a few variables
		$this->template_data = $template_data;
		$url = site_url('addons/blog/entry_submit');
		$blog = $this->_param('blog');
		$page = $this->_param('page');
				
		// Limit access by page
		if (!$this->_limited_access_by_page($page)) {
			return '';
		}
		
		// Check we've got a blog parameter!
		if (!$blog) {
			return 'Blog Parameter Required';
		}
		
		// Start preparing the entry form
		$html = "<div class='mojo_blog_entry_form'>";
			$html .= "<input type='hidden' name='mojo_blog_blog' class='mojo_blog_blog' value='$blog' />";
			$html .= "<h1>New Blog Entry</h1>";
			$html .= "<p><input style='padding: 5px; font-size: 14px; width: 90%' type='text' name='mojo_blog_title' class='mojo_blog_title' value='Title' /></p>";
			$html .= "<p><textarea class='mojo_blog_content'></textarea></p>";
			$html .= "<p><input type='submit' name='mojo_blog_submit' class='mojo_blog_submit' value='Create New Entry' /></p>";
		$html .= "</div>";
		
		// Write the CKEditor JS...
		$js = 'window.onload = function(){';
		
		$js .= 'function ckeditorise() { jQuery(".mojo_blog_content").each(function() { ';
			$js .= '/* Add a unique class */ var d = new Date(), t = d.getTime(), r = Math.random()*20000, u = Math.floor(t + r); jQuery(this).addClass("random_class_"+u);';				
			$js .= '/* CKify */ jQuery(".random_class_"+u).ckeditor(function(){}, {';
			$js .= '"skin": "mojo,"+Mojo.URL.editor_skin_path,';
			$js .= '"startupMode": Mojo.edit_mode,';
			$js .= '"toolbar": Mojo.toolbar,';
			$js .= '"extraPlugins": "cancel,mojoimage",';
			$js .= '"removePlugins": "save",';
			$js .= '"toolbarCanCollapse": false,';
			$js .= '"toolbarStartupExpanded": true,';
			$js .= '"resize_enabled": true,';
			$js .= 'filebrowserBrowseUrl : Mojo.URL.site_path+"editor/browse",';
			$js .= 'filebrowserWindowWidth : "780",';
			$js .= 'filebrowserWindowHeight : "500",';
			$js .= 'filebrowserUploadUrl : Mojo.URL.site_path+"editor/upload"';
		$js .= '}); }); }; ckeditorise(); ';
		
		// Custom MojoBlog Save + Cancel
		$js .= 'CKEDITOR.plugins.registered.cancel = {';
			$js .= 'init: function(editor) {
						var command = editor.addCommand( "cancel", {
							modes : { wysiwyg:1, source:1 },
							exec : function( editor ) {
								var par = jQuery("#cke_"+editor.name).parent();
								var html = unescape(jQuery(par).parent().find(".mojo_blog_orig_html").val());
								
								if (jQuery(par).attr("id") == "mojo_region_update_form") {
									editor.setData(mojoEditor.original_contents, function () {
										mojoEditor.remove_editor(editor);
									});
									
									handle_mojo_blog_regions();
								} else {
									editor.destroy();
									
									jQuery(par).parent().attr("data-active", "false");
									jQuery(par).parent().attr("data-is-editable-region", "false");
									jQuery(par).parent().addClass("mojo_blog_entry_region");
									jQuery(par).parent().html(html);
									
									jQuery(".mojo_blog_entry_region").live("click", function(){
										if (jQuery(this).attr("data-active") !== "true") {
											if (mojoEditor.is_open && mojoEditor.is_active === false) {
												handle_mojo_blog_edit(this);
											}
										}
									});
								}
						}});
						
						editor.ui.addButton("Cancel", {label : "Cancel", command : "cancel", icon : CKEDITOR.plugins.registered.cancel.path + "images/cancel.png"});
					}
				}';
		$js .= "\n";
		
		// Handle the entry submission
		$js .= 'jQuery("input.mojo_blog_submit").click(function(){ var par = jQuery(this).parent().parent(); jQuery.ajax({ type: "POST", url: "'.$url.'", ';
		$js .= 'data: { mojo_blog_title: jQuery(par).find(".mojo_blog_title").val(), mojo_blog_content: jQuery(par).find(".mojo_blog_content").val(), mojo_blog_blog: jQuery(par).find(".mojo_blog_blog").val() },';
		$js .= 'complete: function () { window.location.reload() }';
		$js .= '}); });';
		
		// Make sure the form slides up and down with the MojoBar
		$js .= 'if (!mojoEditor.is_open) { jQuery(".mojo_blog_entry_form").hide(); }';
		$js .= 'jQuery("#mojo_bar_view_mode, #collapse_tab").click(function(){ if (mojoEditor.is_open) { jQuery(".mojo_blog_entry_form").slideDown(); } else { jQuery(".mojo_blog_entry_form").slideUp(); }; return false; });';
		
		// Special magic title autofiller thing
		$js .= 'jQuery(".mojo_blog_title").focus(function(){ if(jQuery(this).val() == "Title") { jQuery(this).val(""); } });
				jQuery(".mojo_blog_title").blur(function(){ if(jQuery(this).val() == "") { jQuery(this).val("Title"); } });';
				
		// Editing regions
		$js .= 'function handle_mojo_blog_edit(entry) { var origHTML = jQuery(entry).html(); jQuery.get("'.site_url('addons/blog/entry_get').'/"+jQuery(entry).attr("data-post-id"), {}, function(data) {
			var title = data["title"], blog = data["blog"], content = data["content"];
			jQuery(entry).html("<input type=\'hidden\' class=\'mojo_blog_orig_html\' value=\'"+escape(origHTML)+"\' /><input type=\'hidden\' name=\'mojo_blog_id\' class=\'mojo_blog_id\' value=\'"+data["id"]+"\' /><input type=\'hidden\' name=\'mojo_blog_blog\' class=\'mojo_blog_blog\' value=\'"+data["blog"]+"\' /><p><input style=\'padding: 5px; font-size: 14px; width: 90%\' type=\'text\' name=\'mojo_blog_title\' class=\'mojo_blog_title\' value=\'"+data["title"]+"\' /></p><p><textarea class=\'mojo_blog_content\'>"+data["content"]+"</textarea></p><p><input type=\'submit\' class=\'mojo_blog_update\' name=\'mojo_blog_update\' class=\'mojo_blog_update\' value=\'Update Entry\' /></p>");
			
			ckeditorise();
			jQuery(entry).attr("data-active", "true");
			jQuery(entry).removeClass("mojo_blog_entry_region");
			
			jQuery(".mojo_blog_update").click(function(){
				var par = jQuery(this).parent().parent();
				var blogdata = { mojo_blog_id: jQuery(par).find(".mojo_blog_id").val(), mojo_blog_title: jQuery(par).find(".mojo_blog_title").val(), mojo_blog_content: jQuery(par).find(".mojo_blog_content").val(), mojo_blog_blog: jQuery(par).find(".mojo_blog_blog").val() };
				
				jQuery.post("'.site_url('addons/blog/entry_update').'", blogdata, function() {
					window.location.reload();
				});
				
				return false;
		} ); })};';
		$js .= 'function handle_mojo_blog_regions() { if (mojoEditor.is_open) { jQuery(".mojo_blog_entry_region").each(function() {
			if (!jQuery(this).attr("data-is-editable-region")) {
				mod_editable_layer = jQuery("<div class=\'mojo_blog_editable_region\'></div>").css({"background": "#FFEB72", "border-radius": "6px", "-moz-border-radius": "6px", "-webkit-border-radius": "6px", "margin": "-3px -6px", "padding": "0px", "position": "absolute", "border": "3px solid green", opacity: 0.4, width: jQuery(this).width(), height: jQuery(this).outerHeight()}).fadeIn(\'fast\');
				jQuery(this).attr("data-is-editable-region", "true");
				jQuery(this).prepend(jQuery("<div class=\'mojo_editable_layer_header\'><p>Blog : Entry ID "+jQuery(this).attr(\'data-post-id\')+"</p></div>")).prepend(mod_editable_layer); } }); } else { jQuery(".mojo_blog_entry_region").each(function() { jQuery(".mojo_editable_layer_header, .mojo_editable_layer").fadeOut(\'fast\', function(){jQuery(this).remove();}); }); }; 
		jQuery(".mojo_blog_entry_region").live("click", function(){
			if (jQuery(this).attr("data-active") !== "true") {
				if (mojoEditor.is_open && mojoEditor.is_active === false) {
					handle_mojo_blog_edit(this);
				}
			}
		}); }';
		$js .= 'jQuery("#mojo_bar_view_mode, #collapse_tab").click(function(){ handle_mojo_blog_regions(); return false; });';
		$js .= 'handle_mojo_blog_regions();';
		$js .= '}';
		
		// Push out the appropriate JavaScript
		$html .= "<script type='text/javascript'>$js</script>";
		
		// Done!
		return $html;
	}
	
	/**
	 * Submit a new entry via the AJAX POST form.
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function entry_submit() {
		// Are we allowed to do this?
		if (!$this->mojo->auth->is_editor()) {
			die('Unauthorised access!');
		}
		
		// Get the POST vars
		$blog = $this->mojo->input->post('mojo_blog_blog');
		$title = $this->mojo->input->post('mojo_blog_title');
		$content = $this->mojo->input->post('mojo_blog_content');
		$date = date('Y-m-d H:i:s', time());
		
		// Create the new post
		$this->mojo->db->insert('blog_entries', array(
			'blog' => $blog,
			'title' => $title,
			'content' => $content,
			'date' => $date
		));
		
		// Then re-retrive it!
		$post = $this->mojo->db->where('id', $this->mojo->db->insert_id())->get('blog_entries')->row();
		
		// Return it as JSON
		header('Content-type: application/json');
		echo json_encode($post);
		
		// And we're done!
		exit;
	}
	
	/**
	 * Get a specific entry in JSON format
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function entry_get() {
		// Get the post
		$id = $this->mojo->uri->segment(4);
		$post = $this->mojo->db->where('id', $id)->get('blog_entries')->row();
		
		// Return it as JSON
		header('Content-type: application/json');
		echo json_encode($post);
		
		// And we're done!
		exit;
	}
	
	/**
	 * Update a entry
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function entry_update() {
		$id = $this->mojo->input->post('mojo_blog_id');
		$title = $this->mojo->input->post('mojo_blog_title');
		$content = $this->mojo->input->post('mojo_blog_content');
		
		// Update the title and content
		$this->mojo->db->set('title', $title)->set('content', $content)->where('id', $id);
		$this->mojo->db->update('blog_entries');
		
		// Done!
		exit;
	}
	
	/**
	 * Fetch a parameter
	 *
	 * @param string $key 
	 * @return void
	 * @author Jamie Rumbelow
	 */
	private function _param($key) {
		return (isset($this->template_data['parameters'][$key])) ? $this->template_data['parameters'][$key] : FALSE;
	}
	
	/**
	 * Limit the access by page name, or bar separated list 
	 * of page names.
	 *
	 * @param string $page 
	 * @param boolean $global 
	 * @return boolean
	 * @author Jamie Rumbelow
	 */
	private function _limited_access_by_page($page, $global = FALSE) {
		// Let's check the page variable, because
		// we don't want to show anything if we're
		// not on the right page.
		// 
		// ignore this if it's global.
		if ($global !== 'yes') {
			if ($page) {
				// Allow for bar|separated|pages
				if (strpos('|', $page)) {
					$pages = explode('|', $page);
				} else {
					$pages = array($page);
				}
				
				// Let's use a boolean to check for permissions
				$yo_brother_can_i_access_your_blog = FALSE;
				$default_page = $this->mojo->site_model->default_page();
				
				// Loop through the pages and check
				foreach ($pages as $possible_page) {
					if ($possible_page == $this->mojo->uri->rsegment(3) || $possible_page == $default_page) {
						$yo_brother_can_i_access_your_blog = TRUE;
					}
				}
				
				// Are we on the right page? No? Well leave!
				if (!$yo_brother_can_i_access_your_blog) {
					return FALSE;
				}
			}
		}
		
		// I'm glad we got that over with
		return TRUE;
	}
	
	/**
	 * Creates the blog table if it doesn't exist
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	private function _install() {
		$this->mojo->load->dbforge();
		$this->mojo->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'blog' => array(
				'type' => 'VARCHAR',
				'constraint' => '100'
			),
			'title' => array(
				'type' => 'VARCHAR',
				'constraint' => '250'
			),
			'content' => array(
				'type' => 'TEXT'
			),
			'date' => array(
				'type' => 'DATETIME'
			)
		));
		$this->mojo->dbforge->add_key('id', TRUE);
		$this->mojo->dbforge->create_table('blog_entries', TRUE);
	}
}