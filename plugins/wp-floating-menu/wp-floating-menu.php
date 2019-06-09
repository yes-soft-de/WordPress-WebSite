<?php

defined('ABSPATH') or die("No script kiddies please!");

/**
  Plugin name: WP Floating Menu
  Plugin URI: https://accesspressthemes.com/wordpress-plugins/wp-floating-menu/
  Description: Plugin to enhance your website usability with easy one page navigator and sticky navigation menu.
  Version: 1.2.5
  Author: AccessPress Themes
  Author URI: http://accesspressthemes.com
  Text Domain: wp-floating-menu
  Domain Path: /languages/
 */
if (!defined('WPFM_VERSION')) {
    define('WPFM_VERSION', '1.2.5');
}

if (!defined('WPFM_IMAGE_DIR')) {
    define('WPFM_IMAGE_DIR', plugin_dir_url(__FILE__) . 'images/');
}

if (!defined('WPFM_JS_DIR')) {
    define('WPFM_JS_DIR', plugin_dir_url(__FILE__) . 'js/');
}

if (!defined('WPFM_CSS_DIR')) {
    define('WPFM_CSS_DIR', plugin_dir_url(__FILE__) . 'css/');
}

if (!defined('WPFM_TEXT_DOMAIN')) {
    define('WPFM_TEXT_DOMAIN', 'wp-floating-menu');
}

if (!defined('WPFM_LANG_DIR')) {
    define('WPFM_LANG_DIR', basename(dirname(__FILE__)) . '/languages/');
}

if (!defined('WPFM_SETTINGS')) {
    define('WPFM_SETTINGS', 'wpfm-settings');
}
if (!defined('WPFM_FILE_ROOT_DIR')) {
    define('WPFM_FILE_ROOT_DIR', plugin_dir_path(__FILE__));
}

/** Declaring Class for Plugin */
if (!class_exists('WPFM_FREE')) {

    class WPFM_FREE {

        var $wpfm_settings;

        function __construct() {
            $this->wpfm_settings = get_option(WPFM_SETTINGS);
            add_action('init', array($this, 'wpfm_plugin_text_domain'));
            register_activation_hook(__FILE__, array($this, 'wpfm_plugin_activation'));
            add_action('admin_menu', array($this, 'wpfm_add_plugin_menu'));
            add_action('admin_enqueue_scripts', array($this, 'wpfm_register_admin_assets'));
            add_action('wp_enqueue_scripts', array($this, 'wpfm_register_frontend_assets'));
            add_action('wp_ajax_wpfm_pull_data_contents', array($this, 'wpfm_pull_data_contents'));
            add_action('admin_post_wpfm_add_menu_field_options', array($this, 'wpfm_add_menu_field_options'));
            add_action('admin_post_wpfm_save_menu_options', array($this, 'wpfm_save_menu_options'));
            add_action('admin_post_wpfm_save_menu_field_options', array($this, 'wpfm_save_menu_options'));
            add_action('admin_post_wpfm_menu_delete_options', array($this, 'wpfm_menu_delete_options'));
            add_action('admin_post_wpfm_menu_delete_template_options', array($this, 'wpfm_menu_delete_template_options'));
            add_action('admin_post_wpfm_save_menu_show_options', array($this, 'wpfm_save_menu_show_options'));
            add_action('wp_footer', array($this, 'wpfm_menu_call_frontend'));
            add_action('admin_post_wpfm_save_template_options', array($this, 'wpfm_save_template_options'));
            add_action('admin_post_wpfm_save_edited_template_options', array($this, 'wpfm_save_template_options'));
            add_action('wp_print_styles', array($this, 'wpfm_load_fonts'));
        }

        /**  Function to load plugin text domain for translation */
        function wpfm_plugin_text_domain() {
            load_plugin_textdomain('wp-floating-menu', false, WPFM_LANG_DIR);
        }

        /** Implement default setting on plugin activation */
        function wpfm_plugin_activation() {
            include( 'inc/backend/includes/activate.php' );
        }

        /** Create Menu on activating New Plugin */
        function wpfm_add_plugin_menu() {
            add_menu_page('WP Floating Menu', 'WP Floating Menu', 'manage_options', 'wpfm-admin', array($this, 'wpfm_main_page'), 'dashicons-admin-page');
            add_submenu_page('wpfm-admin', 'Floating Menu', 'Floating Menu', 'manage_options', 'wpfm-admin', array($this, 'wpfm_main_page'));
            add_submenu_page('wpfm-admin', 'Menu setting', 'Menu setting', 'manage_options', 'wpfm_menu_setting', array($this, 'wpfm_menu_setting'));
            add_submenu_page('wpfm-admin', 'Custom Template', 'Custom Template', 'manage_options', 'wpfm-custom-template', array($this, 'wpfm_custom_template'));
            add_submenu_page('wpfm-admin', 'How To Use', 'How To Use', 'manage_options', 'how-to-use', array($this, 'wpfm_how_to_use'));
            add_submenu_page('wpfm-admin', 'More WordPress Stuffs', 'More WordPress Stuffs', 'manage_options', 'wpfm-about', array($this, 'wpfm_about'));
        }

        /** Main Menu setting */
        function wpfm_main_page() {
            include( 'inc/backend/setting-tabs/menu-listing.php' );
        }

        /** Edit Menu */
        function wpfm_custom_template() {
            include( 'inc/backend/setting-tabs/template-listing.php' );
        }

        /** Menu Setting */
        function wpfm_menu_setting() {
            include( 'inc/backend/setting-tabs/menu-options.php' );
        }

        /** How To use */
        function wpfm_how_to_use() {
            include( 'inc/backend/setting-tabs/how-to-use.php' );
        }

        /** About */
        function wpfm_about() {
            include( 'inc/backend/setting-tabs/about.php' );
        }

        /** Register Backend Assets */
        function wpfm_register_admin_assets() {
            $wpfm_admin_ajax_nonce = wp_create_nonce('wpfm-admin-ajax-nonce');
            $wpfm_admin_ajax_object = array('ajax_url' => admin_url('admin-ajax.php'), 'ajax_nonce' => $wpfm_admin_ajax_nonce);
            wp_enqueue_script('wpfm-admin-js', WPFM_JS_DIR . 'backend.js', array('jquery', 'jquery-ui-sortable', 'wp-color-picker'), WPFM_VERSION);
            wp_localize_script('wpfm-admin-js', 'wpfm_backend_js_params', $wpfm_admin_ajax_object);
            wp_enqueue_style('wpfm-backend-css', WPFM_CSS_DIR . 'back-end.css', WPFM_VERSION);
            if (isset($_GET['page']) && $_GET['page'] == 'wpfm-admin' || isset($_GET['page']) && $_GET['page'] == 'wpfm-edit-menu' || isset($_GET['page']) && $_GET['page'] == 'wpfm-add-menu' || isset($_GET['page']) && $_GET['page'] == 'wpfm-custom-template') {
                wp_enqueue_script('wpfm-icon-picker-js', WPFM_JS_DIR . 'icon-picker.js', array('wpfm-admin-js'), WPFM_VERSION);
                wp_enqueue_style('wpfm-icon-picker-genericons-css', WPFM_CSS_DIR . 'genericons.css', WPFM_VERSION);
                wp_enqueue_style('wpfm-icon-picker-css', WPFM_CSS_DIR . 'icon-picker.css', WPFM_VERSION);
            }
            wp_enqueue_script('wpfm-webfont', '//ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js');
            wp_enqueue_style('wpfm-fontawesome', WPFM_CSS_DIR . 'font-awesome/font-awesome.min.css', WPFM_VERSION);
            wp_enqueue_style('wp-color-picker');
        }

        /** Register Front-end assets */
        function wpfm_register_frontend_assets() {
            wp_enqueue_script('wpfm-frontend-js', WPFM_JS_DIR . 'frontend.js', array('jquery'), WPFM_VERSION);
            wp_enqueue_style('wpfm-frontend-font-awesome', WPFM_CSS_DIR . 'font-awesome/font-awesome.min.css', WPFM_VERSION);
            wp_enqueue_style('wpfm-frontend-genericons-css', WPFM_CSS_DIR . 'genericons.css', WPFM_VERSION);
            wp_enqueue_style('wpfm-frontend-css', WPFM_CSS_DIR . 'front-end.css', WPFM_VERSION);
            wp_enqueue_style('dashicons');
            wp_enqueue_style('wpfm-frontend-vesper-icons-css', WPFM_CSS_DIR . 'vesper-icons.css', WPFM_VERSION);
        }

        /** Register font for front-end */
        function wpfm_load_fonts() {
            wp_register_style('wpfm-google-fonts', '//fonts.googleapis.com/css?family=Roboto:100italic,100,300italic,300,400italic,400,500italic,500,700italic,700,900italic,900');
            wp_enqueue_style('wpfm-google-fonts');
        }

        /** Pull Custom link append fields to append in Menu structure tab */
        function wpfm_pull_data_contents() {
            if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'wpfm-admin-ajax-nonce') && current_user_can('manage_options')) {
                $page_id = sanitize_text_field($_POST['page_value']);
                $field_data = sanitize_text_field($_POST['field_data']);
                $submit_field_data = sanitize_text_field($_POST['submit_field_data']);
                $custom_link_name = sanitize_text_field($_POST['custom_link_name']);
                $custom_link_url = sanitize_text_field($_POST['custom_link_url']);
                $custom_link_field_data = sanitize_text_field($_POST['custom_link_field_data']);
                include( 'inc/backend/menu-actions/menu-detail-filter.php' );
                die();
            }
        }

        /** Add new Menu */
        function wpfm_add_menu_field_options() {
            if (current_user_can('manage_options')) {
                if (isset($_POST['wpfm_add_nonce_add_menu_fields']) && isset($_POST['wpfm_add_menu_fields']) && wp_verify_nonce($_POST['wpfm_add_nonce_add_menu_fields'], 'wpfm_nonce_add_menu_fields') && current_user_can('manage_options')) {
                    include( 'inc/backend/menu-actions/create-new-menu-action.php' );
                }
            } else {
                die('No script kiddies please!');
            }
        }

        /** Function to save Menu */
        function wpfm_save_menu_options() {
            if (current_user_can('manage_options')) {
                if (isset($_POST['wpfm_add_nonce_save_menu_settings']) && isset($_POST['wpfm_save_menu_settings']) && wp_verify_nonce($_POST['wpfm_add_nonce_save_menu_settings'], 'wpfm_nonce_save_menu_settings') || (isset($_POST['wpfm_add_nonce_save_menu_fields']) && isset($_POST['wpfm_save_menu_fields']) && wp_verify_nonce($_POST['wpfm_add_nonce_save_menu_fields'], 'wpfm_nonce_save_menu_fields'))) {
                    include( 'inc/backend/menu-actions/save-menu-action.php' );
                    die();
                }
            } else {
                die('No script kiddies please!');
            }
        }

        /** Function to Delete Menu */
        function wpfm_menu_delete_options() {
            if (current_user_can('manage_options')) {
                $wpfm_delete_nonce = $_REQUEST['_wpnonce'];
                if (!empty($_GET) && wp_verify_nonce($wpfm_delete_nonce, 'wpfm-remove-menu-settings-nonce')) {
                    include( 'inc/backend/menu-actions/delete-menu-action.php' );
                    wp_redirect(admin_url() . 'admin.php?page=wpfm-admin&message=3');
                    exit;
                } else {
                    die('No script kiddies please!');
                }
            } else {
                die('No script kiddies please!');
            }
        }

        /** Function to save menu setting values into option table */
        function wpfm_save_menu_show_options() {
            if (isset($_POST['wpfm_add_nonce_save_menu_show_settings']) && isset($_POST['wpfm_save_menu_show_settings']) && wp_verify_nonce($_POST['wpfm_add_nonce_save_menu_show_settings'], 'wpfm_nonce_save_menu_show_settings') && current_user_can('manage_options')) {
                include( 'inc/backend/save-setting.php' );
            } else {
                die('No script kiddies please!');
            }
        }

        /** Function to call menu into front-end */
        function wpfm_menu_call_frontend() {
            include( 'inc/frontend/front-end.php' );
        }

        /** Get Menu Content Data From Table. */
        function get_menu_data($id) {
            global $wpdb;
            $table_name = $wpdb->prefix . "wp_floating_menu_details";
            if (intval($id)) {
                $menu_content = $wpdb->get_results("SELECT * FROM $table_name where id = $id");
            } else {
                $menu_content = $wpdb->get_results("SELECT * FROM $table_name");
            }
            return $menu_content;
        }

        /** Get Template Content Data From Table. */
        function get_template_data($id) {
            global $wpdb;
            $table_name = $wpdb->prefix . "wp_floating_menu_custom_templates";
            if (intval($id)) {
                $template_content = $wpdb->get_results("SELECT * FROM $table_name where id = $id");
            } else {
                $template_content = $wpdb->get_results("SELECT * FROM $table_name");
            }
            return $template_content;
        }

        /** Save Template Value * */
        function wpfm_save_template_options() {
            if (current_user_can('manage_options')) {
                if (isset($_POST['wpfm_add_nonce_save_template_settings']) && isset($_POST['wpfm_save_template_settings']) && wp_verify_nonce($_POST['wpfm_add_nonce_save_template_settings'], 'wpfm_nonce_save_template_settings') || isset($_POST['wpfm_add_nonce_save_edited_template_settings']) && isset($_POST['wpfm_save_edited_template_settings']) && wp_verify_nonce($_POST['wpfm_add_nonce_save_edited_template_settings'], 'wpfm_nonce_save_edited_template_settings')) {
                    include( 'inc/backend/template-actions/save-template-action.php' );
                }
            } else {
                die('No script kiddies please!');
            }
        }

        /** Function to Delete Template */
        function wpfm_menu_delete_template_options() {
            $wpfm_delete_template_nonce = $_REQUEST['_wpnonce'];
            if (!empty($_GET) && wp_verify_nonce($wpfm_delete_template_nonce, 'wpfm-remove-template-settings-nonce') && current_user_can('manage_options')) {
                include( 'inc/backend/template-actions/delete-template-action.php' );
                wp_redirect(admin_url() . 'admin.php?page=wpfm-custom-template&message=3');
                exit;
            } else {
                die('No script kiddies please!');
            }
        }

    }

    $wpfm_object = new WPFM_FREE();
}