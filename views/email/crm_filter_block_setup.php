<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();
$block_filter = eh_crm_get_settingsmeta("0", "email_block_filters");
if(!$block_filter)
{
    $block_filter = array();
}
?>
<div class="crm-form-element">
    <div class="col-md-12">
        <div style="vertical-align: middle">
            <label for="blocked_emails" style="padding-right:1em !important;"><?php _e('Blocked Emails','wsdesk'); ?></label>
            <button type="button" id="block_email_add_button" class="btn btn-primary btn-xs pull-right"> <span class="glyphicon glyphicon-plus"></span> <?php _e('Add Filter','wsdesk'); ?></button>
        </div>
        <ul class="list-group list-group-sortable-connected list-group-block-data">
            <?php
                if(!empty($block_filter))
                {
                    foreach ($block_filter as $email => $data)
                    {
                        echo '<li class="list-group-item list-group-item-success" style="padding: 10px 10px !important;" id="'.$email.'"> '.$email.' -> [ '.$data.' ]<span class="pull-right">';
                        echo '<span class="glyphicon glyphicon-trash block_email_delete_type" id="'.$email.'" data-toggle="wsdesk_tooltip" data-container="body" title="'.__('Delete Filter','wsdesk').'" style="margin-right:5px;cursor:pointer;font-size: large;"></span> ';
                        echo '</span></li>';
                    }
                }
                else
                {
                    echo '<li class="list-group-item list-group-item-success">'.__('There are no blocked email ! Block a new Email.','wsdesk').'</li>';
                }
            ?>
        </ul>
    </div>
    <div id="block_email_add_display" style="display: none;">
        <span class="crm-divider"></span>
        <div class="crm-form-element">
            <div class="col-md-12">
                <div style="vertical-align: middle">
                    <label for="block_address_add" style="padding-right:1em !important;"><?php _e('Add Filter','wsdesk'); ?></label>
                    <button type="button" id="block_email_cancel_add_button" class="btn btn-primary btn-xs pull-right"> <span class="glyphicon glyphicon-remove"></span> <?php _e('Cancel','wsdesk'); ?></button>
                </div>
                <span style="vertical-align: middle;" id="block_address_add_section">
                    <input type="hidden" value="" id="add_block_address_yes">
                    <span class="help-block"><?php _e('Enter Details for New Block Address?','wsdesk'); ?> </span>
                    <input type="text" id="block_address_add_email" placeholder="<?php _e('Enter Email or matchcase','wsdesk'); ?>" class="form-control crm-form-element-input">
                    <span class="help-block"><?php _e('Choose the blocking type?.','wsdesk'); ?></span>
                    <span style="vertical-align: middle;" id="add_block_address_rights">
                        <input type="checkbox" checked style="margin-top: 0;" class="form-control" name="add_block_rights" id="add_block_rights_send" value="send"> <?php _e("Block Sending Emails", 'wsdesk'); ?><br>
                        <input type="checkbox" checked style="margin-top: 0;" class="form-control" name="add_block_rights" id="add_block_rights_receive" value="receive"> <?php _e("Block Receiving Emails", 'wsdesk'); ?><br>
                    </span>
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <button type="button" id="save_email_filter_block" class="btn btn-primary btn-sm"> <span class="glyphicon glyphicon-ok"></span> <?php _e('Save Changes','wsdesk'); ?></button>
    </div>
</div>
<div class="crm-form-element">
    <div class="col-md-12">
        <div style="vertical-align: middle">
            <label for="blocked_emails" style="padding-right:1em !important;"><?php _e('Blocked Email Subjects','wsdesk'); ?> <span class="wsdesk_super">Premium</span></label>
            <button disabled type="button" id="block_subject_add_button" class="btn btn-primary btn-xs pull-right"> <span class="glyphicon glyphicon-plus"></span> <?php _e('Add Filter','wsdesk'); ?></button>
        </div>
        <ul class="list-group list-group-sortable-connected list-group-block-data">
            <?php
                echo '<li class="list-group-item list-group-item-success">'.__('There are no blocked subjects! Block a subject.','wsdesk').'</li>';
            ?>
        </ul>
    </div>
    
        <div class="col-md-12">
        <button disabled type="button" id="save_subject_filter_block" class="btn btn-primary btn-sm"> <span class="glyphicon glyphicon-ok"></span> <?php _e('Save Changes','wsdesk'); ?></button>
    </div>
</div>
<?php
return ob_get_clean();