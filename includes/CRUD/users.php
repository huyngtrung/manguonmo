<?php
if (!defined('ABSPATH')) {
    exit;
}

function food_store_manage_users_page()
{
    global $wpdb;
    $table = $wpdb->prefix . 'store_users';

    $message = '';
    $message_class = '';

    // Xử lý POST thêm/sửa/xóa
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = sanitize_text_field($_POST['action']);
        $user_id = intval($_POST['user_id'] ?? 0);
        $username = sanitize_text_field($_POST['username'] ?? '');
        $full_name = sanitize_text_field($_POST['full_name'] ?? '');
        $email = sanitize_email($_POST['email'] ?? '');
        $password_raw = $_POST['password'] ?? '';
        $role = sanitize_text_field($_POST['role'] ?? '');
        $phone = sanitize_text_field($_POST['phone'] ?? '');
        $address = sanitize_text_field($_POST['address'] ?? '');

        if ($action === 'delete') {
            if ($user_id > 0) {
                $deleted = $wpdb->delete($table, ['id' => $user_id]);
                if ($deleted !== false) {
                    $message = 'Đã xóa người dùng.';
                    $message_class = 'notice-success';
                } else {
                    $message = 'Xóa người dùng thất bại.';
                    $message_class = 'notice-error';
                }
            }
        } elseif ($action === 'add' || $action === 'edit') {
            if (empty($username) || empty($full_name) || empty($email) || empty($role) || ($action === 'add' && empty($password_raw))) {
                $message = 'Vui lòng nhập đầy đủ thông tin bắt buộc.';
                $message_class = 'notice-error';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = 'Email không hợp lệ.';
                $message_class = 'notice-error';
            } elseif (!empty($password_raw) && (!preg_match('/^(?=.*[\W_]).{6,}$/', $password_raw))) {
                $message = 'Mật khẩu phải có ít nhất 6 ký tự và ít nhất 1 ký tự đặc biệt.';
                $message_class = 'notice-error';
            } elseif (!empty($phone) && (!preg_match('/^\d{10,}$/', $phone))) {
                $message = 'Số điện thoại phải là số và chứa ít nhất 10 chữ số.';
                $message_class = 'notice-error';
            } else {
                $data = [
                    'username' => $username,
                    'full_name' => $full_name,
                    'email' => $email,
                    'role' => $role,
                    'phone' => $phone,
                    'address' => $address
                ];

                if ($action === 'add') {
                    $data['password'] = password_hash($password_raw, PASSWORD_DEFAULT);
                    $inserted = $wpdb->insert($table, $data);
                    if ($inserted) {
                        $message = 'Đã thêm người dùng mới.';
                        $message_class = 'notice-success';
                    } else {
                        $message = 'Thêm người dùng thất bại.';
                        $message_class = 'notice-error';
                    }
                } elseif ($action === 'edit') {
                    if ($user_id > 0) {
                        if (!empty($password_raw)) {
                            $data['password'] = password_hash($password_raw, PASSWORD_DEFAULT);
                        }
                        $updated = $wpdb->update($table, $data, ['id' => $user_id]);
                        if ($updated !== false) {
                            $message = 'Đã cập nhật người dùng.';
                            $message_class = 'notice-success';
                        } else {
                            $message = 'Cập nhật người dùng thất bại hoặc không có thay đổi.';
                            $message_class = 'notice-error';
                        }
                    }
                }
            }
        }
    }

    // LẤY GIÁ TRỊ SEARCH VÀ FILTER TỪ POST (thay vì GET)
    $search = isset($_POST['search_user']) ? sanitize_text_field($_POST['search_user']) : '';
    $filter_role = isset($_POST['filter_role']) ? sanitize_text_field($_POST['filter_role']) : '';

    // LẤY TOÀN BỘ NGƯỜI DÙNG (không lọc trong SQL nữa)
    $all_users = $wpdb->get_results("SELECT * FROM $table ORDER BY id DESC");

    // Lọc mảng người dùng trong PHP theo search và filter_role
    $users = array_filter($all_users, function ($user) use ($search, $filter_role) {
        $matches_search = true;
        if (!empty($search)) {
            $search_lower = mb_strtolower($search);
            $matches_search =
                (mb_stripos($user->username, $search_lower) !== false) ||
                (mb_stripos($user->full_name, $search_lower) !== false) ||
                (mb_stripos($user->email, $search_lower) !== false);
        }

        $matches_role = true;
        if (!empty($filter_role)) {
            $matches_role = ($user->role === $filter_role);
        }

        return $matches_search && $matches_role;
    });

?>

    <div class="wrap">
        <h1>Quản lý Người dùng</h1>

        <?php if ($message): ?>
            <div class="notice <?php echo esc_attr($message_class); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="qlch-form qlch-add-form" style="margin-bottom:30px;">
            <input type="hidden" name="action" value="add">
            <h2>Thêm người dùng</h2>
            <table class="form-table">
                <tr>
                    <th>Username</th>
                    <td><input name="username" required></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><input name="email" type="email" required></td>
                </tr>
                <tr>
                    <th>Họ và tên</th>
                    <td><input name="full_name" required></td>
                </tr>
                <tr>
                    <th>Vai trò</th>
                    <td>
                        <select name="role" required>
                            <option value="">-- Chọn vai trò --</option>
                            <option value="admin">Admin</option>
                            <option value="staff">Staff</option>
                            <option value="customer">Customer</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>Số điện thoại</th>
                    <td><input name="phone"></td>
                </tr>
                <tr>
                    <th>Địa chỉ</th>
                    <td><input name="address"></td>
                </tr>
                <tr>
                    <th>Mật khẩu</th>
                    <td><input name="password" type="password" required></td>
                </tr>
            </table>
            <button class="button button-primary">Thêm</button>
        </form>

        <form method="POST" style="margin-bottom: 20px;">
            <input type="text" name="search_user" placeholder="Tìm kiếm theo username, họ tên, email" value="<?php echo esc_attr($search); ?>" style="width: 250px;">
            <select name="filter_role">
                <option value="">-- Lọc theo vai trò --</option>
                <option value="admin" <?php selected($filter_role, 'admin'); ?>>Admin</option>
                <option value="staff" <?php selected($filter_role, 'staff'); ?>>Staff</option>
                <option value="customer" <?php selected($filter_role, 'customer'); ?>>Customer</option>
            </select>
            <button class="button">Lọc</button>
        </form>


        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Họ tên</th>
                    <th>Email</th>
                    <th>Vai trò</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Password</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <form method="POST" onsubmit="return confirm('Lưu thay đổi?');">
                                <input type="hidden" name="action" value="edit">
                                <input type="hidden" name="user_id" value="<?php echo esc_attr($user->id); ?>">
                                <td><?php echo esc_html($user->id); ?></td>
                                <td><input name="username" value="<?php echo esc_attr($user->username); ?>" required></td>
                                <td><input name="full_name" value="<?php echo esc_attr($user->full_name); ?>" required></td>
                                <td><input type="email" name="email" value="<?php echo esc_attr($user->email); ?>" required></td>
                                <td>
                                    <select name="role" required>
                                        <option value="admin" <?php selected($user->role, 'admin'); ?>>Admin</option>
                                        <option value="staff" <?php selected($user->role, 'staff'); ?>>Staff</option>
                                        <option value="customer" <?php selected($user->role, 'customer'); ?>>Customer</option>
                                    </select>
                                </td>
                                <td><input name="phone" value="<?php echo esc_attr($user->phone); ?>"></td>
                                <td><input name="address" value="<?php echo esc_attr($user->address); ?>"></td>
                                <td><input name="password" type="password" placeholder="Để trống nếu không đổi"></td>
                                <td>
                                    <button class="button button-primary" type="submit">Lưu</button>
                            </form>
                            <form method="POST" onsubmit="return confirm('Xóa người dùng này?');" style="display:inline-block;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?php echo esc_attr($user->id); ?>">
                                <button class="button button-danger" type="submit">Xóa</button>
                            </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9">Không có người dùng nào.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

<?php
}


?>
<style>
    .qlch-user-table th:nth-child(1),
    .qlch-user-table td:nth-child(1) {
        width: 5%;
        text-align: center;
    }

    .qlch-user-table th:nth-child(2),
    .qlch-user-table td:nth-child(2),
    .qlch-user-table th:nth-child(3),
    .qlch-user-table td:nth-child(3) {
        width: 12%;
    }

    .qlch-user-table th:nth-child(9),
    .qlch-user-table td:nth-child(9) {
        width: 8%;
        text-align: center;
    }

    .qlch-user-table th,
    .qlch-user-table td {
        vertical-align: middle;
    }

    .qlch-user-table input[type="text"],
    .qlch-user-table input[type="email"],
    .qlch-user-table select {
        width: 100%;
        box-sizing: border-box;
    }

    .qlch-user-table code {
        word-break: break-word;
        display: block;
        font-size: 10px;
        color: #555;
        background: #f9f9f9;
        padding: 2px 4px;
        border-radius: 3px;
    }
</style>