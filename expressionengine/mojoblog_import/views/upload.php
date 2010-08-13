<p>Welcome to the MojoBlog ExpressionEngine Importer. The import process is extremely simple, but if you get stuck at any point along the way then feel free to read the <a href="http://getsparkplugs.com/mojoblog/docs/tools#importer">extensive documentation</a> which should help clarify anything. Because the MojoBlog Importer uses the same member system as MojoMotor, <strong>you must make sure that you run the MojoMotor Importer before the MojoBlog Importer. Additionally, please don't make any changes to your ExpressionEngine or MojoMotor site before you have run both exporters, otherwise things may get confused!</strong></p>

<br />

<?=form_open_multipart('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=mojoblog_import')?>
	<p>Export File: <?=form_upload('userfile')?></p>
	<p><input type="submit" value="Upload" class="submit" /></p>
<?=form_close()?>