jQuery(document).ready(function (jQuery) {
    jQuery('.eh_crm_support_main').on('submit','form#eh_crm_ticket_form',function (e) {
        var btn = jQuery("#crm_form_submit");
        btn.prop("disabled","disabled");
        var fd = new FormData();
        if(jQuery("#ticket_attachment").length !=0 )
        {
            var file = jQuery("#ticket_attachment");
            jQuery.each(jQuery(file), function (i, obj) {
                jQuery.each(obj.files, function (j, file) {
                    fd.append('file[' + j + ']', file);
                });
            });
        }
        fd.append("form", jQuery(this).serialize());
        fd.append('action', 'eh_crm_new_ticket_post');
        jQuery.ajax({
            type: "POST",
            url: support_object.ajax_url,
            cache: false,
            processData: false,
            contentType: false,
            data:fd, // serializes the form's elements.
            success: function (data)
            {
                jQuery('.support_option_choose').hide();
                jQuery('.main_new_suppot_request_form').html(data);
                eh_crm_check_ticket_request(this);
            }
        });
        e.preventDefault(); // avoid to execute the actual submit of the form.
    });
    jQuery('.eh_crm_support_main').on('click', 'button.eh_crm_new_request', function (e) {
        //jQuery("div .spinner").show();
        var btn = jQuery(this);
        btn.prop("disabled","disabled");
        // business logic...
        jQuery.ajax({
            type: "POST",
            url: support_object.ajax_url,
            data: {
                action: 'eh_crm_new_ticket_form'
            },
            success: function (data)
            {
                btn.removeProp("disabled");
                jQuery('.support_option_choose').hide();
                jQuery('.eh_crm_support_main').append(data);
                jQuery('textarea').each(function () {
                    this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
                }).on('input', function () {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });

            }
        });
        e.preventDefault();
    });
    jQuery('.eh_crm_support_main').on('click', 'button.eh_crm_check_request', function (e) {
        e.preventDefault();
        eh_crm_check_ticket_request(this);
    });
    setInterval(function()
    {
        if(jQuery(".single_ticket_panel").length != 0)
        {
            eh_crm_check_section_data();
        }
    }, 30000);
    
    function eh_crm_check_section_data()
    {
        jQuery(".ticket_loader").css("display", "inline");
        var ticket_id = jQuery(".single_ticket_panel").prop("id");
        jQuery.ajax({
            type: "POST",
            url: support_object.ajax_url,
            data: {
                action: 'eh_crm_ticket_client_section_load',
                ticket_id:ticket_id
            },
            success: function (data)
            {
                jQuery(".ticket_loader").css("display", "none");
                jQuery('.comment-list').html(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
    
    function eh_crm_check_ticket_request(comp)
    {
        var btn = jQuery(comp);
        btn.prop("disabled","disabled");
        jQuery.ajax({
            type: "POST",
            url: support_object.ajax_url,
            data: {
                action: 'eh_crm_check_ticket_request',
                url:window.location.pathname
            },
            success: function (data)
            {
                btn.removeProp("disabled");
                jQuery('.support_option_choose').hide();
                var parse = JSON.parse(data);
                if(parse.status == 'success')
                {
                    jQuery('.ticket_table_wrapper').html(parse.content);
                }
                else
                {
                    window.location.href = parse.url;
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(textStatus, errorThrown);
            }
        });
    }
    jQuery('.eh_crm_support_main').on('click', 'button.eh_crm_support_back', function (e) {
        jQuery("div .spinner").show();
        jQuery('.main_new_suppot_request_form').remove();
        jQuery('.support_option_choose').show();
        jQuery("div .spinner").hide();
    });
    jQuery('.eh_crm_support_main').on('click', '#support-table tr', function (e) {
        if (!jQuery(e.target).closest('.except_view').length) {
            var ticket_id = jQuery(this).prop("id");
            jQuery(".table_loader").css("display", "inline");
            jQuery.ajax({
                type: 'post',
                url: support_object.ajax_url,
                data: {
                    action: 'eh_crm_ticket_single_view_client',
                    ticket_id: ticket_id
                },
                success: function (data) {
                    jQuery(".table_loader").css("display", "none");
                    jQuery('.ticket_load_content').html(data);
                    jQuery('.reply_textarea').each(function () {
                        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
                    }).on('input', function () {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                    jQuery('html, body').animate({
                        scrollTop: jQuery("hr").offset().top
                    }, 1000);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log(textStatus, errorThrown);
                }
            });
        }
    });
    jQuery(".eh_crm_support_main").on("click", ".ticket_reply_action_button", function (e) {
        e.preventDefault();
        var ticket_id = jQuery(this).prop("id");
        var text = jQuery("#reply_textarea_" + ticket_id).val();
        if (text != "")
        {
            jQuery(".ticket_loader").css("display", "inline");
            var btn = jQuery(this);
            btn.prop("disabled","disabled");
            jQuery("#reply_textarea_" + ticket_id).css("border", "1px solid #F2F2F2");
            var security = jQuery("#security").val();
            var fd = new FormData();
            var file = jQuery("#files_" + ticket_id);
            jQuery.each(jQuery(file), function (i, obj) {
                jQuery.each(obj.files, function (j, file) {
                    fd.append('file[' + j + ']', file);
                });
            });
            fd.append("ticket_reply", text);
            fd.append("ticket_id", ticket_id);
            fd.append("security", security);
            fd.append('action', 'eh_crm_ticket_reply_raiser');
            jQuery.ajax({
                type: 'POST',
                url: support_object.ajax_url,
                data: fd,
                cache: false,
                processData: false,
                contentType: false,
                success: function (data) {
                    jQuery(".ticket_loader").css("display", "none");
                    btn.removeProp('disabled');
                    var parse = JSON.parse(data);
                    jQuery(".ticket_table_wrapper").html(parse.table);
                    jQuery(".ticket_load_content").html(parse.ticket);
                    jQuery('.reply_textarea').each(function () {
                        this.setAttribute('style', 'height:' + (this.scrollHeight) + 'px;overflow-y:hidden;');
                    }).on('input', function () {
                        this.style.height = 'auto';
                        this.style.height = (this.scrollHeight) + 'px';
                    });
                }
            });
        } else
        {
            jQuery("#reply_textarea_" + ticket_id).css("border", "1px solid red");
        }
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
                if (/\.(doc?x|pdf|xml|csv|xlsx|xls|txt|zip)$/i.test(file.name)) {
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