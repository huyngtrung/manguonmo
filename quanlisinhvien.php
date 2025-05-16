<?php
/*
Plugin Name: Quản Lý Sinh Viên
Description: Plugin quản lý sinh viên cho WordPress.
Version: 1.8
Author: huy
*/

if (!defined('ABSPATH')) {
    exit; //tránh việc truy cập trực tiếp từ ủl
}

define('QLSV_PLUGIN_DIR', plugin_dir_path(__FILE__)); //đường dẫn root

register_activation_hook(__FILE__, 'qlsv_activate_plugin');
register_deactivation_hook(__FILE__, 'qlsv_deactivate_plugin');

function qlsv_activate_plugin()
{
    require_once QLSV_PLUGIN_DIR . 'includes/database.php';
    qlsv_create_tables();
}

function qlsv_deactivate_plugin()
{
    // require_once QLSV_PLUGIN_DIR . 'includes/database.php';
    // qlsv_drop_tables();
}

//tạo action
if (is_admin()) {
    require_once QLSV_PLUGIN_DIR . 'includes/admin-page.php';
    add_action('admin_menu', 'qlsv_register_menu');
}

//tạo dashboard
function qlsv_register_menu()
{
    add_menu_page(
        'Quản lý sinh viên',
        'Quản lý sinh viên',
        'manage_options',
        'quan-ly-sinh-vien',
        'qlsv_admin_page_content',
        'dashicons-welcome-learn-more',
        20
    );
}
