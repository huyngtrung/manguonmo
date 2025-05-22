<?php
function qlch_dashboard_page()
{
    global $wpdb;

    $products_table = $wpdb->prefix . 'store_products';
    $users_table = $wpdb->prefix . 'store_users';
    $orders_table = $wpdb->prefix . 'store_orders';
    $items_table = $wpdb->prefix . 'store_order_items';

    // Tổng số sản phẩm, người dùng, đơn hàng
    $total_products = $wpdb->get_var("SELECT COUNT(*) FROM $products_table");
    $total_users = $wpdb->get_var("SELECT COUNT(*) FROM $users_table");
    $total_orders = $wpdb->get_var("SELECT COUNT(*) FROM $orders_table");

    // Doanh thu theo tháng
    $current_month = date('Y-m');
    $last_month = date('Y-m', strtotime('-1 month'));

    $current_revenue = $wpdb->get_var(
        $wpdb->prepare("SELECT SUM(total_amount) FROM $orders_table WHERE status = 'completed' AND DATE_FORMAT(created_at, '%%Y-%%m') = %s", $current_month)
    );

    $last_revenue = $wpdb->get_var(
        $wpdb->prepare("SELECT SUM(total_amount) FROM $orders_table WHERE status = 'completed' AND DATE_FORMAT(created_at, '%%Y-%%m') = %s", $last_month)
    );

    $current_revenue = $current_revenue ?: 0;
    $last_revenue = $last_revenue ?: 0;

    $growth = ($last_revenue > 0) ? round((($current_revenue - $last_revenue) / $last_revenue) * 100, 2) : 'N/A';

    // Sản phẩm bán chạy nhất tháng
    $best_seller = $wpdb->get_row(
        $wpdb->prepare("
            SELECT p.name, SUM(i.quantity) as total_sold
            FROM $items_table i
            JOIN $orders_table o ON i.order_id = o.id
            JOIN $products_table p ON i.product_id = p.id
            WHERE DATE_FORMAT(o.created_at, '%%Y-%%m') = %s AND o.status = 'completed'
            GROUP BY i.product_id
            ORDER BY total_sold DESC
            LIMIT 1
        ", $current_month)
    );

    echo '<div class="wrap">';
    echo '<h1>📊 Tổng quan cửa hàng</h1>';
    echo '<p>Thống kê dữ liệu tháng ' . date('m/Y') . '</p>';

    echo '<div style="display: flex; gap: 20px; margin-top: 20px;">';

    $cards = [
        ['title' => 'Sản phẩm', 'value' => $total_products, 'icon' => '📦'],
        ['title' => 'Người dùng', 'value' => $total_users, 'icon' => '👥'],
        ['title' => 'Đơn hàng', 'value' => $total_orders, 'icon' => '🧾'],
        ['title' => 'Doanh thu tháng này', 'value' => number_format($current_revenue, 0, ',', '.') . ' đ', 'icon' => '💰'],
        ['title' => 'Tăng trưởng so với tháng trước', 'value' => is_numeric($growth) ? $growth . '%' : 'Chưa có dữ liệu', 'icon' => '📈'],
    ];

    foreach ($cards as $card) {
        echo '<div style="flex: 1; background: #fff; padding: 20px; border: 1px solid #ddd; border-radius: 8px; box-shadow: 0 0 5px rgba(0,0,0,0.05);">';
        echo '<h2 style="font-size: 20px; margin-bottom: 10px;">' . $card['icon'] . ' ' . $card['title'] . '</h2>';
        echo '<p style="font-size: 28px; font-weight: bold; margin: 0;">' . $card['value'] . '</p>';
        echo '</div>';
    }

    echo '</div>'; // end cards

    if ($best_seller) {
        echo '<div style="margin-top: 30px; padding: 20px; background: #f9f9f9; border-left: 4px solid #28a745;">';
        echo '<h3>🔥 Sản phẩm bán chạy nhất tháng</h3>';
        echo '<p><strong>' . esc_html($best_seller->name) . '</strong> với <strong>' . $best_seller->total_sold . '</strong> lượt bán.</p>';
        echo '</div>';
    }

    echo '</div>';
}
