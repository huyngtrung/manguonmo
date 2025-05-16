<?php
// database.php
function qlsv_create_tables()
{
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_students = $wpdb->prefix . 'students';
    $sql_students = "CREATE TABLE $table_students (
        id INT NOT NULL AUTO_INCREMENT,
        student_id VARCHAR(20) NOT NULL UNIQUE,
        full_name VARCHAR(100) NOT NULL,
        class VARCHAR(50) NOT NULL,
        hometown VARCHAR(100) NOT NULL,
        date_of_birth DATE,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql_students);
}
