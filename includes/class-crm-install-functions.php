<?php

if (!defined('ABSPATH')) {
    exit;
}

class EH_CRM_Install {

    public static function install_tables() {
        global $wpdb;
        $search_query = "SHOW TABLES LIKE %s";
        $charset_collate = $wpdb->get_charset_collate();
        $like = '%'.$wpdb->prefix.'wsdesk_settings%';
        if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
            $table_name = $wpdb->prefix . 'wsdesk_settings';
            $sql_settings = "CREATE TABLE $table_name 
                (
                    `settings_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT , 
                    `slug` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                    `title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                    `filter` VARCHAR(3) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no' , 
                    `type` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                    `vendor` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                    PRIMARY KEY (`settings_id`)
                )$charset_collate;";
            dbDelta($sql_settings);
            EH_CRM_Install::install_defaults("settings");
        }
        $like = '%'.$wpdb->prefix.'wsdesk_settingsmeta%';
        if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
            $table_name = $wpdb->prefix . 'wsdesk_settingsmeta';
            $sql_settingsmeta = "CREATE TABLE $table_name 
                (
                    `meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
                    `settings_id` BIGINT(20) UNSIGNED NOT NULL ,
                    `meta_key` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL ,
                    `meta_value` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL , 
                    PRIMARY KEY (`meta_id`)
                )$charset_collate;";
            dbDelta($sql_settingsmeta);
            EH_CRM_Install::install_defaults("settingsmeta");
        }
        $like = '%'.$wpdb->prefix.'wsdesk_tickets%';
        if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
            $table_name = $wpdb->prefix . 'wsdesk_tickets';
            $sql_tickets = "CREATE TABLE $table_name
                    (   `ticket_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
                        `ticket_author` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0, 
                        `ticket_email` VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                        `ticket_date` VARCHAR(100) NOT NULL, 
                        `ticket_title` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                        `ticket_content` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                        `ticket_parent` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0, 
                        `ticket_category` TEXT NOT NULL , 
                        `ticket_vendor` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL , 
                        PRIMARY KEY (`ticket_id`)
                    )$charset_collate;";
            dbDelta($sql_tickets);
        }
        $like = '%'.$wpdb->prefix.'wsdesk_ticketsmeta%';
        if (!$wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_N)) {
            $table_name = $wpdb->prefix . 'wsdesk_ticketsmeta';
            $sql_ticketsmeta = "CREATE TABLE $table_name 
                (
                    `meta_id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT ,
                    `ticket_id` BIGINT(20) UNSIGNED NOT NULL ,
                    `meta_key` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL ,
                    `meta_value` LONGTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL , 
                    PRIMARY KEY (`meta_id`)
                )$charset_collate;";
            dbDelta($sql_ticketsmeta);
        }
    }

    public static function install_defaults($type) {
        global $wpdb;        
        switch ($type) {
            case "settings":
                $table_name = $wpdb->prefix . 'wsdesk_settings';
                $insert_settings = "INSERT INTO $table_name 
                    (`settings_id`, `slug`, `title`, `filter`, `type`, `vendor`) 
                    VALUES 
                        ('1', 'request_email', 'Email', 'yes', 'field', ''),
                        ('2', 'request_title', 'Subject', 'yes', 'field', ''),
                        ('3', 'request_description', 'Description', 'yes', 'field', ''),
                        ('4', 'label_LL01', 'Unsolved', 'yes', 'label', ''),
                        ('5', 'label_LL02', 'Solved', 'yes', 'label', ''),
                        ('6', 'label_LL03', 'Pending', 'yes', 'label', ''),
                        ('7', 'tag_TT01', 'Support', 'yes', 'tag', '')
                    ";
                $wpdb->query($insert_settings);
                break;
            case "settingsmeta":
                $table_name = $wpdb->prefix . 'wsdesk_settingsmeta';
                $insert_settingsmeta = "INSERT INTO $table_name
                    (`meta_id`, `settings_id`, `meta_key`, `meta_value`) 
                    VALUES 
                    (NULL, '0', 'default_assignee', 'no_assignee'),
                    (NULL, '0', 'default_label', 'label_LL01'),
                    (NULL, '0', 'ticket_raiser', 'all'),
                    (NULL, '0', 'ticket_rows', '25'),
                    (NULL, '0', 'input_width', ''),
                    (NULL, '0', 'main_ticket_form_title', ''),
                    (NULL, '0', 'new_ticket_form_title', ''),
                    (NULL, '0', 'existing_ticket_title', ''),
                    (NULL, '1', 'field_type', 'email'),
                    (NULL, '1', 'field_placeholder', 'Enter Email'),
                    (NULL, '1', 'field_require', 'yes'),
                    (NULL, '1', 'field_default', ''),
                    (NULL, '1', 'field_description', 'Your Email by which we will get back to you.'),
                    (NULL, '2', 'field_type', 'text'),
                    (NULL, '2', 'field_placeholder', 'Enter Subject'),
                    (NULL, '2', 'field_require', 'yes'),
                    (NULL, '2', 'field_default', ''),
                    (NULL, '2', 'field_description', 'Your request Subject for which you are going to raise the ticket.'),
                    (NULL, '3', 'field_type', 'textarea'),
                    (NULL, '3', 'field_require', 'yes'),
                    (NULL, '3', 'field_default', ''),
                    (NULL, '3', 'field_description', 'Please enter the details of your request. A member of our support staff will respond as soon as possible.'),
                    (NULL, '4', 'label_color', '#F5CA00'),
                    (NULL, '5', 'label_color', '#94BA3C'),
                    (NULL, '6', 'label_color', '#E82A2A')";
                $wpdb->query($insert_settingsmeta);
                break;
            default:
                break;
        }
        global $wp_roles;
        $user_roles = $wp_roles->role_names;
        $user_roles_create = array("WSDesk Agents", "WSDesk Supervisor");
        for ($i = 0; $i < count($user_roles_create); $i++) {
            $new_user_role = str_replace(' ', '_', $user_roles_create[$i]);
            if (($new_user_role != '' && $user_roles_create[$i] != '' ) && !( array_key_exists($new_user_role, $user_roles) )) {
                add_role($new_user_role, $user_roles_create[$i], array('crm_role'=>true,'read' => true,'view_admin_dashboard'=>true));
            }
        }
    }
    
    public static function update_tables($base) {
        update_option("wsdesk_version_".$base, EH_CRM_VERSION);
        global $wpdb;
        $table = $wpdb->prefix . 'wsdesk_tickets';
        if ($wpdb->get_var("SHOW COLUMNS FROM $table LIKE 'ticket_updated'")) {
            return false;
        }
        $wpdb->query("ALTER TABLE $table ADD `ticket_updated` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `ticket_date`;");
        
        $role = get_role('WSDesk_Agents');
        if(in_array('view_admin_dashboard',$role->capabilities))
        {
            $role->add_cap('view_admin_dashboard',true);
        }
        $role = get_role('WSDesk_Supervisor');
        if(in_array('view_admin_dashboard',$role->capabilities))
        {
            $role->add_cap('view_admin_dashboard',true);
        }
    }
}
