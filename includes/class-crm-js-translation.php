<?php

if (!defined('ABSPATH')) {
    exit;
}

function eh_crm_js_translation_obj($page) {
    switch ($page) {
        case "settings":
            $js_var = array
                (
                'Choose_the_Vendors' => __('Choose the Vendor(s)', 'wsdesk'),
                'No_Vendors_Found' => __('No Vendor Found', 'wsdesk'),
                'No_Labels_Found' => __('No Status Found', 'wsdesk'),
                'Select_Ticket_Labels' => __("Select Ticket Status", 'wsdesk'),
                'Select_Ticket_Fields' => __("Select Ticket Fields", 'wsdesk'),
                'No_Fields_Found' => __("No Fields Found", 'wsdesk'),
                'Select_Ticket_Views' => __("Select Ticket Views", 'wsdesk'),
                'No_Views_Found' => __("No Views Found", 'wsdesk'),
                'Select_Triggers' => __("Select Triggers", 'wsdesk'),
                'No_Triggers_Found' => __("No Triggers Found", 'wsdesk'),
                'Search_and_Choose' => __("Search and Choose", 'wsdesk'),
                'No_Posts_Found' => __("No Posts Found", 'wsdesk'),
                'Select_Ticket_Tags' => __("Select Ticket Tags", 'wsdesk'),
                'No_Tags_Found' => __("No Tags Found", 'wsdesk'),
                'General' => __("General", 'wsdesk'),
                'Ticket_Fields' => __("Ticket Fields", 'wsdesk'),
                'Ticket_labels' => __("Ticket Status", 'wsdesk'),
                'Ticket_Tags' => __("Ticket Tags", 'wsdesk'),
                'Ticket_Views' => __("Ticket Views", 'wsdesk'),
                'Triggers_Automation' => __("Triggers & Automation", 'wsdesk'),
                'Appearance' => __("Form Settings", 'wsdesk'),
                'Backup_Restore' => __("Backup & Restore", 'wsdesk'),
                'Activation_of_WSDesk' => __("Activation of WSDesk", 'wsdesk'),
                'General_Settings' => __("General Settings", 'wsdesk'),
                'Updated_and_Saved_Successfully' => __("Updated and Saved Successfully", 'wsdesk'),
                'WooCommerce_Settings' => __("WooCommerce Settings", 'wsdesk'),
                'Appearance_Settings' => __("Appearance Settings", 'wsdesk'),
                'Add_Ticket_Field' => __("Add Ticket Field", 'wsdesk'),
                'Enter_title_for_the_Field' => __("Enter title for the Field", 'wsdesk'),
                'Default_Value_is_not_Matched' => __("Default Value is not Matched", 'wsdesk'),
                'Site_Key_and_Secret_Key_is_Required' => __("Site Key and Secret Key is Required", 'wsdesk'),
                'Edit_Ticket_Field' => __("Edit Ticket Field", 'wsdesk'),
                'Add_Value' => __("Add Value", 'wsdesk'),
                'Enter_Details_for_custom' => __("Enter details for custom", 'wsdesk'),
                'Enter_Title' => __("Enter Title", 'wsdesk'),
                'Enter_Site_Key' => __("Enter Site Key", 'wsdesk'),
                'Enter_Secret_Key' => __("Enter Secret Key", 'wsdesk'),
                'Want_to_give_some_description_to_this_field' => __("Want to give some description to this field?", 'wsdesk'),
                'Specify_whether_this_Field_is_Optional_or_Required' => __("Specify whether this field is mandatory or not", 'wsdesk'),
                'Yes_This_Field_is_Mandatory' => __("Yes! This Field is Mandatory", 'wsdesk'),
                'No_Its_an_Optional_Field' => __("No! It's an Optional Field", 'wsdesk'),
                'Auto_fill_products' => __("Auto fill products", 'wsdesk'),
                'Specify_the_Product_values' => __("Specify Product values", 'wsdesk'),
                'Enter_Default_Values' => __("Enter Default Values", 'wsdesk'),
                'Want_to_use_this_field_for_Filter_Tickets' => __("Want to use this field to filter tickets", 'wsdesk'),
                'Yes_I_will_use_it_for_Filter' => __("Yes! I will use it to filter tickets", 'wsdesk'),
                'No_Just_for_Information' => __("No! Just for Information", 'wsdesk'),
                'Auto_fill_categories' => __("Auto fill categories", 'wsdesk'),
                'Specify_the_Category_values' => __("Specify Category values", 'wsdesk'),
                'Specify_the_Tag_values' => __("Specify Tag values", 'wsdesk'),
                'Auto_fill_tags' => __("Auto fill tags", 'wsdesk'),
                'Specify_the_Vendor' => __("Specify the Vendor", 'wsdesk'),
                'Auto_fill_Vendors' => __("Auto fill Vendors", 'wsdesk'),
                'Specify_the_Radio_values' => __("Specify Radio values", 'wsdesk'),
                'Enter_first_value' => __("Enter first value", 'wsdesk'),
                'Specify_the_Checkbox_values' => __("Specify the Checkbox values", 'wsdesk'),
                'Specify_the_Dropdown_values' => __("Specify the Dropdown values", 'wsdesk'),
                'Enter_Placeholder' => __("Enter Placeholder", 'wsdesk'),
                'Enter_next_value' => __("Enter next value", 'wsdesk'),
                'Remove_Values' => __("Remove Values", 'wsdesk'),
                'Add_Ticket_Label' => __("Add Ticket Status", 'wsdesk'),
                'Enter_title_for_the_Label' => __("Enter status", 'wsdesk'),
                'Ticket_Label' => __("Ticket Status", 'wsdesk'),
                'Add_Ticket_Tag' => __("Add Ticket Tag", 'wsdesk'),
                'Enter_title_for_the_Tag' => __("Enter title for the Tag", 'wsdesk'),
                'Ticket_Tags' => __("Ticket Tags", 'wsdesk'),
                'Condition' => __("Condition", 'wsdesk'),
                'Enter_Value' => __("Enter Value", 'wsdesk'),
                'View_condition_field' => __("View condition field", 'wsdesk'),
                'Remove_Condition' => __("Remove Condition", 'wsdesk'),
                'Enter_Period_for_New_Trigger' => __("Enter Period for New Trigger", 'wsdesk'),
                'Edit_Period_for_the_Trigger' => __("Edit Period for the Trigger", 'wsdesk'),
                'Trigger_condition_field' => __("Trigger condition field", 'wsdesk'),
                'Add_Trigger' => __("Add Trigger", 'wsdesk'),
                'Enter_title_for_the_Trigger' => __("Enter title for the Trigger", 'wsdesk'),
                'Specify_some_action_for_the_Trigger' => __("Specify some action for the Trigger", 'wsdesk'),
                'Edit_Trigger' => __("Edit Trigger", 'wsdesk'),
                'Triggers' => __("Triggers", 'wsdesk'),
                'Select_Condition_Values' => __("Select Condition Values", 'wsdesk'),
                'No_Values_Found' => __("No Values Found", 'wsdesk'),
                'Action' => __("Action", 'wsdesk'),
                'Trigger_Action_field' => __("Trigger Action field", 'wsdesk'),
                'Remove_Action' => __("Remove Action", 'wsdesk'),
                'Backup_Restore_Alert' => __("Backup & Restore Alert", 'wsdesk'),
                'Choose_some_data_to_Backup' => __("Choose some data to Backup", 'wsdesk'),
                'WSDesk_Restore_Alert' => __("WSDesk Restore Alert", 'wsdesk'),
                'Keep_Calm_while_Restoring_Data_Dont_Refresh_the_Page' => __("Keep Calm while Restoring Data. Don\'t Refresh the Page", 'wsdesk'),
                'Yes_I_Will_Wait' => __("Yes! I Will Wait", 'wsdesk'),
                'Restore_Finished' => __("Restore Finished", 'wsdesk'),
                'Refresh_and_Hit_to_go' => __("Refresh and Hit to go", 'wsdesk'),
                'Select_a_Backup_File' => __("Select a Backup File", 'wsdesk'),
                'Specify_the_Mail_Subject' => __("Specify the Mail Subject", 'wsdesk'),
                'Enter_mail_subject' => __("Enter mail subject", 'wsdesk'),
                'Codes_for_Notification_EMail' => __("Codes for Notification EMail", 'wsdesk'),
                'To_Insert_Ticket_Number_in_the_notification_email' => __("To Insert Ticket Number in the notification email", 'wsdesk'),
                'To_Insert_Ticket_Assignee_in_the_notification_email' => __("To Insert Ticket Assignee in the notification email", 'wsdesk'),
                'To_Insert_Ticket_Tags_in_the_notification_mail' => __("To Insert Ticket Tags in the notification mail", 'wsdesk'),
                'To_Insert_Ticket_Date_and_Time_in_the_notification_email' => __("To Insert Ticket Date and Time in the notification email", 'wsdesk'),
                'To_Insert_Ticket_Content_in_the_notification_email' => __("To Insert Ticket Content in the notification email", 'wsdesk'),
                'To_Insert_Satisfaction_URL_in_the_notification_email' => __("To Insert Satisfaction URL in the notification email", 'wsdesk'),
                'To_Insert_Conversation_History_in_the_notification_email' => __("To Insert Conversation History in the notification email", 'wsdesk'),
                'Note_For_Satisfaction_Survey_place_the_shortcode_in_new_page' => __("Note : For Satisfaction Survey place the [wsdesk_satisfaction] shortcode in new page.", 'wsdesk'),
                'Specify_the_Mail_Body' => __("Specify the Mail Body", 'wsdesk'),
                'Enter_mail_body' => __("Enter mail body", 'wsdesk'),
                'Select_Action_Values' => __("Select Action Values", 'wsdesk'),
            );
            return $js_var;
        case 'tickets':
            $js_var = array
                (
                'WSDesk_Alert' => __("WSDesk Alert", 'wsdesk'),
                'Do_You_want_to_Delete_Ticket' => __('Do You want to Delete Ticket?', 'wsdesk'),
                'Yes_Delete' => __('Yes! Delete', 'wsdesk'),
                'WSDesk_Tickets_Notification' => __('WSDesk Tickets Notification', 'wsdesk'),
                'Tickets_Deleted_Successfully' => __('Tickets Deleted Successfully', 'wsdesk'),
                'Do_You_want_to_Update_Tickets_Label' => __('Do You want to Update Tickets Status?', 'wsdesk'),
                'Yes_Update' => __('Yes! Update', 'wsdesk'),
                'Tickets_Updated_Successfully' => __('Tickets Updated Successfully', 'wsdesk'),
                'New_Ticket_Created_Successfully' => __('New Ticket Created Successfully', 'wsdesk'),
                'missing_some_data' => __('missing some data', 'wsdesk'),
                'Replied_Successfully' => __('Replied Successfully', 'wsdesk'),
                'needs_reply_content' => __('needs reply content', 'wsdesk'),
                'No_results_found' => __('No results found', 'wsdesk'),
                "Select_Tag" => __("Select Tag", 'wsdesk'),
                "No_Tags_Tagged" => __("No Tags Tagged", 'wsdesk'),
                'ticket_admin_url' => admin_url("admin.php?page=wsdesk_tickets")
            );
            return $js_var;
        case 'agents':
            $js_var = array
                (
                "Search_Tags" => __("Search - (Eg:Support)", 'wsdesk'),
                "No_Tags_Found" => __("No Tags Found", 'wsdesk'),
                "Select_User" => __("Select existing users", 'wsdesk'),
                "No_Users_Found" => __("No Users Found", 'wsdesk'),
                "Add_Agents" => __("Add Agents", 'wsdesk'),
                "Manage_Agents" => __("Manage Agents", 'wsdesk'),
                "WSDesk_Agents" => __("WSDesk Agents", 'wsdesk'),
                "Agents_Updated_Successfully" => __("Agents Updated Successfully", 'wsdesk'),
                "Agents_Removed_Successfully" => __("Agents Removed Successfully", 'wsdesk'),
                "Agents_added_Successfully" => __("Agents added Successfully", 'wsdesk'),
                "Show_Settings_Page" => __("Show Settings Page", 'wsdesk'),
                "Show_Agents_Page" => __("Show Agents Page", 'wsdesk'),
                "Show_Email_Page" => __("Show Email Page", 'wsdesk'),
                "Show_Import_Page" => __("Show Import Page", 'wsdesk'),
            );
            return $js_var;
        case 'email':
            $js_var = array
                (
                "Google_OAuth_Setup" => __("Google OAuth Setup", 'wsdesk'),
                "IMAP_EMail_Setup" => __("IMAP EMail Setup", 'wsdesk'),
                "Support_Email" => __("Support Email", 'wsdesk'),
                "EMail_Filter_Block" => __("EMail Filter & Block", 'wsdesk'),
                'Select_Blocked_Address' => __("Select EMail", 'wsdesk'),
                'No_Address_Found' => __('No Address Found', 'wsdesk'),
                'Add_Email_Block' => __("Block Email", 'wsdesk'),
                'Enter_Email_for_the_Block' => __("Enter Email to Block", 'wsdesk'),
                "Google_OAuth_Revoked" => __("Google OAuth Revoked", 'wsdesk'),
                "IMAP_EMail_Deactivated" => __("IMAP EMail Deactivated", 'wsdesk'),
                "Updated_and_Saved_Successfully" => __("Updated and Saved Successfully", 'wsdesk')
            );
            return $js_var;
        case 'reports':
            $js_var = array
                (
                "Select_Products_for_Reports" => __("Select Products for Reports", 'wsdesk'),
                "No_Products_Found" => __("No Products Found", 'wsdesk'),
                "Select_Category_for_Reports" => __("Select Category for Reports", 'wsdesk'),
                "No_Category_Found" => __("No Category Found", 'wsdesk')
            );
            return $js_var;
        case 'import':
            $js_var = array
                (
                "From_Zendesk" => __("From Zendesk", 'wsdesk'),
                "Zendesk_Import" => __("Zendesk Import", 'wsdesk'),
                "Tickets_Import_Successful" => __("Tickets Import Successful", 'wsdesk'),
                "Something_Went_Wrong" => __("Something Went Wrong", 'wsdesk'),
                "Import_Stopped_Manually" => __("Import Stopped Manually", 'wsdesk'),
            );
            return $js_var;
        default:
            break;
    }
}
