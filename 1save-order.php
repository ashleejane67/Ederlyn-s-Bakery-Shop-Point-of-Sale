<?php
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_order'])) {

    $customer_id = isset($_POST['customer_id']) ? intval($_POST['customer_id']) : 0; 
    $operator = htmlspecialchars($_POST['operator']); 
    $order_items = $_POST['order'];

    if (!is_array($order_items) || empty($order_items)) {
        echo json_encode(['success' => false, 'message' => 'No items in order to save.']);
        exit;
    }

    
    foreach ($order_items as $item) {
        if (!isset($item['product_id']) || !isset($item['quantity']) || !isset($item['price'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid order item data.']);
            exit;
        }
        
    }

    
    $_SESSION['saved_order'] = [
        'customer_id' => $customer_id,
        'operator' => $operator,
        'order_items' => $order_items
    ];

    echo json_encode(['success' => true, 'message' => 'Order saved successfully.']);

} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>