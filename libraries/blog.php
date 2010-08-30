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
	private $injected_javascript = FALSE;
	private $outfielder = FALSE;
	
	public function __construct() {
		$this->mojo =& get_instance();
		
		$this->mojo->load->database();
		
		$this->mojo->load->model('member_model');
		$this->mojo->load->model('site_model');
		$this->mojo->load->model('blog_model');
		
		$this->mojo->load->library('auth');
		
		// Check that we're setup and the DB table exists
		$this->mojo->blog_model->install();
		
		// Inject MojoBlog JavaScript if we need to!
		if ($this->injected_javascript == FALSE) {
			$this->javascript_tag();
		}
		
		// Outfielder support
		if (is_dir(APPPATH.'/third_party/outfielder/')) {
		    $this->mojo->load->add_package_path(APPPATH.'/third_party/outfielder/');
		    $this->mojo->load->model('fields_model', 'fields');
		    $this->mojo->load->remove_package_path();
			
			// Outfielder is loaded
		    $this->outfielder = TRUE;
			$this->mojo->cp->appended_output[] = '<script charset="utf-8" type="text/javascript">mojoEditor.outfielder = true;</script>';
		}
	}
	
	/**
	 * Loops through a blog's entries and displays them
	 *
	 * {mojo:blog:entries 
	 * 			blog="blog" editable="no" page="about|home" global="yes" limit="10" orderby="date" sort="desc" 
	 * 			date_format="Y-m-d" no_posts="No posts!" paginate="yes" per_page="5" pagination_trigger="p"}
	 *	   	{posts}
	 *     		<h1>{title}</h1>
	 *     		<p>{content}</p>
	 * 		{/posts}
	 *
	 * 		{pagination}{first_page_url} {prev_page_url} - Page {current_page} of {total_pages} - {next_page_url} {last_page_url}{/pagination}
	 * {/mojo:blog:entries}
	 *
	 * @todo Add {page_number_list} (Google style, 1 - 2 - 3 - *4* - 5)
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
		$paginate = $this->_param('paginate');
		$per_page = $this->_param('per_page');
		$pagination_trigger = $this->_param('pagination_trigger');
				
		// Limit access by page
		if (!$this->_limited_access_by_page($page)) {
			return '';
		}
			
		// Blog time!
		$this->mojo->blog_model->where('blog', $blog);
		
		// Orderby and sort
		$orderby = ($orderby) ? $orderby : 'date';
		$sort = ($sort) ? strtoupper($sort) : 'DESC';
		
		$this->mojo->blog_model->order_by("$orderby $sort");
		
		// Paginate?
		if ($paginate) {
			$per_page = ($per_page) ? $per_page : 5;
			$pagination_trigger = ($pagination_trigger) ? $pagination_trigger : 'p';
			
			// Get the page by the pagination trigger (we know it'll be > segment 2)
			$possible_segments = array_slice($this->mojo->uri->segments, 2);
			
			if (in_array($pagination_trigger, $possible_segments)) {
				$flipped = array_flip($possible_segments);
				$segment = $possible_segments[$pagination_trigger];
				
				if (isset($possible_segments[$segment+1])) {
					$page = $possible_segments[$segment+1];
				} else {
					$page = 1;
				}
			} else {
				$page = 1;
			}
			
			// Work out the offset
			$offset = ($page-1) * $per_page;
			
			// Limit & offset!
			$this->mojo->blog_model->limit($per_page, $offset);
		} else {
			// Limit
			if ($limit) {
				$this->mojo->blog_model->limit($limit);
			}
		}
		
		// Get the posts
		$posts = $this->mojo->blog_model->get();
		$entries_tag = "";
		
		// Get a count for pagination
		if ($paginate) {
			$this->mojo->blog_model->where('blog', $blog);
			$count = $this->mojo->blog_model->count_all_results();
		}
		
		// Any posts?
		if (!$posts) {
			return ($no_posts) ? $no_posts : '';
		} else {
			$parsed = "";
			
			// Do we have the {entries} tag at all?
			if (preg_match("/{entries}/", $this->template_data['template'])) {			
				// Strip the template tags and replace with nothing
				$divs = '';
				$tags = array('{mojo:blog:entries}', '{/mojo:blog:entries}');		
				$parsed = str_replace($tags, $divs, $this->template_data['template']);
				
				// Get the contents of the {posts}{/posts} tag
				preg_match("/\{entries\}(.*)\{\/entries\}/is", $this->template_data['template'], $internal_template);
				$internal_template = $internal_template[1];
								
				// Loop through and parse
				foreach ($posts as $post) {
					$tmp = $internal_template;
				
					// First, check that we're editable
					if ($editable !== "no") { 
						// ...and add the MojoBlog divs if we are
						$tmp = "<div class=\"mojo_blog_entry_region\" data-is-editable-region=\"false\" data-active=\"false\" data-post-id=\"{$post->id}\">\n$tmp\n</div>";
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
										
					// Outfielder metadata!
					if ($this->outfielder) {
						$metadata = $this->mojo->fields->get_via_page_title("mojo_blog_entry_".$post->id);
						
						if ($metadata) {
							foreach ($metadata as $row) {
								// Is there an {if} tag for this key? If so, remove it
								if (preg_match("/\{if $row->field_key\}(.*)\{\/if\}/", $tmp)) {
									$tmp = preg_replace("/\{if $row->field_key\}(.*)\{\/if\}/", "$1", $tmp);
								}
								
								// Replace {field_key} with the value
								$tmp = str_replace("{".$row->field_key."}", $row->field_value, $tmp);
							}
						}
						
						// Remove the rest of the {if tag}{tag}{/if}s
						$tmp = preg_replace("/\{if (\w+)\}(.*)\{\/if\}/", "", $tmp);
					}
					
					// Finally, add it to the buffer
					$entries_tag .= $tmp;
				}
			}
			
			// Replace the entries with the entirety of the tag parsed
			$parsed = preg_replace("/\{entries\}(.*)\{\/entries\}/is", $entries_tag, $parsed);
			
			// Finish off with pagination
			if (preg_match("/\{pagination\}(.*)\{\/pagination\}/", $parsed, $pagtmp)) {
				if ($paginate) {			
					$first_page_url = site_url('page/'.$this->mojo->mojomotor_parser->url_title);
					$prev_page_url = ($page > 1) ? site_url('page/'.$this->mojo->mojomotor_parser->url_title.'/'.$pagination_trigger.'/'.(string)($page-1)) : FALSE;
					$current_page = $page;
					$total_pages = round($count/$per_page);
					$next_page_url = ($page < ($count/$per_page)) ? site_url('page/'.$this->mojo->mojomotor_parser->url_title.'/'.$pagination_trigger.'/'.(string)($page+1)) : FALSE;
					$last_page_url = site_url('page/'.$this->mojo->mojomotor_parser->url_title.'/'.$pagination_trigger.'/'.(string)round($count/$per_page));
					$pagtmp = $pagtmp[1];

					// Prev and next page conditionals
					if ($prev_page_url) {
						if (preg_match("/\{if prev_page\}(.*?)\{\/if\}/", $pagtmp)) {
							$pagtmp = preg_replace("/\{if prev_page\}(.*?)\{\/if\}/", "$1", $pagtmp);
						}
					} else {
						if (preg_match("/\{if prev_page\}(.*?)\{\/if\}/", $pagtmp)) {
							$pagtmp = preg_replace("/\{if prev_page\}(.*?)\{\/if\}/", "", $pagtmp);
						}
					}

					if ($next_page_url) {
						if (preg_match("/\{if next_page\}(.*?)\{\/if\}/", $pagtmp)) {
							$pagtmp = preg_replace("/\{if next_page\}(.*?)\{\/if\}/", "$1", $pagtmp);
						}
					} else {
						if (preg_match("/\{if next_page\}(.*?)\{\/if\}/", $pagtmp)) {
							$pagtmp = preg_replace("/\{if next_page\}(.*?)\{\/if\}/", "", $pagtmp);
						}
					}

					// Variable swap fun
					$pagtmp = preg_replace("/\{first_page_url\}/", $first_page_url, $pagtmp);
					$pagtmp = preg_replace("/\{prev_page_url\}/", $prev_page_url, $pagtmp);
					$pagtmp = preg_replace("/\{current_page\}/", $current_page, $pagtmp);
					$pagtmp = preg_replace("/\{total_pages\}/", $total_pages, $pagtmp);
					$pagtmp = preg_replace("/\{next_page_url\}/", $next_page_url, $pagtmp);
					$pagtmp = preg_replace("/\{last_page_url\}/", $last_page_url, $pagtmp);
				
					// Replace {pagination} tags
					$parsed = preg_replace("/\{pagination\}(.*?)\{\/pagination\}/is", $pagtmp, $parsed);
				}
			}
			
			// Return the parsed string!
			return $parsed;
		}
	}
	
	/**
	 * Shows the entry form at the location specified.
	 *
	 * {mojo:blog:entry_form blog="blog" page="about|contact" editor="no" field="textarea" outfielder="yes"}
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
		$delete_url = site_url('addons/blog/entry_delete');
		$blog = $this->_param('blog');
		$page = $this->_param('page');
		$editor = $this->_param('editor');
		$field = $this->_param('field');
		$outfielder = $this->_param('outfielder');
				
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
			$html .= form_open("addons/blog/entry_submit");
			$html .= "<input type='hidden' name='mojo_blog_blog' class='mojo_blog_blog' value='$blog' />";
			$html .= "<h1>New Blog Entry</h1>";
			$html .= "<p><input style='padding: 5px; font-size: 14px; width: 90%' type='text' name='mojo_blog_title' class='mojo_blog_title' value='Title' /></p>";
			
		// Textarea or input	
		if ($field == 'input') {
			$html .= "<p><input type='text' name='mojo_blog_content' class='mojo_blog_content mojo_blog_new_entry' /></p>";
		} else {
			if ($editor == "no") {
				$html .= "<p><textarea name='mojo_blog_content' class='mojo_blog_content mojo_blog_new_entry' data-editor='no'></textarea></p>";
			} else {
				$html .= "<p><textarea class='mojo_blog_content mojo_blog_new_entry'></textarea></p>";
			}
		}
		
		// Outfielder support
		if ($this->outfielder && $outfielder == "yes") {
		    $html .= '<h3>Metadata</h3>';
		
			$html .= '<div class="mojoblog_outfielder_group">';
				$html .= '<ul style="list-style:square;"><li><input type="text" name="metadata_keys[]" value="Key" class="mojoblog_outfielder_metadata_key newinput" /> = <input type="text" name="metadata_values[]" value="Value" class="mojoblog_outfielder_metadata_value newinput" /> <a href="#" class="mojoblog_outfielder_add"><img src="'.site_url("addons/blog/image/add.png").'" alt="Add" /></a></li></ul>';
			$html .= '</div>';
		}
		
		// Entry submission, close the div
		$html .= "<p><input type='submit' name='mojo_blog_submit' class='mojo_blog_submit' value='Create New Entry' /></p>";
		$html .= "</form></div>";
				
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
		
		// Setup the post array
		$post = array(
			'blog' => $blog,
			'author_id' => $this->mojo->session->userdata('id'),
			'title' => $title,
			'content' => $content,
			'date' => $date
		);
		
		// Create the new post
		$id = $this->mojo->blog_model->insert($post);
		$post['id'] = $id;
		
		// Outfielder
		if ($this->outfielder) {
			$keys = $this->mojo->input->post('metadata_keys');
			$values = $this->mojo->input->post('metadata_values');
			$metadata = array();
			
			// Loop through the keys, match it up with the values
			// and insert metadata into Outfielder!
			foreach ($keys as $index => $key) {
				if ($key && $values[$index]) {
					$this->mojo->fields->create($key, $values[$index], "mojo_blog_entry_$id");
				}
			}
		}
		
		// Return it as JSON
		header('Content-type: application/json');
		echo json_encode($post);
		
		// And we're done!
		exit;
	}
	
	/**
	 * Display an RSS feed from the supplied blog,
	 * with an optional limit parameter (defaulting to 10)
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function rss() {
		// Get the variables, brother
		$blog = $this->mojo->uri->segment(4);
		$limit = $this->mojo->uri->segment(5);
		$link_page = $this->mojo->uri->segment(6);
		
		// Make sure we've got a blog variable
		if (!$blog) {
			show_error("You're missing the blog name!");
		}
		
		// Get the posts, my friend
		$data['posts'] = $this->mojo->blog_model->where('blog', $blog)->order_by('date DESC')->limit($limit)->get();
		$data['site_name'] = $this->mojo->site_model->get_setting('site_name');
		$data['blog_name'] = $blog;
		$data['blog_pretty_name'] = ucwords(str_replace("_", " ", $blog));
		$data['rss_url'] = site_url('addons/blog/rss/'.$data['blog_name']);
		$data['link_page'] = ($link_page) ? $link_page : $this->mojo->site_model->default_page();
		
		// Set mime types and extract variables
		header("Content-type: text/xml");
		extract($data);
		
		// And output the RSS!
		include('blog/rss.xml');
	}
	
	/**
	 * Outputs the URL to the RSS feed. Takes three parameters,
	 * the required 'blog' and an optional 'limit' and 'link_page'
	 *
	 * {mojo:blog:rss_url blog="blog" limit="15" link_page="about"}
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function rss_url($template_data) {
		// Gather the variables
		$this->template_data = $template_data;
		$blog = $this->_param('blog');
		$limit = $this->_param('limit');
		$link_page = $this->_param('link_page');
		
		// Do we have a limit?
		$limit = ($limit) ? (int)$limit : 10;
		
		// Output the URL, basically
		return site_url("addons/blog/rss/$blog/$limit/$link_page");
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
		$post = $this->mojo->blog_model->where('id', $id)->get(TRUE);
		
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
		$this->mojo->blog_model->set('title', $title)->set('content', $content)->where('id', $id);
		$this->mojo->blog_model->update();
		
		// Done!
		exit;
	}
	
	/**
	 * Delete an entry
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function entry_delete() {
		// Delete the post
		$id = $this->mojo->input->post('entry_id');
		$this->mojo->blog_model->where('id', $id)->delete();
		
		// Wicked
		exit;
	}
	
	/**
	 * Load the MojoBlog JavaScript
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function javascript() {
		$this->mojo->output->set_header("Content-Type: text/javascript");
		exit(file_get_contents(APPPATH.'third_party/blog/javascript/mojoblog.js'));
	}
	
	/**
	 * Returns the correct URL to the MojoBlog JavaScript
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function javascript_url() {
		return site_url('addons/blog/javascript');
	}
	
	/**
	 * Returns a JavaScript tag to the JS if we're an editor
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function javascript_tag() {
		if ($this->injected_javascript == FALSE) {
			if ($this->mojo->auth->is_editor()) {
				$js = "<script type='text/javascript' src='".$this->javascript_url()."'></script>";
			} else {
				$js = '';
			}
		
			$this->mojo->cp->appended_output[] = $js;
			$this->injected_javascript = TRUE;
		}
	}
	
	/**
	 * Uninstalls MojoBlog.
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function uninstall() {
		// Are we allowed to do this?
		if (!$this->mojo->auth->is_admin()) {
			die('Unauthorised access!');
		}
		
		// Bye!
		$this->mojo->blog_model->uninstall();
		die('MojoBlog is uninstalled. Please remove the blog/ folder from mojomotor/third_party <strong>without</strong> loading another page or refreshing.');
	}
	
	/**
	 * Export everything for the EE importer
	 *
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function export() {
		// Are we allowed to do this?
		if (!$this->mojo->auth->is_admin()) {
			die('Unauthorised access!');
		}
		
		// Get all the posts
		$blogs = $this->mojo->blog_model->select("DISTINCT(blog)")->get();
		$posts = array();
		
		foreach ($blogs as $blog) {
			$posts[$blog->blog] = $this->mojo->blog_model->where('blog', $blog->blog)->get();
		}
		
		// Build up the serialised PHP!
		$export_data['mojo_blog_export'] = TRUE;
		$export_data['blogs'] = $posts;
		$data = serialize($export_data);
		$filename = "mojoblog_export_".date('Y-m-d');
		
		header('Content-Type: application/php');
		header('Content-Disposition: attachment; filename="'.$filename.'"');
		header("Content-Transfer-Encoding: binary");
		header('Expires: 0');
		header('Pragma: no-cache');
		header("Content-Length: ".strlen($data));
		
		exit($data);
	}
	
	/**
	 * Render an image from the addon
	 *
	 * @param string $file 
	 * @return void
	 * @author Jamie Rumbelow
	 */
	public function image($file) {
		header('Content-Type: image/png');
		die(file_get_contents(APPPATH.'third_party/blog/images/'.$file));
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
				if (strpos($page, '|')) {
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
}