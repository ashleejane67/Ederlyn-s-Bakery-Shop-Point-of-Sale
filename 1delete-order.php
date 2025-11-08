<?php
include('connect.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($_GET['order_id'])) {
    $order_id = mysqli_escape_string($con, $_GET['order_id']);

    try {
        mysqli_begin_transaction($con);

        
        $sql_delete_items = "DELETE FROM order_item WHERE order_id = $order_id";
        if (!mysqli_query($con, $sql_delete_items)) {
            throw new Exception("Error deleting order items: " . mysqli_error($con));
        }

        
        $sql_delete_order = "DELETE FROM orders WHERE order_id = $order_id";
        if (!mysqli_query($con, $sql_delete_order)) {
            throw new Exception("Error deleting order: " . mysqli_error($con));
        }

        mysqli_commit($con);

        echo json_encode(['message' => 'Order and associated items deleted successfully.']);
        http_response_code(200);

    } catch (Exception $e) {
        mysqli_rollback($con);
        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
        http_response_code(500);
    }

} else {
    echo json_encode(['error' => 'Invalid request. Use DELETE with order_id.']);
    http_response_code(400);
}

mysqli_close($con);
?>