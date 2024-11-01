<div class="wsdesk_wrapper">
    <div class="container">
        <div class="row">
            <ol class="breadcrumb crm-panel-right" style="margin-left: 0 !important;">
                <li><?php _e('WSDesk E-Mail', 'wsdesk'); ?></li>
                <li id="breadcrump_section" class="active"><?php _e('Support Email', 'wsdesk'); ?></li>
            </ol>
            <div class="col-md-3 crm-panel-left">
                <div class="panel panel-default crm-panel">
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked" role="tablist">
                            <li class="active">
                                <a href="#email_support_tab" data-toggle="tab" class="email_support"><?php _e("Support Email - (Outgoing)", 'wsdesk'); ?></a>
                            </li>
                            <li>
                                <a href="#oauth_setup_tab" data-toggle="tab" class="oauth_setup"><?php _e("Google OAuth Setup - (Incoming)", 'wsdesk'); ?></a>
                            </li>
                            <li>
                                <a href="#imap_setup_tab" data-toggle="tab" class="imap_setup"><?php _e("IMAP EMail Setup - (Incoming)", 'wsdesk'); ?></a>
                            </li>
                            <li>
                                <a href="#filter_block_tab" data-toggle="tab" class="filter_block"><?php _e("EMail Filter & Block", 'wsdesk'); ?></a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="alert alert-success" style="display: none" role="alert">
                    <div id="success_alert_text"></div>
                </div>
                <div class="alert alert-danger" style="display: none" role="alert">
                    <div id="danger_alert_text"></div>
                </div>
            </div>
            <div class="col-xs-12 col-md-9">
                <div class="panel panel-default crm-panel">
                    <div class="panel-body" style="padding: 5px !important">
                        <div class="tab-content">
                            <div class="loader"></div>
                            <div class="tab-pane active" id="email_support_tab">
                                <?php echo include(EH_CRM_MAIN_VIEWS . "email/crm_email_support.php"); ?>
                            </div>
                            <div class="tab-pane" id="oauth_setup_tab">
                                <?php echo include(EH_CRM_MAIN_VIEWS . "email/crm_oauth_setup.php"); ?>
                            </div>
                            <div class="tab-pane" id="imap_setup_tab">
                                <?php echo include(EH_CRM_MAIN_VIEWS . "email/crm_imap_setup.php"); ?>
                            </div>
                            <div class="tab-pane" id="filter_block_tab">
                                <?php echo include(EH_CRM_MAIN_VIEWS . "email/crm_filter_block_setup.php"); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>