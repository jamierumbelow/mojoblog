==============================
	MojoBlog Version 2.1.0
	http://getsparkplugs.com/mojoblog/
==============================

Thanks for purchasing MojoBlog! Installation instructions can be found in the documentation on the website, or below. If you've got any questions, queries or problems, just head to the support forums and open a request. The license can be found with your download. Enjoy!

Requirements
============

MojoBlog only requires an installed copy of MojoMotor 1.1.0+ and what’s required by MojoMotor 1.1.0.

Install Instructions
====================

Installing MojoBlog is just like any other MojoMotor addon. Simply drag the blog/ folder found in your download into the system/mojomotor/third_party/ folder. Ensure that your *config/config.php* and *config/routes.php* files are set to have **0777** permissions. Then, point your web browser to *http://domain.com/index.php/admin/addons/blog/install* and MojoBlog will install itself. 

It's that simple - MojoBlog is installed!

Changelog
=========

	Version 2.1.0
		- Adding the {excerpt} variable
		- Fixing an error with file URLs

	Version 2.0.0
		- Added a new MojoBar interface
		- Added a polished install and update interface
		- Added entry statuses
		- Added categories
		- Added entry URL titles
		- Added a tag for Disqus integration
		- Added an {if single_entry_page} conditional
		- Thoroughly cleaned up the code
		- Fixed many bugs
	
	Version 1.1.3
		- Moved the install procedure into a separate method callable from the URL
		- Removed the entry_id_segment parameter and added the entry_id_qs parameter to get round MM1.1
		- Moved pagination over to using querystrings
		- Fixed an error with page restrictions

	Version 1.1.2
		- Added attempted compatibility for MM1.1

	Version 1.1.1
		- Fixed an error with multi-line regexes and made all the tags case insensitive
		- Fixed MM1.1 compatibility
		- Added an {excerpt} variable

	Version 1.1.0
		- Added Outfielder support
		- Added Pagination
		- Added an {author} tag
		- entry_id, no_posts_404 and entry_id_segment parameters (single entry pages)
		- Fixed a language file bug
		- Fixed a bug with apostrophes
		- Fixed MojoMotor save button error
		- Added a title="" parameter

	Version 1.0.0
		- First Release

More Links
==========

	Support: 		http://mojoblog.tenderapp.com
	Documentation:	http://getsparkplugs.com/mojoblog/docs
	Account:		http://getsparkplugs.com/account
	Email: 			jamie@jamierumbelow.net
	Phone: 			+44 (0)7956 363875
	My Blog:	 	http://jamieonsoftware.com
	My Site:	 	http://jamierumbelow.net