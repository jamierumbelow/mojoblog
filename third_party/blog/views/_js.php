<script type="text/javascript" charset="utf-8">
	$(function(){
		$('#mojoblog_entry_content').ckeditor({
			"skin": 'mojo,' + parent.Mojo.URL.editor_skin_path,
			"toolbar": parent.Mojo.toolbar,
			"toolbarCanCollapse": false,
			"toolbarStartupExpanded": true,
			"removePlugins": "scayt,save",
			"language" : "en",
			filebrowserBrowseUrl: parent.Mojo.URL.site_path + "/admin/editor/browse",
			filebrowserWindowWidth: "780",
			filebrowserWindowHeight: "500",
			filebrowserUploadUrl: parent.Mojo.URL.site_path + "/admin/editor/upload"
		});
	});
</script>