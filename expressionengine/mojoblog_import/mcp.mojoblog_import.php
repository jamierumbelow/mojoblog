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

class Mojoblog_import_mcp {
	public function __construct() {
		$this->ee =& get_instance();
		$this->ee->load->library('upload');
		$this->ee->cp->set_variable('cp_page_title', lang('mojoblog_import_module_name')); 
	}
	
	public function index() {
		// First, has the file been uploaded?
		if (!$this->ee->input->post('userfile')) {
			return $this->upload_file();
		}
	}
	
	public function upload_file() {
		return $this->ee->load->view('upload', array(), TRUE);
	}
}