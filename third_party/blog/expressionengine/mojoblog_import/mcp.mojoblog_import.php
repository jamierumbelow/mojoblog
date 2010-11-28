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
		
		$this->ee->load->library('api');
		$this->ee->api->instantiate('channel_structure');
		$this->ee->api->instantiate('channel_entries');
		$this->ee->api->instantiate('channel_fields');
		
		$this->ee->cp->set_variable('cp_page_title', lang('mojoblog_import_module_name')); 
	}
	
	public function index() {
		// First, has the file been uploaded?
		if (!isset($_FILES['userfile'])) {
			return $this->upload_file();
		}
		
		// Fetch the uploaded file data
		if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
			$import = file_get_contents($_FILES['userfile']['tmp_name']);
		} else {
			$this->ee->session->set_flashdata('message_error', "Couldn't upload file!");
			$this->ee->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mojoblog_import');
		}
		
		// Unserialize
		$data = unserialize($import);
		
		// Check that it's a valid MojoBlog export file
		if (!isset($data['mojo_blog_export'])) {
			$this->ee->session->set_flashdata('message_error', "Invalid export file. Please re-export and try again");
			$this->ee->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mojoblog_import');
		}
		
		// Check we've got a custom field group for MojoBlog
		$query = $this->ee->db->where('group_name', 'MojoBlog')->get('field_groups');
		
		if ($query->num_rows > 0) {
			$group = $query->row('group_id');
			$field = $this->ee->db->select('field_id')->where('group_id', $group)->where('field_name', 'content')->get('channel_fields')->row('field_id');
		} else {
			$group = $this->ee->db->insert('field_groups', array(
				'site_id' => config_item('site_id'),
				'group_name' => 'MojoBlog'
			));
			
			$this->ee->db->insert('channel_fields', array(
				'site_id' 						=> config_item('site_id'),
				'group_id' 						=> $group,
				'field_name' 					=> 'content',
				'field_label' 					=> 'Content',
				'field_type' 					=> 'textarea',
				'field_fmt' 					=> 'none',
				'field_show_fmt'				=> 'n',
				'field_required'				=> 'n',
				'field_search'					=> 'y',
				'field_is_hidden'				=> 'n',
				'field_pre_populate'			=> 'n',
				'field_show_spellcheck'			=> 'n',
				'field_show_smileys'			=> 'n',
				'field_show_glossary'			=> 'n',
				'field_show_formatting_btns'	=> 'n',
				'field_show_writemode'			=> 'n',
				'field_show_file_selector'		=> 'n',
				'field_text_direction'			=> 'ltr',
				'field_settings'				=> base64_encode(serialize($this->ee->api_channel_fields->get_global_settings('textarea'))) // default to global settings
			));
			$field = $this->ee->db->insert_id();
		}
		
		$author = $this->ee->db->select('member_id')->where('group_id', 1)->get('members', 1)->row('member_id');
		
		// Loop through the blogs and find/create channels
		foreach ($data['blogs'] as $blog => $posts) {
			// Does a channel exist?
			$query = $this->ee->db->where('channel_name', $blog)->get('channels');
			
			if ($query->num_rows > 0) {
				$channel = $query->row('channel_id');
			} else {
				// Create a new channel
				$data = array(
					'channel_title' => ucwords(str_replace('_', ' ', $blog)),
					'channel_name' => $blog,
					'field_group' => $group
				);
				
				$channel = $this->ee->api_channel_structure->create_channel($data);
			}
			
			// Loop through the entries
			foreach ($posts as $post) {
				$entry["title"] = $post->title;
				$entry["field_id_".$field] = $post->content;
				$entry["entry_date"] = strtotime($post->date);
				$entry["author_id"] = $author;
				$entry["channel_id"] = $channel;
				$entry['ping_servers'] = 0;
				
				$this->ee->api_channel_entries->submit_new_entry($channel, $entry);
			}
		}
		
		// We're done!
		$this->ee->session->set_flashdata('message_success', "Successfully imported MojoBlog data!");
		$this->ee->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mojoblog_import'.AMP.'method=success');
	}
	
	public function upload_file() {
		return $this->ee->load->view('upload', array(), TRUE);
	}
	
	public function success() {
		return $this->ee->load->view('success', array(), TRUE);
	}
}