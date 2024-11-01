jQuery(function () {
    // attach table filter plugin to inputs
    jQuery('[data-action="filter"]').filterTable();
    jQuery('#tickets_page_view').on('click', '.tickets_panel span.filter', function (e) {
        var $this = jQuery(this),
        $panel = $this.parents('.panel');
        $panel.find('.tickets_panel > .panel-body').slideToggle();
        if ($this.css('display') != 'none') {
            $panel.find('.tickets_panel > .panel-body input').focus();
        }
    });
    jQuery('[data-toggle="wsdesk_tooltip"]').wstooltip({trigger : 'hover'});
    collapse_tab();
    jQuery("#default_assignee_ticket").select2({
        width: '100%',
        minimumResultsForSearch: -1
    });
});
function height_adjust(e) {
    jQuery(e).css({'height': 'auto', 'overflow-y': 'hidden'}).height(e.scrollHeight);
}
function assignee_select2_init(id)
{
    jQuery(id).select2({
        width: '100%',
        allowClear: true,
        placeholder: "Select Assignee",
        formatNoMatches: function () {
            return "No Assignee";
        },
        language: {
            noResults: function (params) {
                return "No Assignee";
            }
        }
    });
}
jQuery(function () {
    jQuery('#search_ticket_input').keypress(function(e){
        if(e.which == 13){
            var search = jQuery('#search_ticket_input').val();
            jQuery("#search_ticket_input").blur(); 
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'eh_crm_ticket_search',
                    search: search
                },
                success: function (data) {
                    var parse = jQuery.parseJSON(data);
                    if(parse.data === "ticket")
                    {
                        var ticket_id = search;
                        if (jQuery(".elaborate > li#tab_" + ticket_id).length == 0)
                        {
                            var tab_head = '<li role="presentation" class="visible_tab" id="tab_' + ticket_id + '" style="min-width:200px;">' + parse.tab_head + '</li>';
                            var tab_content = '<div class="tab-pane" id="tab_content_' + ticket_id + '">' + parse.tab_content + '</div>';
                            jQuery('.elaborate > li').last().before(tab_head);
                            jQuery('.tab-content').append(tab_content);
                            trigger_load_single_ticket(ticket_id);
                            jQuery("#reply_textarea_" + ticket_id).val(jQuery('#direct_reply_textarea_' + ticket_id).val());
                            jQuery('.visible_tab a#tab_content_a_' + ticket_id).click();
                            collapse_tab();
                        }
                        else
                        {
                            jQuery(".elaborate > li#tab_" + ticket_id).children('a').click();
                        }
                    }
                    else
                    {
                        
                        var search_key = search.replace(' ', '_');
                        while(search_key.indexOf(" ")!=-1)
                        {
                            search_key = search_key.replace(' ', '_');
                        }
                        while(search_key.indexOf("@")!=-1)
                        {
                            search_key = search_key.replace('@', '_1attherate1_');
                        }
                        while(search_key.indexOf(".")!=-1)
                        {
                            search_key = search_key.replace('.', '_1dot1_');
                        }
                        while(search_key.indexOf(";")!=-1)
                        {
                            search_key = search_key.replace(';', '_1semicolon1_');
                        }
                        while(search_key.indexOf("?")!=-1)
                        {
                            search_key = search_key.replace('?', '_1questionmark1_');
                        }
                        if (jQuery(".elaborate > li#tab_" + search_key).length == 0)
                        {
                            var tab_head = '<li role="presentation" class="visible_tab" id="tab_' + search_key + '" style="min-width:200px;">' + parse.tab_head + '</li>';
                            var tab_content = '<div class="tab-pane" id="tab_content_' + search_key + '">' + parse.tab_content + '</div>';
                            jQuery('.elaborate > li').last().before(tab_head);
                            jQuery('.tab-content').append(tab_content);
                            jQuery('.visible_tab a#tab_content_a_'+search_key).click();
                            collapse_tab();
                        }
                        else
                        {
                            jQuery(".elaborate > li#tab_" + search_key).children('a').click();
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
            jQuery("#search_ticket_input").val('');
        }
    });
    jQuery(".nav-tabs").on("click", ".close_tab", function () {
        var anchor = jQuery(this).parent();
        var li = anchor.parent();
        var count = jQuery(".elaborate li").length;
        if(count <= 3)
        {
            jQuery(".elaborate > li").first().children('a').click();
        }
        else
        {
            if(li.hasClass('active'))
            {
                if(li.next().hasClass('visible_tab'))
                {
                    li.next().children('a').click();
                }
                else
                {
                    li.prev().children('a').click();
                }
            }
        }
        refresh_left_bar();
        refresh_right_bar();
        jQuery(anchor.attr('href')).remove();
        jQuery(anchor).parent().remove();
        collapse_tab();
    });
    
    function refresh_left_bar()
    {
        jQuery(".ticket-delete-btn").hide();
        jQuery(".ticket_select_all").removeProp("checked");
        jQuery(".labels_loader").css("display", "inline");
        jQuery(".agents_loader").css("display", "inline");
        jQuery(".tags_loader").css("display", "inline");
        jQuery(".users_loader").css("display", "inline");
        var active = jQuery("#left_bar_all_tickets").find(".active").children('a').attr('id');
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_ticket_refresh_left_bar',
                active: active
            },
            success: function (data) {
                jQuery(".labels_loader").css("display", "none");
                jQuery(".agents_loader").css("display", "none");
                jQuery(".tags_loader").css("display", "none");
                jQuery(".users_loader").css("display", "none");
                jQuery("#left_bar_all_tickets").html(data);
                window.history.pushState('Tickets', 'Title', js_obj.ticket_admin_url+'&view='+active);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
    function refresh_right_bar(ul, pagination, current)
    {
        ul = ul||'';
        pagination = pagination||'';
        current = current||0;
        jQuery('[data-toggle="wsdesk_tooltip"]').wstooltip('destroy');
        jQuery(".table_loader").css("display", "inline");
        var active = jQuery("#left_bar_all_tickets").find(".active").children('a').attr('id');
        jQuery("." + ul + "_loader").css("display", "inline");
        var current_page = (current==0)?jQuery("#current_page_no").val():0;
        if($_GET('order'))
        {
            var order = $_GET('order');
        }
        else
        {
            var order = 'DESC';
        }
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_ticket_refresh_right_bar',
                active: active,
                current_page:current_page,
                pagination_type:pagination,
                order: order
            },
            success: function (data) {
                jQuery(".table_loader").css("display", "none");
                jQuery("." + ul + "_loader").css("display", "none");
                jQuery("#right_bar_all_tickets").html(data);
                jQuery('[data-action="filter"]').filterTable();
                jQuery('[data-toggle="wsdesk_tooltip"]').wstooltip({trigger : 'hover'});
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
    jQuery(".tab-content").on("click", "#left_bar_all_tickets a", function (e)
    {
        e.preventDefault();
        var li = jQuery(this).parent();
        var ul = jQuery(li).parent().prop("id");
        jQuery("#left_bar_all_tickets").find(".active").removeClass("active");
        jQuery(li).addClass("active");
        refresh_left_bar();
        refresh_right_bar(ul,"",1);
    });
    
    
    jQuery("#refresh_tickets").on("click", function (e)
    {
        e.preventDefault();
        refresh_right_bar();
        jQuery(".ticket_select_all").removeProp("checked");
        jQuery(".ticket-delete-btn").hide();
    });
    
    jQuery(".tab-content").on("click", ".pagination_tickets", function (e)
    {
        jQuery(".ticket-delete-btn").hide();
        jQuery(".ticket_select_all").removeProp("checked");
        e.preventDefault();
        var id = jQuery(this).prop("id");
        refresh_right_bar("",id);
    });
    var stop = false;
    jQuery('.tab-content').on('click', '#dev-table tr', function (e) {
        if (!jQuery(e.target).closest('.except_view').length) {
            var ticket_id = jQuery(this).prop("id");
            if (jQuery("ul > li#tab_" + ticket_id).length == 0)
            {
                jQuery(".table_loader").css("display", "inline");
                if (stop)
                    return false;
                stop = true;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: {
                        action: 'eh_crm_ticket_single_view',
                        ticket_id: ticket_id
                    },
                    success: function (data) {
                        jQuery(".table_loader").css("display", "none");
                        var parse = jQuery.parseJSON(data);
                        var tab_head = '<li role="presentation" class="visible_tab" id="tab_' + ticket_id + '" style="min-width:200px;">' + parse.tab_head + '</li>';
                        var tab_content = '<div class="tab-pane" id="tab_content_' + ticket_id + '">' + parse.tab_content + '</div>';
                        jQuery('.elaborate > li').last().before(tab_head);
                        jQuery('.tab-content').append(tab_content);
                        trigger_load_single_ticket(ticket_id);
                        jQuery("#reply_textarea_" + ticket_id).val(jQuery('#direct_reply_textarea_' + ticket_id).val());
                        jQuery('.visible_tab a#tab_content_a_'+ticket_id).click();
                        collapse_tab();
                        stop = false;
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            } else
            {
                if(jQuery("ul.collapse_ul > li#tab_" + ticket_id).length!=0)
                {
                    jQuery('.elaborate > li').last().before(jQuery("ul.collapse_ul > li#tab_" + ticket_id));
                    collapse_tab();
                }
                jQuery(".elaborate > li#tab_" + ticket_id).children('a').click();
            }
        }

    });
    jQuery('.tab-content').on('click', '#search-table tr', function (e) {
        if (!jQuery(e.target).closest('.except_view').length) {
            var ticket_id = jQuery(this).prop("id");
            if (jQuery("ul > li#tab_" + ticket_id).length == 0)
            {
                jQuery(".search_table_loader").css("display", "inline");
                if (stop)
                    return false;
                stop = true;
                jQuery.ajax({
                    type: 'post',
                    url: ajaxurl,
                    data: {
                        action: 'eh_crm_ticket_single_view',
                        ticket_id: ticket_id
                    },
                    success: function (data) {
                        jQuery(".search_table_loader").css("display", "none");
                        var parse = jQuery.parseJSON(data);
                        var tab_head = '<li role="presentation" class="visible_tab" id="tab_' + ticket_id + '" style="min-width:200px;">' + parse.tab_head + '</li>';
                        var tab_content = '<div class="tab-pane" id="tab_content_' + ticket_id + '">' + parse.tab_content + '</div>';
                        jQuery('.elaborate > li').last().before(tab_head);
                        jQuery('.tab-content').append(tab_content);
                        trigger_load_single_ticket(ticket_id);
                        jQuery("#reply_textarea_" + ticket_id).val(jQuery('#direct_reply_textarea_' + ticket_id).val());
                        jQuery('.visible_tab a#tab_content_a_'+ticket_id).click();
                        collapse_tab();
                        stop = false;
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    }
                });
            } else
            {
                if(jQuery("ul.collapse_ul > li#tab_" + ticket_id).length!=0)
                {
                    jQuery('.elaborate > li').last().before(jQuery("ul.collapse_ul > li#tab_" + ticket_id));
                    collapse_tab();
                }
                jQuery(".elaborate > li#tab_" + ticket_id).children('a').click();
            }
        }

    });
    function trigger_load_single_ticket(ticket_id)
    {
        assignee_select2_init("#assignee_ticket_" + ticket_id);
        tag_select2("#tags_ticket_" + ticket_id);
        jQuery('.reply_textarea').each(function () {
            this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
        }).on('input', function () {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        if(jQuery(".cc_select_" + ticket_id).length != 0)
        {
            cc_select2("#cc_ticket_" + ticket_id);
        }
        if(jQuery(".bcc_select_" + ticket_id).length != 0)
        {
            cc_select2("#bcc_ticket_" + ticket_id);
        }

    }
    jQuery(".tab-content").on("change", "#dev-table-action-bar .ticket_select_all", function (e)
    {
        if (this.checked)
        {
            jQuery(".ticket-delete-btn").show();
            jQuery('.ticket_select').each(function () {
                this.checked = true;
            });
        } else
        {
            jQuery(".ticket-delete-btn").hide();
            jQuery('.ticket_select').each(function () {
                this.checked = false;
            });
        }
        e.preventDefault();

    });
    jQuery(".tab-content").on("change", "#dev-table .ticket_select", function (e)
    {
        
        jQuery(".ticket_select_all").removeProp("checked");
        var all_uncheked = true;
        jQuery('.ticket_select').each(function () {
                if(this.checked){
                    all_uncheked = false;
                }
            });
            
        if (!all_uncheked)
        {
            jQuery(".ticket-delete-btn").show();
        } else
        {
            jQuery(".ticket-delete-btn").hide();
        }
        
        e.preventDefault();

    });
    jQuery('.add-ticket').click(function (e) {
        e.preventDefault();
        if (jQuery("ul > li#tab_new").length == 0)
        {
            jQuery(".table_loader").css("display", "inline");
            jQuery.ajax({
                type: 'post',
                url: ajaxurl,
                data: {
                    action: 'eh_crm_ticket_add_new'
                },
                success: function (data) {
                    jQuery(".table_loader").css("display", "none");
                    var parse = jQuery.parseJSON(data);
                    var tab_head = '<li role="presentation" class="visible_tab" id="tab_new" style="min-width:200px;">' + parse.tab_head + '</li>';
                    var tab_content = '<div class="tab-pane" id="tab_content_new">' + parse.tab_content + '</div>';
                    jQuery('.elaborate > li').last().before(tab_head);
                    jQuery('.tab-content').append(tab_content);
                    trigger_load_single_ticket('new');
                    jQuery('.visible_tab a#tab_content_a_new').click();
                    collapse_tab();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }
        else
        {
                if(jQuery("ul.collapse_ul > li#tab_new").length!=0)
                {
                    jQuery('.elaborate > li').last().before(jQuery("ul.collapse_ul > li#tab_new"));
                    collapse_tab();
                }
                jQuery(".elaborate > li#tab_new").children('a').click();
        }
    });
    jQuery("#tickets_page_view").on("click", ".quick_view_ticket", function (e) {
        e.preventDefault();
        var target = jQuery(this).attr("data-target");
        if (jQuery(target).hasClass("in"))
        {
            jQuery(target).parent().css("display", "none");
            jQuery(this).children('.glyphicon').addClass("glyphicon-eye-open").removeClass("glyphicon-eye-close");
        } else
        {
            jQuery(target).parent().css("display", "table-cell");
            jQuery(this).children('.glyphicon').addClass("glyphicon-eye-close").removeClass("glyphicon-eye-open");
        }
    });
    jQuery(".tab-content").on("click", ".single_ticket_action", function (e) {
        e.preventDefault();
        var label = jQuery(this).prop("id");
        var ticket_id = jQuery(this).parent().prop("id");
        jQuery(".table_loader").css("display", "inline");
        jQuery(".single_ticket_action_button_" + ticket_id).prop("disabled", true);
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_ticket_single_ticket_action',
                ticket_id: ticket_id,
                label: label
            },
            success: function (data) {
                jQuery(".table_loader").css("display", "none");
                if (jQuery("#tab_" + ticket_id).length != 0 && jQuery("#tab_content_" + ticket_id).length != 0)
                {
                    jQuery("#tab_content_" + ticket_id).html(data);
                    trigger_load_single_ticket(ticket_id);
                }
                refresh_left_bar();
                refresh_right_bar();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });

    });
    jQuery(".tab-content").on("click", ".ticket_action_delete", function (e) {
        e.preventDefault();
        var ticket_id = jQuery(this).prop("id");
        BootstrapDialog.show({
            title: "WSDesk Alert",
            message: 'Do You want to Delete Ticket?',
            cssClass: 'wsdesk_wrapper',
            buttons: [{
                    label: 'Yes! Delete',
                    // no title as it is optional
                    cssClass: 'btn-primary',
                    action: function (dialogItself) {
                        jQuery(".ticket_loader_" + ticket_id).css("display", "inline");
                        jQuery.ajax({
                            type: 'post',
                            url: ajaxurl,
                            data: {
                                action: 'eh_crm_ticket_single_delete',
                                ticket_id: ticket_id
                            },
                            success: function (data) {
                                jQuery("#tab_" + ticket_id + ">a>.close_tab").trigger("click");
                                jQuery(".ticket_loader_" + ticket_id).css("display", "none");
                                jQuery(".alert-success").css("display", "block");
                                jQuery(".alert-success").css("opacity", "1");
                                jQuery("#success_alert_text").html("<strong>WSDesk Tickets Notification</strong><br>Ticket #" + ticket_id + " Deleted Successfully!");
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
    jQuery(".tab-content").on("click", ".multiple_ticket_action", function (e) {
        e.preventDefault();
        var label = jQuery(this).prop("id");
        var ticket = getValue_checkbox("ticket_select");
        if(ticket != '')
        {
            if(label === "delete_tickets")
            {
                BootstrapDialog.show({
                    title: "WSDesk Alert",
                    message: 'Do You want to Delete Tickets?',
                    cssClass: 'wsdesk_wrapper',
                    buttons: [{
                            label: 'Yes! Delete',
                            // no title as it is optional
                            cssClass: 'btn-primary',
                            action: function (dialogItself) {
                                
                                jQuery(".table_loader").css("display", "inline");
                                jQuery.ajax({
                                    type: 'post',
                                    url: ajaxurl,
                                    data: {
                                        action: 'eh_crm_ticket_multiple_delete',
                                        tickets_id: JSON.stringify(ticket)
                                    },
                                    success: function (data) {
                                        jQuery(".ticket-delete-btn").hide();
                                        for(i=0;i<ticket.length;i++)
                                        {
                                            if (jQuery("#tab_" + ticket[i]).length != 0 && jQuery("#tab_content_" + ticket[i]).length != 0)
                                            {
                                                jQuery("#tab_" + ticket[i] + ">a>.close_tab").trigger("click");
                                            }                                            
                                        }
                                        jQuery(".table_loader").css("display", "none");
                                        jQuery(".alert-success").css("display", "block");
                                        jQuery(".alert-success").css("opacity", "1");
                                        jQuery("#success_alert_text").html("<strong>WSDesk Tickets Notification</strong><br>Tickets Deleted Successfully!");
                                        window.setTimeout(function () {
                                            jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                                                jQuery(this).css("display", "none");
                                            });
                                        }, 4000);
                                        refresh_left_bar();
                                        refresh_right_bar();
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
            }
            else
            {
                BootstrapDialog.show({
                    title: "WSDesk Alert",
                    message: 'Do You want to Update Tickets Label?',
                    cssClass: 'wsdesk_wrapper',
                    buttons: [{
                            label: 'Yes! Update',
                            // no title as it is optional
                            cssClass: 'btn-primary',
                            action: function (dialogItself) {
                                jQuery(".table_loader").css("display", "inline");
                                jQuery.ajax({
                                    type: 'post',
                                    url: ajaxurl,
                                    data: {
                                        action: 'eh_crm_ticket_multiple_ticket_action',
                                        tickets_id: JSON.stringify(ticket),
                                        label:label
                                    },
                                    success: function (data) {
                                        jQuery(".table_loader").css("display", "none");
                                        jQuery(".alert-success").css("display", "block");
                                        jQuery(".alert-success").css("opacity", "1");
                                        jQuery("#success_alert_text").html("<strong>WSDesk Tickets Notification</strong><br>Tickets Updated Successfully!");
                                        window.setTimeout(function () {
                                            jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                                                jQuery(this).css("display", "none");
                                            });
                                        }, 4000);
                                        refresh_left_bar();
                                        refresh_right_bar();
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
            }
        }
        else
        {
            BootstrapDialog.alert('Select tickets to make actions!');
        }
    });
    jQuery(".tab-content").on("click", ".ticket_submit_new", function (e) {
        e.preventDefault();
        var ticket_id = jQuery(this).parent('li').prop("id");
        var text = jQuery("#reply_textarea_" + ticket_id).val();
        var email = jQuery('#ticket_email_'+ticket_id).val();
        var title = jQuery('#ticket_title_'+ticket_id).val();
        if (text != "" && email != "" && title != "")
        {
            var assignee = jQuery("#assignee_ticket_" + ticket_id).val();
            var tags = jQuery("#tags_ticket_" + ticket_id).val();
            var input_field = {};
            if (jQuery(".ticket_input_text_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_text_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = jQuery(this).val();
                    input_field[key] = value;
                });
            }
            if (jQuery(".ticket_input_email_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_email_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = jQuery(this).val();
                    input_field[key] = value;
                });
            }
            if (jQuery(".ticket_input_number_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_number_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = jQuery(this).val();
                    input_field[key] = value;
                });
            }
            if (jQuery(".ticket_input_pwd_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_pwd_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = jQuery(this).val();
                    input_field[key] = value;
                });
            }
            if (jQuery(".ticket_input_select_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_select_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = jQuery(this).val();
                    input_field[key] = value;
                });
            }
            if (jQuery(".ticket_input_radio_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_radio_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = ((jQuery("input[name='" + key + "']:checked").val() != undefined) ? jQuery("input[name='" + key + "']:checked").val() : "");
                    input_field[key] = value;
                });
            }
            if (jQuery(".ticket_input_checkbox_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_checkbox_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = getValue_checkbox(key);
                    input_field[key] = value;
                });
            }
            if (jQuery(".ticket_input_textarea_" + ticket_id).length != 0)
            {
                jQuery(".ticket_input_textarea_" + ticket_id).each(function () {
                    var key = jQuery(this).prop("id");
                    var value = jQuery(this).val();
                    input_field[key] = value;
                });
            }
            jQuery("#reply_textarea_" + ticket_id).css("border", "1px solid #F2F2F2");
            jQuery("#ticket_email_" + ticket_id).css("border", "1px solid #F2F2F2");
            jQuery("#ticket_title_" + ticket_id).css("border", "1px solid #F2F2F2");
            var submit = jQuery(this).prop("id");
            var security = jQuery("#security" + ticket_id).val();
            var fd = new FormData();
            var file = jQuery("#files_" + ticket_id);
            jQuery.each(jQuery(file), function (i, obj) {
                jQuery.each(obj.files, function (j, file) {
                    fd.append('file[' + j + ']', file);
                });
            });
            fd.append("title", title);
            fd.append("email", email);
            fd.append("desc", text);
            fd.append("security", security);
            fd.append("submit", submit);
            fd.append("assignee",(assignee != null) ? assignee.join(",") : '');
            fd.append("tags",(tags != null) ? tags.join(",") : '');
            fd.append("input",JSON.stringify(input_field));
            fd.append('action', 'eh_crm_ticket_new_submit');
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: fd,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery(".ticket_loader_" + ticket_id).css("display", "none");
                    jQuery("#tab_"+ticket_id+">a>.close_tab").trigger("click");
                    jQuery(".alert-success").css("display", "block");
                    jQuery(".alert-success").css("opacity", "1");
                    var parse = jQuery.parseJSON(data);
                    jQuery("#success_alert_text").html("<strong>"+js_obj.WSDesk_Tickets_Notification+"</strong><br>"+js_obj.New_Ticket_Created_Successfully+"!");
                    window.setTimeout(function () {
                        jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                            jQuery(this).css("display", "none");
                        });
                    }, 4000);
                    ticket_id = parse.id;
                    var tab_head = '<li role="presentation" class="visible_tab" id="tab_' + ticket_id + '" style="min-width:200px;">' + parse.tab_head + '</li>';
                    var tab_content = '<div class="tab-pane" id="tab_content_' + ticket_id + '">' + parse.tab_content + '</div>';
                    jQuery('.elaborate > li').last().before(tab_head);
                    jQuery('.tab-content').append(tab_content);
                    trigger_load_single_ticket(ticket_id);
                    jQuery('.visible_tab a#tab_content_a_' + ticket_id).click();
                    collapse_tab();
                }
            });
        } else
        {
            jQuery("#reply_textarea_" + ticket_id).css("border", "1px solid red");
        }
    });
    jQuery(".tab-content").on("click", ".ticket_reply_action", function (e) {
        e.preventDefault();
        var ticket_id = jQuery(this).parent('li').prop("id");
        var text = jQuery("#reply_textarea_" + ticket_id).val();
        if (text != "")
        {

            jQuery(".ticket_loader_" + ticket_id).css("display", "inline");
            jQuery("#reply_textarea_" + ticket_id).css("border", "1px solid #F2F2F2");
            var submit = jQuery(this).prop("id");
            var security = jQuery("#security" + ticket_id).val();
            var fd = new FormData();
            var file = jQuery("#files_" + ticket_id);
            jQuery.each(jQuery(file), function (i, obj) {
                jQuery.each(obj.files, function (j, file) {
                    fd.append('file[' + j + ']', file);
                });
            });
            if (jQuery("#ticket_title_" + ticket_id).length)
            {
                var title = jQuery("#ticket_title_" + ticket_id).val();
                fd.append("ticket_title", title);
            }
            fd.append("ticket_reply", text);
            fd.append("ticket_id", ticket_id);
            fd.append("security", security);
            fd.append("submit", submit);
            fd.append('action', 'eh_crm_ticket_reply_agent');
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: fd,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    //jQuery("#tab_"+ticket_id+">a>.close_tab").trigger("click");
                    jQuery(".ticket_loader_" + ticket_id).css("display", "none");
                    var parse = jQuery.parseJSON(data);
                    var tab_head = parse.tab_head;
                    var tab_content = parse.tab_content;
                    jQuery("#tab_" + ticket_id).html(tab_head);
                    jQuery("#tab_content_" + ticket_id).html(tab_content);
                    trigger_load_single_ticket(ticket_id);
                    var mail = parse.response;
                    var alert_msg = '';
                    if(typeof(mail.status) !== "undefined" && mail.status !== null) {
                        if(mail.status)
                        {
                            if(typeof(mail.message) !== "undefined" && mail.message !== null) {
                                alert_msg = '<br/>'+mail.message;
                            }
                            else
                            {
                                alert_msg = '<br/>'+"Email sent successfully";
                            }
                        }
                        else
                        {
                            alert_msg = '<br/>'+"Email not sent";
                        }
                    }
                    jQuery(".alert-success").css("display", "block");
                    jQuery(".alert-success").css("opacity", "1");
                    jQuery("#success_alert_text").html("<strong>WSDesk Tickets Notification</strong><br>Ticket #" + ticket_id + " Replied Successfully!"+alert_msg);
                    window.setTimeout(function () {
                        jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                            jQuery(this).css("display", "none");
                        });
                    }, 4000);
                }
            });
        } else
        {
            jQuery("#reply_textarea_" + ticket_id).css("border", "1px solid red");
        }
    });
    jQuery(".tab-content").on("click", ".direct_ticket_reply_action", function (e) {
        e.preventDefault();
        var ticket_id = jQuery(this).parent('li').prop("id");
        var text = jQuery("#direct_reply_textarea_" + ticket_id).val();
        if (text != "")
        {
            jQuery("#direct_reply_textarea_" + ticket_id).css("border", "1px solid #F2F2F2");
            var submit = jQuery(this).prop("id");
            var security = jQuery("#direct_security" + ticket_id).val();
            var fd = new FormData();
            var file = jQuery("#direct_files_" + ticket_id);
            jQuery.each(jQuery(file), function (i, obj) {
                jQuery.each(obj.files, function (j, file) {
                    fd.append('file[' + j + ']', file);
                });
            });
            if (jQuery("#direct_ticket_title_" + ticket_id).length)
            {
                var title = jQuery("#direct_ticket_title_" + ticket_id).val();
                fd.append("ticket_title", title);
            }
            fd.append("ticket_reply", text);
            fd.append("ticket_id", ticket_id);
            fd.append("security", security);
            fd.append("submit", submit);
            fd.append('action', 'eh_crm_ticket_reply_agent');
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: fd,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    if (jQuery("#tab_" + ticket_id).length != 0 && jQuery("#tab_content_" + ticket_id).length != 0)
                    {
                        var parse = jQuery.parseJSON(data);
                        var tab_head = parse.tab_head;
                        var tab_content = parse.tab_content;
                        jQuery("#tab_" + ticket_id).html(tab_head);
                        jQuery("#tab_content_" + ticket_id).html(tab_content);
                        trigger_load_single_ticket(ticket_id);
                    }
                    jQuery('#reply_' + ticket_id).modal('toggle');
                    refresh_left_bar();
                    refresh_right_bar();
                    jQuery(".alert-success").css("display", "block");
                    jQuery(".alert-success").css("opacity", "1");
                    jQuery("#success_alert_text").html("<strong>WSDesk Tickets Notification</strong><br>Ticket #" + ticket_id + " Replied Successfully!");
                    window.setTimeout(function () {
                        jQuery(".alert-success").fadeTo(500, 0).slideUp(500, function () {
                            jQuery(this).css("display", "none");
                        });
                    }, 4000);
                }
            });
        } else
        {
            jQuery("#direct_reply_textarea_" + ticket_id).css("border", "1px solid red");
        }
    });
    jQuery(".tab-content").on("click", ".ticket_action_save_props", function (e) {
        e.preventDefault();
        var ticket_id = jQuery(this).prop("id");
        var button = jQuery(this);
        jQuery(".ticket_loader_" + ticket_id).css("display", "inline");
        jQuery(button).prop("disabled", true);
        var assignee = jQuery("#assignee_ticket_" + ticket_id).val();
        var tags = jQuery("#tags_ticket_" + ticket_id).val();
        var cc = [];
        var bcc = [];
        if(jQuery(".cc_" + ticket_id).length != 0)
        {
            cc = jQuery("#cc_ticket_" + ticket_id).val();
            if(cc === null)
            {
                cc =[];
            }
        }
        if(jQuery(".bcc_select_" + ticket_id).length != 0)
        {
            bcc = jQuery("#bcc_ticket_" + ticket_id).val();
            if(bcc === null)
            {
                bcc =[];
            }
        }

        var input_field = {};
        if (jQuery(".ticket_input_text_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_text_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = jQuery(this).val();
                input_field[key] = value;
            });
        }
        if (jQuery(".ticket_input_email_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_email_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = jQuery(this).val();
                input_field[key] = value;
            });
        }
        if (jQuery(".ticket_input_number_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_number_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = jQuery(this).val();
                input_field[key] = value;
            });
        }
        if (jQuery(".ticket_input_pwd_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_pwd_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = jQuery(this).val();
                input_field[key] = value;
            });
        }
        if (jQuery(".ticket_input_select_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_select_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = jQuery(this).val();
                input_field[key] = value;
            });
        }
        if (jQuery(".ticket_input_radio_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_radio_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = ((jQuery("input[name='" + key + "']:checked").val() != undefined) ? jQuery("input[name='" + key + "']:checked").val() : "");
                input_field[key] = value;
            });
        }
        if (jQuery(".ticket_input_checkbox_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_checkbox_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = getValue_checkbox(key);
                input_field[key] = value;
            });
        }
        if (jQuery(".ticket_input_textarea_" + ticket_id).length != 0)
        {
            jQuery(".ticket_input_textarea_" + ticket_id).each(function () {
                var key = jQuery(this).prop("id");
                var value = jQuery(this).val();
                input_field[key] = value;
            });
        }
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_ticket_single_save_props',
                ticket_id: ticket_id,
                assignee: (assignee != null) ? assignee.join(",") : '',
                tags: (tags != null) ? tags.join(",") : '',
                cc: (cc.length !== 0) ? cc: '',
                bcc: (bcc.length !== 0) ? bcc.join(",") : '',
                input: JSON.stringify(input_field)
            },
            success: function (data) {
                jQuery(".ticket_loader_" + ticket_id).css("display", "none");
                jQuery(button).removeProp("disabled");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    });
    function getValue_checkbox(id) {
        var chkArray = [];
        jQuery("#" + id + ":checked").each(function () {
            chkArray.push(jQuery(this).val());
        });
        if (chkArray.length > 0) {
            return chkArray;
        } else {
            return ('');
        }
    }
    jQuery("#tickets_page_view").on("click", ".collapse_ul > li > a", function (e) {
        e.preventDefault();
        jQuery('.elaborate > li.visible_tab').first().appendTo('ul.collapse_ul');
        jQuery('.elaborate > li').last().before(jQuery(this).parent());
        jQuery(this).click();
    });
    function previewFiles(files, id) {

        function readAndPreview(file) {
            // Make sure `file.name` matches our extensions criteria
            if (/\.(jpe?g|png|gif|mp4|3gp|avi|wmv|mpg|mov|flv)$/i.test(file.name)) {
                var reader = new FileReader();
                reader.addEventListener("load", function () {
                    var img_html = '<a href="' + this.result + '" target="_blank"><img class="img-upload clickable" style="width:150px" title="' + file.name + '" src=' + this.result + '></a>';
                    jQuery(".upload_preview_" + id).append(img_html);
                }, false);

                reader.readAsDataURL(file);
            } else
            {
                if (/\.(doc?x|pdf|xml|csv|xls|xlsx|txt|zip)$/i.test(file.name)) {
                    var ext = (file.name).substr((file.name).lastIndexOf('.') + 1);
                    var reader = new FileReader();

                    reader.addEventListener("load", function () {
                        var img_html = '<a href="' + this.result + '" target="_blank" title="' + file.name + '" class="img-upload"><div class="' + ext + '"></div></a>';
                        jQuery(".upload_preview_" + id).append(img_html);
                    }, false);

                    reader.readAsDataURL(file);
                } else
                {
                    jQuery("#" + id).val("");
                    jQuery("#" + id).trigger("change");
                }
            }
        }

        if (files) {
            [].forEach.call(files, readAndPreview);
        }
    }
    jQuery("body").on('click', ".attachment_reply", function () {
        var file_id = jQuery(this).prop("id");
        jQuery("#" + file_id).val("");
        jQuery("#" + file_id).trigger("change");
    });
    jQuery("body").on('change', ".attachment_reply", function () {
        var file_id = jQuery(this).prop("id");
        previewFiles(jQuery("#" + file_id).prop("files"), file_id);
        jQuery(".upload_preview_" + file_id).empty();
    });
});
(function () {
    'use strict';
    var $ = jQuery;
    $.fn.extend({
        filterTable: function () {
            return this.each(function () {
                $(this).on('keyup', function (e) {
                    $('.filterTable_no_results').remove();
                    var $this = $(this),
                            search = $this.val().toLowerCase(),
                            target = $this.attr('data-filters'),
                            $target = $(target),
                            $rows = $target.find('tbody tr');

                    if (search == '') {
                        $rows.show();
                    } else {
                        $rows.each(function () {
                            var $this = $(this);
                            $this.text().toLowerCase().indexOf(search) === -1 ? $this.hide() : $this.show();
                        });
                        if ($target.find('tbody tr:visible').size() === 0) {
                            var col_count = $target.find('tr').first().find('td').size();
                            var no_results = $('<tr class="filterTable_no_results"><td colspan="12">No results found</td></tr>')
                            $target.find('tbody').append(no_results);
                        }
                    }
                });
            });
        }
    });
    $('[data-action="filter"]').filterTable();
})(jQuery);

function tag_select2(id)
{
    jQuery(id).select2({
        width: '100%',
        allowClear: true,
        placeholder: "Select Tag",
        formatNoMatches: function () {
            return "No Tags Tagged";
        },
        language: {
            noResults: function (params) {
                return "No Tags Tagged";
            }
        }
    });
}
function cc_select2(id)
{
    jQuery(id).select2({
        width: '100%',
        allowClear: true,
        placeholder: "Select Cc",
        formatNoMatches: function () {
            return "No Cc found";
        },
        language: {
            noResults: function (params) {
                return "No Cc found";
            }
        }
    });
}
function bcc_select2(id)
{
    jQuery(id).select2({
        width: '100%',
        allowClear: true,
        placeholder: "Select Bcc",
        formatNoMatches: function () {
            return "No Bcc found";
        },
        language: {
            noResults: function (params) {
                return "No Bcc found";
            }
        }
    });
}
function collapse_tab()
{
    var TAB = {

        wrapper: '.nav-tabs',

        init: function () {
            var _this = this;
            _this.reFlow();

            jQuery(window).on('resize', function () {
                _this.reFlow();
            });
        },

        reFlow: function () {
            var tab_wrapper = jQuery(this.wrapper);

            var wrapper_width = tab_wrapper.width(),
                    dropdown_width = tab_wrapper.find("li.dropdown").width(),
                    width_sum = 0;

            tab_wrapper.find('>li.visible_tab:not(li.dropdown)').each(function () {
                width_sum += jQuery(this).outerWidth(true);
            });

            var hidden_lists = tab_wrapper.find('>li.visible_tab');
            if (hidden_lists.length > 0 && width_sum + dropdown_width + 100 > wrapper_width)
            {
                jQuery("li.dropdown").show();
                jQuery('.elaborate > li.visible_tab').first().appendTo('ul.collapse_ul');
            } else
            {
                if (width_sum + dropdown_width + 100 < wrapper_width)
                {
                    jQuery('.elaborate > li').last().before(jQuery('ul.collapse_ul > li.visible_tab').first());
                }
                if (jQuery(".collapse_ul > li").length === 0)
                {
                    jQuery("li.dropdown").hide();
                }
            }
        }
    };
    if (jQuery('.nav-tabs').length) {
        TAB.init();
    }
}
function $_GET(param) {
    var vars = {};
    window.location.href.replace( location.hash, '' ).replace( 
        /[?&]+([^=&]+)=?([^&]*)?/gi, // regexp
        function( m, key, value ) { // callback
            vars[key] = value !== undefined ? value : '';
        }
    );

    if ( param ) {
        return vars[param] ? vars[param] : null;    
    }
    return vars;
}
function deepview(){
    if($_GET('view') || $_GET('order') )
    {
        if($_GET('order'))
        {
            order = $_GET('order');
        }
        else
        {
            order = "DESC";
        }
        if($_GET('view'))
        {
            active = $_GET('view');
        }
        else
        {
            active = "all";
        }
        jQuery(".table_loader").css("display", "inline");
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_ticket_refresh_left_bar',
                active: active
            },
            success: function (data) {
                jQuery("#left_bar_all_tickets").html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
        jQuery.ajax({
            type: 'post',
            url: ajaxurl,
            data: {
                action: 'eh_crm_ticket_refresh_right_bar',
                active: active,
                order: order
            },
            success: function (data) {
                jQuery("#right_bar_all_tickets").html(data);

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
}
function addToCookie(element,value)
{
    Cookies.set(element, value);
}
function collapse(id)
{
    jQuery('#'+id).animate({
        height: 'hide'
    });
    jQuery('#'+id+'_collapse').hide();
    jQuery('#'+id+'_drop').show();
    if(typeof Cookies.get('collapsed_views') !=='undefined')
    {
        var collapsed_views = JSON.parse(Cookies.get('collapsed_views'));
        collapsed_views.push(id);
    }
    else
    {
        var collapsed_views = new Array(id);
    }
    Cookies.remove('collapsed_views');
    addToCookie('collapsed_views',JSON.stringify(collapsed_views));
}
function drop(id)
{
    jQuery('#'+id).animate({
        height: 'show'
    });
    jQuery('#'+id+'_collapse').show();
    jQuery('#'+id+'_drop').hide();
    if(typeof Cookies.get('collapsed_views') !=='undefined')
    {
        var collapsed_views = JSON.parse(Cookies.get('collapsed_views'));
        while(collapsed_views.indexOf(id)!=-1)
        {
            collapsed_views.splice(collapsed_views.indexOf(id),1);
        }
        Cookies.remove('collapsed_views');
        addToCookie('collapsed_views',JSON.stringify(collapsed_views));
    }
}