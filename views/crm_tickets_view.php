<?php
$default_deep_link = eh_crm_get_settingsmeta('0', "default_deep_link");
if($default_deep_link && !isset($_GET['view']) && !isset($_GET['order']))
{
    echo "<script>window.history.pushState('Tickets', 'Title', js_obj.ticket_admin_url+'&'+'".$default_deep_link."')</script>";
}
?>
<div class="wsdesk_wrapper">
    <div class="container wrapper" id="tickets_page_view">
        <div class="row">
            <div class="col-md-12">
                <div class="panel with-nav-tabs panel-default">
                    <div class="panel-heading col-md-12">
                        <ul class="nav nav-tabs col-md-11 elaborate" role="tablist">
                            <li class="active all_tickets" role="presentation">
                                <a href="#all_tickets_tab" aria-controls="all" style="text-align: center;padding: 13px 15px;margin-right:0px !important;" data-toggle="tab" class="tab_a">
                                    <?php _e('All Tickets', 'wsdesk'); ?>
                                </a>
                            </li>
                            <li role="presentation" class="dropdown"> 
                                <a href="#" id="myTabDrop1" class="dropdown-toggle tab_a" data-toggle="dropdown" aria-controls="myTabDrop1-contents" aria-expanded="true">
                                    <span class="caret"></span> <?php _e('More', 'wsdesk'); ?></a>
                                <ul class="dropdown-menu collapse_ul" aria-labelledby="myTabDrop1" id="myTabDrop1-contents"style="overflow-x: hidden">
                                </ul>
                            </li>
                        </ul>
                        <div class="col-md-1 nav-tabs">
                            <div class="pull-right side_bar">
                                <a href="#" class="add-ticket tab_a" style="text-align: center;" data-placement="bottom" data-toggle="wsdesk_tooltip" title="<?php _e('New Ticket', 'wsdesk'); ?>" data-container="body">
                                    <span class="glyphicon glyphicon-plus"></span>
                                </a>
                            </div>
                            <div class="pull-right">
                                <div class="input-group stylish-input-group" style="margin: 3px 5px;">
                                    <input type="text" class="form-control" id="search_ticket_input"  placeholder="<?php _e('Search', 'wsdesk'); ?>" data-placement="bottom" data-toggle="wsdesk_tooltip" title="<?php _e('Search Tickets', 'wsdesk'); ?>" data-container="body">
                                    <span class="glyphicon glyphicon-search clickable form-control-feedback" id="search_ticket_icon"></span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="alert alert-success" style="position: absolute;top: 10%;right: 5%;z-index: 999;display: none;" role="alert">
                                <div id="success_alert_text"></div>
                            </div>
                            <div class="tab-pane active" id="all_tickets_tab">
                                <?php echo include(EH_CRM_MAIN_VIEWS . "tickets/crm_tickets_all.php"); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>