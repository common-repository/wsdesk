<?php

if (!defined('ABSPATH')) {
    exit;
}

class CRM_Ajax {

    static function eh_crm_ticket_general() {
        $default_assignee = sanitize_text_field($_POST['default_assignee']);
        $default_label = sanitize_text_field($_POST['default_label']);
        $ticket_raiser = sanitize_text_field($_POST['ticket_raiser']);
        $ticket_rows = sanitize_text_field($_POST['ticket_rows']);
        $auto_create_user = sanitize_text_field($_POST['auto_create_user']);
        $enable_api = sanitize_text_field($_POST['enable_api']);
        $api_key = sanitize_text_field($_POST['api_key']);
        $default_deep_link = sanitize_text_field($_POST['default_deep_link']);
        eh_crm_update_settingsmeta('0', "default_assignee", $default_assignee);
        eh_crm_update_settingsmeta('0', "default_label", $default_label);
        eh_crm_update_settingsmeta('0', "ticket_raiser", $ticket_raiser);
        eh_crm_update_settingsmeta('0', "ticket_rows", $ticket_rows);
        eh_crm_update_settingsmeta('0', "auto_create_user", $auto_create_user);
        eh_crm_update_settingsmeta('0', "enable_api", $enable_api);
        eh_crm_update_settingsmeta('0', "api_key", $api_key);
        eh_crm_update_settingsmeta('0', "default_deep_link", $default_deep_link);
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_general.php"));
    }

    static function eh_crm_ticket_appearance() {
        $input_width = sanitize_text_field($_POST['input_width']);
        $main_ticket_title = sanitize_text_field($_POST['main_ticket_title']);
        $new_ticket_title = sanitize_text_field($_POST['new_ticket_title']);
        $existing_ticket_title = sanitize_text_field($_POST['existing_ticket_title']);
        $submit_ticket_button = sanitize_text_field($_POST['submit_ticket_button']);
        $reset_ticket_button = sanitize_text_field($_POST['reset_ticket_button']);
        $existing_ticket_button = sanitize_text_field($_POST['existing_ticket_button']);
        eh_crm_update_settingsmeta('0', "input_width", $input_width);
        eh_crm_update_settingsmeta('0', "main_ticket_form_title", $main_ticket_title);
        eh_crm_update_settingsmeta('0', "new_ticket_form_title", $new_ticket_title);
        eh_crm_update_settingsmeta('0', "existing_ticket_title", $existing_ticket_title);
        eh_crm_update_settingsmeta('0', "submit_ticket_button", $submit_ticket_button);
        eh_crm_update_settingsmeta('0', "reset_ticket_button", $reset_ticket_button);
        eh_crm_update_settingsmeta('0', "existing_ticket_button", $existing_ticket_button);
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_appearance.php"));
    }
    
    static function eh_crm_ticket_field_delete() {
        $fields_remove = sanitize_text_field($_POST['fields_remove']);
        $args = array("type" => "field");
        $fields = array("settings_id", "slug");
        $avail_fields = eh_crm_get_settings($args, $fields);
        for ($i = 0; $i < count($avail_fields); $i++) {
            if ($avail_fields[$i]["slug"] == $fields_remove) {
                eh_crm_delete_settings($avail_fields[$i]["settings_id"]);
            }
        }
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_fields.php"));
    }
    
    static function eh_crm_ticket_field_activate_deactivate() {
        $field_id = sanitize_text_field($_POST['field_id']);
        $type = sanitize_text_field($_POST['type']);
        $selected_fields = eh_crm_get_settingsmeta("0", "selected_fields");
        switch ($type) {
            case "activate":
                if (!in_array($field_id,$selected_fields)) {
                    array_push($selected_fields,$field_id);
                }
                eh_crm_update_settingsmeta("0", "selected_fields", array_values($selected_fields));
                break;
            case "deactivate":
                if(($key = array_search($field_id, $selected_fields)) !== false) {
                    unset($selected_fields[$key]);
                }
                eh_crm_update_settingsmeta("0", "selected_fields", array_values($selected_fields));
                break;
            default:
                break;
        }
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_fields.php"));
    }
    
    static function eh_crm_ticket_field() {
        $selected_fields = explode(",", sanitize_text_field($_POST['selected_fields']));
        $new_field = json_decode(stripslashes(sanitize_text_field($_POST['new_field'])), true);
        $edit_field = json_decode(stripslashes(sanitize_text_field($_POST['edit_field'])), true);
        $args = array("type" => "field");
        $fields = array("settings_id", "slug");
        $temp = eh_crm_get_settings($args, $fields);
        $slug = array();
        for ($i = 0; $i < count($temp); $i++) {
            $slug[$i] = $temp[$i]['slug'];
        }
        for ($i = 0; $i < count($selected_fields); $i++) {
            if (!in_array($selected_fields[$i], $slug)) {
                unset($selected_fields[$i]);
            }
        }
        eh_crm_update_settingsmeta("0", "selected_fields", array_values($selected_fields));
        if (!empty($new_field)) {
            $insert = array(
                'title' => $new_field['title'],
                'filter' => $new_field['filter'],
                'type' => 'field',
                'vendor' => ''
            );
            switch ($new_field['type']) {
                case "file":
                    $meta = array
                    (
                        "field_type" => $new_field['type'],
                        "field_require" => $new_field['required'],
                        "field_description" => $new_field['description'],
                        "file_type" => $new_field['file_type']
                    );
                    eh_crm_insert_settings($insert, $meta);
                    break;
                case "text":
                case "number":
                case "email":
                case "password":
                    $meta = array
                        (
                        "field_type" => $new_field['type'],
                        "field_default" => $new_field['default'],
                        "field_require" => $new_field['required'],
                        "field_placeholder" => $new_field['placeholder'],
                        "field_description" => $new_field['description']
                    );
                    eh_crm_insert_settings($insert, $meta);
                    break;
                case "checkbox":
                case "radio":
                case "select":
                    $meta = array
                        (
                        "field_type" => $new_field['type'],
                        "field_description" => $new_field['description']
                    );
                    if($new_field['type'] === 'select')
                    {
                        $meta["field_placeholder"] = $new_field['placeholder'];
                    }
                    $id = eh_crm_insert_settings($insert, $meta);
                    $args = array("settings_id" => $id);
                    $fields = array("slug");
                    $data = eh_crm_get_settings($args, $fields);
                    $values = $new_field['values'];
                    $gen_val = array();
                    $gen_def = "";
                    for ($i = 0; $i < count($values); $i++) {
                        $key = $data[0]['slug'] . "_V" . $i;
                        $gen_val[$key] = $values[$i];
                        if ($values[$i] === $new_field['default']) {
                            $gen_def = $key;
                        }
                    }
                    eh_crm_insert_settingsmeta($id, "field_default", $gen_def);
                    eh_crm_insert_settingsmeta($id, "field_values", $gen_val);
                    break;
                case 'textarea':
                    $meta = array
                        (
                        "field_type" => $new_field['type'],
                        "field_default" => $new_field['default'],
                        "field_require" => $new_field['required'],
                        "field_description" => $new_field['description']
                    );
                    eh_crm_insert_settings($insert, $meta);
                    break;
            }
        }
        if (!empty($edit_field)) {
            $edit_slug = $edit_field["slug"];
            $edit_title = $edit_field["title"];
            $edit_filter = $edit_field["filter"];
            $edit_required = $edit_field["required"];
            $edit_placeholder = $edit_field["placeholder"];
            $edit_default = $edit_field["default"];
            $edit_values = $edit_field["values"];
            $edit_file_type = $edit_field["file_type"];
            $edit_description = $edit_field["description"];
            $field_data = eh_crm_get_settings(array("slug" => $edit_slug, "type" => "field"), "settings_id");
            if (!empty($field_data)) {
                $field_id = $field_data[0]['settings_id'];
            eh_crm_update_settingsmeta($field_id, "field_placeholder", $edit_placeholder);
            eh_crm_update_settingsmeta($field_id, "field_default", $edit_default);
            
                eh_crm_update_settings($field_id, array("title" => $edit_title, "filter" => $edit_filter));
                eh_crm_update_settingsmeta($field_id, "field_description", $edit_description);
                if ($edit_required !== "") {
                    eh_crm_update_settingsmeta($field_id, "field_require", $edit_required);
                }
                if ($edit_file_type !== "") {
                    eh_crm_update_settingsmeta($field_id, "file_type", $edit_file_type);
                }
                if ($edit_values !== "") {
                    for ($i = 0; $i < count($edit_values); $i++) {
                        $key = $edit_slug . "_V" . $i;
                        $gen_val[$key] = $edit_values[$i];
                        if ($edit_values[$i] === $edit_default) {
                            $gen_def = $key;
                        }
                    }
                    eh_crm_update_settingsmeta($field_id, "field_default", $gen_def);
                    eh_crm_update_settingsmeta($field_id, "field_values", $gen_val);
                }
            }
        }
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_fields.php"));
    }

    static function eh_crm_ticket_field_edit() {
        $field = sanitize_text_field($_POST['field']);
        $args = array("slug" => $field, "type" => "field");
        $fields = array("settings_id", "title", "filter");
        $field_sett = eh_crm_get_settings($args, $fields);
        $field_meta = eh_crm_get_settingsmeta($field_sett[0]['settings_id']);
        $add_value = '<button class="button" id="ticket_field_edit_values_add" style="vertical-align: baseline;margin-bottom: 10px;">'.__('Add Value', 'wsdesk').'</button>';
        $output = '<span class="help-block">'.__('Edit Details for custom', 'wsdesk').' ' . ucfirst($field_meta['field_type']) . '? </span>';
        $output .= '<input type="text" id="ticket_field_edit_title" placeholder="'.__('Enter Title', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $field_sett[0]['title'] . '">';
        switch ($field_meta['field_type']) {
            case '':
                break;
            case 'file':
                $required_end = "";
                if ($field_meta['field_require'] == "yes") {
                    $required_end = "checked";
                }
                $single = "";
                $multiple = "";
                if ($field_meta['file_type'] == "single") {
                    $multiple = "";
                    $single = "checked";
                } else {
                    $multiple = "checked";
                    $single = "";
                }
                $visible = "";
                if (isset($field_meta['field_visible']) && $field_meta['field_visible'] == "yes") {
                    $visible = "checked";
                }
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_visible" class="form-control" name="ticket_field_edit_visible" ' . $visible . ' value="yes"> '.__('Visible for End Users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_require" class="form-control" name="ticket_field_edit_require" ' . $required_end . ' value="yes"> '.__('Mandatory for End users', 'wsdesk').'</span>';
                $output .= '<br><span class="help-block">'.__('Specify whether this Field is Single or Multiple Attachment?', 'wsdesk').' </span><input type="radio" style="margin-top: 0;"  id="ticket_field_edit_file_type" checked class="form-control" name="ticket_field_edit_file_type" ' . $single .' value="single"> '.__('Single Attachment', 'wsdesk').' <br><input type="radio" style="margin-top: 0;" id="ticket_field_edit_file_type" class="form-control" name="ticket_field_edit_file_type" ' . $multiple . ' value="multiple"> '.__('Multiple Attachment', 'wsdesk').' <br>';
                $output .= '<br><span class="help-block">'.__('Want to update description to this field?', 'wsdesk').' </span><textarea id="ticket_field_edit_description" class="form-control crm-form-element-input" style="padding: 10px !important;">' . $field_meta['field_description'] . '</textarea>';
                break;
            case 'radio':
                $required_end = "";
                if ($field_meta['field_require'] == "yes") {
                    $required_end = "checked";
                }
                $required_agent = "";
                if (isset($field_meta['field_require_agent']) && $field_meta['field_require_agent'] == "yes") {
                    $required_agent = "checked";
                }
                $visible = "";
                if (isset($field_meta['field_visible']) && $field_meta['field_visible'] == "yes") {
                    $visible = "checked";
                }
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_visible" class="form-control" name="ticket_field_edit_visible" ' . $visible . ' value="yes"> '.__('Visible for End Users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_require" class="form-control" name="ticket_field_edit_require" ' . $required_end . ' value="yes"> '.__('Mandatory for End users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_agent_require" class="form-control" name="ticket_field_edit_agent_require" ' . $required_agent . ' value="yes"> '.__('Mandatory for Agents', 'wsdesk').'</span>';
                $output .= '<span class="help-block">'.__('Update the Radio values!', 'wsdesk').' </span>';
                $field_values = array_values($field_meta['field_values']);
                $field_keys = array_keys($field_meta['field_values']);
                for ($i = 0; $i < count($field_values); $i++) {
                    if($i==0)
                    {
                        $output .= '<span id="ticket_field_edit_values_span_'. $i .'" class="ticket_field_edit_values_span"><input type="text" id="ticket_field_edit_values[' . $i . ']" class="form-control ticket_field_edit_values crm-form-element-input" value="' . $field_values[$i] . '"><input type="hidden" class="old_ticket_field_edit_values[' . $i . ']" id="'.$field_keys[$i].'" value="'.$field_values[$i].'"></span>';
                    }
                    else
                    {
                        $output .='<span id="ticket_field_edit_values_span_'. $i .'" class="ticket_field_edit_values_span"><input type="text" id="ticket_field_edit_values[' . $i . ']" class="form-control ticket_field_edit_values crm-form-element-input" style="width:90% !important;" value="' . $field_values[$i] . '"><input type="hidden" class="old_ticket_field_edit_values[' . $i . ']" id="'.$field_keys[$i].'" value="'.$field_values[$i].'"><button class="btn btn-warning" title="'.__('Remove Values', 'wsdesk').'" id="ticket_field_edit_values_remove" style="padding: 5px 8px;margin:0px 4px; vertical-align: baseline;"><span class="glyphicon glyphicon-minus"></span></button></span>';
                    }
                }
                $output .= $add_value;
                if($field_meta['field_default']=="")
                {
                    $def = "";
                }
                else
                {
                    $def = (isset($field_meta['field_values'][$field_meta['field_default']])?$field_meta['field_values'][$field_meta['field_default']]:"");
                }
                $output .= '<br><input type="text" id="ticket_field_edit_default" placeholder="'.__('Enter Default Values', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $def . '">';
                $output .= '<br><span class="help-block">'.__('Want to update description to this field?', 'wsdesk').' </span><textarea id="ticket_field_edit_description" class="form-control crm-form-element-input" style="padding: 10px !important;">' . $field_meta['field_description'] . '</textarea>';
                break;
            case 'checkbox':
                $required_end = "";
                if ($field_meta['field_require'] == "yes") {
                    $required_end = "checked";
                }
                $required_agent = "";
                if (isset($field_meta['field_require_agent']) && $field_meta['field_require_agent'] == "yes") {
                    $required_agent = "checked";
                }
                $visible = "";
                if (isset($field_meta['field_visible']) && $field_meta['field_visible'] == "yes") {
                    $visible = "checked";
                }
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_visible" class="form-control" name="ticket_field_edit_visible" ' . $visible . ' value="yes"> '.__('Visible for End Users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_require" class="form-control" name="ticket_field_edit_require" ' . $required_end . ' value="yes"> '.__('Mandatory for End users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_agent_require" class="form-control" name="ticket_field_edit_agent_require" ' . $required_agent . ' value="yes"> '.__('Mandatory for Agents', 'wsdesk').'</span>';
                $output .= '<span class="help-block">'.__('Update the Checkbox values!', 'wsdesk').' </span>';
                $field_values = array_values($field_meta['field_values']);
                $field_keys = array_keys($field_meta['field_values']);
                for ($i = 0; $i < count($field_values); $i++) {
                    if($i==0)
                    {
                        $output .= '<span id="ticket_field_edit_values_span_'. $i .'" class="ticket_field_edit_values_span"><input type="text" id="ticket_field_edit_values[' . $i . ']" class="form-control ticket_field_edit_values crm-form-element-input" value="' . $field_values[$i] . '"><input type="hidden" class="old_ticket_field_edit_values[' . $i . ']" id="'.$field_keys[$i].'" value="'.$field_values[$i].'"></span>';
                    }
                    else
                    {
                        $output .='<span id="ticket_field_edit_values_span_'. $i .'" class="ticket_field_edit_values_span"><input type="text" id="ticket_field_edit_values[' . $i . ']" class="form-control ticket_field_edit_values crm-form-element-input" style="width:90% !important;" value="' . $field_values[$i] . '"><input type="hidden" class="old_ticket_field_edit_values[' . $i . ']" id="'.$field_keys[$i].'" value="'.$field_values[$i].'"><button class="btn btn-warning" title="'.__('Remove Values', 'wsdesk').'" id="ticket_field_edit_values_remove" style="padding: 5px 8px;margin:0px 4px; vertical-align: baseline;"><span class="glyphicon glyphicon-minus"></span></button></span>';
                    }
                }
                $output .= $add_value;
                if($field_meta['field_default']=="")
                {
                    $def = "";
                }
                else
                {
                    $def = (isset($field_meta['field_values'][$field_meta['field_default']])?$field_meta['field_values'][$field_meta['field_default']]:"");
                }
                $output .= '<br><input type="text" id="ticket_field_edit_default" placeholder="'.__('Enter Default Values', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $def . '">';
                $output .= '<br><span class="help-block">'.__('Want to update description to this field?', 'wsdesk').' </span><textarea id="ticket_field_edit_description" class="form-control crm-form-element-input" style="padding: 10px !important;">' . $field_meta['field_description'] . '</textarea>';
                break;
            case 'select':
                $required_end = "";
                if ($field_meta['field_require'] == "yes") {
                    $required_end = "checked";
                }
                $required_agent = "";
                if (isset($field_meta['field_require_agent']) && $field_meta['field_require_agent'] == "yes") {
                    $required_agent = "checked";
                }
                $visible = "";
                if (isset($field_meta['field_visible']) && $field_meta['field_visible'] == "yes") {
                    $visible = "checked";
                }
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_visible" class="form-control" name="ticket_field_edit_visible" ' . $visible . ' value="yes"> '.__('Visible for End Users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_require" class="form-control" name="ticket_field_edit_require" ' . $required_end . ' value="yes"> '.__('Mandatory for End users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_agent_require" class="form-control" name="ticket_field_edit_agent_require" ' . $required_agent . ' value="yes"> '.__('Mandatory for Agents', 'wsdesk').'</span>';
                $output .= '<br><input type="text" id="ticket_field_edit_placeholder" placeholder="'.__('Enter Placeholder', 'wsdesk').'" class="form-control crm-form-element-input" value="' . (isset($field_meta['field_placeholder'])?$field_meta['field_placeholder']:'') . '">';
                $output .= '<span class="help-block">'.__('Update the Dropdown values!', 'wsdesk').' </span>';
                $field_values = array_values($field_meta['field_values']);
                $field_keys = array_keys($field_meta['field_values']);
                for ($i = 0; $i < count($field_values); $i++) {
                    if($i==0)
                    {
                        if(in_array($field, array("woo_product","woo_category","woo_tags")))
                        {
                            $output .='<span id="ticket_field_edit_values_span_'. $i .'" class="ticket_field_edit_values_span"><input type="text" id="ticket_field_edit_values[' . $i . ']" class="form-control ticket_field_edit_values crm-form-element-input" style="width:90% !important;" value="' . $field_values[$i] . '"><input type="hidden" class="old_ticket_field_edit_values[' . $i . ']" id="'.$field_keys[$i].'" value="'.$field_values[$i].'"><button class="btn btn-warning" title="'.__('Remove Values', 'wsdesk').'" id="ticket_field_edit_values_remove" style="padding: 5px 8px;margin:0px 4px; vertical-align: baseline;"><span class="glyphicon glyphicon-minus"></span></button></span>';
                        }
                        else
                        {
                            $output .= '<span id="ticket_field_edit_values_span_'. $i .'" class="ticket_field_edit_values_span"><input type="text" id="ticket_field_edit_values[' . $i . ']" class="form-control ticket_field_edit_values crm-form-element-input" value="' . $field_values[$i] . '"><input type="hidden" class="old_ticket_field_edit_values[' . $i . ']" id="'.$field_keys[$i].'" value="'.$field_values[$i].'"></span>';
                        }
                    }
                    else
                    {
                        $output .='<span id="ticket_field_edit_values_span_'. $i .'" class="ticket_field_edit_values_span"><input type="text" id="ticket_field_edit_values[' . $i . ']" class="form-control ticket_field_edit_values crm-form-element-input" style="width:90% !important;" value="' . $field_values[$i] . '"><input type="hidden" class="old_ticket_field_edit_values[' . $i . ']" id="'.$field_keys[$i].'" value="'.$field_values[$i].'"><button class="btn btn-warning" title="'.__('Remove Values', 'wsdesk').'" id="ticket_field_edit_values_remove" style="padding: 5px 8px;margin:0px 4px; vertical-align: baseline;"><span class="glyphicon glyphicon-minus"></span></button></span>';
                    }
                }
                $output .= $add_value;
                if($field_meta['field_default']=="")
                {
                    $def = "";
                }
                else
                {
                    $def = (isset($field_meta['field_values'][$field_meta['field_default']])?$field_meta['field_values'][$field_meta['field_default']]:"");
                }
                $output .= '<br><input type="text" id="ticket_field_edit_default" placeholder="'.__('Enter Default Values', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $def . '">';
                $output .= '<br><span class="help-block">'.__('Want to update description to this field?', 'wsdesk').' </span><textarea id="ticket_field_edit_description" class="form-control crm-form-element-input" style="padding: 10px !important;">' . $field_meta['field_description'] . '</textarea>';
                break;
            case 'textarea':
                if($field != 'request_description')
                {
                    $required_end = "";
                    if ($field_meta['field_require'] == "yes") {
                        $required_end = "checked";
                    }
                    $required_agent = "";
                    if (isset($field_meta['field_require_agent']) && $field_meta['field_require_agent'] == "yes") {
                        $required_agent = "checked";
                    }
                    $visible = "";
                    if (isset($field_meta['field_visible']) && $field_meta['field_visible'] == "yes") {
                        $visible = "checked";
                    }
                    $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_visible" class="form-control" name="ticket_field_edit_visible" ' . $visible . ' value="yes"> '.__('Visible for End Users', 'wsdesk').'</span>';
                    $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_require" class="form-control" name="ticket_field_edit_require" ' . $required_end . ' value="yes"> '.__('Mandatory for End users', 'wsdesk').'</span>';
                    $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_agent_require" class="form-control" name="ticket_field_edit_agent_require" ' . $required_agent . ' value="yes"> '.__('Mandatory for Agents', 'wsdesk').'</span>';
                }
                $output .= '<br><input type="text" id="ticket_field_edit_default" placeholder="'.__('Enter Default Values', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $field_meta['field_default'] . '">';
                $output .= '<br><span class="help-block">'.__('Want to update description to this field?', 'wsdesk').' </span><textarea id="ticket_field_edit_description" class="form-control crm-form-element-input" style="padding: 10px !important;">' . $field_meta['field_description'] . '</textarea>';
                break;
            case "date":
                $required_end = "";
                if ($field_meta['field_require'] == "yes") {
                    $required_end = "checked";
                }
                $required_agent = "";
                if (isset($field_meta['field_require_agent']) && $field_meta['field_require_agent'] == "yes") {
                    $required_agent = "checked";
                }
                $visible = "";
                if (isset($field_meta['field_visible']) && $field_meta['field_visible'] == "yes") {
                    $visible = "checked";
                }
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_visible" class="form-control" name="ticket_field_edit_visible" ' . $visible . ' value="yes"> '.__('Visible for End Users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_require" class="form-control" name="ticket_field_edit_require" ' . $required_end . ' value="yes"> '.__('Mandatory for End users', 'wsdesk').'</span>';
                $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_agent_require" class="form-control" name="ticket_field_edit_agent_require" ' . $required_agent . ' value="yes"> '.__('Mandatory for Agents', 'wsdesk').'</span>';
                $output .= '<br><input type="text" id="ticket_field_edit_placeholder" placeholder="'.__('Enter Placeholder', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $field_meta['field_placeholder'] . '">';
                $output .= '<br><span class="help-block">'.__('Want to update description to this field?', 'wsdesk').' </span><textarea id="ticket_field_edit_description" class="form-control crm-form-element-input" style="padding: 10px !important;">' . $field_meta['field_description'] . '</textarea>';
                break;
            default :
                if($field != 'request_email' && $field != 'request_title')
                {
                    $required_end = "";
                    if ($field_meta['field_require'] == "yes") {
                        $required_end = "checked";
                    }
                    $required_agent = "";
                    if (isset($field_meta['field_require_agent']) && $field_meta['field_require_agent'] == "yes") {
                        $required_agent = "checked";
                    }
                    $visible = "";
                    if (isset($field_meta['field_visible']) && $field_meta['field_visible'] == "yes") {
                        $visible = "checked";
                    }
                    $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_visible" class="form-control" name="ticket_field_edit_visible" ' . $visible . ' value="yes"> '.__('Visible for End Users', 'wsdesk').'</span>';
                    $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_require" class="form-control" name="ticket_field_edit_require" ' . $required_end . ' value="yes"> '.__('Mandatory for End users', 'wsdesk').'</span>';
                    $output .= '<span class="help-block"><input type="checkbox" style="margin-top: 0;"  id="ticket_field_edit_agent_require" class="form-control" name="ticket_field_edit_agent_require" ' . $required_agent . ' value="yes"> '.__('Mandatory for Agents', 'wsdesk').'</span>';
                }
                $output .= '<br><input type="text" id="ticket_field_edit_placeholder" placeholder="'.__('Enter Placeholder', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $field_meta['field_placeholder'] . '">';
                $output .= '<br><input type="text" id="ticket_field_edit_default" placeholder="'.__('Enter Default Values', 'wsdesk').'" class="form-control crm-form-element-input" value="' . $field_meta['field_default'] . '">';
                $output .= '<br><span class="help-block">'.__('Want to update description to this field?', 'wsdesk').' </span><textarea id="ticket_field_edit_description" class="form-control crm-form-element-input" style="padding: 10px !important;">' . $field_meta['field_description'] . '</textarea>';
                break;
        }
        die($output);
    }

    static function eh_crm_ticket_label_delete() {
        $label_remove = sanitize_text_field($_POST['label_remove']);
        $args = array("type" => "label");
        $fields = array("settings_id", "slug");
        $avail_labels = eh_crm_get_settings($args, $fields);
        for ($i = 0; $i < count($avail_labels); $i++) {
            if ($avail_labels[$i]["slug"] == $label_remove) {
                eh_crm_delete_settings($avail_labels[$i]["settings_id"]);
            }
        }
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_labels.php"));
    }
    
    static function eh_crm_ticket_label() {
        $new_label = json_decode(stripslashes(sanitize_text_field($_POST['new_label'])), true);
        $edit_label = json_decode(stripslashes(sanitize_text_field($_POST['edit_label'])), true);
        if (!empty($new_label)) {
            $insert = array(
                'title' => $new_label['title'],
                'filter' => $new_label['filter'],
                'type' => 'label',
                'vendor' => ''
            );
            $meta = array
                (
                "label_color" => $new_label['color']
            );
            eh_crm_insert_settings($insert, $meta);
        }
        if (!empty($edit_label)) {
            $edit_slug = $edit_label['slug'];
            $edit_title = $edit_label['title'];
            $edit_filter = $edit_label['filter'];
            $edit_color = $edit_label['color'];
            $label_data = eh_crm_get_settings(array("slug" => $edit_slug, "type" => "label"), "settings_id");
            $label_id = $label_data[0]['settings_id'];
            eh_crm_update_settings($label_id, array("title" => $edit_title, "filter" => $edit_filter));
            eh_crm_update_settingsmeta($label_id, "label_color", $edit_color);
        }
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_labels.php"));
    }

    static function eh_crm_ticket_label_edit() {
        $label = sanitize_text_field($_POST['label']);
        $args = array("slug" => $label, "type" => "label");
        $fields = array("settings_id", "title", "filter");
        $label_sett = eh_crm_get_settings($args, $fields);
        $label_meta = eh_crm_get_settingsmeta($label_sett[0]['settings_id']);
        $yes = "";
        $no = "";
        if ($label_sett[0]['filter'] == "yes") {
            $yes = "checked";
            $no = "";
        } else {
            $yes = "";
            $no = "checked";
        }
        $output = '     
                    <span class="help-block">Update Details for ' . $label_sett[0]['title'] . ' status </span>
                    <input type="text" id="ticket_label_edit_title" placeholder="Enter Title" class="form-control crm-form-element-input" value="' . $label_sett[0]['title'] . '">
                    <span class="help-block">Do you want to change the status color?.</span>
                    <span style="vertical-align: middle;">
                        <input type="color" id="ticket_label_edit_color" value = "' . $label_meta['label_color'] . '"/><span> Click and Pick the Color</span>
                    </span>
                    <span class="help-block">Want to use this status for Filter Tickets? </span>
                    <input type="radio" style="margin-top: 0;" checked id="ticket_label_edit_filter" class="form-control" name="ticket_label_edit_filter" ' . $yes . ' value="yes"> Yes! I will use it for Filter<br>
                    <input type="radio" style="margin-top: 0;" id="ticket_label_edit_filter" class="form-control" name="ticket_label_edit_filter" ' . $no . ' value="no"> No! Just for Information';
        die($output);
    }

    static function eh_crm_ticket_tag_delete() {
        $tag_remove = sanitize_text_field($_POST['tag_remove']);
        $args = array("type" => "tag");
        $fields = array("settings_id", "slug");
        $avail_tags = eh_crm_get_settings($args, $fields);
        for ($i = 0; $i < count($avail_tags); $i++) {
            if ($avail_tags[$i]["slug"] == $tag_remove) {
                eh_crm_delete_settings($avail_tags[$i]["settings_id"]);
            }
        }
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_tags.php"));
    }
    
    static function eh_crm_ticket_tag() {
        $new_tag = json_decode(stripslashes(sanitize_text_field($_POST['new_tag'])), true);
        $edit_tag = json_decode(stripslashes(sanitize_text_field($_POST['edit_tag'])), true);
        if (!empty($new_tag)) {
            $insert = array(
                'title' => $new_tag['title'],
                'filter' => $new_tag['filter'],
                'type' => 'tag',
                'vendor' => ''
            );
            $meta = array("tag_posts" => $new_tag['posts']);
            eh_crm_insert_settings($insert, $meta);
        }
        if (!empty($edit_tag)) {
            $edit_slug = $edit_tag['slug'];
            $edit_title = $edit_tag['title'];
            $edit_filter = $edit_tag['filter'];
            $edit_posts = $edit_tag['posts'];
            $tag_data = eh_crm_get_settings(array("slug" => $edit_slug, "type" => "tag"), "settings_id");
            $tag_id = $tag_data[0]['settings_id'];
            eh_crm_update_settings($tag_id, array("title" => $edit_title, "filter" => $edit_filter));
            eh_crm_update_settingsmeta($tag_id, "tag_posts", $edit_posts);
        }
        die(include(EH_CRM_MAIN_VIEWS . "settings/crm_settings_tags.php"));
    }

    static function eh_crm_ticket_tag_edit() {
        $tag = sanitize_text_field($_POST['tag']);
        $args = array("slug" => $tag, "type" => "tag");
        $fields = array("settings_id", "title", "filter");
        $tag_sett = eh_crm_get_settings($args, $fields);
        $tag_meta = eh_crm_get_settingsmeta($tag_sett[0]['settings_id']);
        $yes = "";
        $no = "";
        if ($tag_sett[0]['filter'] == "yes") {
            $yes = "checked";
            $no = "";
        } else {
            $yes = "";
            $no = "checked";
        }
        $response = array();
        if(!empty($tag_meta['tag_posts']))
        {
            $args_post = array(
                'orderby' => 'ID',
                'numberposts' => -1,
                'post_type' => array('post', 'product'),
                'post__in' => $tag_meta['tag_posts']
            );
            $posts = get_posts($args_post);
            for ($i = 0; $i < count($posts); $i++) {
                $response[$i]['id'] = $posts[$i]->ID;
                $response[$i]['title'] = $posts[$i]->post_title;
            }
        }
        $output = '   
                    <span class="help-block">Update Details for ' . $tag_sett[0]['title'] . ' Tag? </span>
                    <input type="text" id="ticket_tag_edit_title" placeholder="Enter Title" class="form-control crm-form-element-input" value="' . $tag_sett[0]['title'] . '">
                    <span class="help-block">Update the Post which should be Tagged if required? </span>
                    <select class="ticket_tag_edit_posts form-control crm-form-element-input" multiple="multiple">
                    ';
        if (!empty($response)) {
            for ($i = 0; $i < count($response); $i++) {
                $output .= '<option value="' . $response[$i]['id'] . '" selected title="' . $response[$i]['title'] . '"></option>';
            }
        }
        $output .= '</select>
                    <span class="help-block">Want to use this Tag for Filter Tickets? </span>
                    <input type="radio" style="margin-top: 0;"  id="ticket_tag_edit_filter" class="form-control" name="ticket_tag_edit_filter" ' . $yes . ' value="yes"> Yes! I will use it for Filter<br>
                    <input type="radio" style="margin-top: 0;" id="ticket_tag_edit_filter" class="form-control" name="ticket_tag_edit_filter" ' . $no . ' value="no"> No! Just for Information';
        die($output);
    }

    static function eh_crm_search_post() {
        global $wpdb;
        $table = $wpdb->prefix . 'posts';
        $like = sanitize_text_field($_POST['q']);
        $search_query = "SELECT ID FROM " . $table . " WHERE ( LOWER(post_title) LIKE lower('%$like%') OR  LOWER(post_content) LIKE lower('%$like%') ) AND post_status ='publish'";
        $quote_ids = array();
        $response = array();
        $results = $wpdb->get_results($search_query, ARRAY_A);
        for ($i = 0; $i < count($results); $i++) {
            $quote_ids[$i] = $results[$i]['ID'];
        }
        $args = array(
            'orderby' => 'ID',
            'numberposts' => -1,
            'post_type' => array('post', 'product'),
            'post__in' => $quote_ids
        );
        $posts = array();
        if(!empty($quote_ids))
        {
            $posts = get_posts($args);
        }
        for ($i = 0; $i < count($posts); $i++) {
            $response[$i]['id'] = $posts[$i]->ID;
            $response[$i]['title'] = $posts[$i]->post_title;
            $response[$i]['guid'] = $posts[$i]->guid;
            $response[$i]['content'] = (strlen($posts[$i]->post_content) > 100 ? substr($posts[$i]->post_content,0,100)."..." : $posts[$i]->post_content);
            switch ($posts[$i]->post_type) {
                case 'post':
                    $response[$i]['type'] = 'Post';
                    break;
                case 'product':
                    $response[$i]['type'] = 'Product';
                    break;
            }
        }
        $res = array("total_count" => count($posts), "items" => $response);
        die(json_encode($res));
    }

    static function eh_crm_search_tags() {
        global $wpdb;
        $table = $wpdb->prefix . 'wsdesk_settings';
        $search_query = 'SELECT settings_id,slug,title FROM ' . $table . ' WHERE LOWER(title) LIKE %s AND type ="tag"';
        $like = '%' . sanitize_text_field($_POST['q']) . '%';
        $response = array();
        $results = $wpdb->get_results($wpdb->prepare($search_query, $like), ARRAY_A);
        for ($i = 0; $i < count($results); $i++) {
            $response[$i]['id'] = $results[$i]['slug'];
            $response[$i]['title'] = $results[$i]['title'];
            $meta = eh_crm_get_settingsmeta($results[$i]['settings_id'], "tag_posts");
            if($meta)
            {
                $post = get_post($meta[0]);
                $post_title = strlen($post->post_title) > 15 ? substr($post->post_title, 0, 15) : $post->post_title;
            }
            else 
            {
                $meta = array();
            }
            $res_post = "";
            switch (count($meta)) {
                case 0:
                    $res_post = "No Post Tagged";
                    break;
                case 1:
                    $res_post = $post_title;
                    break;
                default:
                    $res_post = $post_title . " + " . (count($meta) - 1) . " more item";
                    break;
            }
            $response[$i]['posts'] = $res_post;
        }
        $res = array("total_count" => count($results), "items" => $response);
        die(json_encode($res));
    }
    
    static function eh_crm_agent_add_user()
    {
        $role = sanitize_text_field($_POST['role']);
        switch ($role) {
            case "agents":
                $role ="WSDesk_Agents";
                break;
            case "supervisor":
                $role = "WSDesk_Supervisor";
                break;
        }
        $rights = explode(",", sanitize_text_field($_POST['rights']));
        $user_pass = sanitize_text_field($_POST['password']);
        $user_email = sanitize_text_field($_POST['email']);
        $email_check = email_exists($user_email);
        $tags = (($_POST['tags'] !== "") ? explode(",", sanitize_text_field($_POST['tags'])) : NULL);
        $message = "";
        $code = '';
        if($email_check)
        {
            $message = "Email already exists";
            $code = "failed";

        }
        else
        {
            $maybe_username = explode('@', $user_email);
            $maybe_username = sanitize_user($maybe_username[0]);
            $counter = 1;
            $username = $maybe_username;

            while (username_exists($username)) {
                $username = $maybe_username . $counter;
                $counter++;
            }
            $user_login = $username;
            $userdata = compact('user_login', 'user_email', 'user_pass','role');
            $user = wp_insert_user($userdata);
            if(!is_wp_error($user))
            {
                $created = new WP_User($user);
                $created->add_role($role);
                for ($j = 0; $j < count($rights); $j++) {
                    switch ($rights[$j]) {
                        case 'reply':
                            $created->add_cap("reply_tickets", 1);
                            break;
                        case 'delete':
                            $created->add_cap("delete_tickets", 1);
                            break;
                        case 'manage':
                            $created->add_cap("manage_tickets", 1);
                            break;
                        case 'templates':
                            $created->add_cap("manage_templates", 1);
                            break;
                        case 'settings':
                            $created->add_cap("settings_page", 1);
                            break;
                        case 'agents':
                            $created->add_cap("agents_page", 1);
                            break;
                        case 'email':
                            $created->add_cap("email_page", 1);
                            break;
                        case 'import':
                            $created->add_cap("import_page", 1);
                            break;
                        default:
                            break;
                    }
                }
                update_user_meta($user, "wsdesk_tags", $tags);
                $message = "User created successfully";
                $code = "success";
            }
            else
            {
                $message = "Something went wrong!";
                $code = "failed";
            }
        }
        $add_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_add.php");
        $manage_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_manage.php");
        die(json_encode(array("add" => $add_agents, "manage" => $manage_agents, "message" => $message,"code"=>$code)));
    }
    
    static function eh_crm_agent_add() {
        $users = explode(",", sanitize_text_field($_POST['users']));
        $role = sanitize_text_field($_POST['role']);
        $rights = explode(",", sanitize_text_field($_POST['rights']));
        $tags = (($_POST['tags'] !== "") ? explode(",", sanitize_text_field($_POST['tags'])) : NULL);
        for ($i = 0; $i < count($users); $i++) {
            $user_id = $users[$i];
            $user = new WP_User($user_id);
            $roles = $user->roles;
            foreach ($roles as $r) {
                $user->remove_role($r);
            }
            switch ($role) {
                case "agents":
                    $user->add_role("WSDesk_Agents");
                    break;
                case "supervisor":
                    $user->add_role("WSDesk_Supervisor");
                    break;
            }
            foreach ($roles as $r) {
                $user->add_role($r);
            }
            for ($j = 0; $j < count($rights); $j++) {
                switch ($rights[$j]) {
                    case 'reply':
                        $user->add_cap("reply_tickets", 1);
                        break;
                    case 'delete':
                        $user->add_cap("delete_tickets", 1);
                        break;
                    case 'manage':
                        $user->add_cap("manage_tickets", 1);
                        break;
                    case 'templates':
                        $user->add_cap("manage_templates", 1);
                            break;
                    case 'settings':
                        $user->add_cap("settings_page", 1);
                        break;
                    case 'agents':
                        $user->add_cap("agents_page", 1);
                        break;
                    case 'email':
                        $user->add_cap("email_page", 1);
                        break;
                    case 'import':
                        $user->add_cap("import_page", 1);
                        break;
                    default:
                        break;
                }
            }
            update_user_meta($user_id, "wsdesk_tags", $tags);
        }
        $add_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_add.php");
        $manage_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_manage.php");
        die(json_encode(array("add" => $add_agents, "manage" => $manage_agents)));
    }

    static function eh_crm_edit_agent_html() {
        $user_id = sanitize_text_field($_POST['user_id']);
        $user = new WP_User($user_id);
        $tags_temp = get_user_meta($user_id, "wsdesk_tags", true);
        $caps_temp = array_keys($user->caps);
        $reply = '';
        $delete = '';
        $manage = '';
        $manage_temp = '';
        $settings = '';
        $agents = '';
        $email = '';
        $import = '';
        $checked = '';
        $disabled = '';
        $admin_message =  '';
        if(in_array("administrator", $user->roles))
        {
            $reply = 'checked';
            $delete = 'checked';
            $manage = 'checked';
            $manage_temp = 'checked';
            $settings = 'checked';
            $agents = 'checked';
            $email = 'checked';
            $import = 'checked';
            $disabled = 'disabled';
            $admin_message = "(WSDesk Rights for Administrator cannot be edited.)";
        }
        for ($j = 0; $j < count($caps_temp); $j++) {
            switch ($caps_temp[$j]) {                
                case "reply_tickets":
                    $reply = 'checked';
                    break;
                case "delete_tickets":
                    $delete = 'checked';
                    break;
                case "manage_tickets":
                    $manage = 'checked';
                    break;
                case "manage_templates":
                    $manage_temp = 'checked';
                    break;
                case "settings_page":
                    $settings = 'checked';
                    break;
                case "agents_page":
                    $agents = 'checked';
                    break;
                case "email_page":
                    $email = 'checked';
                    break;
                case "import_page":
                    $import = 'checked';
                    break;
            }
        }
        $access = '<input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_reply" value="reply" ' . $reply . '> Reply to Tickets<br>
                    <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_delete" value="delete" ' . $delete . '> Delete Tickets<br>
                    <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_manage" value="manage" ' . $manage . '> Manage Tickets<br>';

        if (in_array("WSDesk_Supervisor", $user->roles) || in_array("administrator", $user->roles)) {
            $access .= '
                        <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_templates" value="templates" ' . $manage_temp . '> Manage Templates<br>
                        <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_settings" value="settings" ' . $settings . '> Show Settings Page<br>
                        <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_agents" value="agents" ' . $agents . '> Show Agents Page<br>
                        <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_email" value="email" ' . $email . '> Show Email Page<br>
                        <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" id="edit_agents_rights_import" value="import" ' . $import . '> Show Import Page<br>
                        <input '.$disabled.' type="checkbox" style="margin-top: 0;" class="form-control" name="edit_agents_rights_' . $user_id . '" disabled> Merge Tickets <span class="wsdesk_super">Premium</span><br>';
        }
        $tags = '';
        if (!empty($tags_temp)) {
            for ($j = 0; $j < count($tags_temp); $j++) {
                $tag = eh_crm_get_settings(array("slug" => $tags_temp[$j], "type" => "tag"), array("title"));
                if(!empty($tag))
                {
                    $tags .= '<option selected value="' . $tags_temp[$j] . '" title="' . $tag[0]['title'] . '">  </option>';
                }
            }
        }
        $output = '<span class="crm-divider"></span>
                    <div class="crm-form-element">
                        <div class="col-md-3">
                            <label for="edit_agents_rights" style="padding-right:1em !important;">WSDesk Rights</label>
                        </div>
                        <div class="col-md-9">
                            <span class="help-block">Mention Access Rights that are going to assign for selected User(s)? '.$admin_message.'</span>
                            <span style="vertical-align: middle;" id="edit_agents_access_rights">
                                ' . $access . '
                            </span>
                        </div>
                    </div>
                    <div class="crm-form-element">
                        <div class="col-md-3">
                            <label for="edit_agents_tags" style="padding-right:1em !important;">Edit tags</label>
                        </div>
                        <div class="col-md-9">
                            <span class="help-block">Wish to edit ticket tags for Users? <br>The tickets will be assigned automatically if Default Assignee is [ Depends on Tags ]</span>
                            <select class="edit_agents_tags_' . $user_id . '" multiple="multiple">
                                ' . $tags . '
                            </select>
                        </div>
                    </div>
                    <span class="crm-divider"></span>
                    <div class="crm-form-element">
                        <button type="button" id="save_edit_agents_' . $user_id . '" class="btn btn-primary btn-sm save_edit_agents" style="margin-left:10px;">Update Agents</button>
                        <button type="button" id="cancel_edit_agents_' . $user_id . '" class="btn btn-default btn-sm cancel_edit_agents" style="margin-left:10px;">Cancel Update</button>
                    </div>';
        die($output);
    }

    static function eh_crm_edit_agent() {
        $user_id = sanitize_text_field($_POST['user_id']);
        $rights = explode(",", sanitize_text_field($_POST['rights']));
        $tags = (($_POST['tags'] !== "") ? explode(",", sanitize_text_field($_POST['tags'])) : NULL);
        $user = new WP_User($user_id);
            $user->remove_cap("reply_tickets");
            $user->remove_cap("delete_tickets");
            $user->remove_cap("manage_tickets");
            $user->remove_cap("manage_templates");
            $user->remove_cap("settings_page");
            $user->remove_cap("agents_page");
            $user->remove_cap("email_page");
            $user->remove_cap("import_page");
        for ($j = 0; $j < count($rights); $j++) {
            switch ($rights[$j]) {                
                case 'reply':
                    $user->add_cap("reply_tickets", 1);
                    break;
                case 'delete':
                    $user->add_cap("delete_tickets", 1);
                    break;
                case 'manage':
                    $user->add_cap("manage_tickets", 1);
                    break;
                case 'templates':
                    $user->add_cap("manage_templates", 1);
                        break;
                case 'settings':
                    $user->add_cap("settings_page", 1);
                    break;
                case 'agents':
                    $user->add_cap("agents_page", 1);
                    break;
                case 'email':
                    $user->add_cap("email_page", 1);
                    break;
                case 'import':
                    $user->add_cap("import_page", 1);
                    break;
                default:
                    break;
            }
        }
        update_user_meta($user_id, "wsdesk_tags", $tags);
        $add_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_add.php");
        $manage_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_manage.php");
        die(json_encode(array("add" => $add_agents, "manage" => $manage_agents)));
    }

    static function eh_crm_remove_agent() {
        $user_id = sanitize_text_field($_POST['user_id']);
        $user = new WP_User($user_id);
        if (in_array("WSDesk_Supervisor", $user->roles)) {
            $user->remove_cap("reply_tickets");
            $user->remove_cap("delete_tickets");
            $user->remove_cap("manage_tickets");
            $user->remove_cap("manage_templates");
            $user->remove_cap("settings_page");
            $user->remove_cap("agents_page");
            $user->remove_cap("email_page");
            $user->remove_cap("import_page");
            $user->remove_role("WSDesk_Supervisor");
        } 
        else if(in_array("administrator", $user->roles))
        {
            $user->remove_cap("reply_tickets");
            $user->remove_cap("delete_tickets");
            $user->remove_cap("manage_tickets");
            $user->remove_cap("manage_templates");
            $user->remove_cap("settings_page");
            $user->remove_cap("agents_page");
            $user->remove_cap("email_page");
            $user->remove_cap("import_page");
            $user->remove_role("administrator");
        }
        else {
            $user->remove_cap("reply_tickets");
            $user->remove_cap("delete_tickets");
            $user->remove_cap("manage_tickets");
            $user->remove_role("WSDesk_Agents");
        }
        delete_user_meta($user_id, "wsdesk_tags");
        $add_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_add.php");
        $manage_agents = include(EH_CRM_MAIN_VIEWS . "agents/crm_agents_manage.php");
        die(json_encode(array("add" => $add_agents, "manage" => $manage_agents)));
    }
    
    static function eh_crm_new_ticket_post() {
        $post_values = array();
        parse_str($_POST['form'], $post_values);
        $files = isset($_FILES["file"])?$_FILES["file"]:"";
        $email = $post_values['request_email'];
        $title = stripslashes($post_values['request_title']);
        $desc = str_replace("\n", '<br/>', stripslashes($post_values['request_description']));
        $args = array(
            'ticket_email' => $email,
            'ticket_title' => $title,
            'ticket_content' => $desc,
            'ticket_category' => 'raiser_reply',
            'ticket_vendor' => ''
        );
        if(eh_crm_get_settingsmeta(0,"auto_create_user") === 'enable')
        {
            $email_check = email_exists($email);
            if($email_check)
            {
                $args['ticket_author'] = $email_check;
            }
            else
            {
                
                $maybe_username = explode('@', $email);
                $maybe_username = sanitize_user($maybe_username[0]);
                $counter = 1;
                $username = $maybe_username;
                $password = wp_generate_password(12, true);

                while (username_exists($username)) {
                    $username = $maybe_username . $counter;
                    $counter++;
                }

                $user = wp_create_user($username, $password, $email);
                if(!is_wp_error($user))
                {
                    wp_new_user_notification($user,null,'both');
                    $args['ticket_author'] = $user;
                }
            }
        }
        unset($post_values['request_email']);
        unset($post_values['request_title']);
        unset($post_values['request_description']);
        $meta = array();
        $default_assignee = eh_crm_get_settingsmeta('0', "default_assignee");
        $assignee = array();
        switch ($default_assignee) {
            case "no_assignee":
                break;
            default:
                array_push($assignee, $default_assignee);
                break;
        }
        $meta['ticket_assignee'] = $assignee;
        $meta['ticket_tags'] = array();
        $default_label = eh_crm_get_settingsmeta('0', "default_label");
        if(eh_crm_get_settings(array('slug'=>$default_label)))
        {
            $meta['ticket_label'] = $default_label;
        }
        foreach ($post_values as $key => $value) {
            $meta[$key] = $value;
        }
        if(isset($_FILES["file"]) && !empty($_FILES['file']))
        {   
            $attachment_data = CRM_Ajax::eh_crm_ticket_file_handler($files);
            $meta["ticket_attachment"] = $attachment_data['url'];
            $meta["ticket_attachment_path"] = $attachment_data['path'];
        }
        $meta['ticket_source'] = "Form";
        $gen_id = eh_crm_insert_ticket($args, $meta);
        die("Support Request Received Successfully");
    }
    
    static function eh_crm_new_ticket_form() {
        die(include(EH_CRM_MAIN_VIEWS . "support/crm_support_new.php"));
    }
    
    static function eh_crm_ticket_single_view() {
        $ticket_id = sanitize_text_field($_POST['ticket_id']);
        $content = CRM_Ajax::eh_crm_ticket_single_view_gen($ticket_id);
        $tab = CRM_Ajax::eh_crm_ticket_single_view_gen_head($ticket_id);
        die(json_encode(array("tab_head"=>$tab,"tab_content"=>$content)));
    }
    
    static function eh_crm_ticket_single_view_gen_head($ticket_id) {
        $current = eh_crm_get_ticket(array("ticket_id"=>$ticket_id));
        $tab = '<a href="#tab_content_'.$ticket_id.'" id="tab_content_a_'.$ticket_id.'" aria-controls="#'.$ticket_id.'" role="tab" data-toggle="tab" class="tab_a" style="font-size: 12px;padding: 11px 5px;margin-right:0px !important;"><button type="button" class="btn btn-default btn-circle close_tab pull-right"><span class="glyphicon glyphicon-remove"></span></button><div class="badge">#'.$ticket_id.'</div><span class="tab_head"> '. stripslashes(html_entity_decode(htmlentities($current[0]['ticket_title']))).'</span></a>';
        return $tab;
    }
    
    static function eh_crm_ticket_single_view_gen($ticket_id) {
        ob_start();
        $current = eh_crm_get_ticket(array("ticket_id"=>$ticket_id));
        $current_meta = eh_crm_get_ticketmeta($ticket_id);
        $logged_user = wp_get_current_user();
        $logged_user_caps = array_keys($logged_user->caps);
        $avail_caps = array("reply_tickets","delete_tickets","manage_tickets");
        $access = array();
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
        $users_data = get_users(array("role__in" => array("administrator", "WSDesk_Agents", "WSDesk_Supervisor")));
        $users = array();
        for ($i = 0; $i < count($users_data); $i++) {
            $current_user = $users_data[$i];
            $temp = array();
            $roles = $current_user->roles;
            foreach ($roles as $value) {
                $current_role = $value;
                $temp[$i] = ucfirst(str_replace("_", " ", $current_role));
            }
            $users[implode(' & ', $temp)][$current_user->ID] = $current_user->data->display_name;
        }
        $avail_fields = eh_crm_get_settings(array("type" => "field"), array("slug", "title", "settings_id"));
        $selected_fields = eh_crm_get_settingsmeta(0, 'selected_fields');
        $avail_tags = eh_crm_get_settings(array("type" => "tag"),array("slug","title","settings_id"));
        $avail_labels = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
        $ticket_label = "";
        $ticket_label_slug ="";
        $eye_color = "";
        for($j=0;$j<count($avail_labels);$j++)
        {
            if($avail_labels[$j]['slug'] == $current_meta['ticket_label'])
            {
                $ticket_label = $avail_labels[$j]['title'];
                $ticket_label_slug = $avail_labels[$j]['slug'];
            }
            if($avail_labels[$j]['slug'] == $current_meta['ticket_label'])
            {
                $eye_color = eh_crm_get_settingsmeta($avail_labels[$j]['settings_id'], "label_color");
            }
        }
        $ticket_tags_list = "";
        $response = array();
        $co = 0;
        if(!empty($avail_tags))
        {
            for($j=0;$j<count($avail_tags);$j++)
            {
                $current_ticket_tags=(isset($current_meta['ticket_tags'])?$current_meta['ticket_tags']:array());
                for($k=0;$k<count($current_ticket_tags);$k++)
                {
                    if($avail_tags[$j]['slug'] == $current_ticket_tags[$k])
                    {
                        $args_post = array(
                            'orderby' => 'ID',
                            'numberposts' => -1,
                            'post_type' => array('post', 'product'),
                            'post__in' => eh_crm_get_settingsmeta($avail_tags[$j]['settings_id'], 'tag_posts')
                        );
                        $posts = get_posts($args_post);
                        $temp = get_post();
                        for ($m = 0; $m < count($posts); $m++,$co++) {
                            $response[$co]['title'] = $posts[$m]->post_title;
                            $response[$co]['guid'] = $posts[$m]->guid;
                        }
                        $ticket_tags_list .= '<span class="label label-info">#'.$avail_tags[$j]['title'].'</span>';
                    }
                }
            }
        }
        ?>
            <div class="row" style="margin-top: 10px;">
                <div class="col-md-12">
                    <ol class="breadcrumb col-md-8" style="margin: 0 !important;background: none !important;border:none;padding: 8px 0px !important; ">
                        <li><?php echo get_bloginfo("name") ?></li>
                        <li><?php echo $ticket_label; ?></li>
                        <li class="active"><span class="label label-success" style="background-color:<?php echo $eye_color; ?> !important">Ticket #<?php echo $ticket_id; ?></span></li>
                        <span class="spinner_loader ticket_loader_<?php echo $ticket_id; ?>">
                            <span class="bounce1"></span>
                            <span class="bounce2"></span>
                            <span class="bounce3"></span>
                        </span>
                    </ol>
                    <?php
                        if(in_array("delete_tickets", $access))
                        {
                            echo '<button type="button" class="btn btn-default ticket_action_delete pull-right" id="'.$ticket_id.'">
                                    <span class="glyphicon glyphicon-trash"></span>
                                  </button>';
                        }
                    ?>
                </div>
            </div>
            <span class="crm-divider" style="margin-bottom:2px;margin-left: -15px;width: 102.8%;"></span>
            <div class="row">
                <div class="col-md-3" style="padding-right: 0px;padding-top: 10px;">
                    <div class="form-group">
                        <span class="help-block"><?php _e("Assignee", 'wsdesk'); ?></span>
                        <select id="assignee_ticket_<?php echo $ticket_id; ?>" class="form-control" aria-describedby="helpBlock" multiple="multiple">
                            <?php
                                $assignee = (isset($current_meta['ticket_assignee'])?$current_meta['ticket_assignee']:array());
                                if($assignee!=="")
                                {
                                    foreach ($users as $key => $value) {
                                        if(in_array("manage_tickets", $access))
                                        {
                                            foreach ($value as $id => $name) {
                                                $selected = '';
                                                if (in_array($id, $assignee)) {
                                                    $selected = 'selected';
                                                }
                                                echo '<option value="' . $id . '" ' . $selected . '>'.$name.' | '.$key.'</option>';
                                            }
                                        }
                                        else
                                        {
                                            foreach ($value as $id => $name) {
                                                if (in_array($id, $assignee)) {
                                                    echo '<option value="' . $id . '" selected>'.$name.' | '.$key.'</option>';
                                                }
                                            }
                                        }                                        
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <?php
                        $cc = (isset($current_meta['ticket_cc'])?$current_meta['ticket_cc']:array());
                        ?>
                        <div class="form-group">
                            <span class="help-block"><?php _e("CC", 'wsdesk'); ?> <span class="glyphicon glyphicon-info-sign" style="color:lightgray;font-size:x-small;vertical-align:baseline;" data-toggle="wsdesk_tooltip" title="<?php _e("To add multiple CC, separate each address with comma without any space.", 'wsdesk'); ?>" data-container="body"></span></span>

                            <input type="text" id="cc_ticket_<?php echo $ticket_id; ?>" class="form-control cc_<?php echo $ticket_id; ?>" placeholder = "CC" value = "<?php echo join(',', $cc);?>">
                        </div>
                    <?php
                        $bcc = (isset($current_meta['ticket_bcc'])?$current_meta['ticket_bcc']:array());
                        if(!empty($bcc))
                        {
                        ?>
                            <div class="form-group">
                                <span class="help-block"><?php _e("Bcc", 'wsdesk'); ?></span>
                                <select id="bcc_ticket_<?php echo $ticket_id; ?>" class="form-control bcc_select_<?php echo $ticket_id; ?>" aria-describedby="helpBlock" multiple="multiple">
                                    <?php
                                        foreach ($bcc as $key => $value) {
                                            if(in_array("manage_tickets", $access))
                                            {
                                                echo '<option value="' . $value . '" selected>'.$value.'</option>';
                                            }
                                            else
                                            {
                                                echo '<option value="' . $value . '" selected>'.$value.'</option>';
                                            }                                        
                                        }
                                    ?>
                                </select>
                            </div>
                        <?php
                        }
                    ?>
                    <div class="form-group">
                        <span class="help-block"><?php _e("Tags", 'wsdesk'); ?></span>
                        <select id="tags_ticket_<?php echo $ticket_id; ?>" class="form-control crm-form-element-input" multiple="multiple">
                            <?php
                                $ticket_tags = (isset($current_meta['ticket_tags'])?$current_meta['ticket_tags']:array());
                                if($ticket_tags!=="" && !empty($avail_tags))
                                {
                                    for($i=0;$i<count($avail_tags);$i++)
                                    {
                                        if(in_array("manage_tickets", $access))
                                        {
                                            $selected = '';
                                            if(in_array($avail_tags[$i]['slug'], $ticket_tags))
                                            {
                                                $selected = 'selected';
                                            }
                                            echo '<option value="' . $avail_tags[$i]['slug'] . '" ' . $selected . '>'.$avail_tags[$i]['title'].'</option>';
                                        }
                                        else
                                        {
                                            if (in_array($avail_tags[$i]['slug'], $ticket_tags)) {
                                                echo '<option value="' . $avail_tags[$i]['slug'] . '" selected>'.$avail_tags[$i]['title'].'</option>';
                                            }
                                        }
                                        
                                    }
                                }
                            ?>
                        </select>
                    </div>
                    <hr>
                    <?php
                    if(empty($selected_fields))
                    {
                        $selected_fields=array();
                    }
                    for ($i = 0; $i < count($selected_fields); $i++) {
                        for ($j = 3; $j < count($avail_fields); $j++) {
                            if ($avail_fields[$j]['slug'] === $selected_fields[$i]) {
                                $field_ticket_value = (isset($current_meta[$avail_fields[$j]['slug']])?$current_meta[$avail_fields[$j]['slug']]:'');
                                $current_settings_meta = eh_crm_get_settingsmeta($avail_fields[$j]['settings_id']);
                                if($current_settings_meta['field_type'] != "file")
                                {
                                    echo '<div class="form-group">';
                                    echo '<span class="help-block">' . $avail_fields[$j]['title'] . '</span>';
                                    switch($current_settings_meta['field_type'])
                                    {
                                        case 'text':
                                            $readonly = "";
                                            if(!in_array("manage_tickets", $access))
                                            {
                                                $readonly = "readonly";
                                            }
                                            echo '<input type="text" AUTOCOMPLETE="off" class="form-control crm-form-element-input ticket_input_text_'.$ticket_id.'" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'" '.$readonly.' value="'.$field_ticket_value.'">';
                                            break;
                                        case 'email':
                                            $readonly = "";
                                            if(!in_array("manage_tickets", $access))
                                            {
                                                $readonly = "readonly";
                                            }
                                            echo '<input type="email" AUTOCOMPLETE="off" class="form-control crm-form-element-input ticket_input_email_'.$ticket_id.'" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'" '.$readonly.' value="'.$field_ticket_value.'">';
                                            break;
                                        case 'number':
                                            $readonly = "";
                                            if(!in_array("manage_tickets", $access))
                                            {
                                                $readonly = "readonly";
                                            }
                                            echo '<input type="number" AUTOCOMPLETE="off" class="form-control crm-form-element-input ticket_input_number_'.$ticket_id.'" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'" '.$readonly.' value="'.$field_ticket_value.'">';
                                            break;
                                        case 'password':
                                            $readonly = "";
                                            if(in_array("manage_tickets", $access))
                                            {
                                                $readonly = 'onfocus="this.removeAttribute(\'readonly\');"';
                                            }
                                            echo '<input type="password" AUTOCOMPLETE="false" readonly class="form-control crm-form-element-input ticket_input_pwd_'.$ticket_id.'" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'" '.$readonly.' value="'.$field_ticket_value.'">';
                                            break;
                                        case 'select':
                                            $field_values = $current_settings_meta['field_values'];
                                            echo '<select class="form-control crm-form-element-input ticket_input_select_'.$ticket_id.'" id="'.$avail_fields[$j]['slug'].'">';
                                            echo '<option value="">'.(isset($current_settings_meta['field_placeholder'])?htmlentities($current_settings_meta['field_placeholder']):'-').'</option>';
                                            foreach($field_values as $key => $value)
                                            {
                                                if(in_array("manage_tickets", $access))
                                                {
                                                    $selected = "";
                                                    if($key === $field_ticket_value)
                                                    {
                                                        $selected = "selected";
                                                    }
                                                    echo '<option value="'.$key.'" '.$selected.'>'.$value.'</option>';
                                                }
                                                else
                                                {
                                                    if($key === $field_ticket_value)
                                                    {
                                                        echo '<option value="'.$key.'" selected>'.$value.'</option>';
                                                    }
                                                }
                                            }
                                            echo '</select>';
                                            break;
                                        case 'radio':
                                            $field_values = $current_settings_meta['field_values'];
                                            echo '<span style="vertical-align: middle;">';
                                            foreach($field_values as $key => $value)
                                            {
                                                if(in_array("manage_tickets", $access))
                                                {
                                                    $checked = "";
                                                    if($key === $field_ticket_value)
                                                    {
                                                        $checked = "checked";
                                                    }
                                                    echo '<input type="radio" style="margin-top: 0;" id="'.$avail_fields[$j]['slug'].'" name="'.$avail_fields[$j]['slug'].'" class="form-control ticket_input_radio_'.$ticket_id.'" value="'.$key.'" '.$checked.'> '.$value.'<br>';
                                                }
                                                else
                                                {
                                                    if($key === $field_ticket_value)
                                                    {
                                                        echo '<input type="radio" style="margin-top: 0;" id="'.$avail_fields[$j]['slug'].'" name="'.$avail_fields[$j]['slug'].'" class="form-control ticket_input_radio_'.$ticket_id.'" value="'.$key.'" checked readonly> '.$value.'<br>';
                                                    }
                                                }
                                            }
                                            echo "</span>";
                                            break;
                                        case 'checkbox':
                                            $field_values = $current_settings_meta['field_values'];
                                            $field_ticket_value = is_array($field_ticket_value)?$field_ticket_value:array();
                                            echo '<span style="vertical-align: middle;">';
                                            foreach($field_values as $key => $value)
                                            {
                                                if(in_array("manage_tickets", $access))
                                                {
                                                    $checked = "";
                                                    if(in_array($key,$field_ticket_value))
                                                    {
                                                        $checked = "checked";
                                                    }
                                                    echo '<input type="checkbox" style="margin-top: 0;" id="'.$avail_fields[$j]['slug'].'" class="form-control ticket_input_checkbox_'.$ticket_id.'" value="'.$key.'" '.$checked.'> '.$value.'<br>';
                                                }
                                                else
                                                {
                                                    if(in_array($key,$field_ticket_value))
                                                    {
                                                        echo '<input type="checkbox" style="margin-top: 0;" id="'.$avail_fields[$j]['slug'].'" class="form-control ticket_input_checkbox_'.$ticket_id.'" value="'.$key.'" checked readonly> '.$value.'<br>';
                                                    }
                                                }
                                            }
                                            echo "</span>";
                                            break;
                                        case 'textarea':
                                            $readonly = "";
                                            if(!in_array("manage_tickets", $access))
                                            {
                                                $readonly = "readonly";
                                            }
                                            echo '<textarea class="form-control ticket_input_textarea_'.$ticket_id.'" id="'.$avail_fields[$j]['slug'].'" '.$readonly.'>'.$field_ticket_value.'</textarea>';
                                            break;
                                    }
                                    echo '</div>';
                                }
                            }
                        }
                    }
                    if(in_array("manage_tickets", $access))
                    {
                        echo '<button type="button" class="btn btn-primary col-md-offset-3 ticket_action_save_props" id="'.$ticket_id.'">
                                <span class="glyphicon glyphicon-saved"></span> Save Properties
                              </button>';
                    }
                ?>
                </div>
                <div class="col-md-9 Ws-content-detail-full">
                    <div class="single_ticket_panel rightPanel">
                        <div class="rightPanelHeader">
                        <div class="leftFreeSpace">
                            <div class="icon" style="top: 5% !important;"><img src="<?php echo EH_CRM_MAIN_IMG.'message_icon.png'?>"></div>
                            <div class="tictxt">
                            <p style="margin-top: 5px;font-size: 16px;">
                                <?php
                                    if(in_array("manage_tickets", $access))
                                    {
                                        echo '<input type="text" value="'. stripslashes(htmlentities($current[0]['ticket_title'])).'" id="ticket_title_'.$ticket_id.'" class="ticket_title_editable">';
                                    }
                                    else
                                    {
                                        echo $current[0]['ticket_title'];
                                    }
                                ?>                                
                            </p>
                            <p style="margin-top: 5px;">
                                <i class="glyphicon glyphicon-user"></i> by
                                <?php
                                    if($current[0]['ticket_author'] != 0)
                                    {
                                        $raiser_obj = new WP_User($current[0]['ticket_author']);
                                        echo '<a href="'. admin_url("user-edit.php?user_id=".$current[0]['ticket_author']).'" target="_blank">'.$raiser_obj->display_name.'</a> ';
                                    }
                                    else
                                    {
                                        echo '<span>'.$current[0]['ticket_email'].'</span>';
                                    }
                                ?>
                                | <i class="glyphicon glyphicon-calendar"></i> <?php echo eh_crm_get_formatted_date($current[0]['ticket_date']); ?>
                                | <i class="glyphicon glyphicon-comment"></i>
                                <?php
                                    $raiser_voice = eh_crm_get_ticket_value_count("ticket_parent",$ticket_id,false,"ticket_category","raiser_reply");
                                    echo count($raiser_voice)." Raiser Voice";                                    
                                ?>
                                | <i class="glyphicon glyphicon-bullhorn"></i>
                                <?php
                                    $agent_voice = eh_crm_get_ticket_value_count("ticket_parent",$ticket_id,false,"ticket_category","agent_reply");
                                    echo count($agent_voice)." Agent Voice";
                                ?>
                            </p>
                            </div>
                            </div>
                        </div>
                        <div class="newMsgFull">
                            <div class="leftFreeSpace">
                                <div class="icon"><img src="<?php echo get_avatar_url($logged_user->user_email,array('size'=>50)); ?>" style="border-radius: 25px;"></div>
                                <div class="content">
                                    <div class="message-box">
                                        <?php
                                            if(in_array("reply_tickets",$access))
                                            {
                                        ?>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="widget-area no-padding blank" style="width:100%">
                                                    <div class="status-upload">
                                                        <?php wp_nonce_field('ajax_crm_nonce', 'security'.$ticket_id); ?>
                                                        <textarea rows="10" cols="30" class="form-control reply_textarea" id="reply_textarea_<?php echo $ticket_id; ?>" name="reply_textarea_<?php echo $ticket_id; ?>"></textarea> 
                                                        <div class="form-group">
                                                            <div class="input-group col-md-12">
                                                                <span class="btn btn-primary fileinput-button">
                                                                    <i class="glyphicon glyphicon-plus"></i>
                                                                    <span>Attachment</span>
                                                                    <input type="file" name="files" id="files_<?php echo $ticket_id; ?>" class="attachment_reply" multiple="">
                                                                </span>
                                                                <div class="btn-group pull-right">
                                                                    <button type="button" class="btn btn-primary dropdown-toggle ticket_reply_action_button_<?php echo $ticket_id; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                      Submit as <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <?php
                                                                            if(in_array("manage_tickets", $access))
                                                                            {
                                                                                for($j=0;$j<count($avail_labels);$j++)
                                                                                {
                                                                                    echo '<li id="'.$ticket_id.'"><a href="#" class="ticket_reply_action" id="'.$avail_labels[$j]['slug'].'">Submit as '.$avail_labels[$j]['title'].'</a></li>';
                                                                                }
                                                                            }
                                                                            else
                                                                            {
                                                                                echo '<li id="'.$ticket_id.'"><a href="#" class="ticket_reply_action" id="'.$ticket_label_slug.'">Submit as '.$ticket_label.'</a></li>';
                                                                            }
                                                                        ?>
                                                                        <li role="separator" class="divider"></li>
                                                                        <li id="<?php echo $ticket_id;?>"><a href="#" class="ticket_reply_action" id="note">Submit as Note</a></li>
                                                                        <li class="text-center"><small class="text-muted">Notes visible to Agents and Supervisors</small></li>
                                                                    </ul>
                                                                  </div>
                                                            </div>
                                                            <div class="upload_preview_files_<?php echo $ticket_id;?>"></div>
                                                        </div>
                                                    </div><!-- Status Upload  -->
                                                </div><!-- Widget Area -->
                                            </div>
                                        </div>
                                        </div>
                            </div>
                        </div>
                            </div>
                                        <?php
                                            }
                                        ?>
                                        
                                            <?php
                                                $reply_id = eh_crm_get_ticket_value_count("ticket_parent", $ticket_id,false,"","","ticket_id","DESC");
                                                array_push($reply_id,array("ticket_id"=>$ticket_id));
                                                   
                                                for($s=0;$s<count($reply_id);$s++)
                                                {
                                                    $reply_ticket = eh_crm_get_ticket(array("ticket_id"=>$reply_id[$s]['ticket_id']));
                                                    $reply_ticket_meta = eh_crm_get_ticketmeta($reply_id[$s]['ticket_id']);
                                                    $replier_name ='';
                                                    $replier_email =$reply_ticket[0]['ticket_email'];
                                                    $replier_pic ='';
                                                    if($reply_ticket[0]['ticket_author']!=0)
                                                    {
                                                        $replier_obj = new WP_User($reply_ticket[0]['ticket_author']);
                                                        $replier_name = $replier_obj->display_name;
                                                        $replier_pic = get_avatar_url($reply_ticket[0]['ticket_author'],array('size'=>50));
                                                    }
                                                    else
                                                    {
                                                        $replier_name = "Guest";
                                                        $replier_pic = get_avatar_url($reply_ticket[0]['ticket_email'],array('size'=>50));
                                                    }
                                                    $attachment = "";
                                                    if(isset($reply_ticket_meta['ticket_attachment']))
                                                    {
                                                        
                                                        $reply_att = $reply_ticket_meta['ticket_attachment'];
                                                        $attachment = '<div>';
                                                        for($at=0;$at<count($reply_att);$at++)
                                                        {
                                                            $current_att = $reply_att[$at];
                                                            $att_ext = pathinfo($current_att, PATHINFO_EXTENSION);
                                                            if(empty($att_ext))
                                                            {
                                                               $att_ext=''; 
                                                            }
                                                            $att_name = pathinfo($current_att, PATHINFO_FILENAME);
                                                            $img_ext = array("jpg","jpeg","png","gif");
                                                            if(in_array(strtolower($att_ext), $img_ext))
                                                            {
                                                                $attachment .= '<a href="'.$current_att.'" target="_blank"><img class="img-upload clickable" style="width:200px" title="' .$att_name. '" src="'.$current_att.'"></a>';
                                                            }
                                                            else
                                                            {
                                                                $check_file_ext = array('doc','docx','pdf','xml','csv','xlsx','xls','txt','zip');
                                                                if(in_array($att_ext,$check_file_ext))
                                                                {
                                                                    $attachment .= '<a href="'.$current_att.'" target="_blank" title="' .$att_name. '" class="img-upload"><div class="'.$att_ext.'"></div></a>';
                                                                }
                                                                else
                                                                {
                                                                    $attachment .= '<a href="'.$current_att.'" target="_blank" title="' .$att_name. '" class="img-upload"><div class="unknown_type"></div></a>';
                                                                }

                                                            }
                                                        }
                                                        $attachment .= '</div>';
                                                    }
                                                                    
                                                    switch ($reply_ticket[0]['ticket_category']) {
                                                        case "raiser_reply":
                                                             echo '<div class="conversation_each">';
                                                            echo '<div class="leftFreeSpace">
                                                                    <div class="icon" style="width:44px;"><img src="'.$replier_pic.'" style="border-radius: 25px;"></div>
                                                                    <h3>'.$replier_name.'</h3>
                                                                    <h4>'.$replier_email.' | '.eh_crm_get_formatted_date($reply_ticket[0]['ticket_date']).' </h4>
                                                                    <p>';
                                                            $input_data = html_entity_decode(stripslashes($reply_ticket[0]['ticket_content']));
                                                            $input_array[0] = '/<((html)[^>]*)>(.*)\<\/(html)>/Us';
                                                            $input_array[1] = '/<((head)[^>]*)>(.*)\<\/(head)>/Us';
                                                            $input_array[2] = '/<((style)[^>]*)>(.*)\<\/(style)>/Us';
                                                            $input_array[3] = '/<((body)[^>]*)>(.*)\<\/(body)>/Us';
                                                            $input_array[4] = '/<((form)[^>]*)>(.*)\<\/(form)>/Us';
                                                            $input_array[5] = '/<((input)[^>]*)>(.*)\<\/(input)>/Us';
                                                            $input_array[7] = '/<((input)[^>]*)>/Us';
                                                            $input_array[6] = '/<((button)[^>]*)>(.*)\<\/(button)>/Us';
                                                            $input_array[8] = '/<((script)[^>]*)>(.*)\<\/(script)>/Us';
                                                            $input_array[9] = '/<((iframe)[^>]*)>(.*)\<\/(iframe)>/Us';
                                                            $output_array[0] = '&lt;$1&gt;$3&lt;/html&gt;';
                                                            $output_array[1] = '&lt;$1&gt;$3&lt;/head&gt;';
                                                            $output_array[2] = '&lt;$1&gt;$3&lt;/style&gt;';
                                                            $output_array[3] = '&lt;$1&gt;$3&lt;/body&gt;';
                                                            $output_array[4] = '&lt;$1&gt;$3&lt;/form&gt;';
                                                            $output_array[5] = '&lt;$1&gt;$3&lt;/input&gt;';
                                                            $output_array[6] = '&lt;$1&gt;$3&lt;/button&gt;';
                                                            $output_array[7] = '&lt;$1&gt;$3&lt;/input&gt;';
                                                            $output_array[8] = '&lt;$1&gt;$3&lt;/script&gt;';
                                                            $output_array[9] = '&lt;$1&gt;$3&lt;/iframe&gt;';
                                                            $input_data = preg_replace($input_array, $output_array, $input_data); 
                                                            echo $input_data.'</p>
                                                        '.$attachment.'
                                                        </div></div>';
                                                            break;
                                                        case "agent_reply":
                                                             echo '<div class="conversation_each">';
                                                            echo '<div class="leftFreeSpace">
                                                                    <div class="icon" style="width: 44px;"><img src="'.$replier_pic.'" style="border-radius: 25px;"></div>
                                                                    <h3>'.$replier_name.'</h3>
                                                                    <h4>'.$replier_email.' | '.eh_crm_get_formatted_date($reply_ticket[0]['ticket_date']).' </h4>
                                                                    <p>';$input_data = html_entity_decode(stripslashes($reply_ticket[0]['ticket_content']));
                                            
                                            $input_array[0] = '/<((html)[^>]*)>(.*)\<\/(html)>/Us';
                                            $input_array[1] = '/<((head)[^>]*)>(.*)\<\/(head)>/Us';
                                            $input_array[2] = '/<((style)[^>]*)>(.*)\<\/(style)>/Us';
                                            $input_array[3] = '/<((body)[^>]*)>(.*)\<\/(body)>/Us';
                                            $input_array[4] = '/<((form)[^>]*)>(.*)\<\/(form)>/Us';
                                            $input_array[5] = '/<((input)[^>]*)>(.*)\<\/(input)>/Us';
                                            $input_array[7] = '/<((input)[^>]*)>/Us';
                                            $input_array[6] = '/<((button)[^>]*)>(.*)\<\/(button)>/Us';
                                            $input_array[8] = '/<((script)[^>]*)>(.*)\<\/(script)>/Us';
                                            $input_array[9] = '/<((iframe)[^>]*)>(.*)\<\/(iframe)>/Us';
                                            $output_array[0] = '&lt;$1&gt;$3&lt;/html&gt;';
                                            $output_array[1] = '&lt;$1&gt;$3&lt;/head&gt;';
                                            $output_array[2] = '&lt;$1&gt;$3&lt;/style&gt;';
                                            $output_array[3] = '&lt;$1&gt;$3&lt;/body&gt;';
                                            $output_array[4] = '&lt;$1&gt;$3&lt;/form&gt;';
                                            $output_array[5] = '&lt;$1&gt;$3&lt;/input&gt;';
                                            $output_array[6] = '&lt;$1&gt;$3&lt;/button&gt;';
                                            $output_array[7] = '&lt;$1&gt;$3&lt;/input&gt;';
                                            $output_array[8] = '&lt;$1&gt;$3&lt;/script&gt;';
                                            $output_array[9] = '&lt;$1&gt;$3&lt;/iframe&gt;';
                                            $input_data = preg_replace($input_array, $output_array, $input_data); 
                                            
                                            echo $input_data.'</p>
                                                                    '.$attachment.'
                                                                    </div></div>
                                                            ';
                                                            break;
                                                        case 'agent_note':
                                                             echo '<div class="conversation_each">';
                                                            echo '<div class="leftFreeSpace" style="background-color: aliceblue!important">
                                                                    <div class="icon" style="width: 44px"><img src="'.$replier_pic.'" style="border-radius: 25px;"></div>
                                                                    <h3>'.$replier_name.'</h3>
                                                                    <h4>'.$replier_email.' | '.eh_crm_get_formatted_date($reply_ticket[0]['ticket_date']).' </h4>
                                                                    <p>';$input_data = html_entity_decode(stripslashes($reply_ticket[0]['ticket_content']));
                                            
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
                                            $input_data = preg_replace($input_array, $output_array, $input_data); 
                                            
                                            echo $input_data.'</p>
                                                                    '.$attachment.'
                                                                    </div></div>';
                                                            break;
                                                        default:
                                                            break;
                                                    }
                                                }
                                            ?>
                        
                                    
                                
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    static function eh_crm_ticket_single_save_props() {
        $ticket_id = sanitize_text_field($_POST['ticket_id']);
        $assignee = ((sanitize_text_field($_POST['assignee']) !== '')?explode(",", sanitize_text_field($_POST['assignee'])):array());
        $tags = ((sanitize_text_field($_POST['tags']) !== '')?explode(",", sanitize_text_field($_POST['tags'])):array());
        $cc = ((sanitize_text_field($_POST['cc']) !== '')?explode(",", sanitize_text_field($_POST['cc'])):array());
        $bcc = ((sanitize_text_field($_POST['bcc']) !== '')?explode(",", sanitize_text_field($_POST['bcc'])):array());
        $input = json_decode(stripslashes(sanitize_text_field($_POST['input'])), true);
        eh_crm_update_ticketmeta($ticket_id, "ticket_assignee", $assignee);
        eh_crm_update_ticketmeta($ticket_id, "ticket_tags", $tags);
        eh_crm_update_ticketmeta($ticket_id, "ticket_cc", $cc);
        eh_crm_update_ticketmeta($ticket_id, "ticket_bcc", $bcc);
        foreach ($input as $key => $value) {
            eh_crm_update_ticketmeta($ticket_id, $key, $value);
        }
    }
    
    static function eh_crm_ticket_single_delete() {
        $ticket_id = sanitize_text_field($_POST['ticket_id']);
        $child = eh_crm_get_ticket_value_count("ticket_parent", $ticket_id);
        eh_crm_delete_ticket($ticket_id);
        for($i=0;$i<count($child);$i++)
        {
            eh_crm_delete_ticket($child[$i]['ticket_id']);
        }
    }
    
    static function eh_crm_ticket_multiple_delete() {
        $tickets_id = json_decode(stripslashes(sanitize_text_field($_POST['tickets_id'])), true);
        for($i=0;$i<count($tickets_id);$i++)
        {
            $child = eh_crm_get_ticket_value_count("ticket_parent", $tickets_id[$i]);
            eh_crm_delete_ticket($tickets_id[$i]);
            for($j=0;$j<count($child);$j++)
            {
                eh_crm_delete_ticket($child[$j]['ticket_id']);
            }
        }
    }
    
    static function eh_crm_ticket_refresh_left_bar() {
        $active = $_POST['active'];
        ob_start();
        $label_args = array("type" => "label", "filter" => "yes");
        $label_fields = array("slug", "title", "settings_id");
        $avail_labels = eh_crm_get_settings($label_args, $label_fields);
        $tag_args = array("type" => "tag", "filter" => "yes");
        $tag_fields = array("slug", "title", "settings_id");
        $avail_tags = eh_crm_get_settings($tag_args, $tag_fields);
        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
        $users = get_users(array("role__in" => $user_roles_default));
        $users_data = array();
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
        for ($i = 0; $i < count($users); $i++) {
            $current = $users[$i];
            $id = $current->ID;
            $user = new WP_User($id);
            $users_data[$i]['id'] = $id;
            $users_data[$i]['name'] = $user->display_name;
            $users_data[$i]['caps'] = $user->caps;
            $users_data[$i]['email'] = $user->user_email;
        }
        ?>
            <ul class="nav nav-pills nav-stacked" id="all_section">
                <li class="<?php echo (($active == "all")?"active":""); ?>"><a href="#" id="all"><span class="badge pull-right"><?php echo count(eh_crm_get_ticket_value_count("ticket_parent",0)); ?></span> All Tickets </a></li>
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
                <span id="labels_drop" class="glyphicon glyphicon-chevron-down" style="float:right; <?php echo ($labels_collapsed)?'': 'display: none;';?>" onclick="drop('labels');">
            </h4>
            <ul class="nav nav-pills nav-stacked" id="labels" <?php echo ($labels_collapsed)?"style='display: none;'":"";?>>
                <?php
                    for ($i = 0; $i < count($avail_labels); $i++) {
                        $label_color = eh_crm_get_settingsmeta($avail_labels[$i]['settings_id'], "label_color");
                        $current_label_count=eh_crm_get_ticketmeta_value_count("ticket_label",$avail_labels[$i]['slug']);
                        echo '<li class="'.(($active == $avail_labels[$i]['slug'])?"active":"").'"><a href="#" id="'.$avail_labels[$i]['slug'].'"><span class="badge pull-right" style="background-color:' . $label_color . ' !important;">'.count($current_label_count).'</span> '.$avail_labels[$i]['title'].' </a></li>';
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
                <ul class="nav nav-pills nav-stacked" id="agents" <?php echo ($agents_collapsed)?"style='display: none;'":"";?>>
                    <?php
                        for ($i = 0; $i < count($users_data); $i++) {
                            $current_agent_count=eh_crm_get_ticketmeta_value_count("ticket_assignee",$users_data[$i]['id']);
                            echo '<li class="'.(($active == $users_data[$i]['id'])?"active":"").'"><a href="#" id="'.$users_data[$i]['id'].'"><span class="badge pull-right">'.count($current_agent_count).'</span> '.$users_data[$i]['name'].' </a></li>';
                        }
                        $current_agent_count=eh_crm_get_ticketmeta_value_count("ticket_assignee",array());
                    ?>
                    <li class="<?php echo (($active == "unassigned")?"active":"");?>"><a href="#" id="unassigned"><span class="badge pull-right"><?php echo count($current_agent_count);?></span> Unassigned </a></li>
                </ul>
                <?php 
            }
            ?>
            <?php
            if(!empty($avail_tags))
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
                <ul class="nav nav-pills nav-stacked" id="tags" <?php echo ($tags_collapsed)?"style='display: none;'":"";?>>
                    <?php
                        for ($i = 0; $i < count($avail_tags); $i++) {
                            $current_tags_count=eh_crm_get_ticketmeta_value_count("ticket_tags",$avail_tags[$i]['slug']);
                            echo '<li class="'.(($active == $avail_tags[$i]['slug'])?"active":"").'"><a href="#" id="'.$avail_tags[$i]['slug'].'"><span class="badge pull-right">'.count($current_tags_count).'</span> '.$avail_tags[$i]['title'].' </a></li>';
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
            <ul class="nav nav-pills nav-stacked" id="users" <?php echo ($users_collapsed)?"style='display: none;'":"";?>>
                <?php
                    $registered_count = eh_crm_get_ticket_value_count("ticket_author",0,true,"ticket_parent",0);
                    echo '<li class="'.(($active == "registeredU")?"active":"").'"><a href="#" id="registeredU" class="user_section"><span class="badge pull-right">'.count($registered_count).'</span> Registered Users </a></li>';
                    $guest_count = eh_crm_get_ticket_value_count("ticket_author",0,false,"ticket_parent",0);
                    echo '<li class="'.(($active == "guestU")?"active":"").'"><a href="#" id="guestU" class="user_section"><span class="badge pull-right">'.count($guest_count).'</span> Guest Users </a></li>';
                ?>
            </ul>
        <?php
         $content = ob_get_clean();
         die($content);
    }
    
    static function eh_crm_ticket_refresh_right_bar() {
        $active = $_POST['active'];
        $order = isset($_POST['order'])?$_POST['order']:'DESC';
        $current_page_no = (isset($_POST['current_page']))?$_POST['current_page']:0;
        $pagination = isset($_POST['pagination_type'])?$_POST['pagination_type']:'';
        $avail_labels_wf = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
        $avail_labels = eh_crm_get_settings(array("type" => "label", "filter" => "yes"), array("slug", "title", "settings_id"));
        $avail_tags_wf = eh_crm_get_settings(array("type" => "tag"), array("slug", "title", "settings_id"));
        $avail_tags = eh_crm_get_settings(array("type" => "tag", "filter" => "yes"), array("slug", "title", "settings_id"));
        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
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
        $ticket_rows = eh_crm_get_settingsmeta(0, "ticket_rows");
        $offset = $current_page_no * $ticket_rows;
        $current_page = $current_page_no;
        if($pagination != "")
        {
            switch ($pagination) {
                case "prev":
                    $current_page = $current_page_no-1;
                    $offset = ($current_page * $ticket_rows);
                    break;
                case "next":
                    $current_page = $current_page_no+1;
                    $offset = ($current_page * $ticket_rows);
                    break;
            }
        }
        switch ($active) {
            case "all":
                $table_title = 'All Tickets';
                $total_count = count(eh_crm_get_ticket_value_count("ticket_parent",0));
                $section_tickets_id = eh_crm_get_ticket_value_count("ticket_parent",0,false,"","","ticket_updated",$order,$ticket_rows,$offset);
                break;
            case "registeredU":
                $table_title = 'Registered Users Tickets';
                $total_count = count(eh_crm_get_ticket_value_count("ticket_author",0,TRUE,"ticket_parent",0));
                $section_tickets_id = eh_crm_get_ticket_value_count("ticket_author",0,TRUE,"ticket_parent",0,"ticket_updated",$order,$ticket_rows,$offset);
                break;
            case "guestU":
                $table_title = 'Guest Users Tickets';
                $total_count = count(eh_crm_get_ticket_value_count("ticket_author",0,FALSE,"ticket_parent",0));
                $section_tickets_id = eh_crm_get_ticket_value_count("ticket_author",0,false,"ticket_parent",0,"ticket_updated",$order,$ticket_rows,$offset);
                break;
            case "unassigned":
                $table_title = 'Unassigned Tickets';
                $total_count = count(eh_crm_get_ticketmeta_value_count("ticket_assignee",array(),"ticket_id"));
                $section_tickets_id = eh_crm_get_ticketmeta_value_count("ticket_assignee",array(),"ticket_updated",$order,$ticket_rows,$offset);
                break;
            default:
                if (strpos($active, 'label_') !== false) 
                {
                    for($i=0;$i<count($avail_labels);$i++)
                    {
                        if($avail_labels[$i]['slug'] === $active)
                        {
                            $table_title = $avail_labels[$i]['title'];
                        }
                    }
                    if(empty($table_title))
                    {
                        $table_title = "(Incorrect Deep Link)";
                    }
                    $table_title = $table_title . ' Tickets';
                    $total_count = count(eh_crm_get_ticketmeta_value_count("ticket_label",$active,"ticket_id"));
                    $section_tickets_id = eh_crm_get_ticketmeta_value_count("ticket_label",$active,"ticket_updated",$order,$ticket_rows,$offset);
                } 
                elseif (strpos($active, 'tag_') !== false) 
                {
                    for($i=0;$i<count($avail_tags);$i++)
                    {
                        if($avail_tags[$i]['slug'] === $active)
                        {
                            $table_title = $avail_tags[$i]['title'];
                        }
                    }
                    if(empty($table_title))
                    {
                        $table_title = "(Incorrect Deep Link)";
                    }
                    $table_title = $table_title . ' Tickets';
                    $total_count = count(eh_crm_get_ticketmeta_value_count("ticket_tags",$active,"ticket_id"));
                    $section_tickets_id = eh_crm_get_ticketmeta_value_count("ticket_tags",$active,"ticket_updated",$order,$ticket_rows,$offset);
                }
                else 
                {
                    for($i=0;$i<count($users_data);$i++)
                    {
                        if($users_data[$i]['id'] == $active)
                        {
                            $table_title = $users_data[$i]['name'];
                        }
                    }
                    if(empty($table_title))
                    {
                        $table_title = "(Incorrect Deep Link)";
                    }
                    $table_title = $table_title . ' Tickets';
                    $total_count = count(eh_crm_get_ticketmeta_value_count("ticket_assignee",$active,"ticket_id"));
                    $section_tickets_id = eh_crm_get_ticketmeta_value_count("ticket_assignee",$active,"ticket_updated",$order,$ticket_rows,$offset);
                }
                break;
        }
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
        ?>
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
                    <span class="clickable filter" data-toggle="wsdesk_tooltip" title="Tickets Filter" data-container="body">
                        <i class="glyphicon glyphicon-filter"></i>
                    </span>
                </div>
                <div class="pull-right" style="margin: -15px 0px 0px 0px;">
                    <span class="text-muted"><b><?php echo ($current_page!=0)?($current_page)*$ticket_rows:"1"; ?></b>–<b><?php echo ($current_page*$ticket_rows)+count($section_tickets_id);?></b> of <b><?php echo $total_count; ?></b></span>
                    <div class="btn-group btn-group-sm">
                        <?php
                                if($current_page != 0)
                                {
                                    ?>
                                        <button type="button"  class="btn btn-default pagination_tickets" id="prev" title="Previous <?php echo $ticket_rows?>" data-container="body">
                                            <span class="glyphicon glyphicon-chevron-left"></span>
                                        </button>
                                    <?php
                                }
                        ?>                        
                        <input type="hidden" id="current_page_no" value="<?php echo $current_page ?>">
                        <?php 
                                if(($current_page*$ticket_rows)+count($section_tickets_id) != $total_count)
                                {
                                    ?>
                                        <button type="button"  class="btn btn-default pagination_tickets" id="next" title="Next <?php echo $ticket_rows?>" data-container="body">
                                            <span class="glyphicon glyphicon-chevron-right"></span>
                                        </button>
                                    <?php
                                }
                        ?>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <input type="text" class="form-control" id="dev-table-filter" data-action="filter" data-filters="#dev-table" placeholder="Filter Tickets" />
            </div>
            <table class="table table-hover" id="dev-table">
                <thead>
                    <tr class="except_view">
                        <th></th>
                        <th>View</th>
                        <th>#</th>
                        <th>Requester</th>
                        <th>Subject</th>
                        <th>Requested</th>
                        <th>Assignee</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        if(empty($section_tickets_id))
                        {
                            echo '<tr class="except_view">
                                <td colspan="12">No Tickets </td></tr>';
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
                                        $ticket_label_slug = $avail_labels_wf[$j]['slug'];
                                        $ticket_label = $avail_labels_wf[$j]['title'];
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
                                    <td>'. eh_crm_get_formatted_date($current[0]['ticket_date']).'</td>
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
                                                            '.stripslashes($latest_content['content']).'
                                                        </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>Actions</th>
                                                        <th>Reply Requester</th>
                                                        <th>Raiser Voices</th>
                                                        <th>Agent Voices</th>
                                                        <th>Tags</th>
                                                        <th>Rating</th>
                                                        <th>Source</th>
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
                                                                echo '<input type="text" value="'.stripslashes(htmlentities($current[0]['ticket_title'])).'" id="direct_ticket_title_'.$current[0]['ticket_id'].'" class="ticket_title_editable">';
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
                                                                        <div class="widget-area no-padding blank" style="width:100%">
                                                                            <div class="status-upload">
                                                                                <?php wp_nonce_field('ajax_crm_nonce', 'direct_security'.$current[0]['ticket_id']); ?>
                                                                                <textarea rows="10" cols="30" class="form-control direct_reply_textarea" id="direct_reply_textarea_<?php echo $current[0]['ticket_id']; ?>" name="reply_textarea_<?php echo $current[0]['ticket_id']; ?>"></textarea> 
                                                                                <div class="form-group">
                                                                                    <div class="input-group col-md-12">
                                                                                        <span class="btn btn-primary fileinput-button">
                                                                                            <i class="glyphicon glyphicon-plus"></i>
                                                                                            <span>Attachment</span>
                                                                                            <input type="file" name="direct_files" id="direct_files_<?php echo $current[0]['ticket_id']; ?>" class="direct_attachment_reply" multiple="">
                                                                                        </span>
                                                                                        <div class="btn-group pull-right">
                                                                                            <button type="button" class="btn btn-primary dropdown-toggle direct_ticket_reply_action_button_<?php echo $current[0]['ticket_id']; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                                              Submit as <span class="caret"></span>
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
                                                                                                <li id="<?php echo $current[0]['ticket_id'];?>"><a href="#" class="direct_ticket_reply_action" id="note">Submit as Note</a></li>
                                                                                                <li class="text-center"><small class="text-muted">Notes visible to Agents and Supervisors</small></li>
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
                                                                echo "<p>You don't Have permisson to Reply this ticket</p>";
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
        <?php
        $content = ob_get_clean();
        die($content);
    }
    
    static function eh_crm_ticket_reply_agent() {
        check_ajax_referer('ajax_crm_nonce', 'security');
        $files = isset($_FILES["file"])?$_FILES["file"]:"";
        $title = (isset($_POST['ticket_title'])?sanitize_text_field(stripslashes($_POST['ticket_title'])):"");
        $ticket_id = sanitize_text_field($_POST['ticket_id']);
        $submit = sanitize_text_field($_POST['submit']);
        $content = str_replace("\n", '<br/>',stripslashes($_POST['ticket_reply']));
        if($title!="")
        {
            eh_crm_update_ticket($ticket_id, array("ticket_title"=>$title));
        }
        $parent = eh_crm_get_ticket(array("ticket_id"=>$ticket_id));
        $user = wp_get_current_user();
        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
        $category ='';
        if (count(array_intersect($user_roles_default, $user->roles)) != 0)
        {
            if($submit == "note")
            {
                $category = 'agent_note';
            }
            else
            {
                eh_crm_update_ticketmeta($ticket_id, "ticket_label", $submit);
                $category = 'agent_reply';
            }            
        }
        else
        {
            $category = 'raiser_reply';
        }
        $child = array(
            'ticket_email' => $user->user_email,
            'ticket_title' => $parent[0]['ticket_title'],
            'ticket_content' => $content,
            'ticket_category' => $category,
            'ticket_parent' => $ticket_id);
        $child_meta = array();
        if(isset($_FILES["file"]) && !empty($_FILES['file']))
        {   
            $attachment_data = CRM_Ajax::eh_crm_ticket_file_handler($files);
            $child_meta["ticket_attachment"] = $attachment_data['url'];
            $child_meta["ticket_attachment_path"] = $attachment_data['path'];
        }
        $gen_id = eh_crm_insert_ticket($child,$child_meta);
        $response = array();
        if($category==="agent_reply")
        {
            eh_crm_debug_error_log(" ------------- WSDesk Email Debug Started ------------- ");
            eh_crm_debug_error_log("Agent Replied for Ticket #".$ticket_id);
            eh_crm_debug_error_log("Email function called for new reply #".$gen_id);
            $response = CRM_Ajax::eh_crm_fire_email($gen_id);
            eh_crm_debug_error_log(" ------------- WSDesk Email Debug Ended ------------- ");
        }
        $content_html = CRM_Ajax::eh_crm_ticket_single_view_gen($ticket_id);
        $tab = CRM_Ajax::eh_crm_ticket_single_view_gen_head($ticket_id);
        die(json_encode(array("tab_head"=>$tab,"tab_content"=>$content_html,'response'=>$response)));
    }
    
    static function eh_crm_ticket_file_handler($files) {
        $attachment_url = array();
        $attachment_path = array();
        $attachment = array();
        if(!function_exists('wp_handle_upload')){
            require_once(admin_url('includes/file.php'));
        }
        $upload_overrides = array( 'test_form' => false,"test_size" => false,"test_type"=>false);
        foreach ($files['name'] as $key => $value) {
            if ($files['name'][$key] && !in_array($files['type'][$key], array('php', 'exe', 'sh', 'js')) ) {
                $file = array(
                    'name' => time().'_'.$files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $files['tmp_name'][$key],
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                );
                $attach_id = wp_handle_upload($file, $upload_overrides);
                $attachment_url[] = $attach_id['url'];
                $attachment_path[] = $attach_id['file'];
            }
        }
        $attachment['url']= $attachment_url;
        $attachment['path'] = $attachment_path;
        return $attachment;
    }
    
    static function eh_crm_ticket_single_ticket_action() {
        $ticket_id = sanitize_text_field($_POST['ticket_id']);
        $label = sanitize_text_field($_POST['label']);
        eh_crm_update_ticketmeta($ticket_id, "ticket_label", $label);
        $content_html = CRM_Ajax::eh_crm_ticket_single_view_gen($ticket_id);
        die($content_html);
    }
    
    static function eh_crm_ticket_multiple_ticket_action() {
        $tickets_id = json_decode(stripslashes(sanitize_text_field($_POST['tickets_id'])), true);
        $label = sanitize_text_field($_POST['label']);
        for($i=0;$i<count($tickets_id);$i++)
        {
            eh_crm_update_ticketmeta($tickets_id[$i], "ticket_label", $label);      
        }
    }
    
    static function eh_crm_ticket_search() {
        $search = $_POST['search'];
        if(eh_crm_get_ticket(array("ticket_id"=>$search,"ticket_parent"=>0)))
        {
            $content = CRM_Ajax::eh_crm_ticket_single_view_gen($search);
            $tab = CRM_Ajax::eh_crm_ticket_single_view_gen_head($search);
            die(json_encode(array("tab_head"=>$tab,"tab_content"=>$content,"data"=>"ticket")));
        }
        else
        {
            $ticket_ids = eh_crm_get_ticket_search($search);
            $content = CRM_Ajax::eh_crm_generate_search_result($ticket_ids,$search);
            $search_key = str_replace(" ", "_", $search);
            $search_key = str_replace('@', '_1attherate1_', $search_key);
            $search_key = str_replace('.', '_1dot1_', $search_key);            
            $search_key = str_replace(';', '_1semicolon1_', $search_key);
            $search_key = str_replace('?', '_1questionmark1_', $search_key);
            $tab='<a href="#tab_content_'.$search_key.'" id="tab_content_a_'.$search_key.'" aria-controls="#'.$search_key.'" role="tab" data-toggle="tab" class="tab_a" style="font-size: 12px;padding: 10px 5px;"><button type="button" class="btn btn-default btn-circle close_tab pull-right"><span class="glyphicon glyphicon-remove"></span></button><div class="badge"><span class="glyphicon glyphicon-search"></span></div><span> '.(strlen($search) > 18 ? substr($search,0,18)."..." : $search).'</span></a>';
            die(json_encode(array("tab_head"=>$tab,"tab_content"=>$content,"data"=>"search")));
        }
    }
    
    static function eh_crm_generate_search_result($section_tickets_id,$search) {
        $avail_labels = eh_crm_get_settings(array("type" => "label", "filter" => "yes"), array("slug", "title", "settings_id"));
        $avail_tags = eh_crm_get_settings(array("type" => "tag", "filter" => "yes"), array("slug", "title", "settings_id"));
        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
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
        ob_start();
        ?>
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-12">
                    <div class="panel panel-default tickets_panel">
                        <div class="panel-heading">
                            <h3 class="panel-title">Search Result "<?php echo $search;?>"
                                <span class="spinner_loader search_table_loader">
                                    <span class="bounce1"></span>
                                    <span class="bounce2"></span>
                                    <span class="bounce3"></span>
                                </span>
                            </h3>
                        </div>
                        <div class="panel-body">
                            <input type="text" class="form-control" id="search-table-filter" data-action="filter" data-filters="#search-table" placeholder="Filter Anything" />
                        </div>
                        <table class="table table-hover" id="search-table">
                            <thead>
                                <tr class="except_view">
                                    <th>View</th>
                                    <th>#</th>
                                    <th>Requester</th>
                                    <th>Subject</th>
                                    <th>Requested</th>
                                    <th>Assignee</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    if(empty($section_tickets_id))
                                    {
                                        echo '<tr class="except_view">
                                            <td colspan="12">No Tickets </td></tr>';
                                    }
                                    else
                                    {
                                        for($i=0;$i<count($section_tickets_id);$i++)
                                        {
                                            $current = eh_crm_get_ticket(array("ticket_id"=>$section_tickets_id[$i]['ticket_id']));
                                            $current_meta = eh_crm_get_ticketmeta($section_tickets_id[$i]['ticket_id']);
                                            $action_value = '';
                                            $eye_color='';
                                            for($j=0;$j<count($avail_labels);$j++)
                                            {
                                                if(in_array("manage_tickets", $access))
                                                {
                                                    $action_value .= '<li id="'.$current[0]['ticket_id'].'"><a href="#" class="single_ticket_action" id="'.$avail_labels[$j]['slug'].'">Mark as '.$avail_labels[$j]['title'].'</a></li>';

                                                }
                                                if($avail_labels[$j]['slug'] == $current_meta['ticket_label'])
                                                {
                                                    $ticket_label_slug = $avail_labels[$j]['slug'];
                                                    $ticket_label = $avail_labels[$j]['title'];
                                                    $eye_color = eh_crm_get_settingsmeta($avail_labels[$j]['settings_id'], "label_color");
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
                                            }
                                            $ticket_tags = "";
                                            if(!empty($avail_tags))
                                            {
                                                for($j=0;$j<count($avail_tags);$j++)
                                                {
                                                    $current_ticket_tags=(isset($current_meta['ticket_tags'])?$current_meta['ticket_tags']:array());
                                                    for($k=0;$k<count($current_ticket_tags);$k++)
                                                    {
                                                        if($avail_tags[$j]['slug'] == $current_ticket_tags[$k])
                                                        {
                                                            $ticket_tags .= '<span class="label label-info">#'.$avail_tags[$j]['title'].'</span>';
                                                        }
                                                    }
                                                }
                                            }
                                            $ticket_rating = (isset($current_meta['ticket_rating'])?$current_meta['ticket_rating']:0);
                                            $raiser_voice = eh_crm_get_ticket_value_count("ticket_parent",$section_tickets_id[$i]['ticket_id'],false,"ticket_category","raiser_reply");
                                            $agent_voice = eh_crm_get_ticket_value_count("ticket_parent",$section_tickets_id[$i]['ticket_id'],false,"ticket_category","agent_reply");
                                            echo '
                                            <tr class="clickable ticket_row" id="'.$current[0]['ticket_id'].'">
                                                <td class="except_view"><button class="btn btn-default btn-xs accordion-toggle quick_view_ticket" style="background-color: '.$eye_color.' !important" data-toggle="collapse" data-target="#search_expand_'.$current[0]['ticket_id'].'" ><span class="glyphicon glyphicon-eye-open"></span></button></td>
                                                <td>'.$current[0]['ticket_id'].'</td>
                                                <td>'.$ticket_raiser.'</td>
                                                <td class="wrap_content" data-toggle="wsdesk_tooltip" title="'.$current[0]['ticket_title'].'" data-container="body">'.$current[0]['ticket_title'].'</td>
                                                <td>'. eh_crm_get_formatted_date($current[0]['ticket_date']).'</td>
                                                <td>'.$ticket_assignee_name.'</td>
                                            </tr>
                                            <tr class="except_view">
                                                <td colspan="12" class="hiddenRow">
                                                    <div class="accordian-body collapse" id="search_expand_'.$current[0]['ticket_id'].'">
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
                                                                        '.$latest_content['content'].'
                                                                    </p>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <th>Reply Requester</th>
                                                                    <th>Raiser Voices</th>
                                                                    <th>Agent Voices</th>
                                                                    <th>Tags</th>
                                                                    <th>Rating</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>
                                                                        '.$current[0]['ticket_email'].'
                                                                    </td>
                                                                    <td>'.count($raiser_voice).'</td>
                                                                    <td>'.count($agent_voice).'</td>
                                                                    <td>'.(($ticket_tags!="")?$ticket_tags:"No Tags").'</td>
                                                                    <td>'.$ticket_rating.'</td>                                                                       
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </td>
                                            </tr>';
                                        }
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php
        $content = ob_get_clean();
        return $content;
    }
    
    static function eh_crm_ticket_add_new() {
        ob_start();
        $logged_user = wp_get_current_user();
        $logged_user_caps = array_keys($logged_user->caps);
        $avail_caps = array("reply_tickets","delete_tickets","manage_tickets");
        $access = array();
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
        $users_data = get_users(array("role__in" => array("administrator", "WSDesk_Agents", "WSDesk_Supervisor")));
        $users = array();
        for ($i = 0; $i < count($users_data); $i++) {
            $current_user = $users_data[$i];
            $temp = array();
            $roles = $current_user->roles;
            foreach ($roles as $value) {
                $current_role = $value;
                $temp[$i] = ucfirst(str_replace("_", " ", $current_role));
            }
            $users[implode(' & ', $temp)][$current_user->ID] = $current_user->data->display_name;
        }
        $avail_fields = eh_crm_get_settings(array("type" => "field"), array("slug", "title", "settings_id"));
        $selected_fields = eh_crm_get_settingsmeta(0, 'selected_fields');
        $avail_tags = eh_crm_get_settings(array("type" => "tag"),array("slug","title","settings_id"));
        $avail_labels = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
        $ticket_label = "";
        $ticket_label_slug ="";
        for($j=0;$j<count($avail_labels);$j++)
        {
            if($avail_labels[$j]['slug'] == eh_crm_get_settingsmeta(0, "default_label"))
            {
                $ticket_label = $avail_labels[$j]['title'];
                $ticket_label_slug = $avail_labels[$j]['slug'];
            }
        }
        ?>
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <ol class="breadcrumb col-md-8" style="margin: 0 !important;background: none !important;border:none;padding: 8px 0px !important; ">
                        <li><?php echo get_bloginfo("name") ?></li>
                        <li>Support</li>
                        <li class="active"><span class="label label-danger">#New</span></li>
                        <span class="spinner_loader ticket_loader_new">
                            <span class="bounce1"></span>
                            <span class="bounce2"></span>
                            <span class="bounce3"></span>
                        </span>
                    </ol>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-sm-4 col-md-3">
                    <div class="form-group">
                        <span class="help-block">Assignee</span>
                        <select id="assignee_ticket_new" class="form-control" aria-describedby="helpBlock" multiple="multiple">
                            <?php
                                foreach ($users as $key => $value) {
                                    foreach ($value as $id => $name) {
                                        echo '<option value="' . $id . '">'.$name.' | '.$key.'</option>';
                                    }                                      
                                }
                            ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <span class="help-block">Tags</span>
                        <select id="tags_ticket_new" class="form-control crm-form-element-input" multiple="multiple">
                            <?php
                            if(!empty($avail_tags))
                            {
                                for($i=0;$i<count($avail_tags);$i++)
                                {
                                    echo '<option value="' . $avail_tags[$i]['slug'] . '">'.$avail_tags[$i]['title'].'</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <hr>
                    <?php
                    if(empty($selected_fields))
                        $selected_fields = array();
                    for ($i = 0; $i < count($selected_fields); $i++) {
                        for ($j = 3; $j < count($avail_fields); $j++) {
                            if ($avail_fields[$j]['slug'] === $selected_fields[$i]) {
                                $current_settings_meta = eh_crm_get_settingsmeta($avail_fields[$j]['settings_id']);
                                if($current_settings_meta['field_type'] != "file")
                                {
                                    echo '<div class="form-group">';
                                    echo '<span class="help-block">' . $avail_fields[$j]['title'] . '</span>';
                                    switch($current_settings_meta['field_type'])
                                    {
                                        case 'text':
                                            echo '<input type="text" AUTOCOMPLETE="off" class="form-control crm-form-element-input ticket_input_text_new" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'">';
                                            break;
                                        case 'email':
                                            echo '<input type="email" AUTOCOMPLETE="off" class="form-control crm-form-element-input ticket_input_email_new" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'">';
                                            break;
                                        case 'number':
                                            echo '<input type="number" AUTOCOMPLETE="off" class="form-control crm-form-element-input ticket_input_number_new" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'">';
                                            break;
                                        case 'password':
                                            echo '<input type="password" AUTOCOMPLETE="false" readonly class="form-control crm-form-element-input ticket_input_pwd_new" id="'.$avail_fields[$j]['slug'].'" placeholder="'.$current_settings_meta['field_placeholder'].'" onfocus="this.removeAttribute(\'readonly\');">';
                                            break;
                                        case 'select':
                                            $field_values = $current_settings_meta['field_values'];
                                            echo '<select class="form-control crm-form-element-input ticket_input_select_new" id="'.$avail_fields[$j]['slug'].'">';
                                            foreach($field_values as $key => $value)
                                            {
                                                echo '<option value="'.$key.'">'.$value.'</option>';
                                            }
                                            echo '</select>';
                                            break;
                                        case 'radio':
                                            $field_values = $current_settings_meta['field_values'];
                                            echo '<span style="vertical-align: middle;">';
                                            foreach($field_values as $key => $value)
                                            {
                                                echo '<input type="radio" style="margin-top: 0;" id="'.$avail_fields[$j]['slug'].'" name="'.$avail_fields[$j]['slug'].'" class="form-control ticket_input_radio_new" value="'.$key.'"> '.$value.'<br>';

                                            }
                                            echo "</span>";
                                            break;
                                        case 'checkbox':
                                            $field_values = $current_settings_meta['field_values'];
                                            echo '<span style="vertical-align: middle;">';
                                            foreach($field_values as $key => $value)
                                            {
                                                echo '<input type="checkbox" style="margin-top: 0;" id="'.$avail_fields[$j]['slug'].'" class="form-control ticket_input_checkbox_new" value="'.$key.'"> '.$value.'<br>';
                                            }
                                            echo "</span>";
                                            break;
                                        case 'textarea':
                                            echo '<textarea class="form-control ticket_input_textarea_new" id="'.$avail_fields[$j]['slug'].'" ></textarea>';
                                            break;
                                    }
                                    echo '</div>';
                                }
                            }
                        }
                    }
                ?>
                </div>
                <div class="col-sm-10 col-md-9">
                    <div class="panel panel-default new_ticket_panel">
                        <div class="panel-heading">
                            <p style="margin-top: 5px;font-size: 16px;">
                                <?php
                                    echo '<div class="form-group"><span class="help-block">Raiser Email : </span><input type="email" id="ticket_email_new" class="form-control crm-form-element-input"></div>';
                                    echo '<div class="form-group"><span class="help-block">Ticket Subject : </span><input type="text" id="ticket_title_new" class="form-control crm-form-element-input"></div>';
                                ?>      
                            </p>
                        </div>
                        <div class="panel-body">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row" style="margin-bottom: 20px;">
                                            <div class="col-md-12">
                                                <div class="widget-area no-padding blank" style="width:100%">
                                                    <div class="status-upload">
                                                        <?php wp_nonce_field('ajax_crm_nonce', 'securitynew'); ?>
                                                        <div class="form-group" style="padding: 5px 5px !important;">
                                                            <span class="help-block">Description</span>
                                                            <textarea rows="10" cols="30" class="form-control reply_textarea" id="reply_textarea_new" name="reply_textarea_new"></textarea> 
                                                        </div>
                                                        <div class="form-group">
                                                            <div class="input-group col-md-12">
                                                                <span class="btn btn-primary fileinput-button">
                                                                    <i class="glyphicon glyphicon-plus"></i>
                                                                    <span>Attachment</span>
                                                                    <input type="file" name="files" id="files_new" class="attachment_reply" multiple="">
                                                                </span>
                                                                <div class="btn-group pull-right">
                                                                    <button type="button" class="btn btn-primary dropdown-toggle ticket_reply_action_button_new" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                      Submit as <span class="caret"></span>
                                                                    </button>
                                                                    <ul class="dropdown-menu">
                                                                        <?php
                                                                            if(in_array("manage_tickets", $access))
                                                                            {
                                                                                for($j=0;$j<count($avail_labels);$j++)
                                                                                {
                                                                                    echo '<li id="new"><a href="#" class="ticket_submit_new" id="'.$avail_labels[$j]['slug'].'">Submit as '.$avail_labels[$j]['title'].'</a></li>';
                                                                                }
                                                                            }
                                                                            else
                                                                            {
                                                                                echo '<li id="new"><a href="#" class="ticket_submit_new" id="'.$ticket_label_slug.'">Submit as '.$ticket_label.'</a></li>';
                                                                            }
                                                                        ?>
                                                                    </ul>
                                                                  </div>
                                                            </div>
                                                            <div class="upload_preview_files_new"></div>
                                                        </div>
                                                    </div><!-- Status Upload  -->
                                                </div><!-- Widget Area -->
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
        <?php
        $content = ob_get_clean();
        $tab = '<a href="#tab_content_new" id="tab_content_a_new" aria-controls="#new" role="tab" data-toggle="tab" class="tab_a" style="font-size: 12px;padding: 11px 5px;margin-right:0px !important;"><button type="button" class="btn btn-default btn-circle close_tab pull-right"><span class="glyphicon glyphicon-remove"></span></button><div class="badge">#New Ticket</div><span></span></a>';
        die(json_encode(array("tab_head"=>$tab,"tab_content"=>$content)));
    }
    
    static function eh_crm_ticket_new_submit() {
        check_ajax_referer('ajax_crm_nonce', 'security');
        $email = sanitize_text_field($_POST['email']);
        $title = sanitize_text_field($_POST['title']);
        $desc = str_replace("\n", '<br/>', $_POST['desc']);
        $submit = sanitize_text_field($_POST['submit']);
        $assignee = ((sanitize_text_field($_POST['assignee']) !== '')?explode(",", sanitize_text_field($_POST['assignee'])):array());
        $tags = ((sanitize_text_field($_POST['tags']) !== '')?explode(",", sanitize_text_field($_POST['tags'])):array());
        $input = json_decode(stripslashes(sanitize_text_field($_POST['input'])), true);
        $files = isset($_FILES["file"])?$_FILES["file"]:"";
        $args = array(
            'ticket_author' => 0,
            'ticket_email' => $email,
            'ticket_title' => $title,
            'ticket_content' => $desc,
            'ticket_category' => 'raiser_reply',
            'ticket_vendor' => ''
        );
        $meta = array();
        $meta['ticket_assignee'] = $assignee;
        $meta['ticket_tags'] = $tags;
        foreach ($input as $key => $value) {
            $meta[$key] = $value;
        }
        if(isset($_FILES["file"]) && !empty($_FILES['file']))
        {   
            $attachment_data = CRM_Ajax::eh_crm_ticket_file_handler($files);
            $meta["ticket_attachment"] = $attachment_data['url'];
            $meta["ticket_attachment_path"] = $attachment_data['path'];
        }
        $meta['ticket_label'] = $submit;
        $meta['ticket_source'] = "Form";
        $id=eh_crm_insert_ticket($args,$meta);
        $content_html = CRM_Ajax::eh_crm_ticket_single_view_gen($id);
        $tab = CRM_Ajax::eh_crm_ticket_single_view_gen_head($id);
        die(json_encode(array("tab_head"=>$tab,"tab_content"=>$content_html,"id"=>$id)));
    }
    
    static function eh_crm_check_ticket_request() {
        $url = sanitize_text_field($_POST['url']);
        if(is_user_logged_in())
        {
           $user_id = get_current_user_id();
           $user = new WP_User($user_id);
           $email = $user->user_email;
           $content = CRM_Ajax::eh_crm_user_ticket_fetch($email, $user_id);
           die(json_encode(array("status"=>"success","content"=>$content)));
        }
        else
        {
            $content = '<div class="form-elements"><span>'.__('You must Login to Check your Existing Ticket', 'wsdesk').'</span><br><a class="btn btn-primary" href="'. wp_login_url().'">'.__('Login', 'wsdesk').'</a></div>';
            $content .= '<div class="form-elements"><span>'.__('Need an Account?', 'wsdesk').'</span><br><a class="btn btn-primary" href="'. wp_registration_url().'">'.__('Register', 'wsdesk').'</a></div>';
            die(json_encode(array("status"=>"success","content"=>$content)));
        }
    }
    
    static function eh_crm_user_ticket_fetch($email,$user) {
        $email_id = eh_crm_get_ticket_value_count("ticket_email", $email,false,"ticket_parent",0);
        $user_id = eh_crm_get_ticket_value_count("ticket_author", $user,false,"ticket_parent",0);
        $ticket = array_values(array_unique(array_merge($email_id, $user_id),SORT_REGULAR));
        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
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
        $avail_labels = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
        ob_start();
        ?>
            <div class="panel panel-default tickets_panel">
                <div class="panel-heading">
                    <h3 class="panel-title">Your Tickets 
                        <span class="spinner_loader table_loader">
                            <span class="bounce1"></span>
                            <span class="bounce2"></span>
                            <span class="bounce3"></span>
                        </span>
                    </h3>
                </div>
                <table class="table table-hover" id="support-table">
                    <thead>
                        <tr class="except_view">
                            <th>#</th>
                            <th>Subject</th>
                            <th>Requested</th>
                            <th>Assignee</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if(!empty($ticket))
                            {
                                for($i=0;$i<count($ticket);$i++)
                                {
                                    $current = eh_crm_get_ticket(array('ticket_id'=>$ticket[$i]['ticket_id']));
                                    $current_meta = eh_crm_get_ticketmeta($ticket[$i]['ticket_id']);
                                    $eye_color='';
                                    $label_name='';
                                    for($j=0;$j<count($avail_labels);$j++)
                                    {
                                        if($avail_labels[$j]['slug'] == $current_meta['ticket_label'])
                                        {
                                            $label_name = $avail_labels[$j]['title'];
                                            $eye_color = eh_crm_get_settingsmeta($avail_labels[$j]['settings_id'], "label_color");
                                        }
                                    }
                                    $ticket_assignee_name =array();
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
                                                }
                                            }
                                        }
                                    }
                                    $ticket_assignee_name = empty($ticket_assignee_name)?"No Assignee":implode(", ", $ticket_assignee_name);
                                    echo 
                                    '<tr class="clickable ticket_row" id="'.$current[0]['ticket_id'].'">
                                        <td>'.$current[0]['ticket_id'].'</td>
                                        <td class="wrap_content">'.$current[0]['ticket_title'].'</td>
                                        <td>'. eh_crm_get_formatted_date($current[0]['ticket_date']).'</td>
                                        <td>'.$ticket_assignee_name.'</td>
                                        <td><span class="label label-info" style="background-color:'.$eye_color.' !important">'.$label_name.'</td>
                                    </tr>
                                    ';
                                }
                            }
                            else
                            {
                                echo '<tr class="except_view">
                                        <td colspan="5">No Tickets</td>
                                    </tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>
                <hr/>
            <div class="ticket_load_content" style="padding:5px !important"></div>
        <?php
        $content = ob_get_clean();
        return $content;
    }
    
    static function eh_crm_ticket_single_view_client() {
        $ticket_id = $_POST['ticket_id'];
        $content = CRM_Ajax::eh_crm_ticket_single_view_client_gen($ticket_id);
        die($content);
    }
    
    static function eh_crm_ticket_single_view_client_gen($ticket_id) {
        $current = eh_crm_get_ticket(array("ticket_id"=>$ticket_id));
        $current_meta = eh_crm_get_ticketmeta($ticket_id);
        $users_data = get_users(array("role__in" => array("administrator", "WSDesk_Agents", "WSDesk_Supervisor")));
        $users = array();
        for ($i = 0; $i < count($users_data); $i++) {
            $current_user = $users_data[$i];
            $temp = array();
            $roles = $current_user->roles;
            foreach ($roles as $value) {
                $current_role = $value;
                $temp[$i] = ucfirst(str_replace("_", " ", $current_role));
            }
            $users[implode(' & ', $temp)][$current_user->ID] = $current_user->data->display_name;
        }
        $avail_tags = eh_crm_get_settings(array("type" => "tag"),array("slug","title","settings_id"));
        $avail_labels = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
        $ticket_label = "";
        $ticket_label_slug ="";
        $eye_color = "";
        for($j=0;$j<count($avail_labels);$j++)
        {
            if($avail_labels[$j]['slug'] == $current_meta['ticket_label'])
            {
                $ticket_label = $avail_labels[$j]['title'];
                $ticket_label_slug = $avail_labels[$j]['slug'];
            }
            if($avail_labels[$j]['slug'] == $current_meta['ticket_label'])
            {
                $eye_color = eh_crm_get_settingsmeta($avail_labels[$j]['settings_id'], "label_color");
            }
        }
        $ticket_tags_list = "";
        $response = array();
        $co = 0;
        if(!empty($avail_tags))
        {
            for($j=0;$j<count($avail_tags);$j++)
            {
                $current_ticket_tags=(isset($current_meta['ticket_tags'])?$current_meta['ticket_tags']:array());
                for($k=0;$k<count($current_ticket_tags);$k++)
                {
                    if($avail_tags[$j]['slug'] == $current_ticket_tags[$k])
                    {
                        $args_post = array(
                            'orderby' => 'ID',
                            'numberposts' => -1,
                            'post_type' => array('post', 'product'),
                            'post__in' => eh_crm_get_settingsmeta($avail_tags[$j]['settings_id'], 'tag_posts')
                        );
                        $posts = get_posts($args_post);
                        $temp = get_post();
                        for ($m = 0; $m < count($posts); $m++,$co++) {
                            $response[$co]['title'] = $posts[$m]->post_title;
                            $response[$co]['guid'] = $posts[$m]->guid;
                        }
                        $ticket_tags_list .= '<span class="label label-info">#'.$avail_tags[$j]['title'].'</span>';
                    }
                }
            }
        }
        ob_start();
        ?>
            <div class="row">
                <div class="col-md-12">
                    <ol class="breadcrumb col-md-8" style="margin: 0 !important;background: none !important;border:none;padding: 8px 0px !important; ">
                        <li><?php echo get_bloginfo("name") ?></li>
                        <li>Support</li>
                        <li><?php echo $ticket_label; ?></li>
                        <li class="active"><span class="label label-success" style="background-color:<?php echo $eye_color; ?> !important">Ticket #<?php echo $ticket_id; ?></span></li>
                        <span class="spinner_loader ticket_loader_<?php echo $ticket_id; ?>">
                            <span class="bounce1"></span>
                            <span class="bounce2"></span>
                            <span class="bounce3"></span>
                        </span>
                    </ol>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="panel panel-default single_ticket_panel Ws-content-detail-full" id="<?php echo $ticket_id;?>">
                    <div class="rightPanel" style="border-left: none;">
                        <div class="panel-heading rightPanelHeader" style="width:100% !important;">
                            <div class="leftFreeSpace">
                                    <div class="icon" style="top: 5% !important;"><img src="<?php echo EH_CRM_MAIN_IMG.'message_icon.png'?>"></div>
                                    <div class="tictxt">
                            <p style="margin-top: 5px;font-size: 16px;">
                                <?php
                                    echo $current[0]['ticket_title'];
                                ?>
                                <span class="spinner_loader ticket_loader">
                                    <span class="bounce1"></span>
                                    <span class="bounce2"></span>
                                    <span class="bounce3"></span>
                                </span>
                            </p>
                            <p style="margin-top: 5px;">
                                <i class="glyphicon glyphicon-user"></i> by
                                <?php
                                    if($current[0]['ticket_author'] != 0)
                                    {
                                        $raiser_obj = new WP_User($current[0]['ticket_author']);
                                        echo $raiser_obj->display_name;
                                    }
                                    else
                                    {
                                        echo '<span>'.$current[0]['ticket_email'].'</span>';
                                    }
                                ?>
                                | <i class="glyphicon glyphicon-calendar"></i> <?php echo eh_crm_get_formatted_date($current[0]['ticket_date']); ?>
                                | <i class="glyphicon glyphicon-comment"></i>
                                <?php
                                    $raiser_voice = eh_crm_get_ticket_value_count("ticket_parent",$ticket_id,false,"ticket_category","raiser_reply");
                                    echo count($raiser_voice)." Raiser Voice";                                    
                                ?>
                                | <i class="glyphicon glyphicon-bullhorn"></i>
                                <?php
                                    $agent_voice = eh_crm_get_ticket_value_count("ticket_parent",$ticket_id,false,"ticket_category","agent_reply");
                                    echo count($agent_voice)." Agent Voice";
                                ?>
                            </p>
                            <p style="margin-top: 5px;">
                                <i class="glyphicon glyphicon-tags"></i> Tags : <?php echo (($ticket_tags_list!="")?$ticket_tags_list:"No Tags"); ?>
                            </p>
                                    </div>
                            </div>
                        </div>
                        
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="row newMsgFull" style="margin-bottom: 20px; padding-left:35px;">
                                            <div class="leftFreeSpace">
                                                <div class="icon"><img src="<?php echo get_avatar_url($current[0]['ticket_email'],array('size'=>50)); ?>" style="border-radius: 25px;"></div>
                                                <div class="content">
                                                    <div class="message-box">
                                                <div class="widget-area no-padding blank" style="width:100%">
                                                    <div class="status-upload">
                                                        <?php wp_nonce_field('ajax_crm_nonce', 'security'); ?>
                                                        <textarea rows="10" cols="30" class="form-control reply_textarea" id="reply_textarea_<?php echo $ticket_id; ?>" name="reply_textarea_<?php echo $ticket_id; ?>"></textarea> 
                                                        <div class="form-group">
                                                            <div class="input-group col-md-12">
                                                                <span class="btn btn-primary fileinput-button">
                                                                    <i class="glyphicon glyphicon-plus"></i>
                                                                    <span>Attachment</span>
                                                                    <input type="file" name="files" style="left: 100px;" id="files_<?php echo $ticket_id; ?>" class="attachment_reply" multiple="">
                                                                </span>
                                                                <div class="btn-group pull-right">
                                                                    <button type="button" class="btn btn-primary ticket_reply_action_button" data-loading-text="Submitting Reply..." id="<?php echo $ticket_id; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                      Submit
                                                                    </button>
                                                                  </div>
                                                            </div>
                                                            <div class="upload_preview_files_<?php echo $ticket_id;?>"></div>
                                                        </div>
                                                    </div><!-- Status Upload  -->
                                                </div><!-- Widget Area -->
                                                </div>
                                            </div>
                                            </div>
                                            </div>
                                        </div>
                                        <section class="comment-list">
                                            <?php echo CRM_Ajax::eh_crm_ticket_reply_section_gen_client($ticket_id); ?>
                                        </section>
                                    </div>
                                </div>
                            
                </div>
                        </div>
                    </div>
            
        <?php
        return ob_get_clean();
    }
    
    static function eh_crm_ticket_client_section_load() {
        $ticket_id = sanitize_text_field($_POST['ticket_id']);
        $content = CRM_Ajax::eh_crm_ticket_reply_section_gen_client($ticket_id);
        die($content);
    }

    static function eh_crm_ticket_reply_section_gen_client($ticket_id) {
        ob_start();
        $reply_id = eh_crm_get_ticket_value_count("ticket_parent", $ticket_id,false,"","","ticket_id","DESC");
        array_push($reply_id,array("ticket_id"=>$ticket_id));
        for($s=0;$s<count($reply_id);$s++)
        {
            $reply_ticket = eh_crm_get_ticket(array("ticket_id"=>$reply_id[$s]['ticket_id']));
            $reply_ticket_meta = eh_crm_get_ticketmeta($reply_id[$s]['ticket_id']);
            $replier_name ='';
            $replier_email =$reply_ticket[0]['ticket_email'];
            $replier_pic ='';
            if($reply_ticket[0]['ticket_author']!=0)
            {
                $replier_obj = new WP_User($reply_ticket[0]['ticket_author']);
                $replier_name = $replier_obj->display_name;
                $replier_pic = get_avatar_url($reply_ticket[0]['ticket_author'],array('size'=>50));
            }
            else
            {
                $replier_name = "Guest";
                $replier_pic = get_avatar_url($reply_ticket[0]['ticket_email'],array('size'=>50));
            }
            $attachment = "";
            if(isset($reply_ticket_meta['ticket_attachment']))
            {
                $reply_att = $reply_ticket_meta['ticket_attachment'];
                $attachment = '<div>';
                for($at=0;$at<count($reply_att);$at++)
                {
                    $current_att = $reply_att[$at];
                    $att_ext = pathinfo($current_att, PATHINFO_EXTENSION);
                    if(empty($att_ext))
                    {
                       $att_ext=''; 
                    }
                    $att_name = pathinfo($current_att, PATHINFO_FILENAME);
                    $img_ext = array("jpg","jpeg","png","gif");
                    if(in_array($att_ext, $img_ext))
                    {
                        $attachment .= '<a href="'.$current_att.'" target="_blank"><img class="img-upload clickable" style="width:200px" title="' .$att_name. '" src="'.$current_att.'"></a></p>';
                    }
                    else
                    {
                        $check_file_ext = array('doc','docx','pdf','xml','csv','xlsx','xls','txt','zip');
                        if(in_array($att_ext,$check_file_ext))
                        {
                            $attachment .= '<a href="'.$current_att.'" target="_blank" title="' .$att_name. '" class="img-upload"><div class="'.$att_ext.'"></div></a>';
                        }
                        else
                        {
                            $attachment .= '<a href="'.$current_att.'" target="_blank" title="' .$att_name. '" class="img-upload"><div class="unknown_type"></div></a>';
                        }
                    }
                }
                $attachment .= '</div>';
            }
            switch ($reply_ticket[0]['ticket_category']) {
                case "agent_reply":
                case "raiser_reply":    
                    echo '
                        <div class="conversation_each" style="width:96% !important;">
                            <div class="leftFreeSpace">
                            <div class="icon">
                                    <img style="border-radius: 25px;" src="'.$replier_pic.'" />
                            </div>
                            <h3>'.$replier_name.'</h3>
                                        <h4>
                                            '.$replier_email.' | 
                                            '. eh_crm_get_formatted_date($reply_ticket[0]['ticket_date']).'
                                        </h4>
                                        <hr>
                                        <div class="comment-post">
                                            <p>';$input_data = html_entity_decode(stripslashes($reply_ticket[0]['ticket_content']));
                                            
                                            $input_array[0] = '/<((html)[^>]*)>(.*)\<\/(html)>/Us';
                                            $input_array[1] = '/<((head)[^>]*)>(.*)\<\/(head)>/Us';
                                            $input_array[2] = '/<((style)[^>]*)>(.*)\<\/(style)>/Us';
                                            $input_array[3] = '/<((body)[^>]*)>(.*)\<\/(body)>/Us';
                                            $input_array[4] = '/<((form)[^>]*)>(.*)\<\/(form)>/Us';
                                            $input_array[5] = '/<((input)[^>]*)>(.*)\<\/(input)>/Us';
                                            $input_array[7] = '/<((input)[^>]*)>/Us';
                                            $input_array[6] = '/<((button)[^>]*)>(.*)\<\/(button)>/Us';
                                            $input_array[8] = '/<((script)[^>]*)>(.*)\<\/(script)>/Us';
                                            $input_array[9] = '/<((iframe)[^>]*)>(.*)\<\/(iframe)>/Us';
                                            $output_array[0] = '&lt;$1&gt;$3&lt;/html&gt;';
                                            $output_array[1] = '&lt;$1&gt;$3&lt;/head&gt;';
                                            $output_array[2] = '&lt;$1&gt;$3&lt;/style&gt;';
                                            $output_array[3] = '&lt;$1&gt;$3&lt;/body&gt;';
                                            $output_array[4] = '&lt;$1&gt;$3&lt;/form&gt;';
                                            $output_array[5] = '&lt;$1&gt;$3&lt;/input&gt;';
                                            $output_array[6] = '&lt;$1&gt;$3&lt;/button&gt;';
                                            $output_array[7] = '&lt;$1&gt;$3&lt;/input&gt;';
                                            $output_array[8] = '&lt;$1&gt;$3&lt;/script&gt;';
                                            $output_array[9] = '&lt;$1&gt;$3&lt;/iframe&gt;';
                                            $input_data = preg_replace($input_array, $output_array, $input_data); 
                                            
                                            echo $input_data;
                                            echo '</p>
                                            '.$attachment.'
                                        </div>
                            </div>                
                        </div>
                    ';
                    break;
                default:
                    break;
            }
        }
        return ob_get_clean();
    }
    
    static function eh_crm_ticket_reply_raiser() {
        check_ajax_referer('ajax_crm_nonce', 'security');
        $files = isset($_FILES["file"])?$_FILES["file"]:"";
        $ticket_id = sanitize_text_field($_POST['ticket_id']);
        $content = str_replace("\n", '<br/>',$_POST['ticket_reply']);
        $parent = eh_crm_get_ticket(array("ticket_id"=>$ticket_id));
        eh_crm_update_ticketmeta($ticket_id, "ticket_label", 'label_LL01');
        $user = wp_get_current_user();
        $category = 'raiser_reply';
        $child = array(
            'ticket_email' => $user->user_email,
            'ticket_title' => $parent[0]['ticket_title'],
            'ticket_content' => $content,
            'ticket_category' => $category,
            'ticket_parent' => $ticket_id);
        $child_meta = array();
        if(isset($_FILES["file"]) && !empty($_FILES['file']))
        {   
            $attachment_data = CRM_Ajax::eh_crm_ticket_file_handler($files);
            $child_meta["ticket_attachment"] = $attachment_data['url'];
            $child_meta["ticket_attachment_path"] = $attachment_data['path'];
        }
        $gen_id = eh_crm_insert_ticket($child,$child_meta);
        $content_ticket = CRM_Ajax::eh_crm_ticket_single_view_client_gen($ticket_id);
        $user_id = get_current_user_id();
        $user_data = new WP_User($user_id);
        $email = $user_data->user_email;
        $content_table = CRM_Ajax::eh_crm_user_ticket_fetch($email, $user_id);
        die(json_encode(array("table"=>$content_table,"ticket"=>$content_ticket)));
    }
    
    static function eh_crm_activate_oauth() {
        $client_id = sanitize_text_field($_POST['client_id']);
        $client_secret = sanitize_text_field($_POST['client_secret']);
        eh_crm_update_settingsmeta(0, "oauth_client_id", $client_id);
        eh_crm_update_settingsmeta(0, "oauth_client_secret", $client_secret);
        $oauth_obj = new EH_CRM_OAuth();
        die($oauth_obj->make_oauth_uri());
    }
    
    static function eh_crm_deactivate_oauth() {
        $oauth_obj = new EH_CRM_OAuth();
        $oauth_obj->revoke_token();
        die(include(EH_CRM_MAIN_VIEWS . "email/crm_oauth_setup.php"));
    }

    static function eh_crm_activate_email_protocol() {
        $server_url = sanitize_text_field($_POST['server_url']);
        $server_port= sanitize_text_field($_POST['server_port']);
        $email      = sanitize_text_field($_POST['email']);
        $email_pwd  = sanitize_text_field($_POST['email_pwd']);
        if(in_array("imap", get_loaded_extensions()))
        {
            $imap = @imap_open("{".$server_url.":".$server_port."/imap/ssl/novalidate-cert}", $email, $email_pwd);
            if(!$imap)
            {
                die(json_encode(array("status"=>"failure","message"=>"EMail Server Not Found")));
            }
            else
            {
                eh_crm_update_settingsmeta(0, "imap_server_url", $server_url);
                eh_crm_update_settingsmeta(0, "imap_server_port", $server_port);
                eh_crm_update_settingsmeta(0, "imap_server_email", $email);
                eh_crm_update_settingsmeta(0, "imap_server_email_pwd", $email_pwd);
                eh_crm_update_settingsmeta(0, "imap_activation", "activated");
                die(json_encode(array("status"=>"success","message"=>"Email IMAP Configured","content"=>include(EH_CRM_MAIN_VIEWS . "email/crm_imap_setup.php"))));
            }
        }
        else
        {
            die(json_encode(array("status"=>"failure","message"=>"IMAP is not enabled in your Server")));
        }
        
    }
    static function eh_crm_deactivate_email_protocol() {
        eh_crm_update_settingsmeta(0, "imap_activation", "deactivated");
        die(include(EH_CRM_MAIN_VIEWS . "email/crm_imap_setup.php"));
    }

    static function eh_crm_email_block_filter()
    {
        $new_block = json_decode(stripslashes(sanitize_text_field($_POST['new_block'])), true);
        if (!empty($new_block)) {
            
            $new_email = $new_block['email'];
            $type =  $new_block['type'];
            $block_filter = eh_crm_get_settingsmeta("0", "email_block_filters");
            if(!$block_filter)
            {
                $block_filter = array();
            }
            $available = true;
            foreach ($block_filter as $email => $data)
            {
                if($new_email == $email)
                {
                    $available = false;
                }
            }
            if($available)
            {
                $block_filter[$new_email] = $type;
                eh_crm_update_settingsmeta("0", "email_block_filters",$block_filter);
            }
        }
        die(include(EH_CRM_MAIN_VIEWS . "email/crm_filter_block_setup.php"));
    }
    
    static function eh_crm_email_block_delete()
    {
        $block_remove = sanitize_text_field($_POST['block_remove']);
        $block_filter = eh_crm_get_settingsmeta("0", "email_block_filters");
        if(!$block_filter)
        {
            $block_filter = array();
        }
        foreach ($block_filter as $email => $data)
        {
            if($email === $block_remove)
            {
                unset($block_filter[$email]);
                eh_crm_update_settingsmeta("0", "email_block_filters",$block_filter);
            }
        }
        die(include(EH_CRM_MAIN_VIEWS . "email/crm_filter_block_setup.php"));
    }
    
    static function eh_crm_fire_email($ticket_id) {
        ini_set('max_execution_time', 300);
        $ticket = eh_crm_get_ticket(array("ticket_id"=>$ticket_id));
        $ticket_meta = eh_crm_get_ticketmeta($ticket_id);
        $parent_id = $ticket[0]['ticket_parent'];
        $meta = eh_crm_get_ticketmeta($parent_id);
        $child_count = count(eh_crm_get_ticket_value_count("ticket_parent", $parent_id));
        $ticket_assignee_name =array();
        $user_roles_default = array("WSDesk_Agents", "WSDesk_Supervisor","administrator");
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
        if(isset($meta['ticket_assignee']))
        {
            $current_assignee = $meta['ticket_assignee'];
            for($k=0;$k<count($current_assignee);$k++)
            {
                for($l=0;$l<count($users_data);$l++)
                {
                    if($users_data[$l]['id'] == $current_assignee[$k])
                    {
                        array_push($ticket_assignee_name, $users_data[$l]['name']);
                    }
                }
            }
        }
        $ticket_assignee = empty($ticket_assignee_name)?"No Assignee":implode(", ", $ticket_assignee_name);
        $ticket_tags = array();
        $avail_tags_wf = eh_crm_get_settings(array("type" => "tag"), array("slug", "title", "settings_id"));
        if(!empty($avail_tags_wf))
        {
            $current_ticket_tags=(isset($meta['ticket_tags'])?$meta['ticket_tags']:array());
            for($j=0;$j<count($avail_tags_wf);$j++)
            {
                for($k=0;$k<count($current_ticket_tags);$k++)
                {
                    if($avail_tags_wf[$j]['slug'] == $current_ticket_tags[$k])
                    {
                        array_push($ticket_tags,$avail_tags_wf[$j]['title']);
                    }
                }
            }
        }
        $ticket_tags_name = empty($ticket_tags)?"No Tags":implode(", ", $ticket_tags);
        $replier_name ='';
        if($ticket[0]['ticket_author']!=0)
        {
            $replier_obj = new WP_User($ticket[0]['ticket_author']);
            $replier_name = $replier_obj->display_name;
        }
        $avail_labels = eh_crm_get_settings(array("type" => "label"), array("slug", "title", "settings_id"));
        $ticket_label = "";
        for($j=0;$j<count($avail_labels);$j++)
        {
            if($avail_labels[$j]['slug'] == $meta['ticket_label'])
            {
                $ticket_label = $avail_labels[$j]['title'];
            }
        }
        $message = eh_crm_get_settingsmeta('0', "support_email_reply_text");
        if($message == "")
        {
            $message = 'Your request (#[id]) has been updated. To add additional comments, reply to this email.

                        Date: [date]

                        [content]

                        Regards,
                        [agent_replied]';
        }
        $message = str_replace('[id]',$ticket[0]['ticket_parent'],$message);
        $message = str_replace('[assignee]',$ticket_assignee,$message);
        $message = str_replace('[tags]',$ticket_tags_name,$message);
        $date = eh_crm_get_formatted_date($ticket[0]['ticket_date']);
        $message = str_replace('[date]',$date,$message);
        $message = str_replace('[content]',stripslashes($ticket[0]['ticket_content']),$message);
        $message = str_replace('[agent_replied]',$replier_name,$message);
        $message = str_replace('[status]',$ticket_label,$message);
        $attachments = (isset($ticket_meta['ticket_attachment_path'])?$ticket_meta['ticket_attachment_path']:array());
        if($child_count == 1)
        {
            $subject = 'Ticket ['.$ticket[0]['ticket_parent'].'] : '.$ticket[0]['ticket_title'];
        }
        else
        {
            $subject = 'Re: Ticket ['.$ticket[0]['ticket_parent'].'] : '.$ticket[0]['ticket_title'];
        }
        eh_crm_debug_error_log("Subject Created - ".$subject);
        $support_email_name = eh_crm_get_settingsmeta('0', "support_reply_email_name");
        $support_email = eh_crm_get_settingsmeta('0', "support_reply_email");
        $headers = array();
	$headers[] = 'Content-Type: text/html; charset=UTF-8';
        if(isset($meta['ticket_cc']))
        {
            foreach ($meta['ticket_cc'] as $cc) {
                $headers[] = 'Cc: '.$cc;
            }
        }
        if(isset($meta['ticket_bcc']))
        {
            foreach ($meta['ticket_bcc'] as $bcc) {
                $headers[] = 'Bcc: '.$bcc;
            }
        }
        if($support_email != '')
        {
	    $headers[] = 'From: '.$support_email_name.' <'.$support_email.'>';
        }
        eh_crm_debug_error_log("Headers Created");
        eh_crm_debug_error_log($headers);
        $to = '';
        if($ticket[0]['ticket_parent']==0)
        {
            $to = $ticket[0]['ticket_email'];
        }
        else
        {
            $ticket_parent = eh_crm_get_ticket(array("ticket_id"=>$ticket[0]['ticket_parent']));
            $to = $ticket_parent[0]['ticket_email'];
        }
        $html = '<html>
                <head>
                <style type="text/css">
                    .powered_wsdesk span
                    {
                        opacity: 0.4;
                        font-size: 10px;
                        color: black;
                    }
                    .powered_wsdesk a
                    {
                        opacity: 0.4;
                        font-size: 10px;
                        color: black !important;
                    }
                    .powered_wsdesk a:focus,
                    .powered_wsdesk a:hover
                    {
                        opacity: 0.8;
                        color: black !important;
                    }
                </style>
                </head>
                 <body>';
        $html.= str_replace("\n", '<br/>', $message);
        $html.= eh_crm_get_poweredby_scripts();
        $html.= '</body></html>';
        $response = false;
        $resmes = 'Email sent successfully';
        try {
            if(eh_crm_validate_email_block($to, 'send'))
            {
                eh_crm_debug_error_log("Triggering wp_mail with these paramters ...");
                eh_crm_debug_error_log("to - ".$to);
                eh_crm_debug_error_log("subject - ".$subject);
                eh_crm_debug_error_log("html - ".$html);
                eh_crm_debug_error_log("headers - ");
                eh_crm_debug_error_log($headers);
                eh_crm_debug_error_log("attachments - ");
                eh_crm_debug_error_log($attachments);
                eh_crm_debug_error_log("Calling wp_mail ...");
                add_filter('wp_mail_from', function($email){
                    return eh_crm_get_settingsmeta('0', "support_reply_email");
                });
                add_filter('wp_mail_from_name', function($name){
                    return eh_crm_get_settingsmeta('0', "support_reply_email_name");
                });
                $response = wp_mail($to, $subject, $html,$headers,$attachments);
                remove_filter( 'wp_mail_from', 'get_wp_mail_from' );
                remove_filter( 'wp_mail_from_name', 'get_wp_mail_from_name' );
                eh_crm_debug_error_log("wp_mail returned: ".(($response)?"TRUE":"FALSE"));
            }
        } catch (Exception $exc) {
            $resmes = $exc->getTraceAsString();
            eh_crm_debug_error_log("exception on triggering email: ".$resmes);
        }
        eh_crm_debug_error_log("returning from triggering email: ".$resmes);
        return array('status'=>$response,"message"=>$resmes);
    }
    
    static function eh_crm_email_support_save() {
        $support_email_name = $_POST['support_email_name'];
        $support_email      = $_POST['support_email'];
        $reply_ticket_text  = $_POST['reply_ticket_text'];
        $debug_status = $_POST['debug_status'];
        eh_crm_update_settingsmeta(0, "support_reply_email_name", $support_email_name);
        eh_crm_update_settingsmeta(0, "support_reply_email", $support_email);
        eh_crm_update_settingsmeta(0, "support_email_reply_text", $reply_ticket_text);
        eh_crm_update_settingsmeta(0, "wsdesk_debug_status", $debug_status);
        die();
    }

    static function eh_crm_zendesk_library() {
        try 
        {
            $response = wp_remote_get("https://wsdesk.com/wp-content/uploads/2017/03/zendesk.zip",array("timeout"=>300));
            $zip = $response['body'];
            $file = EH_CRM_MAIN_VENDOR."zendesk.zip";
            $fp = fopen($file, "w");
            fwrite($fp, $zip);
            fclose($fp);
            WP_Filesystem();
            if (unzip_file($file, EH_CRM_MAIN_VENDOR)) {
                unlink($file);
                die(json_encode(array("status"=>"success","body"=>include(EH_CRM_MAIN_VIEWS . "import/crm_zendesk_import.php"))));
            } 
            else
            {
                die(json_encode(array("status"=>"failure","data"=>"Error while Activating Zendesk")));
            }
        } 
        catch (Exception $exc) 
        {
            die(json_encode(array("status"=>"failure","data"=>$exc->getMessage())));
        }
    }
    
    static function eh_crm_zendesk_save_data(){
        $token = sanitize_text_field($_POST['token']);
        $subdomain = sanitize_text_field($_POST['subdomain']);
        $username = sanitize_text_field($_POST['username']);
        eh_crm_update_settingsmeta(0, "zendesk_accesstoken", $token);
        eh_crm_update_settingsmeta(0, "zendesk_subdomain", $subdomain);
        eh_crm_update_settingsmeta(0, "zendesk_username", $username);
        die("success");
    }
    
    static function eh_crm_zendesk_pull_tickets() {
        eh_crm_update_settingsmeta(0, "zendesk_tickets_import", "started");
        eh_crm_write_log("");
        $page = $_POST['page'];
        $attachment = $_POST['attachment'];
        $plan = $_POST['plan'];
        require_once (EH_CRM_MAIN_PATH . "includes/class-crm-import-tickets.php");
        $import = new EH_CRM_Import_Tickets();
        $response = $import->zendesk_get_ticket($page,$attachment,$plan);
        $return = array();
        if($response['status'] == "success")
        {
            if($response['next'] !=0 )
            {
                $return['status'] = "continue";
                $return['next_page'] = $response['next'];
                $return['percentage'] = ($response['total']/(100/($response['next']-1)));
            }
            else
            {
                $return['total'] = $response['total'];
                $return['status'] = "completed";
                eh_crm_update_settingsmeta(0, "zendesk_tickets_import", "stopped");
            }
        }
        else
        {
            $return['status'] = "failure";
            $return['body'] = $response['message'];
        }
        die(json_encode($return));
    }
    static function eh_crm_live_log() {
        if (isset($_GET['action'])) {
            session_start();
            $upload = wp_upload_dir();
            $handle = fopen($upload['path']."/wsdesk_import", 'r');
            if (isset($_SESSION['offset'])) {
              $data = stream_get_contents($handle, -1, $_SESSION['offset']);
              if(isset($_SESSION['old_data']))
              {
                if($_SESSION['old_data'] == $data)
                {
                    die();
                }
                else
                {
                    $_SESSION['old_data'] = $data;
                    echo "<br/>".nl2br($data);
                }
              }
              else
              {
                $_SESSION['old_data'] = $data;
                echo "<br/>".nl2br($data);
              }
            } else {
              fseek($handle, 0, SEEK_END);
              $_SESSION['offset'] = ftell($handle);
            } 
            die();
        }
    }
    
    static function eh_crm_zendesk_stop_pull_tickets() {
        eh_crm_update_settingsmeta(0, "zendesk_tickets_import", "stopped");
        die();
    }
    
    static function uninstall_reason_submission() {
        global $wpdb;

        if ( ! isset( $_POST['reason_id'] ) ) {
            wp_send_json_error();
        }

        $data = array(
            'reason_id'     => sanitize_text_field( $_POST['reason_id'] ),
            'plugin'        => "wsdesk",
            'auth'          => 'wsdesk_uninstall_1234#',
            'date'          => gmdate("M d, Y h:i:s A"),
            'url'           => home_url(),
            'user_email'    => ($_POST['allow_contacting'])?$_POST['email']:'',
            'reason_info'   => isset( $_REQUEST['reason_info'] ) ? trim( stripslashes( $_REQUEST['reason_info'] ) ) : '',
            'software'      => $_SERVER['SERVER_SOFTWARE'],
            'php_version'   => phpversion(),
            'mysql_version' => $wpdb->db_version(),
            'wp_version'    => get_bloginfo( 'version' ),
            'locale'        => get_locale(),
            'multisite'     => is_multisite() ? 'Yes' : 'No',
            'wsdesk_version'=> EH_CRM_VERSION
        );
        $resp = wp_remote_post('https://wsdesk.com/wp-json/wsdesk/v1/uninstall', array(
                'method'      => 'POST',
                'timeout'     => 45,
                'redirection' => 5,
                'httpversion' => '1.0',
                'blocking'    => false,
                'headers'     => array( 'user-agent' => 'wsdesk/' . md5( esc_url( home_url() ) ) . ';' ),
                'body'        => $data,
                'cookies'     => array()
            )
        );
        
        wp_send_json_success();
    }
    static function eh_crm_get_settingsmeta_from_slug() {
        $fields="settings_id";
        $slug= sanitize_text_field($_POST['slug']);
        $args=array('slug'=>$slug);
        $setting = eh_crm_get_settings($args,$fields);
        $settings_id=$setting[0];
        $settings_meta=eh_crm_get_settingsmeta($settings_id['settings_id']);
        die(json_encode($settings_meta));
    }
    static function wsdesk_api_create_ticket()
    {
        $enable_api = eh_crm_get_settingsmeta('0', 'enable_api');
        if($enable_api!='enable')
            die(json_encode(array('status'=>'error','message'=>'API not enabled')));
        $api_key = eh_crm_get_settingsmeta('0', 'api_key');
        if(!isset($_POST['api_key']) || $_POST['api_key']!=$api_key)
        {
            die(json_encode(array('status'=>'error','message'=>'Authentication error.')));
        }
        
        $post_values = $_POST;
        if(isset($post_values['g-recaptcha-response']))
        {
            if($post_values['g-recaptcha-response']=="")
            {
                die("captcha_failed");
            }
            require_once "recaptcha.php";
            $settings = eh_crm_get_settings(array("slug"=>"google_captcha"),"settings_id");
            $secret = eh_crm_get_settingsmeta($settings[0]['settings_id'], "field_secret_key");
            $reCaptcha = new ReCaptcha($secret);
            $response = $reCaptcha->verifyResponse($_SERVER["REMOTE_ADDR"],$post_values['g-recaptcha-response']);
            if ($response == null && !$response->success) {
                die("captcha_failed");
            }
        }
        $files = isset($_FILES["file"])?$_FILES["file"]:"";
        $email = $post_values['request_email'];
        $title = stripslashes($post_values['request_title']);
        $desc = str_replace("\n", '<br/>', stripslashes($post_values['request_description']));
        $vendor = '';
        if(EH_CRM_WOO_STATUS)
        {
            if(isset($post_values['woo_vendors']))
            {
                $vendor = str_replace("v_","",$post_values['woo_vendors']);
            }
        }
        $args = array(
            'ticket_email' => $email,
            'ticket_title' => $title,
            'ticket_content' => $desc,
            'ticket_category' => 'raiser_reply',
            'ticket_vendor' => $vendor,
        );
        if(eh_crm_get_settingsmeta(0,"auto_create_user") === 'enable')
        {
            $email_check = email_exists($email);
            if($email_check)
            {
                $args['ticket_author'] = $email_check;
            }
            else
            {
                
                $maybe_username = explode('@', $email);
                $maybe_username = sanitize_user($maybe_username[0]);
                $counter = 1;
                $username = $maybe_username;
                $password = wp_generate_password(12, true);

                while (username_exists($username)) {
                    $username = $maybe_username . $counter;
                    $counter++;
                }

                $user = wp_create_user($username, $password, $email);
                if(!is_wp_error($user))
                {
                    wp_new_user_notification($user,null,'both');
                    $args['ticket_author'] = $user;
                }
            }
        }
        unset($post_values['request_email']);
        unset($post_values['request_title']);
        unset($post_values['request_description']);
        $meta = array();
        $req_args = array("type" => "tag");
        $fields = array("slug", "title", "settings_id");
        $avail_tags = eh_crm_get_settings($req_args, $fields);
        $tagged = array();
        if(!empty($avail_tags))
        {
            for ($i = 0, $j = 0; $i < count($avail_tags); $i++) {
                if (preg_match('/' . strtolower($avail_tags[$i]['title']) . '/', strtolower($desc)) || preg_match('/' . strtolower($avail_tags[$i]['title']) . '/', strtolower($title))) {
                    $tagged[$j] = $avail_tags[$i]['slug'];
                    $j++;
                }
            }
        }
        $meta['ticket_tags'] = $tagged;
        $default_assignee = eh_crm_get_settingsmeta('0', "default_assignee");
        $assignee = array();
        switch ($default_assignee) {
            case "ticket_tags":
                $users = get_users(array("role__in" => array("WSDesk_Agents", "WSDesk_Supervisor")));
                $user_tags = array();
                for ($i = 0; $i < count($users); $i++) {
                    $current = $users[$i];
                    $id = $current->ID;
                    $user_tags[$id] = get_user_meta($id, "wsdesk_tags", true);
                }
                foreach ($user_tags as $key => $value) {
                    for($i=0;$i<count($value);$i++)
                    {
                        if(in_array($value[$i], $tagged))
                        {
                            array_push($assignee, $key);
                            break;
                        }
                    }
                }
                break;
            case "ticket_vendors":
                array_push($assignee, $vendor);
                break;
            case "no_assignee":
                break;
            default:
                array_push($assignee, $default_assignee);
                break;
        }
        $meta['ticket_assignee'] = $assignee;
        $default_label = eh_crm_get_settingsmeta('0', "default_label");
        if(eh_crm_get_settings(array('slug'=>$default_label)))
        {
            $meta['ticket_label'] = $default_label;
        }
        foreach ($post_values as $key => $value) {
            $meta[$key] = $value;
        }
        if(isset($_FILES["file"]) && !empty($_FILES['file']))
        {   
            $attachment_data = CRM_Ajax::eh_crm_ticket_file_handler($files);
            $meta["ticket_attachment"] = $attachment_data['url'];
            $meta["ticket_attachment_path"] = $attachment_data['path'];
        }
        $meta['ticket_source'] = "Form";
        $gen_id = eh_crm_insert_ticket($args, $meta);
        $send = eh_crm_get_settingsmeta('0', "auto_send_creation_email");
        if($send == 'enable')
        {
            eh_crm_debug_error_log(" ------------- WSDesk Email Debug Started ------------- ");
            eh_crm_debug_error_log("New ticket auto Email for Ticket #".$gen_id);
            eh_crm_debug_error_log("Email function called for New Ticket #".$gen_id);
            $response = CRM_Ajax::eh_crm_fire_email("new_ticket", $gen_id);
            eh_crm_debug_error_log(" ------------- WSDesk Email Debug Ended ------------- ");
            
        }
        die(json_encode(array('status'=> 'success','message'=>'Support Request Received Successfully')));
    }
}