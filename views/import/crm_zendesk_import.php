<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();
$zendesk_accesstoken = eh_crm_get_settingsmeta('0', "zendesk_accesstoken");
$zendesk_subdomain = eh_crm_get_settingsmeta('0', "zendesk_subdomain");
$zendesk_username = eh_crm_get_settingsmeta('0', "zendesk_username");
$zendesk = false;
if(file_exists(EH_CRM_MAIN_VENDOR."zendesk/autoload.php"))
{
    $zendesk = true;
}
else
{
    $zendesk = false;
}
if(!$zendesk)
{
    ?>
        <center>
            <div class="crm-form-element">
                <div class="col-md-12">
                    <span class="help-block" style="text-align: center"><?php _e('Activate Zendesk Import to Get all Zendesk Tickets','wsdesk'); ?></span>
                    <button class="btn btn-primary btn-lg" id="activate_zendesk"><?php _e('Activate Zendesk','wsdesk'); ?></button>
                </div>
            </div>
        </center>
    <?php
}
else
{
    ?>
<center>
    <div class="crm-form-element" id="zendesk_progress_bar">
        <div class="col-md-12">
            <span class="help-block" id="zendesk_data_progress"><?php _e('Importing Tickets may take some time...','wsdesk'); ?></span>
            <div class="progress">
                <div class="progress-bar progress-bar-striped active" id="zendesk_importing_width" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100" style="width: 1%">
                    <span class="sr-only" style="position: inherit;color: black" id="zendesl_per_progress">1% <?php _e('Completed','wsdesk'); ?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8 col-md-offset-2" id="live_import_main">
        <div id="live_import_log"></div>
        <div>
            <span class="help-block"><?php _e('Stop Pulling Tickets to WSDesk','wsdesk'); ?></span>
            <button type="button" data-loading-text="<?php _e('Stoping Zendesk import...','wsdesk'); ?>" id="stop_pull_tickets" class="btn btn-primary"><?php _e('Stop Import','wsdesk'); ?></button>
        </div>
    </div>
    <div id="blur_on_import">
        <div class="crm-form-element">
            <div class="col-md-12">
                <span class="help-block"><?php _e('What is your plan in Zendesk?','wsdesk'); ?> <span class="glyphicon glyphicon-info-sign" data-toggle="wsdesk_tooltip" title="<?php _e('Importing speed depends on your plan','wsdesk'); ?>" data-container="body"></span></span>
                <select id="zendesk_plan" style="width: 50% !important;display: inline !important" class="form-control" aria-describedby="helpBlock">
                        <option value="essential"><?php _e('Essential','wsdesk'); ?></option>
                        <option value="team"><?php _e('Team','wsdesk'); ?></option>
                        <option value="professional"><?php _e('Professional','wsdesk'); ?></option>
                        <option value="enterprise"><?php _e('Enterprise','wsdesk'); ?></option>
                        <option value="high"><?php _e('High Volume API Add-On (Professional or Enterprise)','wsdesk'); ?></option>
                </select>
            </div>
        </div>
        <div class="crm-form-element">
            <div class="col-md-12">
                <span class="help-block"><?php _e('Enter your Zendesk Access Token','wsdesk'); ?></span>
                <input type="text" id="zendesk_accesstoken" placeholder="<?php _e('Activate Zendesk','wsdesk'); ?>Zendesk Token" value="<?php echo $zendesk_accesstoken; ?>" class="form-control crm-form-element-input">
            </div>
        </div>
        <div class="crm-form-element">
            <div class="col-md-12">
                <span class="help-block"><?php _e('Enter your Zendesk Subdomain','wsdesk'); ?> ( Without https:// and .zendesk.com )</span>
                <input type="text" id="zendesk_subdomain" placeholder="<?php _e('Zendesk Subdomain','wsdesk'); ?>" value="<?php echo $zendesk_subdomain; ?>" class="form-control crm-form-element-input">
            </div>
        </div>
        <div class="crm-form-element">
            <div class="col-md-12">
                <span class="help-block"><?php _e('Enter your Zendesk Username','wsdesk'); ?></span>
                <input type="text" id="zendesk_username" autocomplete="off" placeholder="<?php _e('Zendesk Username','wsdesk'); ?>" value="<?php echo $zendesk_username; ?>" class="form-control crm-form-element-input">
            </div>
        </div>
        <div class="crm-form-element">
            <div class="col-md-12">
                <span class="help-block"><?php _e('Want to download attachment locally?','wsdesk'); ?></span>
                    <span style="vertical-align: middle;">
                        <input type="radio" style="margin-top: 0;" id="download_attachment" class="form-control" name="download_attachment" value="yes"><?php _e('Yes! Download','wsdesk'); ?>
                        <input type="radio" style="margin-top: 0;" id="download_attachment" class="form-control" name="download_attachment" checked="" value="no"><?php _e(' No! I do not want','wsdesk'); ?><br>
                    </span>
            </div>
        </div>
    </div>
    <div class="crm-form-element">
        <div class="col-md-12">
            <span class="help-block"><?php _e('Pull Tickets to WSDesk','wsdesk'); ?></span>
            <button type="button" id="zendesk_pull_tickets" class="btn btn-primary btn-lg"><?php _e('Pull Tickets','wsdesk'); ?></button>
        </div>
    </div>
</center>
<?php
}
return ob_get_clean();