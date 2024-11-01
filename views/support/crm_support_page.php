<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();
$title= eh_crm_get_settingsmeta(0, 'main_ticket_form_title');
$existing= eh_crm_get_settingsmeta(0, 'existing_ticket_button');
$submit= eh_crm_get_settingsmeta(0, 'submit_ticket_button');
if(!$submit)
{
    $submit = __('Submit Request', 'wsdesk');
}
if(!$existing)
{
    $existing = __('Check your Existing Request', 'wsdesk');
}
?>
<div class="eh_crm_support_main wsdesk_wrapper">
    <div class="support_option_choose">
        <?php echo ($title!=='')?'<h3>'.$title.'</h3>':''; ?>
        <button data-loading-text="<?php _e('Fetching Request Form...', 'wsdesk'); ?>" class="btn btn-primary eh_crm_new_request">
            <?php echo  $submit ?>
        </button>
        <br>
        <br>
        <button data-loading-text="<?php _e('Loading your Request...', 'wsdesk'); ?>" class="btn btn-primary eh_crm_check_request">
            <?php echo $existing ?>
        </button>
    </div>
    <div class="ticket_table_wrapper">
    </div>
</div>
<?php
return ob_get_clean();