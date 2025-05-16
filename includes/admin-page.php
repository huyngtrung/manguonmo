<?php
function qlsv_admin_page_content()
{

    //echo là câu lệnh để in ra nội dung trên trình duyệt   
    //tránh bị tấn công Xss
    //tạo kết nối với 
    global $wpdb;
    $table_name = $wpdb->prefix . 'students'; //mặc định wp_

    // Hàm chuẩn hóa tên
    function format_full_name($name)
    {
        return ucwords(strtolower(trim($name))); //Viết hoa chữ cái đầu và bỏ khoảng trắng
    }

    // Hàm chuyển chữ thành số
    function convert_text_to_number($text)
    {
        $words = [
            'không' => 0,
            'một' => 1,
            'hai' => 2,
            'ba' => 3,
            'bốn' => 4,
            'năm' => 5,
            'sáu' => 6,
            'bảy' => 7,
            'tám' => 8,
            'chín' => 9,
            'mười' => 10,
            'mươi' => 10,
            'trăm' => 100,
            'nghìn' => 1000,
            'ngàn' => 1000
        ];

        $text = strtolower(trim($text));
        $text = str_replace(['  ', '.', ',', '-', '?'], ' ', $text);
        $parts = preg_split('/\s+/', $text); //tách từ

        $result = 0;
        $current = 0;

        foreach ($parts as $part) {
            if (is_numeric($part)) {
                $current = $current * 10 + (int)$part;
            } elseif (isset($words[$part])) {
                $value = $words[$part];

                if ($value === 10 && $part === 'mươi') {
                    if ($current === 0) $current = 1;
                    $current *= 10;
                } elseif ($value >= 100) {
                    if ($current === 0)
                        $current *= $value;
                } else {
                    $current += $value;
                }

                if ($value >= 1000) {
                    $result += $current;
                    $current = 0;
                }
            }
        }

        return $result + $current;
    }

    // Hàm kiểm tra ngày sinh hợp lệ
    function is_valid_date_of_birth($date_str)
    {
        //tách
        if (!$date_str) return false;
        $parts = explode('-', $date_str);
        if (count($parts) !== 3) return false;

        [$year, $month, $day] = $parts;
        $year = (int)$year;
        $month = (int)$month;
        $day = (int)$day;

        if ($year <= 0 || $month <= 0 || $day <= 0) return false;

        if (!checkdate($month, $day, $year)) return false;

        $current_year = (int)date('Y');
        return $year >= ($current_year - 80) && $year <= $current_year;
    }

    // Thông báo
    $message = '';
    $message_class = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        global $wpdb;
        $action = $_POST['action'];

        if ($action === 'delete') {
            // Chỉ xử lý xóa
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $deleted = $wpdb->delete($table_name, ['id' => $id]);
                if ($deleted !== false) {
                    $message = 'Xóa sinh viên thành công.';
                    $message_class = 'notice-success';
                } else {
                    $message = 'Không thể xóa sinh viên. Có thể ID không tồn tại.';
                    $message_class = 'notice-error';
                }
            } else {
                $message = 'ID không hợp lệ để xóa.';
                $message_class = 'notice-error';
            }
        } else {
            // Xử lý thêm/sửa
            $student_id_raw = sanitize_text_field($_POST['student_id'] ?? '');
            $full_name_raw = sanitize_text_field($_POST['full_name'] ?? '');
            $class_raw = sanitize_text_field($_POST['class'] ?? '');
            $hometown_raw = sanitize_text_field($_POST['hometown'] ?? '');
            $date_of_birth_raw = sanitize_text_field($_POST['date_of_birth'] ?? '');

            $student_id = intval(convert_text_to_number($student_id_raw));
            $full_name = format_full_name($full_name_raw);
            $class = $class_raw;
            $hometown = $hometown_raw;
            $date_of_birth = $date_of_birth_raw;

            if (empty($student_id) || empty($full_name) || empty($class) || empty($hometown) || empty($date_of_birth)) {
                $message = 'Vui lòng điền đầy đủ tất cả các trường.';
                $message_class = 'notice-error';
            } elseif (!is_valid_date_of_birth($date_of_birth)) {
                $message = 'Ngày sinh không hợp lệ. Năm không vượt quá hiện tại hoặc nhỏ hơn 80 năm trước.';
                $message_class = 'notice-error';
            } else {
                if ($action === 'add') {
                    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE student_id = %s", $student_id));
                    if (!$exists) {
                        $wpdb->insert($table_name, compact('student_id', 'full_name', 'class', 'hometown', 'date_of_birth'));
                        $message = 'Thêm sinh viên thành công.';
                        $message_class = 'notice-success';
                    } else {
                        $message = 'Mã sinh viên đã tồn tại!';
                        $message_class = 'notice-error';
                    }
                } elseif ($action === 'edit') {
                    $id = intval($_POST['id'] ?? 0);
                    if ($id > 0) {
                        $wpdb->update($table_name, compact('student_id', 'full_name', 'class', 'hometown', 'date_of_birth'), ['id' => $id]);
                        $message = 'Cập nhật thông tin thành công.';
                        $message_class = 'notice-success';
                    } else {
                        $message = 'ID không hợp lệ để cập nhật.';
                        $message_class = 'notice-error';
                    }
                }
            }
        }
    }


    // Lấy danh sách các giá trị duy nhất cho dropdown
    //SELECT DISTINCT lấy các giá trị khác nhau  sắp xếp theo A -> Z
    $classes = $wpdb->get_col("SELECT DISTINCT class FROM $table_name ORDER BY class ASC");
    $hometowns = $wpdb->get_col("SELECT DISTINCT hometown FROM $table_name ORDER BY hometown ASC");
    $years = $wpdb->get_col("SELECT DISTINCT YEAR(date_of_birth) as year FROM $table_name ORDER BY year DESC");


    // Truy vấn lọc
    $where = "1=1";
    if (!empty($_GET['search_student_id'])) {
        $search = addcslashes($_GET['search_student_id'], '_%');
        $where .= $wpdb->prepare(" AND student_id LIKE %s", '%' . $search . '%');
    }
    if (!empty($_GET['filter_class'])) {
        $where .= $wpdb->prepare(" AND class = %s", $_GET['filter_class']);
    }
    if (!empty($_GET['filter_hometown'])) {
        $where .= $wpdb->prepare(" AND hometown = %s", $_GET['filter_hometown']);
    }
    if (!empty($_GET['filter_year'])) {
        $where .= $wpdb->prepare(" AND YEAR(date_of_birth) = %d", intval($_GET['filter_year']));
    }


    $students = $wpdb->get_results("SELECT * FROM $table_name WHERE $where");
?>
    <div class="wrap">
        <h1>Quản lý Sinh Viên</h1>
        <?php if (!empty($message)): ?>
            <div class="notice <?php echo esc_attr($message_class); ?> is-dismissible">
                <p><?php echo esc_html($message); ?></p>
            </div>
        <?php endif; ?>

        <!-- Form thêm -->
        <form method="POST">
            <input type="hidden" name="action" value="add">
            <table class="form-table">
                <tr>
                    <th>Mã Sinh Viên</th>
                    <td><input type="text" name="student_id" required></td>
                </tr>
                <tr>
                    <th>Họ Tên</th>
                    <td><input type="text" name="full_name" required></td>
                </tr>
                <tr>
                    <th>Lớp</th>
                    <td><input type="text" name="class" required></td>
                </tr>
                <tr>
                    <th>Quê Quán</th>
                    <td><input type="text" name="hometown" required></td>
                </tr>
                <tr>
                    <th>Ngày Sinh</th>
                    <td><input type="date" name="date_of_birth" required></td>
                </tr>
            </table>
            <button type="submit" class="button button-primary">Thêm Sinh Viên</button>
        </form>

        <!-- Bộ lọc -->
        <form method="GET" style="margin-bottom: 15px;">
            <input type="hidden" name="page" value="quan-ly-sinh-vien">
            <label for="search_student_id"><strong>Tìm theo mã sinh viên:</strong></label>
            <input type="text" id="search_student_id" name="search_student_id" value="<?php echo esc_attr($_GET['search_student_id'] ?? ''); ?>" placeholder="Nhập mã sinh viên...">
            <button type="submit" class="button">Tìm kiếm</button>
        </form>

        <!-- Bộ lọc nâng cao -->
        <form method="GET" style="margin-bottom: 20px; display: flex; justify-content: flex-end; gap: 10px;">
            <input type="hidden" name="page" value="quan-ly-sinh-vien">

            <select name="filter_year">
                <option value="">-- Tất cả năm sinh --</option>
                <?php foreach ($years as $year): ?>
                    <option value="<?php echo esc_attr($year); ?>" <?php selected($_GET['filter_year'] ?? '', $year); ?>><?php echo esc_html($year); ?></option>
                <?php endforeach; ?>
            </select>

            <select name="filter_class">
                <option value="">-- Tất cả lớp --</option>
                <?php foreach ($classes as $class): ?>
                    <option value="<?php echo esc_attr($class); ?>" <?php selected($_GET['filter_class'] ?? '', $class); ?>><?php echo esc_html($class); ?></option>
                <?php endforeach; ?>
            </select>

            <select name="filter_hometown">
                <option value="">-- Tất cả quê quán --</option>
                <?php foreach ($hometowns as $ht): ?>
                    <option value="<?php echo esc_attr($ht); ?>" <?php selected($_GET['filter_hometown'] ?? '', $ht); ?>><?php echo esc_html($ht); ?></option>
                <?php endforeach; ?>
            </select>

            <button type="submit" class="button">Lọc</button>
        </form>

        <h2>Danh sách Sinh Viên</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mã Sinh Viên</th>
                    <th>Họ Tên</th>
                    <th>Lớp</th>
                    <th>Quê Quán</th>
                    <th>Ngày Sinh</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($students as $student): ?>
                    <tr>
                        <form method="POST">
                            <td><?php echo esc_html($student->id); ?><input type="hidden" name="id" value="<?php echo $student->id; ?>"></td>
                            <td><input type="text" name="student_id" value="<?php echo esc_attr($student->student_id); ?>"></td>
                            <td><input type="text" name="full_name" value="<?php echo esc_attr($student->full_name); ?>"></td>
                            <td><input type="text" name="class" value="<?php echo esc_attr($student->class); ?>"></td>
                            <td><input type="text" name="hometown" value="<?php echo esc_attr($student->hometown); ?>"></td>
                            <td><input type="date" name="date_of_birth" value="<?php echo esc_attr($student->date_of_birth); ?>"></td>
                            <td>
                                <input type="hidden" name="action" value="edit">
                                <button type="submit" class="button">Sửa</button>
                        </form>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $student->id; ?>">
                            <button type="submit" class="button button-link-delete">Xóa</button>
                        </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php
}
?>