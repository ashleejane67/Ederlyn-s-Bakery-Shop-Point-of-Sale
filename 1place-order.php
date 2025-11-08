<?php
session_start();
require 'config.php';
header('Content-Type: application/json');

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['place_order']) || !isset($data['order']) || empty($data['order'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request data.']);
    exit();
}


$conn->begin_transaction();

try {
    
    $today = date('Y-m-d');
    $total_amount = 0;
    
    foreach ($data['order'] as $item) {
        $total_amount += $item['quantity'] * $item['price'];
    }
    
    
    $payment_amount = isset($data['payment_amount']) ? floatval($data['payment_amount']) : $total_amount;
    $change_amount = $payment_amount - $total_amount;
    
    
    if ($change_amount < 0) {
        $change_amount = 0;
    }
    
    
    $operator = $data['operator'] ?? 'Unknown';
    $admin_id = null;
    
    $stmt = $conn->prepare("SELECT admin_id FROM admin WHERE username = ?");
    $stmt->bind_param("s", $operator);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
        $admin_id = $admin['admin_id'];
    }
    
    
    $stmt = $conn->prepare("INSERT INTO orders (order_date, total_amount, payment_amount, change_amount, admin_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdddi", $today, $total_amount, $payment_amount, $change_amount, $admin_id);
    $stmt->execute();
    
    $order_id = $conn->insert_id;
    
    
    foreach ($data['order'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $price = $item['price'];
        
        $stmt = $conn->prepare("INSERT INTO order_item (order_id, product_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $order_id, $product_id, $quantity, $price);
        $stmt->execute();
        
        
        $stmt = $conn->prepare("UPDATE products SET quantity_in_stock = quantity_in_stock - ? WHERE product_id = ?");
        $stmt->bind_param("ii", $quantity, $product_id);
        $stmt->execute();
        
        
        $stmt = $conn->prepare("SELECT name, quantity_in_stock FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if ($product['quantity_in_stock'] <= 0) {
           
        } else if ($product['quantity_in_stock'] <= 10) {
            
        }
    }
    
   
    $_SESSION['order_items'] = [];
    
   
    $conn->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Order placed successfully!', 
        'order_id' => $order_id
    ]);
    
} catch (Exception $e) {
    
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>