<?php
function food_store_manage_products_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'store_products';

    $message = '';
    $message_class = '';

    // Xử lý thêm/sửa/xóa
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = sanitize_text_field($_POST['action']);
        $name = sanitize_text_field($_POST['name'] ?? '');
        $description = sanitize_textarea_field($_POST['description'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $stock_quantity = intval($_POST['stock_quantity'] ?? 0);
        $image_url = esc_url_raw($_POST['image_url'] ?? '');

        if ($action === 'delete') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $wpdb->delete($table_name, ['id' => $id]);
                $message = 'Đã xóa sản phẩm.';
                $message_class = 'notice-success';
            }
        } elseif ($action === 'add' || $action === 'edit') {
            if (empty($name) || empty($category) || !is_numeric($price) || $price <= 0 || !is_numeric($stock_quantity) || $stock_quantity < 0) {
                $message = 'Vui lòng nhập đầy đủ thông tin hợp lệ.';
                $message_class = 'notice-error';
            } else {
                $data = compact('name', 'description', 'price', 'stock_quantity', 'category', 'image_url');
                if ($action === 'add') {
                    $wpdb->insert($table_name, $data);
                    $message = 'Đã thêm sản phẩm.';
                    $message_class = 'notice-success';
                } elseif ($action === 'edit') {
                    $id = intval($_POST['id'] ?? 0);
                    if ($id > 0) {
                        $wpdb->update($table_name, $data, ['id' => $id]);
                        $message = 'Đã cập nhật sản phẩm.';
                        $message_class = 'notice-success';
                    }
                }
            }
        }
    }

    // Lấy các giá trị filter từ POST
    // 1. Lấy danh mục sản phẩm để render form lọc (phải làm trước khi render form)
    $categories = $wpdb->get_col("SELECT DISTINCT category FROM $table_name ORDER BY category");

    // 2. Lấy giá trị lọc từ $_POST, giữ nguyên dạng string để kiểm tra rỗng
    $search_name = isset($_POST['search_name']) ? sanitize_text_field($_POST['search_name']) : '';
    $filter_category = isset($_POST['filter_category']) ? sanitize_text_field($_POST['filter_category']) : '';
    $min_price_raw = isset($_POST['min_price']) ? trim($_POST['min_price']) : '';
    $max_price_raw = isset($_POST['max_price']) ? trim($_POST['max_price']) : '';
    $min_stock_raw = isset($_POST['min_stock']) ? trim($_POST['min_stock']) : '';

    // 3. Xây dựng điều kiện WHERE
    $where = "1=1";
    $params = [];

    if (!empty($search_name)) {
        $where .= " AND name LIKE %s";
        $params[] = '%' . $wpdb->esc_like($search_name) . '%';
    }
    if (!empty($filter_category)) {
        $where .= " AND category = %s";
        $params[] = $filter_category;
    }
    // Chỉ thêm điều kiện nếu người dùng nhập giá trị (không rỗng)
    if ($min_price_raw !== '') {
        $min_price = floatval($min_price_raw);
        $where .= " AND price >= %f";
        $params[] = $min_price;
    }
    if ($max_price_raw !== '') {
        $max_price = floatval($max_price_raw);
        $where .= " AND price <= %f";
        $params[] = $max_price;
    }
    if ($min_stock_raw !== '') {
        $min_stock = intval($min_stock_raw);
        $where .= " AND stock_quantity >= %d";
        $params[] = $min_stock;
    }

    // Tạo câu SQL với điều kiện đã build
    $sql = $wpdb->prepare("SELECT * FROM $table_name WHERE $where ORDER BY id DESC", ...$params);

    $products = $wpdb->get_results($sql);

    // 5. Bỏ lấy tổng số sản phẩm và số trang
    // $total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where");
    // $total_pages = ceil($total_items / $limit);

?>
    <div class="wrap qlch-products-page">
        <?php if ($message): ?>
            <div class="notice <?php echo esc_attr($message_class); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Form thêm sản phẩm -->
        <form method="POST" class="qlch-form qlch-add-form">
            <input type="hidden" name="action" value="add">
            <h2>Thêm sản phẩm</h2>
            <table class="form-table">
                <tr>
                    <th>Tên</th>
                    <td><input name="name" required></td>
                </tr>
                <tr>
                    <th>Mô tả</th>
                    <td><textarea name="description"></textarea></td>
                </tr>
                <tr>
                    <th>Danh mục</th>
                    <td><input name="category" required></td>
                </tr>
                <tr>
                    <th>Giá (VNĐ)</th>
                    <td><input name="price" type="number" step="0.01" required></td>
                </tr>
                <tr>
                    <th>Số lượng</th>
                    <td><input name="stock_quantity" type="number" required></td>
                </tr>
                <tr>
                    <th>URL ảnh</th>
                    <td><input name="image_url"></td>
                </tr>
            </table>
            <button class="button button-primary">Thêm</button>
        </form>

        <!-- Form bộ lọc -->
        <form method="POST" class="qlch-filter-form" style="margin: 20px 0;">
            <input type="text" name="search_name" placeholder="Tìm sản phẩm..." value="<?php echo esc_attr($search_name); ?>">
            <select name="filter_category">
                <option value="">-- Danh mục --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo esc_attr($cat); ?>" <?php selected($filter_category, $cat); ?>><?php echo esc_html($cat); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="number" name="min_price" placeholder="Giá từ" step="0.01" value="<?php echo esc_attr($min_price); ?>">
            <input type="number" name="max_price" placeholder="Đến" step="0.01" value="<?php echo esc_attr($max_price); ?>">
            <input type="number" name="min_stock" placeholder="Tồn kho tối thiểu" value="<?php echo esc_attr($min_stock); ?>">
            <button class="button">Lọc</button>
        </form>

        <!-- Danh sách sản phẩm -->
        <h2>Danh sách sản phẩm</h2>
        <table class="wp-list-table widefat striped qlch-products-table">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>ID</th>
                    <th>Tên</th>
                    <th>Mô tả</th>
                    <th>Danh mục</th>
                    <th>Giá</th>
                    <th>Tồn kho</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <form method="POST" class="qlch-edit-form">
                                <input type="hidden" name="id" value="<?php echo $p->id; ?>">
                                <input type="hidden" name="action" value="edit">
                                <td>
                                    <?php if ($p->image_url): ?>
                                        <img src="<?php echo esc_url($p->image_url); ?>" alt="Ảnh" style="max-width:50px; max-height:50px;">
                                    <?php endif; ?>
                                    <input class="qlch-input" name="image_url" value="<?php echo esc_url($p->image_url); ?>">
                                </td>
                                <td><?php echo $p->id; ?></td>
                                <td><input class="qlch-input" name="name" value="<?php echo esc_attr($p->name); ?>"></td>
                                <td><textarea class="qlch-textarea" name="description"><?php echo esc_textarea($p->description); ?></textarea></td>
                                <td><input class="qlch-input" name="category" value="<?php echo esc_attr($p->category); ?>"></td>
                                <td><input class="qlch-input" name="price" type="number" step="0.01" value="<?php echo $p->price; ?>"></td>
                                <td><input class="qlch-input" name="stock_quantity" type="number" value="<?php echo $p->stock_quantity; ?>"></td>
                                <td>
                                    <button class="button button-primary">Sửa</button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?');" style="display:inline;">
                                <input type="hidden" name="id" value="<?php echo $p->id; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button class="button button-danger">Xóa</button>
                            </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8">Không có sản phẩm nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
<?php
}


?>
<style>
    .qlch-products-page h2 {
        margin-top: 30px;
        font-size: 20px;
    }

    .qlch-form input,
    .qlch-form textarea,
    .qlch-form select {
        width: 100%;
        padding: 5px;
        margin: 3px 0 10px 0;
        box-sizing: border-box;
    }

    .qlch-add-form {
        background: #fff;
        padding: 15px;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        margin-bottom: 20px;
    }

    .qlch-products-table {
        table-layout: fixed;
        width: 100%;
    }

    .qlch-products-table th,
    .qlch-products-table td {
        vertical-align: top;
        text-align: left;
        padding: 8px;
    }

    /* Tỉ lệ các cột */
    .qlch-products-table th:nth-child(1),
    .qlch-products-table td:nth-child(1) {
        width: 15%;
    }

    .qlch-products-table th:nth-child(4),
    .qlch-products-table td:nth-child(4) {
        width: 25%;
    }

    .qlch-products-table th:nth-child(2),
    .qlch-products-table td:nth-child(2),
    .qlch-products-table th:nth-child(3),
    .qlch-products-table td:nth-child(3),
    .qlch-products-table th:nth-child(5),
    .qlch-products-table td:nth-child(5),
    .qlch-products-table th:nth-child(6),
    .qlch-products-table td:nth-child(6),
    .qlch-products-table th:nth-child(7),
    .qlch-products-table td:nth-child(7),
    .qlch-products-table th:nth-child(8),
    .qlch-products-table td:nth-child(8) {
        width: 10%;
    }

    .qlch-products-table img {
        max-width: 100%;
        height: auto;
        display: block;
        margin-bottom: 5px;
    }

    .qlch-img-cell input {
        width: 100%;
        box-sizing: border-box;
    }

    .qlch-filter-form input[type="text"],
    .qlch-filter-form select {
        padding: 5px;
        margin-right: 10px;
    }

    .qlch-filter-form button {
        vertical-align: middle;
    }

    /* INPUT/SELECT trong bảng chỉnh width fit theo cột */
    .qlch-edit-form input,
    .qlch-edit-form textarea {
        width: 100%;
        padding: 4px;
        margin: 0;
        box-sizing: border-box;
        font-size: 13px;
    }

    .qlch-edit-form textarea {
        resize: vertical;
        min-height: 60px;
    }

    .qlch-edit-form button {
        background-color: #0073aa;
        border: none;
        color: white;
        padding: 6px 12px;
        border-radius: 3px;
        cursor: pointer;
        width: 100%;
        box-sizing: border-box;
    }

    .qlch-edit-form button:hover {
        background-color: #006799;
    }

    .qlch-input,
    .qlch-textarea {
        width: 100%;
        box-sizing: border-box;
        padding: 4px;
        margin: 0;
        font-size: 13px;
        display: block;
    }

    .qlch-textarea {
        resize: vertical;
        min-height: 60px;
    }

    .qlch-products-table td {
        padding: 4px;
        vertical-align: top;
    }

    .qlch-edit-form button {
        width: 100%;
        box-sizing: border-box;
        padding: 6px 12px;
        font-size: 13px;
    }
</style>