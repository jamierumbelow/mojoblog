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

// Our main function
jQuery(function(){

	/**
	 * After the Pages navigation bar, we want to insert the
	 * MojoBlog Blogs toolbar button.
	 */
	var toolbar_button = $("<li>").attr('id', 'mojo_admin_blog')
								  .html($("<a>").attr('href', Mojo.URL.site_path + '/admin/addons/blog')
												.attr('title', 'Blog')
												.text('Blog'));
	$("#mojo_admin_pages").after(toolbar_button);
});