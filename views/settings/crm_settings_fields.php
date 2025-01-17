<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();
$args = array("type" => "field");
$fields = array("slug","title","settings_id");
$avail_fields = eh_crm_get_settings($args,$fields);
$selected = eh_crm_get_settingsmeta("0", "selected_fields");
if(empty($selected))
{
    $selected = array('request_email','request_title','request_description');
}
if(!in_array('request_description', $selected))
{
    array_unshift($selected, "request_description");
}
if(!in_array('request_title', $selected))
{
    array_unshift($selected, "request_title");
}
if(!in_array('request_email', $selected))
{
    array_unshift($selected, "request_email");
}
?>
<div class="crm-form-element">
    <div class="col-md-12">
        <div style="vertical-align: middle">
            <label for="ticket_fields" style="padding-right:1em !important;"><?php _e('Ticket Fields', 'wsdesk'); ?></label> 
            <button type="button" id="ticket_field_add_button" class="btn btn-primary btn-xs pull-right"> <span class="glyphicon glyphicon-plus"></span> <?php _e('Add Field', 'wsdesk'); ?></button>
        </div>
        <div class="panel panel-default crm-panel" style="margin-top: 15px !important;margin-bottom: 0px !important;">
            <div class="panel-body" style="padding: 5px !important;">
                <div class="col-sm-6" style="padding-right: 5px !important;padding-left: 5px !important;">
                    <span class="col-md-12" style="text-align: center;padding: 5px 0px;"><?php _e('Inactive Fields', 'wsdesk'); ?></span><br>
                    <ul class="list-group">
                        <?php
                            if((count($avail_fields)<=3) || (count($avail_fields) == count($selected)) )
                            {
                                echo '<li class="list-group-item list-group-item-info">No inactive field</li>';
                            }
                            else
                            {
                                for($i=3;$i<count($avail_fields);$i++)
                                {
                                    $field_type = eh_crm_get_settingsmeta($avail_fields[$i]['settings_id'], "field_type");
                                    if(!in_array($avail_fields[$i]['slug'], $selected))
                                    {
                                        echo '<li class="list-group-item list-group-item-info" id="'.$avail_fields[$i]['slug'].'"> '.$avail_fields[$i]['title'].' [ '. ucfirst($field_type).' ] <span class="pull-right"><span style="margin-right:5px; cursor:pointer; text-decoration:underline" class="ticket_field_activate" id="'.$avail_fields[$i]['slug'].'">Activate</span> <span class="glyphicon glyphicon-trash ticket_field_delete_type" id="'.$avail_fields[$i]['slug'].'" data-toggle="wsdesk_tooltip" data-container="body" title="Delete Field" style="margin-right:5px;cursor:pointer;font-size: large;"></span> <span class="glyphicon glyphicon-pencil ticket_field_edit_type" id="'.$avail_fields[$i]['slug'].'" data-toggle="wsdesk_tooltip" data-container="body" title="Edit Field" style="margin-right:5px;cursor:pointer;font-size: large;"></span></span></li>';
                                    }
                                }
                            }
                        ?>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <span class="col-md-12" style="text-align: center;padding: 5px 0px;"><?php _e('Active Fields', 'wsdesk'); ?></span><br>
                    <ul class="list-group list-group-sortable-connected list-group-field-data list-border-settings">
                        <?php
                            for($i=0;$i<count($selected);$i++)
                            {
                                for($j=0;$j<count($avail_fields);$j++)
                                {
                                    if($avail_fields[$j]['slug'] === $selected[$i])
                                    {
                                        $field_type = eh_crm_get_settingsmeta($avail_fields[$j]['settings_id'], "field_type");
                                        echo '<li class="list-group-item list-group-item-success" style="padding: 10px 10px !important;" id="'.$avail_fields[$j]['slug'].'"><span class="dashicons dashicons-menu" style="cursor:move;margin-right:5px;"></span> '.$avail_fields[$j]['title'].' [ '. ucfirst($field_type).' ] <span class="pull-right">';
                                        if(!in_array($avail_fields[$j]['slug'],array('request_email','request_title','request_description')))
                                        {
                                            echo '<span style="margin-right:5px; cursor:pointer; text-decoration:underline" class="ticket_field_deactivate" id="'.$avail_fields[$j]['slug'].'">Deactivate</span> ';
                                        }
                                        if(!in_array($avail_fields[$j]['slug'],array('request_email','request_title','request_description')))
                                        {
                                            echo '<span class="glyphicon glyphicon-trash ticket_field_delete_type" id="'.$avail_fields[$j]['slug'].'" data-toggle="wsdesk_tooltip" data-container="body" title="Delete Field" style="margin-right:5px;cursor:pointer;font-size: large;"></span> ';
                                        }
                                        echo '<span class="glyphicon glyphicon-pencil ticket_field_edit_type" id="'.$avail_fields[$j]['slug'].'" data-toggle="wsdesk_tooltip" data-container="body" title="Edit Field" style="margin-right:5px;cursor:pointer;font-size: large;"></span> ';
                                        echo '</span></li>';
                                    }
                                }

                            }
                        ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="ticket_field_add_display" style="display: none;">
    <span class="crm-divider"></span>
    <div class="crm-form-element">
        <div class="col-md-12">
            <div style="vertical-align: middle">
                <label style="padding-right:1em !important;"><?php _e('Add Field', 'wsdesk'); ?></label> 
                <button type="button" id="ticket_field_cancel_add_button" class="btn btn-primary btn-xs pull-right"> <span class="glyphicon glyphicon-remove"></span> <?php _e('Cancel', 'wsdesk'); ?></button>
            </div>
            <span style="vertical-align: middle;" id="ticket_field_add_section">
                <input type="hidden" value="" id="add_new_field_yes">
                <span class="help-block"><?php _e('Which type of field do you need?', 'wsdesk'); ?> </span>
                <select id="ticket_field_add_type" style="width: 100% !important;display: inline !important" class="form-control" aria-describedby="helpBlock">
                    <option value=""><?php _e('Select the Type of Field', 'wsdesk'); ?></option>
                    <option value="text"><?php _e('Text Box', 'wsdesk'); ?></option>
                    <option value="password"><?php _e('Password', 'wsdesk'); ?></option>
                    <option value="select"><?php _e('Select', 'wsdesk'); ?></option>
                    <option value="radio"><?php _e('Radio', 'wsdesk'); ?></option>
                    <option value="checkbox"><?php _e('Checkbox', 'wsdesk'); ?></option>
                    <option value="number"><?php _e('Number', 'wsdesk'); ?></option>
                    <option value="email"><?php _e('Email', 'wsdesk'); ?></option>
                    <option value="textarea"><?php _e('Text Area', 'wsdesk'); ?></option>
                    <option value="file"><?php _e('Attachment', 'wsdesk'); ?></option>
                    <option disabled value="ip_address"><?php _e('IP Address (Premium)', 'wsdesk');?></option>
                </select>
                <span style="vertical-align: middle;" id="ticket_field_add_append"></span>
            </span>
        </div>
    </div>
</div>
<div id="ticket_field_edit_display" style="display: none;">
    <span class="crm-divider"></span>
    <div class="crm-form-element">
        <div class="col-md-12">
            <div style="vertical-align: middle">
                <label style="padding-right:1em !important;"><?php _e('Edit Field', 'wsdesk'); ?></label>
                <button type="button" id="ticket_field_cancel_edit_button" class="btn btn-primary btn-xs pull-right"> <span class="glyphicon glyphicon-remove"></span> <?php _e('Cancel', 'wsdesk'); ?></button>
            </div>
            <input type="hidden" value="" id="ticket_field_edit_type">
            <span style="vertical-align: middle;" id="ticket_field_edit_append"></span>
        </div>
    </div>
</div>
<div class="crm-form-element">
    <div class="col-md-12">
        <button type="button" id="save_ticket_fields" class="btn btn-primary"> <span class="glyphicon glyphicon-ok"></span> <?php _e('Save Changes', 'wsdesk'); ?></button>
    </div>
</div>
<?php
return ob_get_clean();