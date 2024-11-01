<?php
if (!defined('ABSPATH')) {
    exit;
}
ob_start();
$order = (isset($_GET['order']))?$_GET['order']:"DESC";
$avail_labels_wf = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
$avail_labels_f = eh_crm_get_settings(array("type" => "label", "filter" => "yes"), array("slug", "title", "settings_id"));
$avail_tags_wf = eh_crm_get_settings(array("type" => "tag"), array("slug", "title", "settings_id"));
$avail_tags_f = eh_crm_get_settings(array("type" => "tag", "filter" => "yes"), array("slug", "title", "settings_id"));
$user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
$user_caps_default = array("reply_tickets","delete_tickets","manage_tickets");
$users = get_users(array("role__in" => $user_roles_default));
$users_data = array();
for ($i = 0; $i < count($users); $i++) {
    $current = $users[$i];
    $id = $current->ID;
    $user = new WP_User($id);
    $users_data[$i]['id'] = $id;
    $users_data[$i]['name'] = $user->display_name;
    $users_data[$i]['caps'] = $user->caps;
    $users_data[$i]['email'] = $user->user_email;
}
$table_title = 'All Tickets';
$ticket_rows = eh_crm_get_settingsmeta(0, "ticket_rows");
$section_tickets_id = eh_crm_get_ticket_value_count("ticket_parent",0,false,"","","ticket_updated",$order,$ticket_rows,0);
$avail_caps = array("reply_tickets","delete_tickets","manage_tickets");
$access = array();
$logged_user = wp_get_current_user();
$logged_user_caps = array_keys($logged_user->caps);
if(!in_array("administrator", $logged_user->roles))
{
    for($i=0;$i<count($logged_user_caps);$i++)
    {
        if(!in_array($logged_user_caps[$i], $avail_caps))
        {
            unset($logged_user_caps[$i]);
        }
    }
    $access = $logged_user_caps;
}
else
{
    $access = $avail_caps;
}
$current_page = 0;
$current_page = 0;
if(!isset($_COOKIE['collapsed_views']))
{
    $collapsed_views = array();
}
else
{
    $collapsed_views = stripslashes($_COOKIE['collapsed_views']);
    $collapsed_views = str_replace('"', '', $collapsed_views);
    $collapsed_views = str_replace('[', '', $collapsed_views);
    $collapsed_views = str_replace(']', '', $collapsed_views);
    $collapsed_views = explode(",",$collapsed_views);
}
?>
<div class="container">
    <div class="row" style="margin-top: 10px;">
        <div class="col-md-3" id="left_bar_all_tickets" style="max-height: 100vh;overflow: auto;overflow-x: hidden;">
            <ul class="nav nav-pills nav-stacked side-bar-filter" id="all_section">
                <li class="active"><a href="#" id="all"><span class="badge pull-right"><?php echo count(eh_crm_get_ticket_value_count("ticket_parent",0)); ?></span> All Tickets </a></li>
            </ul>
            <hr>
            <h4>
                Status
                <?php
                $labels_collapsed = false;
                if(in_array('labels', $collapsed_views))
                    $labels_collapsed = true;
                ?>
                <span class="spinner_loader labels_loader">
                    <span class="bounce1"></span>
                    <span class="bounce2"></span>
                    <span class="bounce3"></span>
                </span>
                <span id="labels_collapse" class="glyphicon glyphicon-chevron-up" style="float:right; <?php echo ($labels_collapsed)?'display: none;':'';?>" onclick="collapse('labels');"></span>
                <span id="labels_drop" class="glyphicon glyphicon-chevron-down" style="float:right; <?php echo ($labels_collapsed)?'': 'display: none;';?>" onclick="drop('labels');"></span>
            </h4>
            <ul class="nav nav-pills nav-stacked side-bar-filter" id="labels" <?php echo ($labels_collapsed)?"style='display: none;'":"";?>>
                <?php
                    for ($i = 0; $i < count($avail_labels_f); $i++) {
                        $label_color = eh_crm_get_settingsmeta($avail_labels_f[$i]['settings_id'], "label_color");
                        $current_label_count=eh_crm_get_ticketmeta_value_count("ticket_label",$avail_labels_f[$i]['slug']);
                        echo '<li><a href="#" id="'.$avail_labels_f[$i]['slug'].'"><span class="badge pull-right" style="background-color:' . $label_color . ' !important;">'.count($current_label_count).'</span> '.$avail_labels_f[$i]['title'].' </a></li>';
                    }
                ?>
            </ul>
            <?php
            if(!empty($users_data))
            {
                ?>
                <hr>
                <h4>
                    Agents
                    <?php
                $agents_collapsed = false;
                if(in_array('agents', $collapsed_views))
                    $agents_collapsed = true;
                ?>
                    <span class="spinner_loader agents_loader">
                        <span class="bounce1"></span>
                        <span class="bounce2"></span>
                        <span class="bounce3"></span>
                    </span>
                    <span id="agents_collapse" class="glyphicon glyphicon-chevron-up" style="float:right; <?php echo ($agents_collapsed)?'display: none;':'';?>" onclick="collapse('agents');"></span>
                    <span id="agents_drop" class="glyphicon glyphicon-chevron-down" style="float:right; <?php echo ($agents_collapsed)?'': 'display: none;';?>" onclick="drop('agents');">
                </h4>
                <ul class="nav nav-pills nav-stacked side-bar-filter" id="agents" <?php echo ($agents_collapsed)?"style='display: none;'":"";?>>
                    <?php
                        for ($i = 0; $i < count($users_data); $i++) {
                            $current_agent_count=eh_crm_get_ticketmeta_value_count("ticket_assignee",$users_data[$i]['id']);
                            echo '<li><a href="#" id="'.$users_data[$i]['id'].'"><span class="badge pull-right">'.count($current_agent_count).'</span> '.$users_data[$i]['name'].' </a></li>';
                        }
                        $current_agent_count=eh_crm_get_ticketmeta_value_count("ticket_assignee",array());
                    ?>
                    <li><a href="#" id="unassigned"><span class="badge pull-right"><?php echo count($current_agent_count);?></span> <?php _e('Unassigned', 'wsdesk'); ?> </a></li>
                </ul>
                <?php 
            }
            ?>
            <?php
            if(!empty($avail_tags_f))
            {
                ?>
                <hr>
                <h4>
                    Tags
                    <?php
                    $tags_collapsed = false;
                    if(in_array('tags', $collapsed_views))
                        $tags_collapsed = true;
                    ?>
                    <span class="spinner_loader tags_loader">
                        <span class="bounce1"></span>
                        <span class="bounce2"></span>
                        <span class="bounce3"></span>
                    </span>
                    <span id="tags_collapse" class="glyphicon glyphicon-chevron-up" style="float:right; <?php echo ($tags_collapsed)?'display: none;':'';?>" onclick="collapse('tags');"></span>
                    <span id="tags_drop" class="glyphicon glyphicon-chevron-down" style="float:right; <?php echo ($tags_collapsed)?'': 'display: none;';?>" onclick="drop('tags');">
                </h4>
                <ul class="nav nav-pills nav-stacked side-bar-filter" id="tags" <?php echo ($tags_collapsed)?"style='display: none;'":"";?>>
                    <?php
                        for ($i = 0; $i < count($avail_tags_f); $i++) {
                            $current_tags_count=eh_crm_get_ticketmeta_value_count("ticket_tags",$avail_tags_f[$i]['slug']);
                            echo '<li><a href="#" id="'.$avail_tags_f[$i]['slug'].'"><span class="badge pull-right">'.count($current_tags_count).'</span> '.$avail_tags_f[$i]['title'].' </a></li>';
                        }
                    ?>
                </ul>
                <?php 
            }
            ?>
            <h4>
                Users
                <?php
                $users_collapsed = false;
                if(in_array('users', $collapsed_views))
                    $users_collapsed = true;
                ?>
                <span class="spinner_loader users_loader">
                    <span class="bounce1"></span>
                    <span class="bounce2"></span>
                    <span class="bounce3"></span>
                </span>
                <span id="users_collapse" class="glyphicon glyphicon-chevron-up" style="float:right; <?php echo ($users_collapsed)?'display: none;':'';?>" onclick="collapse('users');"></span>
                <span id="users_drop" class="glyphicon glyphicon-chevron-down" style="float:right; <?php echo ($users_collapsed)?'': 'display: none;';?>" onclick="drop('users');">
            </h4>
            <ul class="nav nav-pills nav-stacked side-bar-filter" id="users" <?php echo ($users_collapsed)?"style='display: none;'":"";?>>
                <?php
                    $registered_count = eh_crm_get_ticket_value_count("ticket_author",0,true,"ticket_parent",0);
                    echo '<li><a href="#" id="registeredU" class="user_section"><span class="badge pull-right">'.count($registered_count).'</span> Registered Users </a></li>';
                    $guest_count = eh_crm_get_ticket_value_count("ticket_author",0,false,"ticket_parent",0);
                    echo '<li><a href="#" id="guestU" class="user_section"><span class="badge pull-right">'.count($guest_count).'</span> Guest Users </a></li>';
                ?>
            </ul>
        </div>
        <div class="col-md-9" style="padding-right:0px;">
            <div class="full_row filter_div" id="dev-table-action-bar" >
                <div class="filter-each"><input type="checkbox" class="ticket_select_all"></div>
                <div class="filter-each" id="refresh_tickets">
                    <div  class="ticket-refresh-button" data-placement="top" data-toggle="wsdesk_tooltip" title="Refresh">
                        <span class="glyphicon glyphicon-refresh"></span>
                    </div>
                </div>
                <div class="btn-group filter-each" style="padding: 0px; width: 100px; height: 35px;">
                <button type="button" class="btn btn-default dropdown-toggle mulitple_ticket_action_button select-full select-btn" data-toggle="dropdown">
                    Actions <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" role="menu">
                    <?php
                        if(in_array("manage_tickets", $access) || in_array("delete_tickets", $access))
                        {
                            if(in_array("manage_tickets", $access))
                            {
                                for($j=0;$j<count($avail_labels_wf);$j++)
                                {
                                    echo '<li><a href="#" class="multiple_ticket_action" id="'.$avail_labels_wf[$j]['slug'].'">Mark as '.$avail_labels_wf[$j]['title'].'</a></li>';
                                }
                            }
                        }
                        else
                        {
                            echo '<li style="padding: 3px 20px;">'.__('No Actions', 'wsdesk').'</li>';
                        }
                    ?>
                </ul>
                </div>
                <?php
                    if(in_array("delete_tickets", $access))
                    {
                        echo '<div class="filter-each ticket-delete-btn multiple_ticket_action" id="delete_tickets" style="display: none;"><div  class="ticket-delete-button" data-placement="top" data-toggle="wsdesk_tooltip" title="Delete Tickets"><span class="glyphicon glyphicon-trash"></span></div></div>';
                    }
                ?>
                </div>
        </div>
        <div class="col-md-9" id="right_bar_all_tickets" style="overflow:scroll; padding-right: 0px;">
            <div class="panel panel-default tickets_panel">
                <div class="panel-heading">
                    <h3 class="panel-title"><?php echo $table_title;?>
                        <span class="spinner_loader table_loader">
                            <span class="bounce1"></span>
                            <span class="bounce2"></span>
                            <span class="bounce3"></span>
                        </span>
                    </h3>
                    <div class="pull-right">
                        <span class="clickable filter" data-toggle="wsdesk_tooltip" title="<?php _e('Tickets Filter', 'wsdesk'); ?>" data-container="body">
                            <i class="glyphicon glyphicon-filter"></i>
                        </span>
                    </div>
                    <div class="pull-right" style="margin: -15px 0px 0px 0px;">
                        <span class="text-muted"><b><?php echo ($current_page!=0)?($current_page)*$ticket_rows:"1"; ?></b>–<b><?php echo (($current_page)*$ticket_rows)+count($section_tickets_id);?></b> of <b><?php echo count(eh_crm_get_ticket_value_count("ticket_parent",0)); ?></b></span>
                        <div class="btn-group btn-group-sm">
                            <?php
                                    if($current_page != 0)
                                    {
                                        ?>
                                            <button type="button"  class="btn btn-default pagination_tickets" id="prev" title="<?php _e('Previous', 'wsdesk'); ?> <?php echo $ticket_rows?>" data-container="body">
                                                <span class="glyphicon glyphicon-chevron-left"></span>
                                            </button>
                                        <?php
                                    }
                            ?>                        
                            <input type="hidden" id="current_page_no" value="<?php echo $current_page ?>">
                            <?php 
                                    if(count($section_tickets_id) == $ticket_rows)
                                    {
                                        ?>
                                            <button type="button"  class="btn btn-default pagination_tickets" id="next" title="<?php _e('Next', 'wsdesk'); ?> <?php echo $ticket_rows?>" data-container="body">
                                                <span class="glyphicon glyphicon-chevron-right"></span>
                                            </button>
                                        <?php
                                    }
                            ?>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <input type="text" class="form-control" id="dev-table-filter" data-action="filter" data-filters="#dev-table" placeholder="<?php _e('Filter Tickets', 'wsdesk'); ?>" />
                </div>
                <table class="table table-hover" id="dev-table">
                    <thead>
                        <tr class="except_view">
                            <th></th>
                            <th><?php _e('View', 'wsdesk'); ?></th>
                            <th>#</th>
                            <th><?php _e('Requester', 'wsdesk'); ?></th>
                            <th><?php _e('Subject', 'wsdesk'); ?></th>
                            <th><?php _e('Requested', 'wsdesk'); ?></th>
                            <th><?php _e('Assignee', 'wsdesk'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(empty($section_tickets_id))
                            {
                                echo '<tr class="except_view">
                                    <td colspan="12">'.__('No Tickets', 'wsdesk').'</td></tr>';
                            }
                            else
                            {
                                for($i=0;$i<count($section_tickets_id);$i++)
                                {
                                    $current = eh_crm_get_ticket(array("ticket_id"=>$section_tickets_id[$i]['ticket_id']));
                                    $current_meta = eh_crm_get_ticketmeta($section_tickets_id[$i]['ticket_id']);
                                    $action_value = '';
                                    $eye_color='';
                                    for($j=0;$j<count($avail_labels_wf);$j++)
                                    {
                                        if(in_array("manage_tickets", $access))
                                        {
                                            $action_value .= '<li id="'.$current[0]['ticket_id'].'"><a href="#" class="single_ticket_action" id="'.$avail_labels_wf[$j]['slug'].'">Mark as '.$avail_labels_wf[$j]['title'].'</a></li>';

                                        }
                                        if($avail_labels_wf[$j]['slug'] == $current_meta['ticket_label'])
                                        {
                                            $eye_color = eh_crm_get_settingsmeta($avail_labels_wf[$j]['settings_id'], "label_color");
                                        }
                                    }
                                    $ticket_raiser = $current[0]['ticket_email'];
                                    if($current[0]['ticket_author'] != 0)
                                    {
                                        $current_user = new WP_User($current[0]['ticket_author']);
                                        $ticket_raiser = $current_user->display_name;
                                    }
                                    $ticket_assignee_name =array();
                                    $ticket_assignee_email = array();
                                    if(isset($current_meta['ticket_assignee']))
                                    {
                                        $current_assignee = $current_meta['ticket_assignee'];
                                        for($k=0;$k<count($current_assignee);$k++)
                                        {
                                            for($l=0;$l<count($users_data);$l++)
                                            {
                                                if($users_data[$l]['id'] == $current_assignee[$k])
                                                {
                                                    array_push($ticket_assignee_name, $users_data[$l]['name']);
                                                    array_push($ticket_assignee_email, $users_data[$l]['email']);
                                                }
                                            }
                                        }
                                    }
                                    $ticket_assignee_name = empty($ticket_assignee_name)?"No Assignee":implode(", ", $ticket_assignee_name);
                                    $latest_reply_id = eh_crm_get_ticket_value_count("ticket_category","agent_note" ,true,"ticket_parent",$current[0]['ticket_id'],'ticket_id','DESC','1');
                                    $latest_content = array();
                                    $attach = "";
                                    if(!empty($latest_reply_id))
                                    {
                                        $latest_ticket_reply = eh_crm_get_ticket(array("ticket_id"=>$latest_reply_id[0]["ticket_id"]));
                                        $input_data = html_entity_decode(stripslashes($latest_ticket_reply[0]['ticket_content']));
                                        $input_array[0] = '/<((html)[^>]*)>(.*)\<\/(html)>/Us';
                                        $input_array[1] = '/<((head)[^>]*)>(.*)\<\/(head)>/Us';
                                        $input_array[2] = '/<((style)[^>]*)>(.*)\<\/(style)>/Us';
                                        $input_array[3] = '/<((body)[^>]*)>(.*)\<\/(body)>/Us';
                                        $input_array[4] = '/<((form)[^>]*)>(.*)\<\/(form)>/Us';
                                        $input_array[5] = '/<((input)[^>]*)>(.*)\<\/(input)>/Us';
                                        $input_array[7] = '/<((input)[^>]*)>/Us';
                                        $input_array[6] = '/<((button)[^>]*)>(.*)\<\/(button)>/Us';
                                        $input_array[8] = '/<((script)[^>]*)>(.*)\<\/(script)>/Us';
                                        $output_array[0] = '&lt;$1&gt;$3&lt;/html&gt;';
                                        $output_array[1] = '&lt;$1&gt;$3&lt;/head&gt;';
                                        $output_array[2] = '&lt;$1&gt;$3&lt;/style&gt;';
                                        $output_array[3] = '&lt;$1&gt;$3&lt;/body&gt;';
                                        $output_array[4] = '&lt;$1&gt;$3&lt;/form&gt;';
                                        $output_array[5] = '&lt;$1&gt;$3&lt;/input&gt;';
                                        $output_array[6] = '&lt;$1&gt;$3&lt;/button&gt;';
                                        $output_array[7] = '&lt;$1&gt;$3&lt;/input&gt;';
                                        $output_array[8] = '&lt;$1&gt;$3&lt;/script&gt;';
                                        $latest_content['content'] = preg_replace($input_array, $output_array, $input_data); 
                                        $latest_content['author_email'] = $latest_ticket_reply[0]['ticket_email'];
                                        $latest_content['reply_date'] = $latest_ticket_reply[0]['ticket_date'];
                                        if($latest_ticket_reply[0]['ticket_author'] != 0)
                                        {
                                            $reply_user = new WP_User($latest_ticket_reply[0]['ticket_author']);
                                            $latest_content['author_name'] = $reply_user->display_name;
                                        }
                                        else
                                        {
                                            $latest_content['author_name'] = "Guest";
                                        }
                                        $latest_reply_meta = eh_crm_get_ticketmeta($latest_reply_id[0]["ticket_id"]);
                                        if(isset($latest_reply_meta['ticket_attachment']))
                                        {
                                            $attach = ' | <small class="glyphicon glyphicon-pushpin"></small> <small style="opacity:0.7;"> '.count($latest_reply_meta['ticket_attachment']).' Attachment</small>';
                                        }
                                    }
                                    else
                                    {
                                        $input_data = html_entity_decode(stripslashes($current[0]['ticket_content']));
                                        $input_array[0] = '/<((html)[^>]*)>(.*)\<\/(html)>/Us';
                                        $input_array[1] = '/<((head)[^>]*)>(.*)\<\/(head)>/Us';
                                        $input_array[2] = '/<((style)[^>]*)>(.*)\<\/(style)>/Us';
                                        $input_array[3] = '/<((body)[^>]*)>(.*)\<\/(body)>/Us';
                                        $input_array[4] = '/<((form)[^>]*)>(.*)\<\/(form)>/Us';
                                        $input_array[5] = '/<((input)[^>]*)>(.*)\<\/(input)>/Us';
                                        $input_array[7] = '/<((input)[^>]*)>/Us';
                                        $input_array[6] = '/<((button)[^>]*)>(.*)\<\/(button)>/Us';
                                        $input_array[8] = '/<((script)[^>]*)>(.*)\<\/(script)>/Us';
                                        $output_array[0] = '&lt;$1&gt;$3&lt;/html&gt;';
                                        $output_array[1] = '&lt;$1&gt;$3&lt;/head&gt;';
                                        $output_array[2] = '&lt;$1&gt;$3&lt;/style&gt;';
                                        $output_array[3] = '&lt;$1&gt;$3&lt;/body&gt;';
                                        $output_array[4] = '&lt;$1&gt;$3&lt;/form&gt;';
                                        $output_array[5] = '&lt;$1&gt;$3&lt;/input&gt;';
                                        $output_array[6] = '&lt;$1&gt;$3&lt;/button&gt;';
                                        $output_array[7] = '&lt;$1&gt;$3&lt;/input&gt;';
                                        $output_array[8] = '&lt;$1&gt;$3&lt;/script&gt;';
                                        $latest_content['content'] = preg_replace($input_array, $output_array, $input_data); 
                                        $latest_content['author_email'] = $current[0]['ticket_email'];
                                        $latest_content['reply_date'] = $current[0]['ticket_date'];
                                        if($current[0]['ticket_author'] != 0)
                                        {
                                            $current_user = new WP_User($current[0]['ticket_author']);
                                            $latest_content['author_name'] = $current_user->display_name;
                                        }
                                        else
                                        {
                                            $latest_content['author_name'] = "Guest";
                                        }
                                        if(isset($current_meta['ticket_attachment']))
                                        {
                                            $attach = ' | <small class="glyphicon glyphicon-pushpin"></small> <small style="opacity:0.7;"> '.count($current_meta['ticket_attachment']).' Attachment</small>';
                                        }
                                    }
                                    $ticket_tags = "";
                                    if(!empty($avail_tags_wf))
                                    {
                                        for($j=0;$j<count($avail_tags_wf);$j++)
                                        {
                                            $current_ticket_tags=(isset($current_meta['ticket_tags'])?$current_meta['ticket_tags']:array());
                                            for($k=0;$k<count($current_ticket_tags);$k++)
                                            {
                                                if($avail_tags_wf[$j]['slug'] == $current_ticket_tags[$k])
                                                {
                                                    $ticket_tags .= '<span class="label label-info">#'.$avail_tags_wf[$j]['title'].'</span>';
                                                }
                                            }
                                        }
                                    }
                                    $ticket_rating = (isset($current_meta['ticket_rating'])?$current_meta['ticket_rating']:0);
                                    $raiser_voice = eh_crm_get_ticket_value_count("ticket_parent",$section_tickets_id[$i]['ticket_id'],false,"ticket_category","raiser_reply");
                                    $agent_voice = eh_crm_get_ticket_value_count("ticket_parent",$section_tickets_id[$i]['ticket_id'],false,"ticket_category","agent_reply");
                                    echo '
                                    <tr class="clickable ticket_row" id="'.$current[0]['ticket_id'].'">
                                        <td class="except_view"><input type="checkbox" class="ticket_select" id="ticket_select" value="'.$current[0]['ticket_id'].'"></td>
                                        <td class="except_view"><button class="btn btn-default btn-xs accordion-toggle quick_view_ticket" style="background-color: '.$eye_color.' !important" data-toggle="collapse" data-target="#expand_'.$current[0]['ticket_id'].'" ><span class="glyphicon glyphicon-eye-open"></span></button></td>
                                        <td>'.$current[0]['ticket_id'].'</td>
                                        <td>'.$ticket_raiser.'</td>
                                        <td class="wrap_content" data-toggle="wsdesk_tooltip" title="'.$current[0]['ticket_title'].'" data-container="body">'.$current[0]['ticket_title'].'</td>
                                        <td>'.eh_crm_get_formatted_date($current[0]['ticket_date']).'</td>
                                        <td>'.$ticket_assignee_name.'</td>
                                    </tr>
                                    <tr class="except_view">
                                        <td colspan="12" class="hiddenRow">
                                            <div class="accordian-body collapse" id="expand_'.$current[0]['ticket_id'].'">
                                                <table class="table table-striped" style="margin-bottom: 0px !important">
                                                    <thead>
                                                        <tr>
                                                            <td colspan="12" style="white-space: normal;">
                                                            <div style="padding:5px 0px;">
                                                                <small class="glyphicon glyphicon-user"></small> <small style="opacity:0.7;">'.$latest_content['author_name'].'</small>
                                                                | <small class="glyphicon glyphicon-envelope"></small> <small style="opacity:0.7;">'.$latest_content['author_email'].'</small>
                                                                | <small class="glyphicon glyphicon-calendar"></small> <small style="opacity:0.7;">'. eh_crm_get_formatted_date($latest_content['reply_date']).'</small>
                                                                '.$attach.'
                                                            </div>
                                                            <hr>
                                                            <p>
                                                                '.preg_replace("~[[:alpha:]]+://[^<>[:space:]]+[[:alnum:]/]~","<a href=\"\\0\" title='\\0' target='_blank'>\\0</a>", stripslashes($latest_content['content'])).'
                                                            </p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>'.__('Actions', 'wsdesk').'</th>
                                                            <th>'.__('Assignee','wsdesk').
                                                            '<span class="wsdesk_super">'.__('Premium','wsdesk').'</span>
                                                            </th>
                                                            <th>'.__('Reply Requester', 'wsdesk').'</th>
                                                            <th>'.__('Raiser Voices', 'wsdesk').'</th>
                                                            <th>'.__('Agent Voices', 'wsdesk').'</th>
                                                            <th>'.__('Tags', 'wsdesk').'</th>
                                                            <th>'.__('Rating', 'wsdesk').'</th>
                                                            <th>'.__('Source', 'wsdesk').'</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>
                                                                <div class="btn-group">
                                                                    <button type="button" class="btn btn-default dropdown-toggle single_ticket_action_button_'.$current[0]['ticket_id'].'" data-toggle="dropdown">
                                                                        Actions <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu" role="menu">
                                                                        '.(($action_value != "")?$action_value:'<li style="padding: 3px 20px;">No Actions</li>').'
                                                                        <li class="divider"></li>
                                                                        <li class="text-center">
                                                                            <small class="text-muted">
                                                                                Select label to assign
                                                                            </small>
                                                                        </li>
                                                                    </ul>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <a href="#reply_'.$current[0]['ticket_id'].'" data-toggle="modal"  title="Compose Reply">
                                                                    '.$current[0]['ticket_email'].'
                                                                </a>
                                                            </td>
                                                            <td>'.count($raiser_voice).'</td>
                                                            <td>'.count($agent_voice).'</td>
                                                            <td>'.(($ticket_tags!="")?$ticket_tags:"No Tags").'</td>
                                                            <td>'.$ticket_rating.'</td>
                                                            <td>'.((isset($current_meta['ticket_source']))?$current_meta['ticket_source']:"").'</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <!-- Modal -->
                                                <div aria-hidden="true" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" id="reply_'.$current[0]['ticket_id'].'" class="modal fade" style="display: none;">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header">
                                                                <button aria-hidden="true" data-dismiss="modal" class="close" type="button">×</button>
                                                                <h4 class="modal-title">Compose Reply</h4>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p style="margin-top: 5px;font-size: 16px;">
                                                                ';  
                                                                if(in_array("manage_tickets", $access))
                                                                {
                                                                    echo '<input type="text" value="'.$current[0]['ticket_title'].'" id="direct_ticket_title_'.$current[0]['ticket_id'].'" class="ticket_title_editable">';
                                                                }
                                                                else
                                                                {
                                                                    echo $current[0]['ticket_title'];
                                                                }
                                                                if(in_array("reply_tickets",$access))
                                                                {
                                                                    ?>
                                                                    </p>
                                                                    <div class="row" style="margin-bottom: 20px;">
                                                                        <div class="col-md-12">
                                                                            <div class="widget-area no-padding blank">
                                                                                <div class="status-upload">
                                                                                    <?php wp_nonce_field('ajax_crm_nonce', 'direct_security'.$current[0]['ticket_id']); ?>
                                                                                    <textarea rows="10" cols="30" class="form-control direct_reply_textarea" id="direct_reply_textarea_<?php echo $current[0]['ticket_id']; ?>" name="reply_textarea_<?php echo $current[0]['ticket_id']; ?>"></textarea> 
                                                                                    <div class="form-group">
                                                                                        <div class="input-group col-md-12">
                                                                                            <span class="btn btn-send fileinput-button">
                                                                                                <i class="glyphicon glyphicon-plus"></i>
                                                                                                <span><?php _e('Attachment', 'wsdesk'); ?></span>
                                                                                                <input type="file" name="direct_files" id="direct_files_<?php echo $current[0]['ticket_id']; ?>" class="direct_attachment_reply" multiple="">
                                                                                            </span>
                                                                                            <div class="btn-group pull-right">
                                                                                                <button type="button" class="btn btn-send dropdown-toggle direct_ticket_reply_action_button_<?php echo $current[0]['ticket_id']; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                                  <?php _e('Submit as', 'wsdesk'); ?> <span class="caret"></span>
                                                                                                </button>
                                                                                                <ul class="dropdown-menu">
                                                                                                    <?php
                                                                                                        if(in_array("manage_tickets", $access))
                                                                                                        {
                                                                                                            for($j=0;$j<count($avail_labels_wf);$j++)
                                                                                                            {
                                                                                                                echo '<li id="'.$current[0]['ticket_id'].'"><a href="#" class="direct_ticket_reply_action" id="'.$avail_labels_wf[$j]['slug'].'">Submit as '.$avail_labels_wf[$j]['title'].'</a></li>';
                                                                                                            }
                                                                                                        }
                                                                                                        else
                                                                                                        {
                                                                                                            echo '<li id="'.$current[0]['ticket_id'].'"><a href="#" class="direct_ticket_reply_action" id="'.$ticket_label_slug.'">Submit as '.$ticket_label.'</a></li>';
                                                                                                        }
                                                                                                    ?>
                                                                                                    <li role="separator" class="divider"></li>
                                                                                                    <li id="<?php echo $current[0]['ticket_id'];?>"><a href="#" class="direct_ticket_reply_action" id="note"><?php _e('Submit as Note', 'wsdesk'); ?></a></li>
                                                                                                    <li class="text-center"><small class="text-muted"><?php _e('Notes visible to Agents and Supervisors', 'wsdesk'); ?></small></li>
                                                                                                </ul>
                                                                                              </div>
                                                                                        </div>
                                                                                        <div class="direct_upload_preview_files_<?php echo $current[0]['ticket_id'];?>"></div>
                                                                                    </div>
                                                                                </div><!-- Status Upload  -->
                                                                            </div><!-- Widget Area -->
                                                                        </div>
                                                                    </div>
                                                                    <?php
                                                                }
                                                                else
                                                                {
                                                                    echo "<p>".__('You do not have permisson to reply this ticket', 'wsdesk')."</p>";
                                                                }
                                                            echo'
                                                        </div><!-- /.modal-content -->
                                                    </div><!-- /.modal-dialog -->
                                                </div><!-- /.modal -->
                                            </div>
                                        </td>
                                    </tr>
                                    ';
                                }
                            }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>deepview();</script>
<?php
return ob_get_clean();