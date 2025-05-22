<?php
function qlch_insert_sample_data()
{
    global $wpdb;

    $users_table = $wpdb->prefix . 'store_users';
    $products_table = $wpdb->prefix . 'store_products';
    $orders_table = $wpdb->prefix . 'store_orders';
    $items_table = $wpdb->prefix . 'store_order_items';

    // Nếu dữ liệu đã tồn tại thì không chèn nữa
    $user_exists = $wpdb->get_var("SELECT COUNT(*) FROM $users_table WHERE username = 'admin'");
    if ($user_exists > 0) {
        return;
    }

    // Thêm 2 người dùng mẫu đầu tiên có address và phone
    $wpdb->insert($users_table, [
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_DEFAULT),
        'email' => 'admin@example.com',
        'full_name' => 'Admin User',
        'address' => '123 Admin Street, City A',
        'phone' => '0123456789',
        'role' => 'admin'
    ]);

    $wpdb->insert($users_table, [
        'username' => 'customer1',
        'password' => password_hash('customer123', PASSWORD_DEFAULT),
        'email' => 'customer1@example.com',
        'full_name' => 'Customer One',
        'address' => '456 Customer Lane, City B',
        'phone' => '0987654321',
        'role' => 'customer'
    ]);

    // Thêm 10 người dùng mẫu nữa
    for ($i = 2; $i <= 11; $i++) {
        $username = 'customer' . $i;
        $email = "customer{$i}@example.com";
        $full_name = "Customer $i";
        $address = "$i Example Street, City $i";
        $phone = '09' . str_pad($i, 8, '0', STR_PAD_LEFT);
        $password = password_hash("customer123", PASSWORD_DEFAULT);
        $role = 'customer';

        $wpdb->insert($users_table, [
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'full_name' => $full_name,
            'address' => $address,
            'phone' => $phone,
            'role' => $role
        ]);
    }

    // Thêm sản phẩm mẫu có ảnh
    $sample_products = [
        [
            'name' => 'Táo đỏ Mỹ',
            'description' => 'Táo đỏ tươi, giòn, nhập khẩu từ Mỹ.',
            'price' => 45000,
            'stock_quantity' => 100,
            'category' => 'trai-cay',
            'image_url' => 'https://images.pexels.com/photos/618775/pexels-photo-618775.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Bơ sáp Đà Lạt',
            'description' => 'Bơ sáp dẻo béo, thu hoạch tại vườn hữu cơ.',
            'price' => 70000,
            'stock_quantity' => 60,
            'category' => 'trai-cay',
            'image_url' => 'https://images.pexels.com/photos/1314041/pexels-photo-1314041.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Cà chua bi',
            'description' => 'Cà chua bi ngọt thanh, phù hợp ăn sống và làm salad.',
            'price' => 25000,
            'stock_quantity' => 80,
            'category' => 'rau-cu',
            'image_url' => 'https://images.pexels.com/photos/128401/pexels-photo-128401.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Dâu tây Hàn Quốc',
            'description' => 'Dâu tây tươi, vị ngọt, không chất bảo quản.',
            'price' => 120000,
            'stock_quantity' => 40,
            'category' => 'trai-cay',
            'image_url' => 'https://images.pexels.com/photos/1927377/pexels-photo-1927377.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Cải bó xôi',
            'description' => 'Rau cải bó xôi xanh tươi, giàu dinh dưỡng.',
            'price' => 18000,
            'stock_quantity' => 120,
            'category' => 'rau-cu',
            'image_url' => 'https://images.pexels.com/photos/2773942/pexels-photo-2773942.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Gà nướng nguyên con',
            'description' => 'Gà ta nướng than hoa thơm ngon, giao nóng.',
            'price' => 150000,
            'stock_quantity' => 25,
            'category' => 'che-bien',
            'image_url' => 'https://images.pexels.com/photos/3688/food-dinner-lunch-chicken.jpg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Trứng gà ta',
            'description' => 'Trứng gà sạch, không kháng sinh.',
            'price' => 30000,
            'stock_quantity' => 200,
            'category' => 'tuoi-song',
            'image_url' => 'https://images.pexels.com/photos/96616/pexels-photo-96616.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Sữa hạt óc chó',
            'description' => 'Sữa hạt không đường, tốt cho tim mạch.',
            'price' => 32000,
            'stock_quantity' => 50,
            'category' => 'do-uong',
            'image_url' => 'https://images.pexels.com/photos/7225580/pexels-photo-7225580.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Cá thu tươi',
            'description' => 'Cá thu biển đánh bắt trong ngày, làm sạch sẵn.',
            'price' => 85000,
            'stock_quantity' => 35,
            'category' => 'hai-san',
            'image_url' => 'https://images.pexels.com/photos/61153/fish-fischer-ocean-market-61153.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
        [
            'name' => 'Khoai tây Đà Lạt',
            'description' => 'Khoai tây sạch, vỏ mỏng, thịt vàng, dễ chế biến.',
            'price' => 28000,
            'stock_quantity' => 75,
            'category' => 'rau-cu',
            'image_url' => 'https://media.istockphoto.com/id/157430678/vi/anh/ba-c%E1%BB%A7-khoai-t%C3%A2y.jpg?b=1&s=612x612&w=0&k=20&c=2jk3IBUwS8_4O0nhI3ctYh4aR73eIpUko37S8s3aUlQ='
        ],
        [
            'name' => 'Nho đen không hạt',
            'description' => 'Nho đen nhập khẩu, ngọt, không hạt, nhiều nước.',
            'price' => 98000,
            'stock_quantity' => 45,
            'category' => 'trai-cay',
            'image_url' => 'https://images.pexels.com/photos/357576/pexels-photo-357576.jpeg?auto=compress&cs=tinysrgb&w=600'
        ],
    ];

    foreach ($sample_products as $product) {
        $wpdb->insert($products_table, array_merge($product, [
            'created_at' => current_time('mysql', 1)
        ]));
    }

    for ($i = 1; $i <= 10; $i++) {
        $username = "customer" . (($i <= 5) ? $i : $i - 5); // customer1 → customer5 lặp lại

        // Lấy ID người dùng
        $user_id = $wpdb->get_var($wpdb->prepare("SELECT id FROM $users_table WHERE username = %s", $username));
        if (!$user_id) continue;

        // Lấy ngẫu nhiên 3 sản phẩm
        $product_rows = $wpdb->get_results("SELECT id, price FROM $products_table ORDER BY RAND() LIMIT 3");

        $total_amount = 0;
        $products_data = [];

        foreach ($product_rows as $product) {
            $quantity = rand(1, 3);
            $subtotal = $product->price * $quantity;
            $total_amount += $subtotal;

            $products_data[] = [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $product->price
            ];
        }

        // Tạo ngày ngẫu nhiên trong tháng này hoặc tháng trước
        $random_day = rand(1, 28);
        if ($i <= 5) {
            // Đơn hàng tháng này
            $created_at = date("Y-m-{$random_day} H:i:s");
        } else {
            // Đơn hàng tháng trước
            $created_at = date("Y-m-{$random_day} H:i:s", strtotime("first day of last month +{$random_day} days"));
        }

        // Tạo đơn hàng
        $wpdb->insert($orders_table, [
            'user_id' => $user_id,
            'total_amount' => $total_amount,
            'status' => 'completed',
            'payment_method' => 'COD',
            'shipping_address' => "Số {$i} Đường XYZ, Quận {$i}, TP.HCM",
            'created_at' => $created_at
        ]);

        $order_id = $wpdb->insert_id;

        // Thêm chi tiết đơn hàng
        foreach ($products_data as $item) {
            $wpdb->insert($items_table, [
                'order_id' => $order_id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price_at_time' => $item['price']
            ]);
        }
    }
}
