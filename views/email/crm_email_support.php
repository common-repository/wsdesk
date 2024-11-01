<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();
$support_email_name = eh_crm_get_settingsmeta('0', "support_reply_email_name");
$support_email = eh_crm_get_settingsmeta('0', "support_reply_email");
$support_email_reply_text = eh_crm_get_settingsmeta('0', "support_email_reply_text");
if(!$support_email_reply_text)
{
    $support_email_reply_text = 'Your request (#[id]) has been updated. To add additional comments, reply to this email.

Date: [date]

[content]

Regards,
[agent_replied]';
}
?>
<div class="crm-form-element">
    <div class="col-md-12">
        <span class="help-block"><?php _e('WSDesk Debug Email','wsdesk'); ?> <span class="glyphicon glyphicon-info-sign" style="color:lightgray;font-size:x-small;vertical-align:baseline;" data-toggle="wsdesk_tooltip" title="<?php _e("Enable this option to place e-mail logs in PHP error log file.", 'wsdesk'); ?>" data-container="body"></span></span>
        <span style="vertical-align: middle;">
            <?php 
                $wsdesk_debug_status = eh_crm_get_settingsmeta('0', "wsdesk_debug_status");
                $debug_enable = '';
                switch ($wsdesk_debug_status) {
                    case "enable":
                        $debug_enable = 'checked';
                        break;
                    default:
                        $debug_enable = '';
                        break;
                }
            ?>
            <input type="checkbox" <?php echo $debug_enable; ?> style="margin-top: 0;" id="wsdesk_debug_email" class="form-control" name="wsdesk_debug_email" value="enable"> <?php _e('Enable','wsdesk'); ?><br>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-12">
        <span class="help-block"><?php _e('Support Reply Email Name','wsdesk'); ?></span>
        <input type="text" id="support_reply_email_name" placeholder="<?php _e('Enter name','wsdesk'); ?>" value="<?php echo $support_email_name; ?>" class="form-control crm-form-element-input">
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-12">
        <span class="help-block"><?php _e('Support Reply Email','wsdesk'); ?></span>
        <input type="text" id="support_reply_email" placeholder="<?php _e('Enter Email','wsdesk'); ?>" value="<?php echo $support_email; ?>" class="form-control crm-form-element-input">
    </div>
</div>
<span class="crm-divider"></span>
<div class="col-md-12">
    <div class="panel-group" id="email_reply_role" style="margin-bottom: 0px !important;cursor: pointer;">
        <div class="panel panel-default">
            <div class="panel-heading collapsed" data-toggle="collapse" data-parent="#email_reply_role" data-target="#content_reply_email">
                <span class ="email-reply-toggle"></span>
                <h4 class="panel-title">
                    <?php _e('Available ShortCodes for Agent Reply Email','wsdesk'); ?>
                </h4>
            </div>
            <div id="content_reply_email" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-2">
                                [id]
                            </div>
                            <div class="col-md-10">
                                <?php _e('To Insert Ticket Number in the Reply','wsdesk'); ?>
                            </div>
                        </div>
                        <span class="crm-divider"></span>
                        <div class="row">
                            <div class="col-md-2">
                                [assignee]
                            </div>
                            <div class="col-md-10">
                                <?php _e('To Insert Ticket Assignee in the Reply','wsdesk'); ?>
                            </div>
                        </div>
                        <span class="crm-divider"></span>
                        <div class="row">
                            <div class="col-md-2">
                                [tags]
                            </div>
                            <div class="col-md-10">
                                <?php _e('To Insert Ticket Tags in the Reply','wsdesk'); ?>
                            </div>
                        </div>
                        <span class="crm-divider"></span>
                        <div class="row">
                            <div class="col-md-2">
                                [date]
                            </div>
                            <div class="col-md-10">
                                <?php _e('To Insert Ticket Date and Time in the Reply','wsdesk'); ?>
                            </div>
                        </div>
                        <span class="crm-divider"></span>
                        <div class="row">
                            <div class="col-md-2">
                                [content]
                            </div>
                            <div class="col-md-10">
                                <?php _e('To Insert Ticket Content in the Reply','wsdesk'); ?>
                            </div>
                        </div>
                        <span class="crm-divider"></span>
                        <div class="row">
                            <div class="col-md-2">
                                [agent_replied]
                            </div>
                            <div class="col-md-10">
                                <?php _e('To Insert Ticket Agent who replied in the Reply','wsdesk'); ?>
                            </div>
                        </div>
                        <span class="crm-divider"></span>
                        <div class="row">
                            <div class="col-md-2">
                                [status]
                            </div>
                            <div class="col-md-10">
                                <?php _e('To Insert Ticket Status in the Reply','wsdesk'); ?>
                            </div>
                        </div>
                        <span class="crm-divider"></span>
                        <div class="row">
                            <div class="col-md-2">
                                [latest_reply] <span class="wsdesk_super"><?php _e("Premium", "wsdesk");?></span>
                            </div>
                            <div class="col-md-9">
                                <?php _e("To Insert Latest Ticket Reply in the Reply", 'wsdesk'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>                    
        </div>
    </div>
</div>
<div class="crm-form-element">
    <div class="col-md-12">
        <span class="help-block"><?php _e('Enter your agent reply mail format','wsdesk'); ?></span>
        <?php
            wp_editor
            (
                $support_email_reply_text, 
                "support_email_reply_text", 
                array
                (
                    'tinymce' => false,
                    "media_buttons" => false,
                    "default_editor"=> "html",
                    "editor_height" => "300px",
                )
            );
        ?>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-12">
        <span class="help-block"><?php _e('Send auto email on ticket creation','wsdesk'); ?> <span class="wsdesk_super">Premium</span></span>
        <span style="vertical-align: middle;">
            
            <input type="checkbox" style="margin-top: 0;" id="auto_send_creation_email_premium" class="form-control" name="auto_send_creation_email_premium" disabled value="enable"> <?php _e('Enable','wsdesk');?><br>
        </span>
    </div>
</div>
<div class="crm-form-element">
    <div class="col-md-12">
        <button type="button" id="save_email_support" class="btn btn-primary"> <span class="glyphicon glyphicon-ok"></span> <?php _e('Save Changes','wsdesk'); ?></button>
    </div>
</div>
<?php
return ob_get_clean();