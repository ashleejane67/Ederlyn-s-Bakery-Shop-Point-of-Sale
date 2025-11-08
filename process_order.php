<?php
session_start();
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $customer_id = $data['customerId'];
    $items = $data['items'];
    $total_amount = $data['totalAmount'];
    $payment = $data['payment'];
    $change = $data['change'];
    $operator_username = $data['operator']; 

    
    $conn->begin_transaction();

    try {
        
        $order_sql = "INSERT INTO orders (order_date, total_amount, customer_id) VALUES (CURDATE(), ?, ?)"; 
        $order_stmt = $conn->prepare($order_sql);
        $order_stmt->bind_param("di", $total_amount, $customer_id); 
        $order_stmt->execute();
        $order_id = $conn->insert_id;  

        if (!$order_id) {
            throw new Exception("Failed to create order.");
        }

        
        $item_sql = "INSERT INTO order_item (order_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)";
        $item_stmt = $conn->prepare($item_sql);
        foreach ($items as $item) {
            
            if ($item['quantity'] > 10000) {  
                throw new Exception("Quantity too large for item: " . $item['name']);
            }
            $item_stmt->bind_param("iiid", $order_id, $item['id'], $item['quantity'], $item['price']);
            $item_stmt->execute();
        }

        
        $sale_sql = "INSERT INTO sales (order_id, transaction_date, amount) VALUES (?, NOW(), ?)";
        $sale_stmt = $conn->prepare($sale_sql);
        $sale_stmt->bind_param("id", $order_id, $total_amount);  
        $sale_stmt->execute();
        $sale_id = $conn->insert_id;

    
        $conn->commit();

        echo json_encode(['success' => true, 'message' => 'Order placed successfully!']);

    } catch (Exception $e) {
        // If any error occurred, roll back the transaction
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);  // Send the error message
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>