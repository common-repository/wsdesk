<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();
$users_data = get_users(array("role__in"=>array("administrator","WSDesk_Agents","WSDesk_Supervisor")));
$users = array();
$select = array();
for($i=0;$i<count($users_data);$i++)
{
    $current = $users_data[$i];
    $temp = array();
    $roles = $current->roles;
    foreach ($roles as $value) {
        $current_role = $value;
        array_push($temp,ucfirst(str_replace("_", " ", $current_role)));
    }
    $users[implode(' & ', $temp)][$current->ID] = $current->data->display_name;
}
$args = array("type" => "label");
$fields = array("slug","title","settings_id");
$avail_labels= eh_crm_get_settings($args,$fields);
$ticket_rows = eh_crm_get_settingsmeta('0', "ticket_rows");
?>
<script>
    function enable_api_click()
    {
        if(document.getElementById('enable_api').checked)
            document.getElementById('api_key').style.display="block";
        else
            document.getElementById('api_key').style.display="none";
    };
</script>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="default_assignee" style="padding-right:1em !important;"><?php _e('Default Assignee', 'wsdesk'); ?></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Choose default assignee for new tickets', 'wsdesk'); ?></span>
        <select id="default_assignee" style="width: 100% !important;display: inline !important" class="form-control" aria-describedby="helpBlock">
            <?php 
                $assignee = eh_crm_get_settingsmeta('0', "default_assignee");
                $tag_selected = '';
                $no_assignee = '';
                switch($assignee)
                {
                    case 'no_assignee':
                        $no_assignee = 'selected';
                        break;
                }
                echo '
                <option value="no_assignee" '.$no_assignee.'>No Assignee</option>';
                foreach ($users as $key => $value) {
                    echo '<optgroup label="'.$key.'">';
                    foreach ($value as $id => $name)
                    {
                        $selected = '';
                        if($assignee == $id)
                        {
                            $selected = 'selected';
                        }
                        echo '<option value="'.$id.'" '.$selected.'>'.$name.'</option>';
                    }
                    echo "</optgroup>";
                }
            ?>
        </select>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="default_label" style="padding-right:1em !important;">Default Status</label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Choose default status for new tickets', 'wsdesk'); ?></span>
        <select id="default_label" style="width: 100% !important;display: inline !important" class="form-control" aria-describedby="helpBlock">
            <?php 
                $label= eh_crm_get_settingsmeta('0', "default_label");
                for($i=0;$i<count($avail_labels);$i++)
                {
                    $selected = '';
                    if($label === $avail_labels[$i]['slug'])
                    {
                        $selected = 'selected';
                    }
                    echo '<option value="'.$avail_labels[$i]['slug'].'" '.$selected.'>'.$avail_labels[$i]['title'].'</option>';
                }
            ?>
        </select>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="ticket_raiser" style="padding-right:1em !important;"><?php _e('Tickets Raisers', 'wsdesk'); ?></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"> <?php _e('Who can raise the tickets?', 'wsdesk'); ?></span>
        <span style="vertical-align: middle;">
            <?php 
                $ticket_raiser = eh_crm_get_settingsmeta('0', "ticket_raiser");
                $all = '';
                $registered = '';
                $guest = '';
                switch ($ticket_raiser) {
                    case "all":
                        $all = 'checked';
                        $registered = '';
                        $guest = '';
                        break;
                    case "registered":
                        $all = '';
                        $registered = 'checked';
                        $guest = '';
                        break;
                    case "guest":
                        $all = '';
                        $registered = '';
                        $guest = 'checked';
                        break;
                }
            ?>
            <input type="radio" style="margin-top: 0;" id="ticket_raiser" class="form-control" name="ticket_raiser" <?php echo $all; ?> value="all"> <?php _e('All', 'wsdesk'); ?><br>
            <input type="radio" style="margin-top: 0;" id="ticket_raiser" class="form-control" name="ticket_raiser" <?php echo $registered; ?> value="registered"> <?php _e('Registered Users', 'wsdesk'); ?><br>
            <input type="radio" style="margin-top: 0;" id="ticket_raiser" class="form-control" name="ticket_raiser" <?php echo $guest; ?> value="guest"> <?php _e('Guest Users', 'wsdesk'); ?>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="ticket_display_row" style="padding-right:1em !important;"><?php _e('Tickets Row', 'wsdesk'); ?></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Number of tickets per page', 'wsdesk'); ?></span>
        <input type="text" id="ticket_display_row" placeholder="20" value="<?php echo $ticket_rows; ?>" class="form-control crm-form-element-input">
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="auto_assign" style="padding-right:1em !important;"><?php _e('Auto Assign Tickets','wsdesk'); ?> <span class="wsdesk_super"><?php _e("Premium","wsdesk");?></span></label> 
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Want to auto assign tickets to the replier if the ticket is unassigned?','wsdesk'); ?></span>
        <span style="vertical-align: middle;">
            <input type="radio" style="margin-top: 0;" id="auto_assign" class="form-control" name="auto_assign" value="enable" disabled> <?php _e('Enable','wsdesk'); ?><br>
            <input type="radio" style="margin-top: 0;" id="auto_assign" class="form-control" name="auto_assign" value="disable" disabled> <?php _e('Disable','wsdesk'); ?><br>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="auto_suggestion" style="padding-right:1em !important;"><?php _e('Auto Suggestion','wsdesk'); ?> <span class="wsdesk_super">Premium</span></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Want to enable auto suggestion for agent and ticket raisers?','wsdesk'); ?></span>
        <span style="vertical-align: middle;">
            <input type="radio" style="margin-top: 0;" id="auto_suggestion_premium" class="form-control" name="auto_suggestion_premium" disabled value="enable"> <?php _e('Enable','wsdesk'); ?><br>
            <input type="radio" style="margin-top: 0;" id="auto_suggestion_premium" class="form-control" name="auto_suggestion_premium" disabled value="disable"> <?php _e('Disable','wsdesk'); ?><br>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="custom-attachment-folder" style="padding-right:1em !important;"><?php _e('Custom Attachment Folder','wsdesk'); ?> <span class="wsdesk_super">Premium</span></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Want to enable custom folder for attachments?','wsdesk'); ?></span>
        <span style="vertical-align: middle;">            
            <input type="radio" style="margin-top: 0;" onclick="custom_attachment();" id="custom-attachment-folder-enable_premium" class="form-control" name="custom_attachment_premium" disabled value="yes"> <?php _e('Enable','wsdesk'); ?><br>
            <input type="radio" style="margin-top: 0;" onclick="custom_attachment();" id="custom-attachment-folder-disable_premium" class="form-control" name="custom_attachment_premium" disabled value="no"> <?php _e('Disable','wsdesk'); ?><br>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="custom-attachment-folder" style="padding-right:1em !important;"><?php _e('Maximum file size of attachments (MB)','wsdesk'); ?> <span class="wsdesk_super">Premium</span></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('You can set maximum size of the attachments','wsdesk'); ?> <span class="glyphicon glyphicon-info-sign" style="color:lightgray;font-size:x-small;vertical-align:baseline;" data-toggle="wsdesk_tooltip" title="<?php _e("Default value: 1MB. Decimal values are allowed.", 'wsdesk'); ?>" data-container="body"></span></span>
        <input type="number" id="max_file_size_premium" disabled class="form-control crm-form-element-input">
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="custom-attachment-folder" style="padding-right:1em !important;"><?php _e('Valid Attachment Extensions','wsdesk'); ?> <span class="wsdesk_super">Premium</span></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Select File Extensions','wsdesk'); ?> <span class="glyphicon glyphicon-info-sign" style="color:lightgray;font-size:x-small;vertical-align:baseline;" data-toggle="wsdesk_tooltip" title="<?php _e("Select nothing to make all extensions valid.", 'wsdesk'); ?>" data-container="body"></span></span>
        
         <span style="vertical-align: middle;">
            <select disabled style="width: 100%"></select>
         </span>
    </div>
</div>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="auto_assign" style="padding-right:1em !important;"><?php _e('Display Tickets As','wsdesk'); ?> <span class="wsdesk_super"><?php _e('Premium','wsdesk');?></span></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('How would you like to display your tickets?','wsdesk'); ?></span>
            <input type="radio" style="margin-top: 0;" id="tickets_display" class="form-control" name="tickets_display" value="html" disabled=""> <?php _e('HTML','wsdesk'); ?><br>
            <input type="radio" style="margin-top: 0;" id="tickets_display" class="form-control" name="tickets_display" value="text" disabled> <?php _e('Text','wsdesk'); ?><br>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="auto_create_user" style="padding-right:1em !important;"><?php _e('Create WordPress User','wsdesk'); ?></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Create wordpress user while guest user submitting the tickets through the form','wsdesk'); ?></span>
        <span style="vertical-align: middle;">
            <?php 
                $auto_create_user = eh_crm_get_settingsmeta('0', "auto_create_user");
                $enable = '';
                switch ($auto_create_user) {
                    case "enable":
                        $enable = 'checked';
                        break;
                    default:
                        $enable = '';
                        break;
                }
            ?>
            <input type="checkbox" <?php echo $enable; ?> style="margin-top: 0;" id="auto_create_user" class="form-control" name="auto_create_user" value="enable"> <?php _e('Enable','wsdesk'); ?><br>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
        <label for="auto_create_user" style="padding-right:1em !important;"><?php _e('WSDesk API','wsdesk'); ?></label>
    </div>
    <div class="col-md-9">
        <span class="help-block"><?php _e('Create tickets via API from any website','wsdesk'); ?></span>
        <span style="vertical-align: middle;">
            <?php
                $api_key = eh_crm_get_settingsmeta('0', "api_key");
                if(empty($api_key))
                    $api_key=md5(time().EH_CRM_MAIN_URL);
                $auto_create_user = eh_crm_get_settingsmeta('0', "enable_api");
                $enable = '';
                switch ($auto_create_user) {
                    case "enable":
                        $enable = 'checked';
                        $path = 'block';
                        break;
                    default:
                        $enable = '';
                        $path = 'none';
                        break;
                }
            ?>
            <input type="checkbox" <?php echo $enable; ?> style="margin-top: 0;" id="enable_api" onclick="enable_api_click();" class="form-control" name="enable_api" value="enable"> <?php _e('Enable API','wsdesk'); ?><br>

            <div id="api_key" style="display: <?=$path?>">
                <span class="help-block"><?php _e('API Key:','wsdesk'); ?> <span class="glyphicon glyphicon-info-sign" style="color:lightgray;font-size:x-small;vertical-align:baseline;" data-toggle="wsdesk_tooltip" title="<?php _e("Please share this API Key only with trusted sources.", 'wsdesk'); ?>" data-container="body"></span></span> 
                <input type="text" id="api_key_textbox" value="<?php echo $api_key; ?>" class="form-control crm-form-element-input">
            </div>
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-3">
       <label for="custom-attachment-folder" style="padding-right:1em !important;"><?php _e('Default Deep Link','wsdesk'); ?></label>
    </div>
    <div class="col-md-9">
        <?php
        $default_deep_link = eh_crm_get_settingsmeta('0', 'default_deep_link');
        ?>
        <span class="help-block"><?php _e('Add a default deep link here','wsdesk'); ?></span>
        <span style="vertical-align: middle;">
            <span class="help-block"><?php _e('Path: '.admin_url().'admin.php?page=wsdesk_tickets&','wsdesk'); ?></span> 
            <input type="text" id="default_deep_link" placeholder="view=all" value="<?php echo $default_deep_link; ?>" class="form-control crm-form-element-input">
        </span>
    </div>
</div>
<span class="crm-divider"></span>
<div class="crm-form-element">
    <div class="col-md-12">
        <button type="button" id="save_general" class="btn btn-primary"> <span class="glyphicon glyphicon-ok"></span> <?php _e('Save Changes', 'wsdesk'); ?></button>
    </div>
</div>
<?php
return ob_get_clean();