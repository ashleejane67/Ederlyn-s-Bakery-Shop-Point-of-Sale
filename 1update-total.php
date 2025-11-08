<?php
include('connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'PUT' && isset($_GET['order_id'])) {
    $order_id = mysqli_escape_string($con, $_GET['order_id']);

    try {
        
        $sql_calculate_total = "SELECT SUM(price_at_sale * quantity) AS total FROM order_item WHERE order_id = $order_id";
        $result_calculate_total = mysqli_query($con, $sql_calculate_total);
        if ($result_calculate_total && mysqli_num_rows($result_calculate_total) > 0) {
            $total_data = mysqli_fetch_assoc($result_calculate_total);
            $total_amount = $total_data['total'] ?? 0;
        } else {
            $total_amount = 0; 
        }

        $sql_update = "UPDATE orders SET total_amount = $total_amount WHERE order_id = $order_id";
        if (mysqli_query($con, $sql_update)) {
            echo json_encode(['message' => 'Order total updated successfully.', 'total_amount' => $total_amount]);
            http_response_code(200);
        } else {
            throw new Exception("Error updating order total: " . mysqli_error($con));
        }

    } catch (Exception $e) {
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500);
    }

} else {
    echo json_encode(['error' => 'Invalid request. Use PUT with order_id.']);
    http_response_code(400);
}

mysqli_close($con);
?>