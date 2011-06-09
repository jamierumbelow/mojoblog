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

class Blog_model extends CI_Model {
	public $validation_errors = '';
	private $_old_query_stuff = array();
	
	public function __construct() {
		parent::__construct();
	}
	
	public function install() {
		$this->load->dbforge();
		
		// Create blog_entries
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'author_id' => array(
				'type' => 'INT'
			),
			'category_id' => array(
				'type' => 'INT'
			),
			'status' => array(
				'type' => 'VARCHAR',
				'constraint' => '20'
			),
			'title' => array(
				'type' => 'VARCHAR',
				'constraint' => '250'
			),
			'url_title' => array(
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
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('blog_entries', TRUE);
		
		// Create the blog_categories table
		$this->dbforge->add_field(array(
			'id' 		=> array( 'type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE ),
			'name'		=> array( 'type' => 'VARCHAR', 'constraint' => 200 ),
			'url_name'	=> array( 'type' => 'VARCHAR', 'constraint' => 200 )
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('blog_categories');
	}
	
	public function install_routing() {
		$f = fopen(APPPATH . 'config/routes.php', 'a');
		fwrite($f, "\n\n // MojoBlog Automatically Installed Routes");
		fwrite($f, "\n\n" . '$route = array(	\'(.+)/entry/(.+)/?\' => \'page/content/$1\' ) + $route;');
		fwrite($f, "\n\n" . '$route = array(	\'(.+)/category/(.+)/?\' => \'page/content/$1\' ) + $route;');
		fwrite($f, "\n\n" . '$route = array(	\'(.+)/p/([0-9]+)/?\' => \'page/content/$1\' ) + $route;');
		fwrite($f, "\n\n // End MojoBlog Automatically Installed Routes");
		fclose($f);
	}
	
	public function uninstall() {
		$this->load->dbforge();
		$this->dbforge->drop_table('blog_entries');
	}
	
	public function upgrade($old, $new) {
		// 1.1.3 -> 2
		if ($new > '1.1.3' && '1.1.3' > $old) {
			// Remove the blog column
			$this->dbforge->drop_column('blog_entries', 'blog');
			
			// Add the URL title, status & cat ID column
			$this->dbforge->add_column('blog_entries', array('url_title' => array('type' => 'VARCHAR', 'constraint' => '250')));
			$this->dbforge->add_column('blog_entries', array('status' => array('type' => 'VARCHAR', 'constraint' => '20')));
			$this->dbforge->add_column('blog_entries', array('category_id' => array('type' => 'INT')));
			
			// Create the blog_categories table
			$this->dbforge->add_field(array(
				'id' 		=> array( 'type' => 'INT', 'unsigned' => TRUE, 'auto_increment' => TRUE ),
				'name'		=> array( 'type' => 'VARCHAR', 'constraint' => 200 ),
				'url_name'	=> array( 'type' => 'VARCHAR', 'constraint' => 200 )
			));
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table('blog_categories');
			
			// Install the routing
			$this->install_routing();
		}
	}
	
	public function get($row = FALSE, $array = FALSE) {
		if (!$row) {
			if ($array) {
				return $this->db->get('blog_entries')->result_array();
			} else {
				return $this->db->get('blog_entries')->result();
			}
		} else {
			if ($array) {
				return $this->db->get('blog_entries')->row_array();
			} else {
				return $this->db->get('blog_entries')->row();
			}
		}
	}
	
	public function insert($data) {
		// Do a little bit of validation
		if (!isset($data['title']) || empty($data['title'])) {
			$this->validation_errors .= "Entry title is required!\n";
		}
		if (!isset($data['content']) || empty($data['content'])) {
			$this->validation_errors .= "Entry content is required!\n";
		}
		
		// Sanitise like a mofo
		if (!isset($data['url_title']) || empty($data['url_title'])) {
			$data['url_title'] = url_title($data['title']);
		}
		
		// Return FALSE if we has errors
		if (!empty($this->validation_errors)) {
			return FALSE;
		}
		
		// Go insert the entry
		$this->db->insert('blog_entries', $data);
		return $this->db->insert_id();
	}
	
	public function update($data = array()) {
		// Do a little bit of validation
		if (!isset($data['title']) || empty($data['title'])) {
			$this->validation_errors .= "Entry title is required!\n";
		}
		if (!isset($data['content']) || empty($data['content'])) {
			$this->validation_errors .= "Entry content is required!\n";
		}
		
		// Sanitise like a mofo
		if (!isset($data['url_title']) || empty($data['url_title'])) {
			$data['url_title'] = url_title($data['title']);
		}
		
		// Return FALSE if we has errors
		if (!empty($this->validation_errors)) {
			return FALSE;
		}
		
		// Update!
		return $this->db->update('blog_entries', $data);
	}
	
	public function delete() {
		return $this->db->delete('blog_entries');
	}
	
	public function count_all_results() {
		return $this->db->count_all_results('blog_entries');
	}
	
	public function categories() {
		return $this->db->select('blog_categories.*')
						->select('COUNT(`mojo_blog_entries`.`id`) AS entries')
						->join('blog_entries', 'blog_entries.category_id = mojo_blog_categories.id', 'left')
						->group_by('blog_categories.id')
						->get('blog_categories')
						->result();
	}
	
	public function category($array = FALSE) {
		if (!$array) {
			return $this->db->get('blog_categories')->row();
		} else {
			return $this->db->get('blog_categories')->row_array();
		}
	}
	
	public function insert_category($data) {
		// Do a little bit of validation
		if (!isset($data['name']) || empty($data['name'])) {
			$this->validation_errors .= "Category name is required!\n";
		}
		
		// Sanitise like a mofo
		if (!isset($data['url_name']) || empty($data['url_name'])) {
			$data['url_name'] = url_title($data['name']);
		}
		
		// Return FALSE if we has errors
		if (!empty($this->validation_errors)) {
			return FALSE;
		}
		
		// Insert
		return $this->db->insert('blog_categories', $data);
	}
	
	public function update_category($data) {
		// Do a little bit of validation
		if (!isset($data['name']) || empty($data['name'])) {
			$this->validation_errors .= "Category name is required!\n";
		}
		
		// Sanitise like a mofo
		if (!isset($data['url_name']) || empty($data['url_name'])) {
			$data['url_name'] = url_title($data['name']);
		}
		
		// Return FALSE if we has errors
		if (!empty($this->validation_errors)) {
			return FALSE;
		}
		
		// Update
		return $this->db->update('blog_categories', $data);
	}
	
	public function delete_category() {
		return $this->db->delete('blog_categories');
	}
	
	public function categories_dropdown() {
		$cats = $this->categories();
		$dropdown = FALSE;
		
		if ($cats) {
			$dropdown[''] = '---';
			
			foreach ($cats as $cat) {
				$dropdown[$cat->id] = $cat->name;
			}
		}
		
		return $dropdown;
	}
	
	public function isolate() {
		// Cache the CodeIgniter ActiveRecord values
		$ar_values = array(
			'ar_select', 'ar_distinct', 'ar_from', 'ar_join', 'ar_where', 'ar_like', 
			'ar_groupby', 'ar_having', 'ar_limit', 'ar_offset', 'ar_order', 'ar_orderby', 
			'ar_set', 'ar_wherein', 'ar_aliased_tables', 'ar_store_array'
		);
		foreach ($ar_values as $val) { $this->_old_query_stuff[$val] = $this->db->$val; $this->db->$val = null; }
		
		// Return $this
		return $this;
	}
	
	public function unisolate() {
		// Restore the AR values
		foreach ($this->_old_query_stuff as $key => $val) { $this->db->$key = $val; }
	}
	
	public function __call($method, $arguments) {
		if (method_exists($this->db, $method)) {
			call_user_func_array(array($this->db, $method), $arguments);
			return $this;
		}
	}
}