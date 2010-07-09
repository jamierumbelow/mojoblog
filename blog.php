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
		// $this->mojo->load->model('blog_model', 'blog');
	}
	
	public function entries() {
		return "Booya";
	}
}