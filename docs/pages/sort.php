<?php
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

function sparkplugs_doc_sort($a, $b) {
	$order = array(
		'introduction.html', 'usage.html', 'template_tags.html'
	);
	
	return (array_search($a, $order) > array_search($b, $order)) ? 1 : -1;
}