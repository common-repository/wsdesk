<?php
$user = (isset($_GET['user'])?$_GET['user']:"all");
$user_name = "";
$sol = eh_crm_get_settings(array("slug" => "label_LL02"), array("settings_id"));
$sol_col = eh_crm_get_settingsmeta($sol[0]['settings_id'], "label_color");
$color =array();
$donut_tags = eh_crm_generate_donut_values_tags();
if($user!="all")
{
    $user_check = get_user_by("ID",$user);
    if($user_check)
    {
        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
        $role = array_intersect($user_roles_default,$user_check->roles);
        if(!empty($role))
        {
            $user_name = $user_check->display_name;
            $bar = eh_crm_generate_bar_values($user);
            $donut_data = eh_crm_generate_donut_values($user);
            $donut = $donut_data['donut'];
            $color = $donut_data['color'];
            $lines = eh_crm_generate_line_values($user);
        }
        else
        {
            wp_die(sprintf('<center><h1>Oops !</h1><h4>User is not Having any WSDesk Role</h4><a href="'. admin_url("admin.php?page=wsdesk_reports").'">Back to Reports</a></center>'));
        }
    }
    else
    {
        wp_die(sprintf('<center><h1>Oops !</h1><h4>User not found</h4><a href="'. admin_url("admin.php?page=wsdesk_reports").'">Back to Reports</a></center>'));
    }
}
else
{
    $user_name = "All";
    $bar = eh_crm_generate_bar_values($user);
    $donut = eh_crm_generate_donut_values($user);
    $lines = eh_crm_generate_line_values($user);
}
?>
<div class="wsdesk_wrapper">
    <div class="container wrapper" id="reports_page_view">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default reports_panel">
                    <div class="panel-heading">
                        <h3 class="panel-title"><?php _e('WSDesk Reports', 'wsdesk'); ?></h3>
                    </div>
                    <div class="panel-body" id="reports_panel_body" style="text-align: center">
                        <span class="help-block"><?php _e('Select the Agents/Supervisors to show report', 'wsdesk'); ?></span>
                        <span style="vertical-align: middle;" id="ticket_field_edit_section">
                            <form method="GET">
                                <input type="hidden" name="page" id="page" value="wsdesk_reports">
                                <select id="user" name="user" style="display: inline !important;" class="form-control" aria-describedby="helpBlock">
                                    <option value="all" <?php echo ($user=="all")?"selected":"" ?> >All</option>
                                    <?php
                                        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
                                        $users = get_users(array("role__in" => $user_roles_default));
                                        $users_data =array();
                                        for ($i = 0; $i < count($users); $i++) {
                                            $current = $users[$i];
                                            $id = $current->ID;
                                            $user_data = new WP_User($id);
                                            $users_data[$i]['id'] = $id;
                                            $users_data[$i]['name'] = $user_data->display_name;
                                        }
                                        for($i=0;$i<count($users_data);$i++)
                                        {
                                            $selected = "selected";
                                            echo '<option value="'.$users_data[$i]["id"].'" '.(($user==$users_data[$i]["id"])?$selected:"").'>'.$users_data[$i]["name"].'</option>';
                                        }
                                    ?>
                                </select>
                                <br>
                                <br>
                                <select id="date" name="date" style="display: inline !important;" class="form-control" aria-describedby="helpBlock" disabled>
                                    <option value=""><?php _e('Last 7 Days','wsdesk');?></option>';
                                    <option value=""><?php _e('Last 30 Days','wsdesk');?></option>'
                                </select>
                                <span class="wsdesk_super"><?php _e('Premium','wsdesk');?></span>
                                <br>
                                <input type="submit" style="margin-top: 10px" class="btn btn-primary" value="<?php _e('Show Report', 'wsdesk'); ?>">
                            </form>
                            <span style="vertical-align: middle;" id="ticket_field_edit_append"></span>
                        </span>
                    </div>
                    <div class="row" style="padding-top: 10px;">
                        <div class="col-md-12">
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php _e('Last 7 Days status of new tickets :', 'wsdesk'); ?> <?php echo $user_name; ?> </h3>
                                    </div>
                                    <div class="panel-body">
                                        <div id="7days-ticket" style="height: 250px;"></div>
                                        <script>
                                            Morris.Bar({
                                                element: '7days-ticket',
                                                data: <?php echo json_encode($bar);?>,
                                                xkey: 'y',
                                                ykeys: ['a'],
                                                labels: ['Tickets', 'Date'],
                                                resize:true
                                            });
                                        </script>    
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php _e('Last 7 Days status of Agent Tickets :', 'wsdesk'); ?> <?php echo $user_name; ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div id="assignee-contribution" style="height: 250px;"></div>
                                                    <script>
                                                        Morris.Donut({
                                                            element: 'assignee-contribution',
                                                            data: <?php echo json_encode($donut);?>,
                                                            <?php echo ((!empty($color))?"colors: ".json_encode($color).",":"");?>
                                                            resize:true
                                                        });
                                                    </script>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="panel panel-default">
                                                    <table class="table table-hover">
                                                    <?php
                                                        for($i=0;$i<count($donut);$i++) {
                                                            if(!empty($color))
                                                            {
                                                                echo "<tr style='background:".$color[$i]."'>";
                                                            }
                                                            else
                                                            {
                                                                echo "<tr>";
                                                            }
                                                            echo "<td>".$donut[$i]['label']."</td>";
                                                            echo "<td>".$donut[$i]['value']."</td>";
                                                            echo "</tr>";
                                                        }
                                                    ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 10px;">
                        <div class="col-md-12">
                            <div class="col-md-12">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php _e('Last 7 Days status of New and Solved Tickets :', 'wsdesk'); ?> <?php echo $user_name; ?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <div id="new-solved-status" style="height: 250px;"></div>
                                        <script>
                                            Morris.Line({
                                                element: 'new-solved-status',
                                                data: <?php echo json_encode($lines);?>,
                                                xkey: 'y',
                                                ykeys: ['a', 'b'],
                                                lineColors: ['#0b62a4','<?php echo $sol_col;?>'],
                                                labels: ['New Tickets', 'Solved Tickets'],
                                                resize:true
                                            });
                                        </script>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="padding-top: 10px;">
                        <div class="col-md-6">
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h3 class="panel-title"><?php _e("Last 7 days Status of Tickets by Tags", 'wsdesk');?></h3>
                                    </div>
                                    <div class="panel-body">
                                        <div class="col-md-12">
                                            <div class="col-md-6">
                                                <div id="date_wise" style="height: 250px;"></div>
                                                    <script>
                                                        Morris.Donut({
                                                            element: 'date_wise',
                                                            data: <?php echo json_encode($donut_tags);?>,
                                                            resize:true
                                                        });
                                                    </script>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="panel panel-default">
                                                    <table class="table table-hover">
                                                    <?php
                                                        for($i=0;$i<count($donut_tags);$i++) {
                                                            if(!empty($color))
                                                            {
                                                                echo "<tr style='background:".$color[$i]."'>";
                                                            }
                                                            else
                                                            {
                                                                echo "<tr>";
                                                            }
                                                            echo "<td>".$donut_tags[$i]['label']."</td>";
                                                            echo "<td>".$donut_tags[$i]['value']."</td>";
                                                            echo "</tr>";
                                                        }
                                                    ?>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>