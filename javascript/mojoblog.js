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
MojoBlog = function(){
    
    /**
     * Loop through all .mojo_blog_content elements
     * and turn into a CKEditor instance
     */
    function ckeditorise() {
        jQuery(".mojo_blog_content:not([data-editor='no'])").each(function() {
            /* Add a unique class */
            var d = new Date(),
            t = d.getTime(),
            r = Math.random() * 20000,
            u = Math.floor(t + r);
            jQuery(this).addClass("random_class_" + u);
            
            /* CKify */
            if (!jQuery(".random_class_" + u).hasClass("mojo_blog_new_entry")) {
                jQuery(".random_class_" + u).ckeditor(function() {},
                {
                    "skin": "mojo," + Mojo.URL.editor_skin_path,
                    "startupMode": Mojo.edit_mode,
                    "toolbar": Mojo.toolbar,
                    "extraPlugins": "cancel",
                    "removePlugins": "save",
                    "toolbarCanCollapse": false,
                    "toolbarStartupExpanded": true,
                    "resize_enabled": true,
                    filebrowserBrowseUrl: Mojo.URL.site_path + "/editor/browse",
                    filebrowserWindowWidth: "780",
                    filebrowserWindowHeight: "500",
                    filebrowserUploadUrl: Mojo.URL.site_path + "/editor/upload"
                });
            } else {
                jQuery(".random_class_" + u).ckeditor(function() {},
                {
                    "skin": "mojo," + Mojo.URL.editor_skin_path,
                    "startupMode": Mojo.edit_mode,
                    "toolbar": Mojo.toolbar,
                    "removePlugins": "save",
                    "toolbarCanCollapse": false,
                    "toolbarStartupExpanded": true,
                    "resize_enabled": true,
                    filebrowserBrowseUrl: Mojo.URL.site_path + "/editor/browse",
                    filebrowserWindowWidth: "780",
                    filebrowserWindowHeight: "500",
                    filebrowserUploadUrl: Mojo.URL.site_path + "/editor/upload"
                });
            }
        });
    };
    
    /**
     * Get a post's data, build the form, ckeditorize,
     * and save the post when the user clicks submit.
     */
    function handle_mojo_blog_edit(entry) {
        var origHTML = jQuery(entry).html();
        jQuery.get(Mojo.URL.site_path + "/addons/blog/entry_get/" + jQuery(entry).attr("data-post-id"), {},
        function(data) {
            var title = data["title"],
            blog = data["blog"],
            content = data["content"];
            jQuery(entry).html("<input type='hidden' class='mojo_blog_orig_html' value='" + escape(origHTML) + "' /><input type='hidden' name='mojo_blog_id' class='mojo_blog_id' value='" + data["id"] + "' /><input type='hidden' name='mojo_blog_blog' class='mojo_blog_blog' value='" + data["blog"] + "' /><p><input style='padding: 5px; font-size: 14px; width: 90%' type='text' name='mojo_blog_title' class='mojo_blog_title' value='" + data["title"] + "' /></p><p><textarea class='mojo_blog_content'>" + data["content"] + "</textarea></p><p><input type='submit' class='mojo_blog_update' name='mojo_blog_update' class='mojo_blog_update' value='Update Entry' /> <small><a href='#' class='mojo_blog_delete'>(delete post)</a></small></p>");
            ckeditorise();
            jQuery(entry).attr("data-active", "true");
            jQuery(entry).removeClass("mojo_blog_entry_region");
            jQuery(".mojo_blog_update").click(function() {
                var par = jQuery(this).parent().parent();
                var blogdata = {
                    mojo_blog_id: jQuery(par).find(".mojo_blog_id").val(),
                    mojo_blog_title: jQuery(par).find(".mojo_blog_title").val(),
                    mojo_blog_content: jQuery(par).find(".mojo_blog_content").val(),
                    mojo_blog_blog: jQuery(par).find(".mojo_blog_blog").val(),
                    ci_csrf_token: Mojo.Vars.csrf
                };
                jQuery.post(Mojo.URL.site_path + "/addons/blog/entry_update", blogdata,
                function() {
                    window.location.reload();
                });
                return false;
            });
        })
    };
    
    /**
     * Handle the clickable, Mojo-esque editing regions
     */
    function handle_mojo_blog_regions() {
        if (mojoEditor.is_open) {
            jQuery(".mojo_blog_entry_region").each(function() {
                if (jQuery(this).attr("data-is-editable-region") == "false") {
                    mod_editable_layer = jQuery("<div class='mojo_blog_editable_region'></div>").css({
                        "background": "#FFEB72",
                        "border-radius": "6px",
                        "-moz-border-radius": "6px",
                        "-webkit-border-radius": "6px",
                        "margin": "-3px -6px",
                        "padding": "0px",
                        "position": "absolute",
                        "border": "3px solid green",
                        opacity: 0.4,
                        width: jQuery(this).width(),
                        height: jQuery(this).outerHeight()
                    }).fadeIn('fast');
                    jQuery(this).attr("data-is-editable-region", "true");
                    jQuery(this).prepend(jQuery("<div class='mojo_editable_layer_header'><p>Blog : Entry ID " + jQuery(this).attr('data-post-id') + "</p></div>")).prepend(mod_editable_layer);
                }
            });
        } else {
            jQuery(".mojo_blog_entry_region").each(function() {
                jQuery(this).attr("data-is-editable-region", "false");
                
                jQuery(".mojo_editable_layer_header, .mojo_blog_editable_region").fadeOut('fast',
                function() {
                    jQuery(this).remove();
                });
            });
        };
        jQuery(".mojo_blog_entry_region").live("click",
        function() {
            if (jQuery(this).attr("data-active") !== "true") {
                if (mojoEditor.is_open && mojoEditor.is_active === false) {
                    handle_mojo_blog_edit(this);
                }
            }
        });
    }
    
    /**
     * When the user clicks cancel on a MojoBlog editor,
     * restore the original post
     */
    CKEDITOR.plugins.registered.cancel = {
        init: function(editor) {
            var command = editor.addCommand("cancel", {
                modes: {
                    wysiwyg: 1,
                    source: 1
                },
                exec: function(editor) {
                    var par = jQuery("#cke_" + editor.name).parent();
                    var html = unescape(jQuery(par).parent().find(".mojo_blog_orig_html").val());
                    if (jQuery(par).attr("id") == "mojo_region_update_form") {
                        editor.setData(mojoEditor.original_contents,
                        function() {
                            mojoEditor.remove_editor(editor);
                        });
                        handle_mojo_blog_regions();
                    } else {
                        editor.destroy();
                        jQuery(par).parent().attr("data-active", "false");
                        jQuery(par).parent().attr("data-is-editable-region", "false");
                        jQuery(par).parent().addClass("mojo_blog_entry_region");
                        jQuery(par).parent().html(html);
                        jQuery(".mojo_blog_entry_region").live("click",
                        function() {
                            if (jQuery(this).attr("data-active") !== "true") {
                                if (mojoEditor.is_open && mojoEditor.is_active === false) {
                                    handle_mojo_blog_edit(this);
                                }
                            }
                        });
                    }
                }
            });
            editor.ui.addButton("Cancel", {
                label: "Cancel",
                command: "cancel",
                icon: CKEDITOR.plugins.registered.cancel.path + "images/cancel.png"
            });
        }
    }
    
    /**
     * Display MojoBlog Export when user is on the Utilities
     * page of MojoBar. We have to hijack mojoEditor.subpage_reinit
     * for this to happen. I feel bad, but it's all for the greater good.
     */
     var oldSubpageReinit = mojoEditor.subpage_reinit;
     
     mojoEditor.subpage_reinit = function(){
         if (jQuery("#mojo_admin_utilities").hasClass('mojo_admin_utilities_active')) {
             var blogExportContent = "<h3>Export MojoBlog data to ExpressionEngine</h3>";
             blogExportContent += "<p class='shun'>MojoBlog allows you to export your blog data into ExpressionEngine 2, just like MojoMotor itself. The resulting file can be imported through the MojoBlog ExpressionEngine Importer, which can be found in your download. Remember to read the <a href='http://getsparkplugs.com/mojoblog/docs/export'>Export documentation</a>.</p>";
             blogExportContent += "<p class='mojo_shift_right'><a href='"+Mojo.URL.site_path+"/addons/blog/export'><button class='button'>Export MojoBlog to ExpressionEngine 2</button></a></p>";
             
             var blogUninstalContent = "<h3>Uninstall MojoBlog</h3>";
             blogUninstalContent += "<p class='shun'>If you wish to uninstall MojoBlog, you can do so with this utility. <strong>BEWARE, YOU WILL LOSE ALL YOUR DATA. REMEMBER TO BACK UP.</strong></p>";
             blogUninstalContent += "<p class='mojo_shift_right'><a href='"+Mojo.URL.site_path+"/addons/blog/uninstall' onclick='if (!confirm(\"Are you sure you want to do this? You will lose all your data!\")) { return false; }'><button class='button'>Uninstall MojoBlog.</button></a></p>";
             
             jQuery("#mojo_reveal_page_content").append("<div id='mojo_blog_export_utility_container'>"+blogExportContent+"</div>");
             jQuery("#mojo_reveal_page_content").append("<div id='mojo_blog_uninstall_utility_container'>"+blogUninstalContent+"</div>");
         } else {
             jQuery("#mojo_blog_export_utility_container").remove();
              jQuery("#mojo_blog_uninstall_utility_container").remove();
         }
         
         oldSubpageReinit();
     };
    
    /**
     * Save the post when the user clicks the submit button
     */
    jQuery("input.mojo_blog_submit").click(function() {
        var par = jQuery(this).parent().parent();
        jQuery.ajax({
            type: "POST",
            url: Mojo.URL.site_path + "/addons/blog/entry_submit",
            data: {
                mojo_blog_title: jQuery(par).find(".mojo_blog_title").val(),
                mojo_blog_content: jQuery(par).find(".mojo_blog_content").val(),
                mojo_blog_blog: jQuery(par).find(".mojo_blog_blog").val(),
                ci_csrf_token: Mojo.Vars.csrf
            },
            complete: function() {
                window.location.reload()
            }
        });
    });
    
    /**
     * Delete the post when the user clicks the delete link
     */
    jQuery(".mojo_blog_delete").live("click", function() {
        var par = jQuery(this).parent().parent().parent();
        jQuery.ajax({
            type: "POST",
            url: Mojo.URL.site_path + "/addons/blog/entry_delete",
            data: {
                entry_id: jQuery(par).find(".mojo_blog_id").val(),
                ci_csrf_token: Mojo.Vars.csrf
            },
            complete: function() {
                window.location.reload()
            }
        });
        return false;
    });
    
    /**
     * When the user opens or closes the MojoBar, hide or show
     * the entry form(s) and green editing highlights
     */
    jQuery("#mojo_bar_view_mode, #collapse_tab").click(function() {
        if (mojoEditor.is_open) {
            jQuery(".mojo_blog_entry_form").slideDown();
        } else {
            jQuery(".mojo_blog_entry_form").slideUp();
        };
       
        handle_mojo_blog_regions();
        return false;
    });
    
    /**
     * Magic title autofiller
     */
    jQuery(".mojo_blog_title").focus(function() {
        if (jQuery(this).val() == "Title") {
            jQuery(this).val("");
        }
    });
    jQuery(".mojo_blog_title").blur(function() {
        if (jQuery(this).val() == "") {
            jQuery(this).val("Title");
        }
    });
    
    // Hide the entry form if it's open
    if (!mojoEditor.is_open) {
        jQuery(".mojo_blog_entry_form").hide();
    }
    
    // Go go go!
    ckeditorise();
    handle_mojo_blog_regions();
};

/**
 * Mr. Allard gave me a good solution to my previous
 * script loading problem! Mr. Allard for president!
 * Canadians FTW!
 */
 
window.onload = function(){
    if (mojoEditor.mojo_editor_ref != "") {
        MojoBlog();
    } else {
        jQuery.getScript(Mojo.URL.site_path + '/javascript/load_ckeditor', function(){
            MojoBlog();
        });
    }
}