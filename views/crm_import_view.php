<div class="wsdesk_wrapper">
    <div class="container">
        <div class="row">
            <ol class="breadcrumb crm-panel-right" style="margin-left: 0 !important;">
                <li><?php _e('Import Tickets', 'wsdesk'); ?></li>
                <li id="breadcrump_section" class="active"><?php _e('From Zendesk', 'wsdesk'); ?></li>
            </ol>
            <div class="col-md-3 crm-panel-left">
                <div class="panel panel-default crm-panel">
                    <div class="panel-body">
                        <ul class="nav nav-pills nav-stacked" role="tablist">
                            <li class="active">
                                <a href="#zendesk_import_tab" data-toggle="tab" class="zendesk_import"><?php _e('From Zendesk', 'wsdesk'); ?></a>
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
                            <div class="tab-pane active" id="zendesk_import_tab">
                                <?php echo include(EH_CRM_MAIN_VIEWS . "import/crm_zendesk_import.php"); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>