<?php

defined('ABSPATH') or die("No script kiddies please!");

if (isset($_POST['wpfm_add_nonce_save_menu_show_settings']) && isset($_POST['wpfm_save_menu_show_settings']) && wp_verify_nonce($_POST['wpfm_add_nonce_save_menu_show_settings'], 'wpfm_nonce_save_menu_show_settings') && current_user_can('manage_options')) {
    $menu_enable_disable = isset($_POST['menu_enable_disable']) ? sanitize_text_field($_POST['menu_enable_disable']) : '0';
    $menu_mobile_enable_disable = isset($_POST['mobile_menu_enable_disable']) ? sanitize_text_field($_POST['mobile_menu_enable_disable']) : '0';
    $menu_select = isset($_POST['menu_list_select']) ? sanitize_text_field($_POST['menu_list_select']) : 'default';
    $menu_show_hide_on = sanitize_text_field($_POST['menu_show_hide_on']);
    $menu_link_add_nofollow = sanitize_text_field($_POST['menu_link_add_nofollow']);
    $menu_enable_offset_to_position = isset($_POST['menu_enable_offset_to_position']) ? sanitize_text_field($_POST['menu_enable_offset_to_position']) : '0';
    $floating_menu_settings = array();

    $floating_menu_settings['menu_enable_disable'] = $menu_enable_disable;
    $floating_menu_settings['mobile_menu_enable_disable'] = $menu_mobile_enable_disable;
    $floating_menu_settings['menu_list_selected'] = $menu_select;
    $floating_menu_settings['menu_show_hide_on'] = $menu_show_hide_on;
    $floating_menu_settings['menu_link_add_nofollow'] = $menu_link_add_nofollow;
    $floating_menu_settings['menu_enable_offset_to_position'] = $menu_enable_offset_to_position;
    update_option('wpfm-settings', $floating_menu_settings);
    wp_redirect(admin_url('admin.php?page=wpfm_menu_setting&message=1'));
    exit();
} else {
    die('No script kiddies please!');
} 