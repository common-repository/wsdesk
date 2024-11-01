<?php

if (!defined('ABSPATH')) {
    exit;
}

function eh_crm_write_log($body)
{
    $upload = wp_upload_dir();
    $fp = fopen($upload['path']."/wsdesk_import", "w+");
    fwrite($fp, date("M d, Y h:i:s A",time())." : ".$body);
    fclose($fp);
}

function eh_random_slug_generate($size) {
    $alpha_key = '';
    $keys = range('A', 'Z');
    for ($i = 0; $i < 2; $i++) {
        $alpha_key .= $keys[array_rand($keys)];
    }
    $length = $size - 2;
    $key = '';
    $keys = range(0, 9);
    for ($i = 0; $i < $length; $i++) {
        $key .= $keys[array_rand($keys)];
    }
    return $alpha_key . $key;
}

/**
 * Insert Data into wsdesk_settings table.
 *
 *
 * @param array $args (title,filter,type,vendor)
 * @param array $meta (meta_key,meta_value) | Optional
 * @return int The Settings ID on success. The value 0 or false on failure.
 */
function eh_crm_insert_settings($args, $meta = NULL) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settings';
    $defaults = array(
        'title' => '',
        'filter' => 'no',
        'type' => '',
        'vendor' => ''
    );
    $data = wp_parse_args($args, $defaults);
    $slug_check = true;
    do {
        $data['slug'] = $data['type'] . '_' . eh_random_slug_generate(4);
        if (!$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE slug = '".$data['slug']."'")) {
            $slug_check = false;
        }
    } while ($slug_check);
    $result = $wpdb->insert($table, $data);
    $settings_id = (int) $wpdb->insert_id;
    if ($meta !== NULL) {
        foreach ($meta as $key => $value) {
            eh_crm_insert_settingsmeta($settings_id, $key, $value);
        }
    }
    if (!$result) {
        return false;
    }
    return $settings_id;
}

/**
 * Update Existing Data into wsdesk_settings table.
 *
 *
 * @param int|string $id Corresponding Settings ID that needs to be updated
 * @param array $data (title,type,filter,vendor)
 * @return bool True on success. False on failure.
 */
function eh_crm_update_settings($id, $data) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settings';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE settings_id = %d", (int) $id))) {
        return false;
    }
    $where = array('settings_id' => (int) $id);
    $result = $wpdb->update($table, $data, $where);
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Delete Existing Data from wsdesk_settings table.
 *
 *
 * @param int|string $id Corresponding Settings ID that needs to be deleted
 * @return bool True on success. False on failure.
 */
function eh_crm_delete_settings($id) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settings';
    $table_meta = $wpdb->prefix . 'wsdesk_settingsmeta';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE settings_id = %d", (int) $id))) {
        return false;
    }
    $query = "DELETE FROM $table WHERE settings_id = $id";
    $result = $wpdb->query($query);
    $meta_query = "DELETE FROM $table_meta WHERE settings_id = $id";
    $wpdb->query($meta_query);
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Get Existing Data into wsdesk_settings table.
 *
 *
 * @param array $args (type,vendor,settings_id,slug,title,filter) | Filter By column and value Eg: array('settings_id'=>4)
 * @param array|string $fields (type,vendor,settings_id,slug,title,filter) | Optional (Fields to return)
 * @return array Filtered or provided ID data as key value pair
 */
function eh_crm_get_settings($args, $fields = NULL) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settings';
    $query_search = '';
    $query_field = '';
    if ($fields !== NULL) {
        if (is_array($fields)) {
            $query_field = implode(',', $fields);
        } else {
            $query_field = $fields;
        }
    } else {
        $query_field = '*';
    }
    if (is_array($args)) {
        $a = 1;
        foreach ($args as $key => $value) {
            switch ($key) {
                case 'type':
                    $query_search .= "type = '" . $args['type'] . "'";
                    break;
                case 'vendor':
                    $query_search .= "vendor = '" . $args['vendor'] . "'";
                    break;
                case 'settings_id':
                    $query_search .= "settings_id = " . (int) $args['settings_id'];
                    break;
                case 'slug':
                    $query_search .= "slug = '" . $args['slug'] . "'";
                    break;
                case 'title':
                    $query_search .= "title = '" . $args['title'] . "'";
                    break;
                case 'filter':
                    $query_search .= "filter = '" . $args['filter'] . "'";
                    break;
                default:
                    break;
            }
            if ($a < count(array_keys($args))) {
                $query_search .= " AND ";
                $a++;
            }
        }
    }
    $query = "select $query_field from $table WHERE $query_search";
    $data = $wpdb->get_results($query, ARRAY_A);
    if (!$data) {
        return false;
    }
    return $data;
}

/**
 * Insert Data into wsdesk_settingsmeta table.
 *
 *
 * @param int|string $id settings ID
 * @param string $key meta key
 * @param string $value meta value
 * @return bool True on success. False on failure.
 */
function eh_crm_insert_settingsmeta($id, $key, $value) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settingsmeta';
    if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE meta_key = %s AND settings_id = %d", $key, (int) $id))) {
        return false;
    }
    if (is_array($value)) {
        $data = serialize($value);
    } else {
        $data = $value;
    }
    $result = $wpdb->insert($table, array(
        'settings_id' => (int) $id,
        'meta_key' => $key,
        'meta_value' => $data
    ));
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Update Data into wsdesk_settingsmeta table.
 *
 *
 * @param int|string $id settings ID
 * @param string $key meta key
 * @param string $value meta value
 * @return bool True on success. False on failure.
 */
function eh_crm_update_settingsmeta($id, $key, $value) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settingsmeta';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE meta_key = %s AND settings_id = %d", $key, (int) $id))) {
        eh_crm_insert_settingsmeta($id, $key, $value);
    }
    $where = array('settings_id' => (int) $id, 'meta_key' => $key);
    if (is_array($value)) {
        $data = array("meta_value" => serialize($value));
    } else {
        $data = array("meta_value" => $value);
    }
    $result = $wpdb->update($table, $data, $where);
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Delete Data from wsdesk_settingsmeta table.
 *
 *
 * @param int|string $id settings ID
 * @param string $key meta key
 * @return bool True on success. False on failure.
 */
function eh_crm_delete_settingsmeta($id, $key) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settingsmeta';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE meta_key = %s AND settings_id = %d", $key, (int) $id))) {
        return false;
    }
    $query = "DELETE FROM $table WHERE settings_id = $id AND meta_key = '$key'";
    $result = $wpdb->query($query);
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Delete Data from wsdesk_settingsmeta table.
 *
 *
 * @param int|string $id settings ID
 * @param string $key meta key | Optional 
 * @return mixed ( If provided will return particular value or will return array of all meta of settings ID)
 */
function eh_crm_get_settingsmeta($id, $key = NULL) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_settingsmeta';
    $meta_key = '';
    $retrived = array();
    if ($key !== NULL) {
        $meta_key = " AND meta_key = '" . $key . "'";
    }
    $data = $wpdb->get_results($wpdb->prepare("SELECT meta_key,meta_value FROM $table WHERE settings_id = %d" . $meta_key, (int) $id), ARRAY_A);
    if (!$data) {
        return false;
    }
    if ($key !== NULL) {
        return is_serialized($data[0]['meta_value']) ? unserialize($data[0]['meta_value']) : $data[0]['meta_value'];
    }
    for ($i = 0; $i < count($data); $i++) {
        $retrived[$data[$i]['meta_key']] = is_serialized($data[$i]['meta_value']) ? unserialize($data[$i]['meta_value']) : $data[$i]['meta_value'];
    }
    return is_serialized($retrived) ? unserialize($retrived) : $retrived;
}

function eh_crm_get_poweredby_scripts()
{
    return' <br>
        <div class="powered_wsdesk"><span>Email is a service from '. get_bloginfo('name').'.</span><span> Powered by WSDesk</span></div>
        ';
}

/**
 * Insert Data into wsdesk_tickets table.
 *
 *
 * @param array $args (ticket_title,ticket_email,ticket_content,ticket_category,ticket_vendor)
 * @param array $meta (meta_key,meta_value) | Optional
 * @return int The Tickets ID on success. The value 0 or false on failure.
 */
function eh_crm_insert_ticket($args, $meta = NULL) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $defaults = array(
        'ticket_author' => (is_user_logged_in()) ? get_current_user_id() : 0,
        'ticket_date' => gmdate("M d, Y h:i:s A"),
        'ticket_updated' => current_time('mysql'),
        'ticket_email' => '',
        'ticket_title' => '',
        'ticket_content' => '',
        'ticket_category' => '',
        'ticket_vendor' => ''
    );
    $data = wp_parse_args($args, $defaults);
    if(isset($data['ticket_parent']))
    {
        eh_crm_update_ticket($data['ticket_parent'],array("ticket_updated" => current_time('mysql')));
    }
    $result = $wpdb->insert($table, $data);
    $ticket_id = (int) $wpdb->insert_id;
    if ($meta !== NULL) {
        foreach ($meta as $key => $value) {
            eh_crm_insert_ticketmeta($ticket_id, $key, $value);
        }
    }
    if (!$result) {
        return false;
    }
    return $ticket_id;
}

/**
 * Update Existing Data into wsdesk_tickets table.
 *
 *
 * @param int|string $id Corresponding Settings ID that needs to be updated
 * @param array $data (ticket_title,ticket_email,ticket_content,ticket_category,ticket_vendor)
 * @return bool True on success. False on failure.
 */
function eh_crm_update_ticket($id, $data) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE ticket_id = %d", (int) $id))) {
        return false;
    }
    $where = array('ticket_id' => (int) $id);
    $result = $wpdb->update($table, $data, $where);
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Delete Existing Data from wsdesk_tickets table.
 *
 *
 * @param int|string $id Corresponding Settings ID that needs to be deleted
 * @return bool True on success. False on failure.
 */
function eh_crm_delete_ticket($id) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $table_meta = $wpdb->prefix . 'wsdesk_ticketsmeta';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE ticket_id = %d", (int) $id))) {
        return false;
    }
    $query = "DELETE FROM $table WHERE ticket_id = $id";
    $result = $wpdb->query($query);
    $meta = eh_crm_get_ticketmeta($id);
    if(isset($meta["ticket_attachment_path"]))
    {
        $attachment = $meta['ticket_attachment_path'];
        for($i=0;$i<count($attachment);$i++)
        {
            wp_delete_file($attachment[$i]);           
        }
    }
    $meta_query = "DELETE FROM $table_meta WHERE ticket_id = $id";
    $wpdb->query($meta_query);
    if (!$result) {
        return false;
    }
    return true;
}

function eh_crm_get_ticket_search($value) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $query = "select ticket_id from $table WHERE lower(concat(ticket_title,ticket_email,ticket_content,ticket_date)) LIKE lower('%$value%') AND ticket_parent=0";
    $data = $wpdb->get_results($query, ARRAY_A);
    if (!$data) {
        return array();
    }
    return $data;
}
/**
 * Get Existing Data into wsdesk_tickets table.
 *
 *
 * @param array $args (ticket_title,ticket_email,ticket_content,ticket_category,ticket_vendor,ticket_parent) | ticket_email By column and value Eg: array('ticket_id'=>4)
 * @param array|string $fields (ticket_title,ticket_email,ticket_content,ticket_category,ticket_vendor) | Optional (Fields to return)
 * @return array Filtered or provided ID data as key value pair
 */
function eh_crm_get_ticket($args, $fields = NULL) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $query_search = '';
    $query_field = '';
    if ($fields !== NULL) {
        if (is_array($fields)) {
            $query_field = implode(',', $fields);
        } else {
            $query_field = $fields;
        }
    } else {
        $query_field = '*';
    }
    if (is_array($args)) {
        $a = 1;
        foreach ($args as $key => $value) {
            switch ($key) {
                case 'ticket_id':
                    $query_search .= "ticket_id = " . (int) $args['ticket_id'];
                    break;
                case 'ticket_author':
                    $query_search .= "ticket_author = '" . $args['ticket_author'] . "'";
                    break;
                case 'ticket_email':
                    $query_search .= "ticket_email = '" . $args['ticket_email'] . "'";
                    break;
                case 'ticket_date':
                    $query_search .= "ticket_date = '" . $args['ticket_date'] . "'";
                    break;
                case 'ticket_title':
                    $query_search .= "ticket_title = '" . $args['ticket_title'] . "'";
                    break;
                case 'ticket_parent':
                    $query_search .= "ticket_parent = '" . $args['ticket_parent'] . "'";
                    break;
                case 'ticket_category':
                    $query_search .= "ticket_category = '" . $args['ticket_category'] . "'";
                    break;
                case 'ticket_vendor':
                    $query_search .= "ticket_vendor = '" . $args['ticket_vendor'] . "'";
                    break;
                default:
                    break;
            }
            if ($a < count(array_keys($args))) {
                $query_search .= " AND ";
                $a++;
            }
        }
    }
    $query = "select $query_field from $table WHERE $query_search";
    $data = $wpdb->get_results($query, ARRAY_A);
    if (!$data) {
        return false;
    }
    return $data;
}

/**
 * Insert Data into wsdesk_ticketsmeta table.
 *
 *
 * @param int|string $id ticket ID
 * @param string $key meta key
 * @param string $value meta value
 * @return bool True on success. False on failure.
 */
function eh_crm_insert_ticketmeta($id, $key, $value) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_ticketsmeta';
    $query = "SELECT COUNT(*) FROM $table WHERE meta_key = '".$key."' AND ticket_id =".(int)$id;
    if ($wpdb->get_var($query)) {
        return false;
    }
    if (is_array($value)) {
        $data = serialize($value);
    } else {
        $data = $value;
    }
    $result = $wpdb->insert($table, array(
        'ticket_id' => (int) $id,
        'meta_key' => $key,
        'meta_value' => $data
    ));
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Update Data into wsdesk_ticketsmeta table.
 *
 *
 * @param int|string $id ticket ID
 * @param string $key meta key
 * @param string $value meta value
 * @return bool True on success. False on failure.
 */
function eh_crm_update_ticketmeta($id, $key, $value) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_ticketsmeta';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE meta_key = %s AND ticket_id = %d", $key, (int) $id))) {
        eh_crm_insert_ticketmeta($id, $key, $value);
    }
    $where = array('ticket_id' => (int) $id, 'meta_key' => $key);
    if (is_array($value)) {
        $data = array("meta_value" => serialize($value));
    } else {
        $data = array("meta_value" => $value);
    }
    $result = $wpdb->update($table, $data, $where);
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Delete Data from wsdesk_ticketsmeta table.
 *
 *
 * @param int|string $id ticket ID
 * @param string $key meta key
 * @return bool True on success. False on failure.
 */
function eh_crm_delete_ticketmeta($id, $key) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_ticketsmeta';
    if (!$wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE meta_key = %s AND ticket_id = %d", $key, (int) $id))) {
        return false;
    }
    $query = "DELETE FROM $table WHERE ticket_id = $id AND meta_key = '$key'";
    $result = $wpdb->query($query);
    if (!$result) {
        return false;
    }
    return true;
}

/**
 * Delete Data from wsdesk_ticketsmeta table.
 *
 *
 * @param int|string $id ticket ID
 * @param string $key meta key | Optional 
 * @return mixed ( If provided will return particular value or will return array of all meta of ticket ID)
 */
function eh_crm_get_ticketmeta($id, $key = NULL) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_ticketsmeta';
    $meta_key = '';
    $retrived = array();
    if ($key !== NULL) {
        $meta_key = " AND meta_key = '" . $key . "'";
    }
    $data = $wpdb->get_results($wpdb->prepare("SELECT meta_key,meta_value FROM $table WHERE ticket_id = %d" . $meta_key, (int) $id), ARRAY_A);
    if (!$data) {
        return false;
    }
    if ($key !== NULL) {
        return is_serialized($data[0]['meta_value']) ? unserialize($data[0]['meta_value']) : $data[0]['meta_value'];
    }
    for ($i = 0; $i < count($data); $i++) {
        $retrived[$data[$i]['meta_key']] = is_serialized($data[$i]['meta_value']) ? unserialize($data[$i]['meta_value']) : $data[$i]['meta_value'];
    }
    return is_serialized($retrived) ? unserialize($retrived) : $retrived;
}
/**
 * Get Value Count Data from wsdesk_ticketsmeta table.
 *
 *
 * @param string $key meta key
 * @param string $value meta value
 * @param string $order order by value
 * @param string $type Desc by default (sort type)
 * @param string $limit limit number of rows
 * @param string $offset starting from
 * @return array (Returns all match ticket_id)
 */
function eh_crm_get_ticketmeta_value_count($key, $value,$order = 'ticket_id', $type = 'DESC', $limit = 0,$offset=0) {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $tablemeta = $wpdb->prefix . 'wsdesk_ticketsmeta';
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $where = '';
    $id = array();
    $return_id = array();
    if ($key !== NULL) {
        $where = "m.meta_key = '%s'";
        if ($order !== '') {
            $where .= " ORDER BY f." . $order;
        }
        if($type !== '')
        {
            $where.= " " .$type;
        }
    }
    $data = $wpdb->get_results($wpdb->prepare("SELECT m.ticket_id,m.meta_value FROM $tablemeta m JOIN $table f ON m.ticket_id = f.ticket_id WHERE " . $where, $key), ARRAY_A);
    if (!$data) {
        return array();
    } else {
        for ($i = 0; $i < count($data); $i++) {
            $meta_value = is_serialized($data[$i]['meta_value']) ? unserialize($data[$i]['meta_value']) : $data[$i]['meta_value'];
            if (is_array($meta_value)) {
                if (in_array($value, $meta_value)) {
                    array_push($id, array("ticket_id"=>$data[$i]['ticket_id']));
                }
                elseif(is_array($value))
                {
                    if ($meta_value === $value) {
                        array_push($id, array("ticket_id"=>$data[$i]['ticket_id']));
                    }
                }
            } else {
                if ($meta_value === $value) {
                    array_push($id, array("ticket_id"=>$data[$i]['ticket_id']));
                }
            }
        }
        if($limit!=0)
        {
            $return_id = array_slice($id,$offset,$limit);
        }
        else
        {
            $return_id = $id;
        }
    }
    return $return_id;
}

/**
 * Get Value Count Data from wsdesk_ticketsmeta table.
 *
 *
 * @param string $key tickets key
 * @param string $value tickets value
 * @param string $not (check not equal to) | Default False
 * @param string $exp_key tickets key
 * @param string $exp_value tickets value
 * @param string $order order by value
 * @param string $type Desc by default (sort type)
 * @param string $limit limit number of rows
 * @param string $offset starting from
 * @return array (Returns all match ticket_id)
 */

function eh_crm_get_ticket_value_count($key, $value, $not = false, $exp_key = '', $exp_value = '', $order = 'ticket_id', $type = 'DESC', $limit = '',$offset='') {
    ini_set('max_execution_time', 300);
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $where = '';
    if ($key !== NULL) {
        if ($not === false) {
            $where = " WHERE $key = '%s'";
        } else {
            $where = " WHERE $key != '%s'";
        }
        if ($exp_key !== '') {
            $where .= " AND $exp_key = '$exp_value'";
        }
        if ($order !== '') {
            $where .= " ORDER BY " . $order;
        }
        if($type !== '')
        {
            $where.= " " .$type;
        }
        if($limit!=='')
        {
            if($offset!='')
            {
                $where.=" LIMIT ".$offset.", ".$limit;
            }
            else 
            {
                $where.=" LIMIT ".$limit;
            }
        }
    } else {
        return array();
    }
    $data = $wpdb->get_results($wpdb->prepare("SELECT ticket_id FROM $table" . $where, $value), ARRAY_A);
    if (!$data) {
        return array();
    }
    return $data;
}

function eh_crm_generate_bar_values($get_for = 'all')
{
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $bar = array();
    for ($i = 6; $i >= 0; $i--) {
        $day = date('M d, Y', time() - $i * 86400);
        $data = $wpdb->get_results("SELECT ticket_id FROM $table WHERE ticket_parent = 0 AND ticket_date LIKE '%$day%'", ARRAY_A);
        $count = 0;
        if($get_for != "all")
        {
            for($j=0;$j<count($data);$j++)
            {
                $current_meta = eh_crm_get_ticketmeta($data[$j]['ticket_id'], "ticket_assignee");
                if($current_meta)
                {
                    if (in_array($get_for, $current_meta)) {
                            $count++;
                    }
                }
            }
            array_push($bar, array("y"=>$day,"a"=>$count));
        }
        else
        {
            array_push($bar, array("y"=>$day,"a"=>count($data)));
        }
    }
    return $bar;
}

function eh_crm_generate_donut_values($get_for = 'all')
{
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $donut = array();
    $result = array();
    $week_tickets = array();
    $week_tickets_replies = array();
    $colors = array();
    $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
    for ($i = 6; $i >= 0; $i--) {
        $day = date('M d, Y', time() - $i * 86400);
        $data = $wpdb->get_results("SELECT ticket_id FROM $table WHERE ticket_parent = 0 AND ticket_date LIKE '%$day%'", ARRAY_A);
        for($j=0;$j<count($data);$j++)
        {
            array_push($week_tickets, $data[$j]['ticket_id']);
        }
    }
    for ($i = 6; $i >= 0; $i--) {
        $day = date('M d, Y', time() - $i * 86400);
        $data = $wpdb->get_results("SELECT ticket_id FROM $table WHERE ticket_parent != 0 AND ticket_date LIKE '%$day%'", ARRAY_A);
        for($j=0;$j<count($data);$j++)
        {
            array_push($week_tickets_replies, $data[$j]['ticket_id']);
        }
    }
    if($get_for == "all")
    {
        $users = get_users(array("role__in" => $user_roles_default));
        for ($i = 0; $i < count($users); $i++) {
            $user_tickets =array();
            $current = $users[$i];
            $id = $current->ID;
            $user = new WP_User($id);
            $count = eh_crm_get_ticketmeta_value_count("ticket_assignee", $id);
            for($j=0;$j<count($count);$j++)
            {
                array_push($user_tickets, $count[$j]['ticket_id']);
            }
            $result = array_intersect($week_tickets, $user_tickets);
            array_push($donut, array("label"=>$user->display_name,"value"=>count($result)));
        }
        return $donut;
    }    
    else
    {
        $user = get_user_by("ID",$get_for);
        $assigned = eh_crm_get_ticketmeta_value_count("ticket_assignee",$user->ID);
        $user_tickets_as =array();
        for($j=0;$j<count($assigned);$j++)
        {
            array_push($user_tickets_as, $assigned[$j]['ticket_id']);
        }
        array_push($donut, array("label"=>"Assigned","value"=>count(array_intersect($week_tickets, $user_tickets_as))));
        array_push($colors,"#79b2c4");
        $labels = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
        for($l=0;$l<count($labels);$l++)
        {
            $label_color = eh_crm_get_settingsmeta($labels[$l]['settings_id'],"label_color");
            $status = eh_crm_get_ticketmeta_value_count("ticket_label",$labels[$l]['slug']);
            $user_tickets_so =array();
            for($j=0;$j<count($status);$j++)
            {
                array_push($user_tickets_so, $status[$j]['ticket_id']);
            }
            array_push($donut, array("label"=>$labels[$l]['title'],"value"=>count(array_intersect($week_tickets,array_intersect($user_tickets_as, $user_tickets_so)))));
            array_push($colors,$label_color);
        }
        $user_tickets_re = array();
        $replies = eh_crm_get_ticket_value_count("ticket_parent",0,TRUE,"ticket_author",$user->ID);
        for($j=0;$j<count($replies);$j++)
        {
            array_push($user_tickets_re, $replies[$j]['ticket_id']);
        }
        array_push($donut, array("label"=>"Replies","value"=>count(array_intersect($week_tickets_replies, $user_tickets_re))));
        array_push($colors,"#6181e2");
        $data['donut'] = $donut;
        $data['color']= $colors;
        return $data;
    }
}

function eh_crm_generate_donut_values_tags()
{
    global $wpdb;
    $tablemeta = $wpdb->prefix.'wsdesk_ticketsmeta';
    $table = $wpdb->prefix.'wsdesk_tickets';
    $avail_tags_wf = eh_crm_get_settings(array("type" => "tag"), array("slug", "title", "settings_id"));
    $from_date = floor((time()-(7*86400))/(60*60*24));
    $to_date = floor(time()/(60*60*24));
    $donut = array();
    foreach ($avail_tags_wf as $key=>$value) {
        $donut[$key]['label'] = $value['title'];
        $donut[$key]['value'] = 0;
        for ($j = $to_date-$from_date-1; $j >= 0; $j--) {
            $day = date('M d, Y', time() - $j * 86400);
            $data = $wpdb->get_results("SELECT m.ticket_id,m.meta_value FROM $tablemeta m JOIN $table f ON m.ticket_id = f.ticket_id WHERE m.meta_key = 'ticket_tags' AND f.ticket_date LIKE '%$day%' ORDER BY f.ticket_id DESC", ARRAY_A);
            for($i=0;$i<count($data); $i++)
            {
                $meta_value = unserialize($data[$i]['meta_value']);
                if(in_array($value['slug'], $meta_value))
                    $donut[$key]['value']++; 
            }
        }
    }
    return $donut;
}
function eh_crm_generate_line_values($get_for = 'all')
{
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $lines = array();
    for ($i = 6; $i >= 0; $i--) {
        $day = date('M d, Y', time() - $i * 86400);
        $eday = date('Y-m-d', time() - $i * 86400);
        $data = $wpdb->get_results("SELECT ticket_id FROM $table WHERE ticket_parent = 0 AND ticket_date LIKE '%$day%'", ARRAY_A);
        $new_count = 0;
        $sol_count = 0;
        if($get_for != "all")
        {
            for($j=0;$j<count($data);$j++)
            {
                $current_meta = eh_crm_get_ticketmeta($data[$j]['ticket_id'], "ticket_assignee");
                if($current_meta)
                {
                    if (in_array($get_for, $current_meta)) {
                            $new_count++;
                            $current_meta = eh_crm_get_ticketmeta($data[$j]['ticket_id'], "ticket_label");
                            if($current_meta)
                            {
                                if ($current_meta == "label_LL02") {
                                        $sol_count++;
                                }
                            }
                    }
                }
            }
            array_push($lines, array("y"=>$eday,"a"=>$new_count,"b"=>$sol_count));
        }
        else
        {
            for($j=0;$j<count($data);$j++)
            {
                $current_meta = eh_crm_get_ticketmeta($data[$j]['ticket_id'], "ticket_label");
                if($current_meta)
                {
                    if ($current_meta == "label_LL02") {
                            $sol_count++;
                    }
                }
            }
            array_push($lines, array("y"=>$eday,"a"=>count($data),"b"=>$sol_count));
        }
    }
    return $lines;
}
function eh_crm_validate_email_block($email,$status)
{
    $block_filter = eh_crm_get_settingsmeta("0", "email_block_filters");
    if(!$block_filter)
    {
        $block_filter = array();
    }
    $keys = array_keys($block_filter);
    foreach ($keys as $block) 
    {
        if(is_array($email))
        {
            foreach ($email as $smail) {
                if(strpos($smail, $block) !== false)
                {
                    $stat = explode(',', $block_filter[$block]);
                    if(in_array($status, $stat))
                    {
                        return false;
                    }
                }
            }
        }
        else 
        {
            if(strpos($email, $block) !== false)
            {
                $stat = explode(',', $block_filter[$block]);
                if(in_array($status, $stat))
                {
                    return false;
                }
            }
        }
    }
    return true;
}

function eh_crm_debug_error_log($reponse)
{
    $add_log = eh_crm_get_settingsmeta(0, "wsdesk_debug_status");
    if($add_log == 'enable')
    {
        $backtrace = debug_backtrace();
        error_log('['.gmdate("M d, Y h:i:s A").'] - '.'( '.$backtrace[1]['class'].'::'.$backtrace[1]['function'].' ) '.print_r($reponse,true));
    }
}

function eh_crm_get_formatted_date($date)
{
    $timeformat = get_option('date_format').' '.get_option('time_format');
    $return = ((get_option('timezone_string')!=="")?get_date_from_gmt($date, $timeformat):date_i18n($timeformat,strtotime("+".(get_option('gmt_offset')*60)." minutes", strtotime($date))));
    return $return;
}

/**
* Returns ticket_id of all tickets in the database irrespective of trash value. Used in factory reset.
**/
function eh_crm_get_all_tickets()
{
    global $wpdb;
    $table = $wpdb->prefix . 'wsdesk_tickets';
    $data = $wpdb->get_results("SELECT ticket_id FROM $table", ARRAY_A);
    if (!$data) {
        return array();
    }
    return $data;
}