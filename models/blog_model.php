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
	public function __construct() {
		parent::CI_Model();
	}
	
	public function install() {
		$this->load->dbforge();
		$this->dbforge->add_field(array(
			'id' => array(
				'type' => 'INT',
				'unsigned' => TRUE,
				'auto_increment' => TRUE
			),
			'author_id' => array(
				'type' => 'INT'
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
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('blog_entries', TRUE);
	}
	
	public function uninstall() {
		$this->load->dbforge();
		$this->dbforge->drop_table('blog_entries');
	}
	
	public function get($row = FALSE) {
		if (!$row) {
			return $this->db->get('blog_entries')->result();
		} else {
			return $this->db->get('blog_entries')->row();
		}
	}
	
	public function insert($data) {
		$this->db->insert('blog_entries', $data);
		return $this->db->insert_id();
	}
	
	public function update($data = array()) {
		return $this->db->update('blog_entries', $data);
	}
	
	public function delete() {
		return $this->db->delete('blog_entries');
	}
	
	public function __call($method, $arguments) {
		if (method_exists($this->db, $method)) {
			call_user_func_array(array($this->db, $method), $arguments);
			return $this;
		}
	}
}