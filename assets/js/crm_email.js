jQuery(document).ready(function () {
    filter_block_tab_load();
});

function filter_block_tab_load()
{
    jQuery("#email_block_remove").select2({
        width: '100%',
        allowClear: true,
        placeholder: "Select EMail",
        formatNoMatches: function () {
            return js_obj.No_Address_Found;
        },
        language: {
            noResults: function (params) {
                return js_obj.No_Address_Found;
            }
        }
    });
}
jQuery(function () {

    //Change Breadcrump Text while switching tab
    jQuery(".nav-pills").on("click", "a", function (e) {
        e.preventDefault();
        switch (jQuery(this).prop("class"))
        {
            case 'oauth_setup':
                jQuery('#breadcrump_section').html(js_obj.Google_OAuth_Setup);
                break;
            case 'imap_setup':
                jQuery('#breadcrump_section').html(js_obj.IMAP_EMail_Setup);
                break;
            case 'email_support':
                jQuery('#breadcrump_section').html(js_obj.Support_Email);
                break;
            case 'filter_block':
                jQuery('#breadcrump_section').html(js_obj.EMail_Filter_Block);
                break;
        }
    });
    jQuery("#oauth_setup_tab").on("click", "#activate_oauth", function (e) {
        e.preventDefault();
        var client_id = jQuery("#oauth_client_id").val();
        var client_secret = jQuery("#oauth_client_secret").val();
        if(client_id != "" && client_secret != "")
        {
            var btn = jQuery(this);
            btn.prop("disabled","disabled");
            jQuery("#oauth_client_id").css("border", "1px solid #ddd");
            jQuery("#oauth_client_secret").css("border", "1px solid #ddd;");
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'eh_crm_activate_oauth',
                    client_id: client_id,
                    client_secret : client_secret
                },
                success: function (data) {
                    window.location.href = data;
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }
        else
        {
            if(client_id == "")
            {
                jQuery("#oauth_client_id").css("border", "1px solid red");
            }
            if(client_secret == "")
            {
                jQuery("#oauth_client_secret").css("border", "1px solid red");
            }
        }
    });
    
    jQuery("#imap_setup_tab").on("click", "#activate_imap", function (e) {
        e.preventDefault();
        var server_url = jQuery("#server_url").val();
        var server_port = jQuery("#server_port").val();
        var email = jQuery("#server_email").val();
        var email_pwd = jQuery("#server_email_pwd").val();
        if(server_url != "" && server_port != "" && email != "" && email_pwd != "")
        {
            var btn = jQuery(this);
            btn.attr("disabled","disabled");
            jQuery("#server_url").css("border", "1px solid #ddd");
            jQuery("#server_port").css("border", "1px solid #ddd;");
            jQuery("#server_email").css("border", "1px solid #ddd");
            jQuery("#server_email_pwd").css("border", "1px solid #ddd;");
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'eh_crm_activate_email_protocol',
                    server_url: server_url,
                    server_port : server_port,
                    email : email,
                    email_pwd : email_pwd
                },
                success: function (data) {
                    btn.removeAttr("disabled",false);
                    var parse = JSON.parse(data);
                    if(parse.status == 'success')
                    {
                        jQuery(".alert-success").css("display", "block");
                        jQuery(".alert-success").css("opacity", "1");
                        jQuery("#success_alert_text").html("<strong>IMAP EMail Setup</strong><br>"+parse.message+"!");
                        window.setTimeout(function () {
                            jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                                jQuery(this).css("display", "none");
                            });
                        }, 4000);
                        jQuery("#imap_setup_tab").html(parse.content);
                    }
                    else
                    {
                        jQuery(".alert-danger").css("display", "block");
                        jQuery(".alert-danger").css("opacity", "1");
                        jQuery("#danger_alert_text").html("<strong>IMAP EMail Setup</strong><br>"+parse.message+"!");
                        window.setTimeout(function () {
                            jQuery(".alert-danger").fadeTo(500, 0).slideUp(500, function () {
                                jQuery(this).css("display", "none");
                            });
                        }, 4000);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }
        else
        {
            if(server_url == "")
            {
                jQuery("#server_url").css("border", "1px solid red");
            }
            if(server_port == "")
            {
                jQuery("#server_port").css("border", "1px solid red");
            }
            if(email == "")
            {
                jQuery("#server_email").css("border", "1px solid red");
            }
            if(email_pwd == "")
            {
                jQuery("#server_email_pwd").css("border", "1px solid red");
            }
        }
    });
    jQuery("#oauth_setup_tab").on("click", "#deactivate_oauth", function (e) {
        e.preventDefault();
        var btn = jQuery(this);
        btn.prop("disabled","disabled");
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_deactivate_oauth'
            },
            success: function (data) {
                btn.removeProp("disabled");
                jQuery(".alert-success").css("display", "block");
                jQuery(".alert-success").css("opacity", "1");
                jQuery("#success_alert_text").html("<strong>Google OAuth Setup</strong><br>Google OAuth Revoked!");
                window.setTimeout(function () {
                    jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                        jQuery(this).css("display", "none");
                    });
                }, 4000);
                jQuery("#oauth_setup_tab").html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    jQuery("#imap_setup_tab").on("click", "#deactivate_imap", function (e) {
        e.preventDefault();
        var btn = jQuery(this);
        btn.prop("disabled","disabled");
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_deactivate_email_protocol'
            },
            success: function (data) {
                btn.removeProp("disabled");
                jQuery(".alert-success").css("display", "block");
                jQuery(".alert-success").css("opacity", "1");
                jQuery("#success_alert_text").html("<strong>IMAP EMail Setup</strong><br>IMAP EMail Deactivated!");
                window.setTimeout(function () {
                    jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                        jQuery(this).css("display", "none");
                    });
                }, 4000);
                jQuery("#imap_setup_tab").html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    
    jQuery("#email_support_tab").on("click", "#save_email_support", function (e) {
        e.preventDefault();
        jQuery(".loader").css("display", "block");
        var debug_status = '';
        if(jQuery("input[name='wsdesk_debug_email']:checked").val() !== undefined)
        {
            debug_status  = jQuery("input[name='wsdesk_debug_email']:checked").val();
        }
        var support_email_name  = jQuery("#support_reply_email_name").val();
        var support_email       = jQuery("#support_reply_email").val();
        var reply_ticket        = jQuery("#support_email_reply_text").val();
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_email_support_save',
                debug_status:debug_status,
                support_email_name : support_email_name,
                support_email : support_email,
                reply_ticket_text:reply_ticket
            },
            success: function (data) {
                jQuery(".loader").css("display", "none");
                jQuery(".alert-success").css("display", "block");
                jQuery(".alert-success").css("opacity", "1");
                jQuery("#success_alert_text").html("<strong>Support EMail</strong><br>Updated and Saved Successfully!");
                window.setTimeout(function () {
                    jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                        jQuery(this).css("display", "none");
                    });
                }, 4000);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    jQuery("#filter_block_tab").on("click", "#save_email_filter_block", function (e) {
        e.preventDefault();
        var new_block = {};
        if (jQuery("#add_block_address_yes").val() === "yes")
        {
            if(jQuery("#block_address_add_email").val() === "")
            {
                jQuery(".loader").css("display", "none");
                if(jQuery("#block_address_add_email").val() === "")
                {
                    jQuery("#block_address_add_email").css("border","1px solid red");
                }
                jQuery(".alert-danger").css("display", "block");
                jQuery(".alert-danger").css("opacity", "1");
                jQuery("#danger_alert_text").html("<strong>Email Block & Filters</strong><br>Updated and Saved Successfully!");
                window.setTimeout(function () {
                    jQuery(".alert-danger").fadeTo(500, 0).slideUp(500, function () {
                        jQuery(this).css("display", "none");
                    });
                }, 4000);
                jQuery('html, body').animate({
                    scrollTop: jQuery("#filter_block_tab").offset().top
                }, 1000);
                return false;
            }
            new_block['email'] = jQuery("#block_address_add_email").val();
            new_block['type'] = getValue_checkbox_values('add_block_rights');
        }
        jQuery(".loader").css("display", "block");
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_email_block_filter',
                new_block: JSON.stringify(new_block)
            },
            success: function (data) {
                jQuery(".loader").css("display", "none");
                jQuery(".alert-success").css("display", "block");
                jQuery(".alert-success").css("opacity", "1");
                jQuery("#success_alert_text").html("<strong>Email Block & Filters</strong><br>Updated and Saved Successfully!")
                window.setTimeout(function () {
                    jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                        jQuery(this).css("display", "none");
                    });
                }, 4000);
                jQuery("#filter_block_tab").html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    jQuery("#filter_block_tab").on("click", ".block_email_delete_type", function (e) {
        var filter_id = jQuery(this).prop("id");
        BootstrapDialog.show({
            title: "WSDesk Alert",
            message: 'Do you want to delete the filter?',
            cssClass: 'wsdesk_wrapper',
            buttons: [{
                    label: 'Yes! Delete',
                    // no title as it is optional
                    cssClass: 'btn-primary',
                    action: function (dialogItself) {
                        jQuery(".loader").css("display", "block");
                        jQuery.ajax({
                            type: 'post',
                            url: ajaxurl,
                            data: {
                                action: 'eh_crm_email_block_delete',
                                block_remove: filter_id
                            },
                            success: function (data) {
                                jQuery(".loader").css("display", "none");
                                jQuery(".alert-success").css("display", "block");
                                jQuery(".alert-success").css("opacity", "1");
                                jQuery("#success_alert_text").html("<strong>Email Block & Filters</strong><br>Updated and Saved Successfully!");
                                window.setTimeout(function () {
                                    jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                                        jQuery(this).css("display", "none");
                                    });
                                }, 4000);
                                jQuery("#filter_block_tab").html(data);
                            },
                            error: function (jqXHR, textStatus, errorThrown) {
                                console.log(textStatus, errorThrown);
                            }
                        });
                        dialogItself.close();
                    }
                }, {
                    label: 'Close',
                    action: function (dialogItself) {
                        dialogItself.close();
                    }
                }]
        });
    });
    jQuery("#filter_block_tab").on("click", "#block_email_add_button", function (e) {
        e.preventDefault();
        jQuery("#block_email_add_display").slideDown(10).show();
        jQuery("#add_block_address_yes").val("yes");
    });
    jQuery("#filter_block_tab").on("click", "#block_email_cancel_add_button", function (e) {
        e.preventDefault();
        jQuery("#block_email_add_display").slideUp(10).hide();
        jQuery("#add_block_address_yes").val("no");
    });
    function getValue_checkbox_values(name) {
        var chkArray = [];
        jQuery("input[name='" + name + "']:checked").each(function () {
            chkArray.push(jQuery(this).val());
        });
        var selected;
        selected = chkArray.join(',') + ",";
        if (selected.length > 1) {
            return (selected.slice(0, -1));
        } else {
            return ("");
        }
    }
});