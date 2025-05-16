
<?php
/*
Plugin Name: Quản Lý Cửa Hàng Thực Phẩm
Description: Plugin quản lý cửa hàng thực phẩm cho WordPress.
Version: 1.0
Author: huy
*/

if (!defined('ABSPATH')) {
    exit;
}

define('QLCH_PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, 'qlch_activate_plugin');
register_deactivation_hook(__FILE__, 'qlch_deactivate_plugin');

function qlch_activate_plugin() {
    require_once QLCH_PLUGIN_DIR . 'includes/database.php';
    qlch_create_tables();
}

function qlch_deactivate_plugin() {
    // Uncomment to drop tables on deactivation
    // require_once QLCH_PLUGIN_DIR . 'includes/database.php';
    // qlch_drop_tables();
}

if (is_admin()) {
    require_once QLCH_PLUGIN_DIR . 'includes/admin-page.php';
    add_action('admin_menu', 'qlch_register_menu');
}

function qlch_register_menu() {
    add_menu_page(
        'Quản lý Cửa hàng',
        'Quản lý Cửa hàng',
        'manage_options',
        'quan-ly-cua-hang',
        'qlch_admin_page_content',
        'dashicons-store',
        20
    );

    add_submenu_page(
        'quan-ly-cua-hang',
        'Quản lý Sản phẩm',
        'Sản phẩm',
        'manage_options',
        'quan-ly-san-pham',
        'qlch_products_page'
    );

    add_submenu_page(
        'quan-ly-cua-hang',
        'Quản lý Đơn hàng',
        'Đơn hàng',
        'manage_options',
        'quan-ly-don-hang',
        'qlch_orders_page'
    );

    add_submenu_page(
        'quan-ly-cua-hang',
        'Thống kê Doanh thu',
        'Doanh thu',
        'manage_options',
        'thong-ke-doanh-thu',
        'qlch_revenue_page'
    );
}
