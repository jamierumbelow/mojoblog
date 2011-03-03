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
		fwrite($f, "\n\n" . '$route = array(	\'(.+)/entry/(.+)/?\' => \'page/content/$1\' ) + $route;');
		fclose($f);
	}
	
	public function uninstall() {
		$this->load->dbforge();
		$this->dbforge->drop_table('blog_entries');
	}
	
	public function upgrade($old, $new) {
		// 1.1.3 -> 2
		if ($new > '1.1.3') {
			// Remove the blog column
			$this->dbforge->drop_column('blog_entries', 'blog');
			
			// Add the status & cat ID column
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
	
	public function __call($method, $arguments) {
		if (method_exists($this->db, $method)) {
			call_user_func_array(array($this->db, $method), $arguments);
			return $this;
		}
	}
}