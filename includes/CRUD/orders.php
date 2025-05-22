<?php
function food_store_manage_orders_page()
{
    global $wpdb;
    $table_orders = $wpdb->prefix . 'store_orders';
    $table_order_items = $wpdb->prefix . 'store_order_items';
    $table_users = $wpdb->prefix . 'store_users';
    $table_products = $wpdb->prefix . 'store_products';

    // Xử lý cập nhật trạng thái
    if (isset($_POST['update_status'])) {
        $order_id = intval($_POST['order_id']);
        $new_status = sanitize_text_field($_POST['status']);
        $wpdb->update($table_orders, ['status' => $new_status], ['id' => $order_id]);
        echo '<div class="updated"><p>Đã cập nhật trạng thái đơn hàng #' . esc_html($order_id) . '.</p></div>';
    }

    // Xử lý xóa đơn hàng
    if (isset($_GET['delete_order_id'])) {
        $order_id = intval($_GET['delete_order_id']);
        $wpdb->delete($table_order_items, ['order_id' => $order_id]);
        $wpdb->delete($table_orders, ['id' => $order_id]);
        echo '<div class="updated"><p>Đã xóa đơn hàng #' . esc_html($order_id) . '.</p></div>';
    }

    // Hiển thị chi tiết đơn hàng nếu có ID
    if (isset($_GET['view_order_id'])) {
        $order_id = intval($_GET['view_order_id']);
        $order = $wpdb->get_row($wpdb->prepare("
            SELECT o.*, u.full_name 
            FROM $table_orders o
            JOIN $table_users u ON o.user_id = u.id
            WHERE o.id = %d
        ", $order_id));
        if ($order) {
            $items = $wpdb->get_results($wpdb->prepare("
                SELECT i.*, p.name 
                FROM $table_order_items i
                JOIN $table_products p ON i.product_id = p.id
                WHERE i.order_id = %d
            ", $order_id));

            echo "<h2>Chi tiết đơn hàng #{$order->id}</h2>";
            echo "<p><strong>Khách hàng:</strong> " . esc_html($order->full_name) . "</p>";
            echo "<p><strong>Trạng thái:</strong> " . esc_html($order->status) . "</p>";
            echo "<p><strong>Phương thức thanh toán:</strong> " . esc_html($order->payment_method) . "</p>";
            echo "<p><strong>Địa chỉ giao hàng:</strong> " . esc_html($order->shipping_address) . "</p>";
            echo "<p><strong>Tổng tiền:</strong> " . number_format($order->total_amount, 0, ',', '.') . "đ</p>";

            echo "<h3>Sản phẩm</h3>";
            echo "<table class='qlch-table'><thead><tr><th>Tên</th><th>Số lượng</th><th>Đơn giá</th><th>Thành tiền</th></tr></thead><tbody>";
            foreach ($items as $item) {
                $subtotal = $item->quantity * $item->price_at_time;
                echo "<tr><td>" . esc_html($item->name) . "</td><td>" . esc_html($item->quantity) . "</td><td>" . number_format($item->price_at_time, 0, ',', '.') . "đ</td><td>" . number_format($subtotal, 0, ',', '.') . "đ</td></tr>";
            }
            echo "</tbody></table>";

            echo "<p><a class='button' href='" . admin_url('admin.php?page=qlch-orders') . "'>Quay lại danh sách đơn hàng</a></p>";
        } else {
            echo '<div class="error"><p>Không tìm thấy đơn hàng.</p></div>';
            echo "<p><a class='button' href='" . admin_url('admin.php?page=qlch-orders') . "'>Quay lại danh sách đơn hàng</a></p>";
        }
        return;
    }

    // Lấy dữ liệu lọc từ GET
    $search_keyword = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $filter_status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $date_from = isset($_GET['date_from']) ? sanitize_text_field($_GET['date_from']) : '';
    $date_to = isset($_GET['date_to']) ? sanitize_text_field($_GET['date_to']) : '';

    // Xây dựng điều kiện WHERE
    $where = "WHERE 1=1";
    $params = [];

    if (!empty($search_keyword)) {
        $where .= " AND u.full_name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($search_keyword) . '%';
    }
    if (!empty($filter_status)) {
        $where .= " AND o.status = %s";
        $params[] = $filter_status;
    }
    if (!empty($date_from)) {
        $where .= " AND o.created_at >= %s";
        $params[] = $date_from . ' 00:00:00';
    }
    if (!empty($date_to)) {
        $where .= " AND o.created_at <= %s";
        $params[] = $date_to . ' 23:59:59';
    }

    // Chuẩn bị truy vấn với tham số
    $query = "
        SELECT o.*, u.full_name 
        FROM $table_orders o
        JOIN $table_users u ON o.user_id = u.id
        $where
        ORDER BY o.created_at DESC
    ";

    // Thực hiện truy vấn an toàn với $wpdb->prepare
    if (!empty($params)) {
        $orders = $wpdb->get_results($wpdb->prepare($query, ...$params));
    } else {
        // Nếu không có tham số thì query trực tiếp
        $orders = $wpdb->get_results($query);
    }

    $statuses = ['pending', 'processing', 'completed', 'failed', 'cancelled'];
?>
    <div class="wrap">
        <h2>Quản lý Đơn hàng</h2>
        <form method="get" style="margin-bottom: 20px;">
            <input type="hidden" name="page" value="qlch-orders" />

            <input type="text" name="search" placeholder="Tìm theo tên khách hàng..." value="<?= esc_attr($search_keyword) ?>" />

            <select name="status">
                <option value="">-- Lọc trạng thái --</option>
                <?php foreach ($statuses as $s): ?>
                    <option value="<?= esc_attr($s) ?>" <?= $filter_status === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="date_from">Từ:</label>
            <input type="date" name="date_from" value="<?= esc_attr($date_from) ?>" />

            <label for="date_to">Đến:</label>
            <input type="date" name="date_to" value="<?= esc_attr($date_to) ?>" />

            <button type="submit" class="button">Tìm kiếm</button>
        </form>

        <table class="qlch-table wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Khách hàng</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders): ?>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= esc_html($order->id) ?></td>
                            <td><?= esc_html($order->full_name) ?></td>
                            <td><?= number_format($order->total_amount, 0, ',', '.') ?>đ</td>
                            <td>
                                <form method="post" style="display: flex; gap: 5px; align-items: center;">
                                    <input type="hidden" name="order_id" value="<?= esc_attr($order->id) ?>">
                                    <select name="status">
                                        <?php foreach ($statuses as $status): ?>
                                            <option value="<?= esc_attr($status) ?>" <?= $order->status === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" name="update_status" class="button small">Cập nhật</button>
                                </form>
                            </td>
                            <td><?= esc_html($order->created_at) ?></td>
                            <td>
                                <a href="<?= esc_url(admin_url('admin.php?page=qlch-orders&view_order_id=' . $order->id)) ?>" class="button">Chi tiết</a>
                                <a href="<?= esc_url(admin_url('admin.php?page=qlch-orders&delete_order_id=' . $order->id)) ?>" class="button delete-order" onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?')">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">Không tìm thấy đơn hàng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
}
