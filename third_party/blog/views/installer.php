<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head>
		<title>MojoBlog - Installer</title>
		
		<link rel="stylesheet" href="<?=site_url('assets/css')?>" type="text/css" /> 
		<link rel="stylesheet" href="<?=site_url('assets/css/standalone_pages')?>" type="text/css" />
		<link rel="stylesheet" href="<?=site_url('admin/addons/blog/css')?>" type="text/css" />
	</head>
	
	<body>
		<div id="mojoblog_install">
			<div id="mojoblog_install_header" class="mojo_header">
				<h1>MojoBlog</h1>
			</div>
		
			<div class="mojo_login"> 
				<h2><?=$title?></h2> 
				<p><?=$message?></p>
				
				<br />
				
				<p><a href="<?=base_url()?>">Return to your site</a></p>
			</div>
		</div>
	</body>
</html>