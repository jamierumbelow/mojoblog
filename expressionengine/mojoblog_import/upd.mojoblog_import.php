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

class Mojoblog_import_upd {
	public function __construct() {
		$this->ee =& get_instance();
		$this->version = '1.0.0';
	}
	
	public function install() {
		$this->ee->db->insert('exp_modules', array(
			'module_name' => 'Mojoblog_import',
			'module_version' => $this->version,
			'has_cp_backend' => 'y',
			'has_publish_fields' => 'n'
		));
		return TRUE;
	}
	
	public function uninstall() {
		$this->ee->db->where('module_name', 'Mojoblog_import')->delete('exp_modules');
		return TRUE;
	}
	
	public function update($version = '') {
		return TRUE;
	}
}