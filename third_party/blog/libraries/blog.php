<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * MojoBlog
 *
 * A small, quick, and painfully simple 
 * blogging system for MojoMotor 
 *
 * @package 	mojoblog
 * @author 		Jamie Rumbelow <http://jamierumbelow.net>
 * @version		2.0.0
 * @copyright 	(c)2011 Jamie Rumbelow
 */

class Blog {
	
	/* --------------------------------------------------------------
	 * VARIABLES
	 * ------------------------------------------------------------ */
	
	private $mojo;
	private $data = array();
	private $version = '2.0.0';
	
	/* --------------------------------------------------------------
	 * GENERIC METHODS
	 * ------------------------------------------------------------ */
	
	public function __construct() {
		$this->mojo =& get_instance();
		
		$this->mojo->load->database();
		
		$this->mojo->load->model('member_model');
		$this->mojo->load->model('site_model');
		$this->mojo->load->model('blog_model');
		
		$this->mojo->load->library('auth');
		$this->mojo->load->library('javascript');
		$this->mojo->load->library('pagination');
		
		$this->mojo->load->helper('form');
		$this->mojo->load->helper('page');
		$this->mojo->load->helper('url');
	}
	
	/* --------------------------------------------------------------
	 * CONTROL PANEL
	 * ------------------------------------------------------------ */
	
	/**
	 * Display a list of entries in MojoBlog so that the user can 
	 * edit and create new posts
	 */
	public function index($offset = 0) {
		// Paginate like a mofo
		$config['base_url'] = site_url('admin/addons/blog/index');
		$config['total_rows'] = $this->mojo->blog_model->count_all_results();
		$config['uri_segment'] = 5;
		
		$this->mojo->pagination->initialize($config);
		
		// Load the entries from the DB
		$this->data['entries'] = $this->mojo->blog_model->limit($this->mojo->pagination->per_page, $offset)->get();
		
		// Load the view
		$this->_view('index');
	}
	
	/**
	 * Display the create entry form
	 */
	public function create() {
		// Setup some variables
		$this->data['validation'] = '';
		$this->data['entry'] = array('title' => '', 'url_title' => '', 'content' => '', 'status' => '', 'category_id' => '');
		$this->data['statuses'] = array('' => '---', 'published' => 'Published', 'draft' => 'Draft', 'review' => 'Review');
		$this->data['categories'] = $this->mojo->blog_model->categories_dropdown();
		
		// Handle entry submission
		if ($this->mojo->input->post('entry')) {
			// Get the entry data and set some stuff
			$this->data['entry']		 		= $this->mojo->input->post('entry');
			$this->data['entry']['author_id'] 	= $this->mojo->session->userdata('id');
			$this->data['entry']['date']		= date('Y-m-d H:i:s');
			$this->data['entry']['status']		= ($this->data['entry']['status']) ? $this->data['entry']['status'] : 'published';
			
			// Insert it!
			if ($this->mojo->blog_model->insert($this->data['entry'])) {
				// It's success
				$response['result'] = 'success';
				$response['reveal_page'] = site_url('admin/addons/blog/index');
				$response['message'] = 'Successfully created new entry';
				
				exit($this->mojo->javascript->generate_json($response));
			} else {
				// There have been validation errors
				$response['result'] = 'error';
				$response['message'] = $this->mojo->blog_model->validation_errors;
				
				// Output the response
				exit($this->mojo->javascript->generate_json($response));
			}
		}
		
		// Load that bitchin' view
		$this->_view('create');
	}
	
	/**
	 * Display the edit entry form
	 */
	public function edit($entry_id) {
		// Setup some variables
		$this->data['validation'] = '';
		$this->data['entry'] = $this->mojo->blog_model->where('id', $entry_id)->get(TRUE, TRUE);
		$this->data['statuses'] = array('' => '---', 'published' => 'Published', 'draft' => 'Draft', 'review' => 'Review');
		$this->data['categories'] = $this->mojo->blog_model->categories_dropdown();
		
		// Handle entry submission
		if ($this->mojo->input->post('entry')) {
			// Get the entry data and set some stuff
			$this->data['entry']		 		= $this->mojo->input->post('entry');
			$this->data['entry']['author_id'] 	= $this->mojo->session->userdata('id');
			$this->data['entry']['date']		= date('Y-m-d H:i:s');
			$this->data['entry']['status']		= ($this->data['entry']['status']) ? $this->data['entry']['status'] : 'published';
			
			// Insert it!
			if ($this->mojo->blog_model->where('id', $entry_id)->update($this->data['entry'])) {
				// It's success
				$response['result'] = 'success';
				$response['reveal_page'] = site_url('admin/addons/blog/index');
				$response['message'] = 'Successfully updated entry';
				
				exit($this->mojo->javascript->generate_json($response));
			} else {
				// There have been validation errors
				$response['result'] = 'error';
				$response['message'] = $this->mojo->blog_model->validation_errors;
				
				// Output the response
				exit($this->mojo->javascript->generate_json($response));
			}
		}
		
		// Load that bitchin' view
		$this->_view('edit');
	}
	
	/**
	 * Delete an entry
	 */
	public function delete($id) {
		// We've already confirmed by this point, so we
		// can go ahead and delete it
		$this->mojo->blog_model->where('id', $id)->delete();
		
		// Build the response
		$response['result'] = 'success';
		$response['message'] = 'Successfully deleted entry';
		$response['id'] = $id;
		
		// Output in JSON
		exit($this->mojo->javascript->generate_json($response));
	}
	
	/**
	 * Display a list of all the categories
	 */
	public function categories_all() {
		// Simple! Get the categories
		$this->data['categories'] = $this->mojo->blog_model->categories();
		
		// ...and display the view. We won't
		// be needing Pagination or anything like that
		$this->_view('categories');
	}
	
	/**
	 * Add a new category
	 */
	public function category_add() {
		// Setup validation
		$this->data['validation'] = "";
		$this->data['category']	= array('name' => '', 'url_name' => '');
		
		// Handle POST
		if ($this->mojo->input->post('category')) {
			// Get the category data
			$this->data['category']	= $this->mojo->input->post('category');
			
			// Insert it!
			if ($this->mojo->blog_model->insert_category($this->data['category'])) {
				// It's success
				$response['result'] = 'success';
				$response['reveal_page'] = site_url('admin/addons/blog/categories_all');
				$response['message'] = 'Successfully created category';
				
				exit($this->mojo->javascript->generate_json($response));
			} else {
				// There have been validation errors
				$response['result'] = 'error';
				$response['message'] = $this->mojo->blog_model->validation_errors;
				
				// Output the response
				exit($this->mojo->javascript->generate_json($response));
			}
		}
		
		// Show the view
		$this->_view('category_add');
	}
	
	/**
	 * Edit a category
	 */
	public function category_edit($id) {
		// Setup validation
		$this->data['validation'] = "";
		$this->data['category']	= $this->mojo->blog_model->where('id', $id)->category(TRUE);
		
		// Handle POST
		if ($this->mojo->input->post('category')) {
			// Get the category data
			$this->data['category']	= $this->mojo->input->post('category');
			
			// Insert it!
			if ($this->mojo->blog_model->where('id', $id)->update_category($this->data['category'])) {
				// It's success
				$response['result'] = 'success';
				$response['reveal_page'] = site_url('admin/addons/blog/categories_all');
				$response['message'] = 'Successfully updated category';
				
				exit($this->mojo->javascript->generate_json($response));
			} else {
				// There have been validation errors
				$response['result'] = 'error';
				$response['message'] = $this->mojo->blog_model->validation_errors;
				
				// Output the response
				exit($this->mojo->javascript->generate_json($response));
			}
		}
		
		// Show the view
		$this->_view('category_edit');
	}
	
	/**
	 * Delete a category
	 */
	public function category_delete($id) {
		// We've already confirmed by this point, so we
		// can go ahead and delete it
		$this->mojo->blog_model->where('id', $id)->delete_category();
		
		// Build the response
		$response['result'] = 'success';
		$response['message'] = 'Successfully deleted category';
		$response['id'] = $id;
		
		// Output in JSON
		exit($this->mojo->javascript->generate_json($response));
	}
	
	/* --------------------------------------------------------------
	 * TEMPLATE TAGS
	 * ------------------------------------------------------------ */
	
	/**
	 * Initialises the MojoBlog system, loading the key scripts and
	 * stylesheets needed to run a copy of MojoBlog
	 */
	public function init() {
		if ($this->mojo->auth->is_editor()) {
			$html = 	'<script type="text/javascript" src="'.$this->javascript_url().'"></script>';
			$html .= 	'<script type="text/javascript">$(function(){ Mojo.URL.mojoblog_skin_url = "' 
								. base_url() . SYSDIR . '/mojomotor/third_party/blog/javascript/ckeditor/skins/kama/"; });</script>';
			$html .= 	'<link rel="stylesheet" type="text/css" href="'.$this->stylesheet_url().'" />';
		
			$this->mojo->cp->appended_output[] = $html;
		}
	}
	
	/**
	 * Loops through a blog's entries and displays them
	 *
	 * {mojo:blog:entries 
	 * 			page="about|home" global="yes" limit="10" entry_id="1" entry_id_segment="3" entry_url_title_segment="3" no_posts_404="yes" status="published"
	 *			orderby="date" sort="desc" date_format="Y-m-d" no_posts="No posts!" paginate="yes" per_page="5" pagination_segment="p" paginate_once="yes"
	 *			category_segment="3"}
	 *	   	{entries}
	 *     		<h1>{title}</h1>
	 *     		<p>{content}</p>
	 * 		{/entries}
	 *
	 * 		{pagination}{first_page_url} {prev_page_url} - Page {current_page} of {total_pages} - {next_page_url} {last_page_url}{/pagination}
	 * {/mojo:blog:entries}
	 *
	 * @todo Add {page_number_list} (Google style, 1 - 2 - 3 - *4* - 5)
	 */
	public function entries($template_data) {
		$this->template_data 		= $template_data;
		$page 						= $this->_param('page');
		$global 					= $this->_param('global');
		$limit 						= $this->_param('limit');
		$status 					= $this->_param('status');
		$entry_id 					= $this->_param('entry_id');
		$entry_id_segment 			= $this->_param('entry_id_segment');
		$entry_url_title_segment 	= $this->_param('entry_url_title_segment');
		$no_posts_404	 			= $this->_param('no_posts_404');
		$orderby 					= $this->_param('orderby');
		$sort 						= $this->_param('sort');
		$date_format 				= $this->_param('date_format');
		$no_posts 					= $this->_param('no_posts');
		$paginate 					= $this->_param('paginate');
		$paginate_once				= $this->_param('paginate_once');
		$per_page 					= $this->_param('per_page');
		$pagination_segment			= $this->_param('pagination_segment');
		$category_segment			= $this->_param('category_segment');
		$cond						= array('single_entry_page' => FALSE, 'category_page' => FALSE);
		
		// Limit access by page
		if (!$this->_limited_access_by_page($page, $global)) {
			return '';
		}
		
		// Is there an entry ID in the URL?
		if ($entry_id_segment && $this->mojo->uri->segment((int)$entry_id_segment) && $this->mojo->uri->segment((int)$entry_id_segment-1) == 'entry') {
			$this->mojo->blog_model->where('id', $this->mojo->uri->segment((int)$entry_id_segment));
			
			$paginate = FALSE;
			$cond['single_entry_page'] = TRUE;
		} 
		
		// What about an entry URL title?
		elseif ($entry_url_title_segment && $this->mojo->uri->segment((int)$entry_url_title_segment) && $this->mojo->uri->segment((int)$entry_url_title_segment-1) == 'entry') {
			$this->mojo->blog_model->where('url_title', $this->mojo->uri->segment((int)$entry_url_title_segment));
			
			$paginate = FALSE;
			$cond['single_entry_page'] = TRUE;
		}
		
		// What about a category page?
		elseif ($category_segment && $this->mojo->uri->segment((int)$category_segment) && $this->mojo->uri->segment((int)$category_segment-1) == 'category') {
			$category = $this->mojo->blog_model->where('url_name', $this->mojo->uri->segment((int)$category_segment))->category();
			
			if ($category) {
				$this->mojo->blog_model->where('category_id', $category->id);
			
				$paginate = FALSE;
				$cond['category_page'] = TRUE;
			
				$category_name 		= $category->name;
				$category_url_name 	= $category->url_name;
				$category_id 		= $category->id;
			} else {
				$category_name 		= '';
				$category_url_name 	= '';
				$category_id 		= '';
			}
		}
		
		// Status
		if ($status) {
			// Get rid of 'not '
			$not = FALSE;
			if (substr($status, 0, 4) == 'not ') { $status = substr($status, 4); $not = TRUE; }
			
			// Multiple statuses
			$statuses = explode('|', $status);
			
			foreach ($statuses as $status) {
				if ($not) {
					$this->mojo->blog_model->where('status !=', $status);
				} else {
					$this->mojo->blog_model->or_where('status', $status);
				}
			}
		}
		
		// Orderby and sort
		$orderby = ($orderby) ? $orderby : 'date';
		$sort = ($sort) ? strtoupper($sort) : 'DESC';
		
		$this->mojo->blog_model->order_by("$orderby $sort");
		
		// Entry ID
		if ($entry_id) {
			$this->mojo->blog_model->where('id', $entry_id);
		}
		
		// Paginate?
		if ($paginate) {
			$per_page = ($per_page) ? $per_page : 5;
			$pagination_segment = ($pagination_segment) ? $pagination_segment : 3;
						
			if ($this->mojo->uri->segment((int)$pagination_segment) && $this->mojo->uri->segment((int)$pagination_segment-1) == 'p') {
				$page = (int)$this->mojo->uri->segment((int)$pagination_segment);
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
			$count = $this->mojo->blog_model->count_all_results();
		}
		
		// Any posts?
		if (!$posts) {
			if ($no_posts_404 == "yes") {
				show_404();
			} else {
				return ($no_posts) ? $no_posts : '';
			}
		} else {
			$parsed = "";
			
			// Do we have the {entries} tag at all?
			if (preg_match("/{entries}/", $this->template_data['template'])) {
				// Strip the template tags and replace with nothing
				$divs = '';
				$tags = array('{mojo:blog:entries}', '{/mojo:blog:entries}');
				$parsed = str_replace($tags, $divs, $this->template_data['template']);
				
				// Get the contents of the {entries}{/entries} tag
				preg_match("/\{entries\}(.*)\{\/entries\}/is", $this->template_data['template'], $internal_template);
				$internal_template = $internal_template[1];
								
				// Loop through and parse
				foreach ($posts as $post) {
					$tmp = $internal_template;
					
					$post->category = $this->mojo->db->where('id', (int)$post->category_id)->get('blog_categories')->row();
					$post->author = $this->mojo->db->where('id', $post->author_id)->get('members')->row()->email;
					
					// Start off with the basic variables
					$tmp = preg_replace("/{id}/i", $post->id, $tmp);
					$tmp = preg_replace("/{title}/i", $post->title, $tmp);
					$tmp = preg_replace("/{url_title}/i", $post->url_title, $tmp);
					$tmp = preg_replace("/{url_title_path}/i", site_url($this->mojo->mojomotor_parser->url_title . '/entry/' . $post->url_title), $tmp);
					$tmp = preg_replace("/{content}/i", $post->content, $tmp);
					$tmp = preg_replace("/{status}/i", ucwords($post->status), $tmp);
					$tmp = preg_replace("/{author}/i", $post->author, $tmp);
					
					// Category
					if ($post->category) {
						$tmp = preg_replace("/{category_name}/i", $post->category->name, $tmp);
						$tmp = preg_replace("/{category_url_name}/i", $post->category->url_name, $tmp);
						$tmp = preg_replace("/{category_id}/i", $post->category_id, $tmp);
					} else {
						$tmp = preg_replace("/{category_name}/i", '', $tmp);
						$tmp = preg_replace("/{category_url_name}/i", '', $tmp);
						$tmp = preg_replace("/{category_id}/i", '', $tmp);
					}
					
					// Category conditional...
					// TRUE condition
					if (preg_match("/\{if category\}(.*?)\{\/if\}/is", $tmp)) {
						$tmp = preg_replace("/\{if category\}(.*?)\{\/if\}/is", (($post->category_id) ? "$1" : ""), $tmp);
					}

					// FALSE condition
					if (preg_match("/\{if not category\}(.*?)\{\/if\}/is", $tmp)) {
						$tmp = preg_replace("/\{if not category\}(.*?)\{\/if\}/is", ((!$post->category_id) ? "$1" : ""), $tmp);
					}
					
					// Then to the date!
					if ($date_format) {
						$tmp = preg_replace("/{date}/i", date($date_format, strtotime($post->date)), $tmp);
					} else {
						$tmp = preg_replace("/{date}/i", date('d/m/Y', strtotime($post->date)), $tmp);
					}
					
					// Finally, add it to the buffer
					$entries_tag .= $tmp;
				}
			}
			
			// Replace the entries with the entirety of the tag parsed
			$parsed = preg_replace("/\{entries\}(.*)\{\/entries\}/is", $entries_tag, $parsed);
			
			// Finish off with pagination
			if (preg_match("/\{pagination\}(.*)\{\/pagination\}/is", $parsed, $pagtmp)) {
				if ($paginate) {
					$first_page_url = site_url($this->mojo->mojomotor_parser->url_title);
					$prev_page_url = ($page > 1) ? site_url($this->mojo->mojomotor_parser->url_title.'/p/'.(string)($page-1)) : FALSE;
					$current_page = $page;
					$total_pages = ceil($count/$per_page);
					$next_page_url = ($page < $total_pages) ? site_url($this->mojo->mojomotor_parser->url_title.'/p/'.(string)($page+1)) : FALSE;
					$last_page_url = site_url($this->mojo->mojomotor_parser->url_title.'/p/'.(string)round($count/$per_page));
					$pagtmp = $pagtmp[1];
					
					// Prev and next page conditionals
					if ($prev_page_url) {
						if (preg_match("/\{if prev_page\}(.*?)\{\/if\}/is", $pagtmp)) {
							$pagtmp = preg_replace("/\{if prev_page\}(.*?)\{\/if\}/is", "$1", $pagtmp);
						}
					} else {
						if (preg_match("/\{if prev_page\}(.*?)\{\/if\}/is", $pagtmp)) {
							$pagtmp = preg_replace("/\{if prev_page\}(.*?)\{\/if\}/is", "", $pagtmp);
						}
					}
					
					if ($next_page_url) {
						if (preg_match("/\{if next_page\}(.*?)\{\/if\}/is", $pagtmp)) {
							$pagtmp = preg_replace("/\{if next_page\}(.*?)\{\/if\}/is", "$1", $pagtmp);
						}
					} else {
						if (preg_match("/\{if next_page\}(.*?)\{\/if\}/is", $pagtmp)) {
							$pagtmp = preg_replace("/\{if next_page\}(.*?)\{\/if\}/is", "", $pagtmp);
						}
					}
					
					// Variable swap fun
					$pagtmp = preg_replace("/\{first_page_url\}/i", $first_page_url, $pagtmp);
					$pagtmp = preg_replace("/\{prev_page_url\}/i", $prev_page_url, $pagtmp);
					$pagtmp = preg_replace("/\{current_page\}/i", $current_page, $pagtmp);
					$pagtmp = preg_replace("/\{total_pages\}/i", $total_pages, $pagtmp);
					$pagtmp = preg_replace("/\{next_page_url\}/i", $next_page_url, $pagtmp);
					$pagtmp = preg_replace("/\{last_page_url\}/i", $last_page_url, $pagtmp);
					
					// Replace {pagination} tags
					$parsed = preg_replace("/\{pagination\}(.*?)\{\/pagination\}/is", $pagtmp, $parsed);
				} else {
					$parsed = preg_replace("/\{pagination\}(.*?)\{\/pagination\}/is", '', $parsed);
				}
			}
			
			// Swap out the conditionals
			foreach ($cond as $key => $val) {
				// TRUE condition
				if (preg_match("/\{if ".$key."\}(.*?)\{\/if\}/is", $parsed)) {
					$parsed = preg_replace("/\{if ".$key."\}(.*?)\{\/if\}/is", (($val) ? "$1" : ""), $parsed);
				}
				
				// FALSE condition
				if (preg_match("/\{if not ".$key."\}(.*?)\{\/if\}/is", $parsed)) {
					$parsed = preg_replace("/\{if not ".$key."\}(.*?)\{\/if\}/is", ((!$val) ? "$1" : ""), $parsed);
				}
			}
			
			// Categories stuff
			if ($cond['category_page']) {
				$parsed = preg_replace("/\{category_name\}/i", $category_name, $parsed);
				$parsed = preg_replace("/\{category_url_name\}/i", $category_url_name, $parsed);
				$parsed = preg_replace("/\{category_id\}/i", $category_id, $parsed);
			}
			
			// Finally, are there any mojo:blog tags internally? Run it through MM's template
			// parser so that it catches everythang. Reset tag data temporarily
			$tag_data = $this->mojo->mojomotor_parser->tag_data;
			$loop_count = $this->mojo->mojomotor_parser->loop_count;
			$this->mojo->mojomotor_parser->tag_data = array();
			$this->mojo->mojomotor_parser->loop_count = 0;
			
			$parsed = $this->mojo->mojomotor_parser->parse_template($parsed);
			
			$this->mojo->mojomotor_parser->tag_data = $tag_data;
			$this->mojo->mojomotor_parser->loop_count = $loop_count;
			
			// Return the parsed string!
			return $parsed;
		}
	}
	
	/**
	 * Loop through the site's available categories
	 */
	public function categories($template_data) {
		// Get the vars...
		$this->template_data = $template_data;
		$limit = $this->_param('limit');
		$orderby = $this->_param('orderby');
		$sort = $this->_param('sort');
		
		// Limit
		if ($limit) {
			$this->mojo->blog_model->limit($limit);
		}
		
		// Orderby and sort
		$orderby = ($orderby) ? $orderby : 'id';
		$sort = ($sort) ? strtoupper($sort) : 'ASC';
		
		$this->mojo->blog_model->order_by("$orderby $sort");
		
		// Get the categories
		$categories = $this->mojo->blog_model->categories();
		
		// Loop through them and build up a parsed template
		// string to return
		$parsed = "";
		
		// Strip the template tags and replace with nothing
		$divs = '';
		$tags = array('{mojo:blog:categories}', '{/mojo:blog:categories}');
		$parsed = str_replace($tags, $divs, $this->template_data['template']);
		$return = "";
		
		foreach ($categories as $category) {
			// Get template data and parse
			$tmp = $parsed;
			$tmp = preg_replace("/\{id\}/", $category->id, $tmp);
			$tmp = preg_replace("/\{name\}/", $category->name, $tmp);
			$tmp = preg_replace("/\{url_name\}/", $category->url_name, $tmp);
			$tmp = preg_replace("/\{entries\}/", $category->entries, $tmp);
			
			// Append to the total parsed
			$return .= $tmp;
		}
		
		// Return the parsed template
		return $return;
	}
	
	/**
	 * Outputs the URL to the RSS feed. Takes three parameters,
	 * the required 'blog' and an optional 'limit' and 'link_page'
	 *
	 * {mojo:blog:rss_url limit="15" link_page="about"}
	 */
	public function rss_url($template_data) {
		// Gather the variables
		$this->template_data = $template_data;
		$limit = $this->_param('limit');
		$link_page = $this->_param('link_page');
		
		// Defaults
		$limit = ($limit) ? (int)$limit : 10;
		$link_page = ($link_page) ? $link_page : $this->mojo->mojomotor_parser->url_title;
		
		// Output the URL, basically
		return site_url("admin/addons/blog/rss/$limit/$link_page");
	}
	
	/**
	 * Outputs the JavaScript necessary to include a Disqus box.
	 *
	 * {mojo:blog:disqus shortname="mymojosite" entry_id="{id}"}
	 */
	public function disqus($template_data) {
		// Gather vars
		$this->template_data = $template_data;
		$shortname = $this->_param('shortname');
		$entry_id = $this->_param('entry_id');
		
		// Make sure we have a shortname
		if (!$shortname) {
			show_error('Shortname is required for the {mojo:blog:disqus} tag to work!');
		}
		
		// Display the HTML!
		$html = '<div id="mojoblog_disqus">'.PHP_EOL;
		$html .= '<div id="disqus_thread"></div>'.PHP_EOL;
		$html .= '<script type="text/javascript">'.PHP_EOL;
		$html .= 'var disqus_shortname = "'.$shortname.'";'.PHP_EOL;
		
		// Do a quick check to test if we're developing locally
		if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1') {
			$html .= 'var disqus_developer = 1;'.PHP_EOL;
		}
		
		// Do we have an entry ID?
		if ($entry_id) {
			// Get the entry
			$entry = $this->mojo->blog_model->where('id', $entry_id)->get(TRUE);
			
			// Output that mofo
			$html .= 'var disqus_identifier = "mojoblog_'.$shortname.'_entry_'.$entry->id.'";'.PHP_EOL;
			$html .= 'var disqus_url = "'.site_url($this->mojo->mojomotor_parser->url_title.'/entry/'.$entry->url_title).'";'.PHP_EOL;
		}
		
		// Output the rest
		$html .= "(function() {
		        var dsq = document.createElement('script'); dsq.type = 'text/javascript'; dsq.async = true;
		        dsq.src = 'http://' + disqus_shortname + '.disqus.com/embed.js';
		        (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);
		    })();
		</script>";
		$html .= '<noscript>Please enable JavaScript to view the <a href="http://disqus.com/?ref_noscript">comments powered by Disqus.</a></noscript>'.PHP_EOL;
		//$html .= '<a href="http://disqus.com" class="dsq-brlink">blog comments powered by <span class="logo-disqus">Disqus</span></a>'.PHP_EOL;
		$html .= '</div>'.PHP_EOL;
		
		// And return it!
		return $html;
	}
	
	/* --------------------------------------------------------------
	 * RSS/ATOM FEEDS
	 * ------------------------------------------------------------ */
	
	/**
	 * Display an RSS feed from the supplied blog,
	 * with an optional limit parameter (defaulting to 10)
	 */
	public function rss() {
		// Get the variables, brother
		$limit = ($this->mojo->uri->segment(5)) ? $this->mojo->uri->segment(5) : 10;
		$link_page = ($this->mojo->uri->segment(6)) ? $this->mojo->uri->segment(6) : $this->mojo->site_model->default_page();
		
		// Get the posts, my friend
		$data['posts'] = $this->mojo->blog_model->order_by('date DESC')->limit($limit)->get();
		$data['site_name'] = $this->mojo->site_model->get_setting('site_name');
		$data['rss_url'] = site_url('admin/addons/blog/rss/'.$limit.'/'.$link_page);
		$data['link_page'] = $link_page;
		
		// Set mime types and extract variables
		header("Content-type: application/rss+xml");
		extract($data);
		
		// And output the RSS!
		include('blog/rss.xml');
	}
	
	/* --------------------------------------------------------------
	 * ASSETS (CSS & JAVASCRIPT)
	 * ------------------------------------------------------------ */
	
	/**
	 * Load the MojoBlog JavaScript
	 */
	public function javascript() {
		header("Content-Type: text/javascript");
		echo file_get_contents(APPPATH.'third_party/blog/javascript/mojoblog.js');
	}
	
	/**
	 * Load the MojoBlog CSS
	 */
	public function css() {
		header("Content-Type: text/css");
		echo file_get_contents(APPPATH.'third_party/blog/css/mojoblog.css');
	}
	
	/**
	 * Returns the correct URL to the MojoBlog JavaScript
	 */
	public function javascript_url() {
		return site_url('admin/addons/blog/javascript');
	}
	
	/**
	 * Returns the correct URL to the MojoBlog CSS
	 */
	public function stylesheet_url() {
		return site_url('admin/addons/blog/css');
	}
	
	/**
	 * Render an image from the addon
	 */
	public function image($file) {
		header('Content-Type: image/png');
		die(file_get_contents(APPPATH.'third_party/blog/images/'.$file));
	}
	
	/**
	 * Wrapper for image() (because of CKEditor's load path)
	 */
	public function images($file) {
		$this->image($file);
	}
		
	/* --------------------------------------------------------------
	 * INSTALLATION ROUTINE
	 * ------------------------------------------------------------ */
	
	/**
	 * Installs MojoBlog
	 */
	public function install() {
		// Are we installed?
		$current = config_item('mojoblog_version');
		
		// Begone!
		if ($current) {
			$this->data['title'] = 'Already installed!';
			$this->data['message'] = 'MojoBlog is already installed, so there\'s nothing to do here. Enjoy using MojoBlog!';
			$this->_view('installer');
			
			exit;
		}
		
		// Check that we're setup and the DB table exists
		$this->mojo->blog_model->install();
		
		// Make sure we update the routing
		$this->mojo->blog_model->install_routing();
		
		// Let the user know about it
		$this->data['title'] = 'Installation successful';
		$this->data['message'] = 'MojoBlog has been successfully installed. Thanks for purchasing, and please remember to read the documentation thoroughly before using. If you have any concerns or issues with MojoBlog, don\'t hesitate to report them at our dedicated support site. Enjoy!';
		$this->_view('installer');
		
		exit;
	}
	
	/**
	 * Uninstalls MojoBlog.
	 */
	public function uninstall() {
		// Are we allowed to do this?
		if (!$this->mojo->auth->is_admin()) {
			die('Unauthorised access!');
		}
		
		// Bye!
		$this->mojo->blog_model->uninstall();
		
		// Let the user know
		$this->data['title'] = 'Uninstallation successful';
		$this->data['message'] = 'MojoBlog is uninstalled. Please remove the <strong>blog/</strong> folder from <strong>mojomotor/third_party</strong>.';
		$this->_view('installer');
		
		exit;
	}
	
	/**
	 * Update routine
	 */
	public function update() {
		$this->mojo->load->dbforge();
		
		// What's the current system version?
		$current = config_item('mojoblog_version');
		
		// Do we even have it?
		if (!$current) {
			// We know it's 1.1.3
			$current = '1.1.3';
		}
		
		// Do we need to upgrade at all?
		if ($this->version > $current) {
			// Run the upgrade
			$this->mojo->blog_model->upgrade($current, $this->version);
			
			// Write the current version
			$this->mojo->config->config_update(array('mojoblog_version' => $this->version));

			// Output a message
			$this->data['title'] = "Upgrade successful";
			$this->data['message'] = 'You have upgraded MojoBlog to version ' . $this->version . '. Enjoy!';
		} else {
			// Output a message
			$this->data['title'] = "You're already up to date";
			$this->data['message'] = 'You are already running MojoBlog ' . $this->version . ', so there\'s no upgrade needed!';
		}
		
		$this->_view('installer');
		exit;
	}
	
	/**
	 * Export everything for the EE importer
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
	
	/* --------------------------------------------------------------
	 * HELPER METHODS
	 * ------------------------------------------------------------ */
	
	/**
	 * Load a view
	 */
	private function _view($view) {
		// ...save the original view path, and set to our Foo Bar package view folder
		$orig_view_path = $this->mojo->load->_ci_view_path;
		$this->mojo->load->_ci_view_path = APPPATH.'third_party/blog/views/';
		
		// ...load the view
		echo $this->mojo->load->view($view, $this->data, TRUE);
		
		// ...then return the view path to the application's original view path
		$this->mojo->load->_ci_view_path = $orig_view_path;
	}
	
	/**
	 * Fetch a parameter
	 */
	private function _param($key) {
		return (isset($this->template_data['parameters'][$key])) ? $this->template_data['parameters'][$key] : FALSE;
	}
	
	/**
	 * Limit the access by page name, or bar separated list 
	 * of page names.
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
					$url = implode('/', $this->mojo->uri->rsegments);
					
					if ('page/content/' . $possible_page == $url || $possible_page == $default_page) {
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