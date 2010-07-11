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
		$this->mojo->load->library('auth');
		// $this->mojo->load->model('blog_model', 'blog');
		
		// Check that we're setup and the DB table exists
		$this->_install();
	}
	
	/**
	 * Loops through a blog's entries and displays them
	 *
	 * {mojo:blog:entries blog="blog" limit="5" orderby="date" sort="desc" date_format="Y-m-d" no_posts="No posts!"}
	 *     <h1>{title}</h1>
	 *     <p>{content}</p>
	 * {/mojo:blog:entries}
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function entries($template_data) {
		$this->template_data = $template_data;
		$blog = $this->_param('blog');
		$limit = $this->_param('limit');
		$orderby = $this->_param('orderby');
		$sort = $this->_param('sort');
		$date_format = $this->_param('date_format');
		$no_posts = $this->_param('no_posts');
		
		// Strip the template tags
		$tags = array('{mojo::blog:entries}', '{/mojo::blog:entries}');
		$this->template_data['template'] = str_replace($tags, '', $this->template_data['template']);
		
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
			
			// Loop through and parse
			foreach ($posts as $post) {
				$tmp = $this->template_data['template'];
				
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
			
			// Return the parsed string!
			return $parsed;
		}
	}
	
	/**
	 * Shows the entry form at the location specified.
	 *
	 * {mojo:blog:entry_form blog="blog"}
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
		$hash = sha1(time()+rand(0,500));
		
		// Check we've got a blog parameter!
		if (!$blog) {
			return 'Blog Parameter Required';
		}
		
		// Start preparing the entry form
		$html = "<div class='mojo_blog_entry_form' data-random-id='$hash'>";
			$html .= "<input type='hidden' name='mojo_blog_blog' id='mojo_blog_blog_$hash' value='$blog' />";
			$html .= "<h1>New Blog Entry</h1>";
			$html .= "<p><input style='padding: 5px; font-size: 14px; width: 90%' type='text' name='mojo_blog_title' id='mojo_blog_title_$hash' value='Title' /></p>";
			$html .= "<p><textarea name='mojo_blog_content' id='mojo_blog_content_$hash'></textarea></p>";
			$html .= "<p><input type='submit' name='mojo_blog_submit' id='mojo_blog_submit_$hash' value='Create New Entry' /></p>";
		$html .= "</div>";
		
		// Write the CKEditor JS...
		$js = 'window.onload = function(){ jQuery("#mojo_blog_content_'.$hash.'").ckeditor(function(){}, {';
			$js .= '"skin": "mojo,"+Mojo.URL.editor_skin_path,';
			$js .= '"startupMode": Mojo.edit_mode,';
			$js .= '"toolbar": Mojo.toolbar,';
			$js .= '"removePlugins": "save",';
			$js .= '"toolbarCanCollapse": false,';
			$js .= '"toolbarStartupExpanded": true,';
			$js .= '"resize_enabled": true,';
			$js .= 'filebrowserBrowseUrl : Mojo.URL.site_path+"editor/browse",';
			$js .= 'filebrowserWindowWidth : "780",';
			$js .= 'filebrowserWindowHeight : "500",';
			$js .= 'filebrowserUploadUrl : Mojo.URL.site_path+"editor/upload"';
		$js .= '});';
		
		// Handle the entry submission
		$js .= 'jQuery("#mojo_blog_submit_'.$hash.'").click(function(){ jQuery.ajax({ type: "POST", url: "'.$url.'", ';
		$js .= 'data: { mojo_blog_title: jQuery("#mojo_blog_title_'.$hash.'").val(), mojo_blog_content: jQuery("#mojo_blog_content_'.$hash.'").val(), mojo_blog_blog: jQuery("#mojo_blog_blog_'.$hash.'").val() },';
		$js .= 'complete: function () { window.location.reload() }';
		$js .= '}); });';
		
		// Make sure the form slides up and down with the MojoBar
		$js .= 'if (!mojoEditor.is_open) { jQuery(".mojo_blog_entry_form[data-random-id=\''.$hash.'\']").hide(); }';
		$js .= 'jQuery("#mojo_bar_view_mode, #collapse_tab").click(function(){ if (mojoEditor.is_open) { jQuery(".mojo_blog_entry_form[data-random-id=\''.$hash.'\']").slideDown(); } else { jQuery(".mojo_blog_entry_form[data-random-id=\''.$hash.'\']").slideUp(); }; return false; });';
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