<?php
/*
Plugin Name: Quản Lý Cửa Hàng Thực Phẩm
Description: Plugin quản lý cửa hàng thực phẩm cho WordPress
Version: 1.2.0
Author: Huy
*/

if (!defined('ABSPATH')) exit;

define('QLCH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('QLCH_PLUGIN_URL', plugin_dir_url(__FILE__)); // Định nghĩa URL plugin để gọi assets chính xác

// Hook kích hoạt
register_activation_hook(__FILE__, 'qlch_activate_plugin');

// Hook hủy kích hoạt
register_deactivation_hook(__FILE__, 'qlch_deactivate_plugin');

function qlch_activate_plugin()
{
    require_once QLCH_PLUGIN_DIR . 'includes/database.php';
    qlch_create_tables();
    update_option('qlch_needs_sample_data', true);
}

function qlch_deactivate_plugin()
{
    // Nếu cần xóa bảng khi deactivate, mở 2 dòng này:
    // require_once QLCH_PLUGIN_DIR . 'includes/database.php';
    // qlch_drop_tables();
}

add_action('admin_init', 'qlch_maybe_insert_sample_data');
function qlch_maybe_insert_sample_data()
{
    if (get_option('qlch_needs_sample_data')) {
        require_once QLCH_PLUGIN_DIR . 'includes/sample-data.php';
        qlch_insert_sample_data();
        delete_option('qlch_needs_sample_data');
    }
}

// Tải file giao diện admin nếu là trang admin
if (is_admin()) {
    require_once QLCH_PLUGIN_DIR . 'includes/functions.php';
    require_once QLCH_PLUGIN_DIR . 'includes/CRUD/dashboard.php';
    require_once QLCH_PLUGIN_DIR . 'includes/CRUD/products.php';
    require_once QLCH_PLUGIN_DIR . 'includes/CRUD/users.php';
    require_once QLCH_PLUGIN_DIR . 'includes/CRUD/orders.php';


    add_action('admin_menu', 'qlch_register_menu');
    add_action('admin_enqueue_scripts', 'qlch_enqueue_admin_styles');
}
add_action('admin_enqueue_scripts', 'qlch_enqueue_admin_styles');

function qlch_register_menu()
{
    add_menu_page(
        'Quản lý Cửa hàng',
        'Quản lý Cửa hàng',
        'manage_options',
        'qlch-dashboard',
        'qlch_dashboard_page',
        'dashicons-store',
        20
    );

    add_submenu_page(
        'qlch-dashboard',
        'Quản lý Sản phẩm',
        'Sản phẩm',
        'manage_options',
        'qlch-products',
        'food_store_manage_products_page'
    );

    add_submenu_page(
        'qlch-dashboard',
        'Quản lý Người dùng',
        'Người dùng',
        'manage_options',
        'qlch-users',
        'food_store_manage_users_page'
    );

    add_submenu_page(
        'qlch-dashboard',
        'Quản lý Đơn hàng',
        'Đơn hàng',
        'manage_options',
        'qlch-orders',
        'food_store_manage_orders_page'
    );
}


function qlch_enqueue_admin_styles()
{
    wp_enqueue_style('qlch-admin-style', QLCH_PLUGIN_URL . 'assets/style.css');
}
