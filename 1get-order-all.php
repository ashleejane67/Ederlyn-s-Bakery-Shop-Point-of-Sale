<?php
include('connect.php');

header('Content-Type: application/json');

try {
    $sql_select_orders = "SELECT * FROM orders ORDER BY order_date DESC";
    $result_select_orders = mysqli_query($con, $sql_select_orders);
    $orders = [];

    if ($result_select_orders && mysqli_num_rows($result_select_orders) > 0) {
        while ($order = mysqli_fetch_assoc($result_select_orders)) {
            $order['items'] = [];
            $order_id = $order['order_id'];

            $sql_select_items = "SELECT oi.*, p.name AS product_name, p.image_path FROM order_item oi JOIN products p ON oi.product_id = p.product_id WHERE oi.order_id = $order_id";
            $result_select_items = mysqli_query($con, $sql_select_items);

            if ($result_select_items && mysqli_num_rows($result_select_items) > 0) {
                while ($item = mysqli_fetch_assoc($result_select_items)) {
                    $order['items'][] = $item;
                }
            }
            $orders[] = $order;
        }
    }

    echo json_encode($orders);
    http_response_code(200);

} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    http_response_code(500);
}

mysqli_close($con);
?>