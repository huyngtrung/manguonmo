
<?php
function qlch_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    // Users table
    $table_users = $wpdb->prefix . 'store_users';
    $sql_users = "CREATE TABLE IF NOT EXISTS $table_users (
        id INT NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        role ENUM('admin', 'staff', 'customer') DEFAULT 'customer',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Products table
    $table_products = $wpdb->prefix . 'store_products';
    $sql_products = "CREATE TABLE IF NOT EXISTS $table_products (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        stock_quantity INT NOT NULL DEFAULT 0,
        category VARCHAR(50) NOT NULL,
        image_url VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    // Orders table
    $table_orders = $wpdb->prefix . 'store_orders';
    $sql_orders = "CREATE TABLE IF NOT EXISTS $table_orders (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status ENUM('pending', 'processing', 'completed', 'cancelled') DEFAULT 'pending',
        payment_method VARCHAR(50),
        shipping_address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (user_id) REFERENCES ${table_users}(id)
    ) $charset_collate;";

    // Order items table
    $table_order_items = $wpdb->prefix . 'store_order_items';
    $sql_order_items = "CREATE TABLE IF NOT EXISTS $table_order_items (
        id INT NOT NULL AUTO_INCREMENT,
        order_id INT NOT NULL,
        product_id INT NOT NULL,
        quantity INT NOT NULL,
        price_at_time DECIMAL(10,2) NOT NULL,
        PRIMARY KEY (id),
        FOREIGN KEY (order_id) REFERENCES ${table_orders}(id),
        FOREIGN KEY (product_id) REFERENCES ${table_products}(id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_users);
    dbDelta($sql_products);
    dbDelta($sql_orders);
    dbDelta($sql_order_items);
}

function qlch_drop_tables() {
    global $wpdb;
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}store_order_items");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}store_orders");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}store_products");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}store_users");
}
