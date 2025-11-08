<?php
session_start();
require 'config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);
    
    if (!$data || !isset($data['order_id'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid request data']);
        exit;
    }
    
    $order_id = intval($data['order_id']);
    
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $order = $result->fetch_assoc();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Order not found']);
        exit;
    }
    
    
    $stmt = $conn->prepare("SELECT oi.*, p.name AS product_name, p.image_path 
                           FROM order_item oi 
                           JOIN products p ON oi.product_id = p.product_id 
                           WHERE oi.order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $order_items = [];
    while ($row = $result->fetch_assoc()) {
        $order_items[] = $row;
    }
    
    
    $stmt = $conn->prepare("SELECT amount FROM payment WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $payment = $result->fetch_assoc();
    
    $payment_amount = $payment ? $payment['amount'] : 0;
    $change = $payment_amount - $order['total_amount'];
    
    ob_start();
?>
    <div class="receipt-header">
        <h2>Ederlyn's Bakery</h2>
        <p>Order Receipt</p>
        <p>Date: <?php echo date('F d, Y', strtotime($order['order_date'])); ?></p>
    </div>
    
    <div class="receipt-details">
        <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
        <p><strong>Operator:</strong> <?php echo htmlspecialchars($order['operator']); ?></p>
    </div>
    
    <table class="items-table">
        <thead>
            <tr style="background-color: #4a90e2; color: white;">
                <th>Product</th>
                <th>Price</th>
                <th>Qty</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($order_items as $item): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                    <td>₱<?php echo number_format($item['price'], 2); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Subtotal:</strong></td>
                <td>₱<?php echo number_format($order['subtotal'], 2); ?></td>
            </tr>
            <?php if ($order['discount_amount'] > 0): ?>
            <tr>
                <td colspan="3" class="text-right"><strong>Discount:</strong></td>
                <td>₱<?php echo number_format($order['discount_amount'], 2); ?></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="3" class="text-right"><strong>Total:</strong></td>
                <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Amount Paid:</strong></td>
                <td>₱<?php echo number_format($payment_amount, 2); ?></td>
            </tr>
            <tr>
                <td colspan="3" class="text-right"><strong>Change:</strong></td>
                <td>₱<?php echo number_format($change, 2); ?></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="receipt-footer">
        <p>Thank you for shopping at Ederlyn's Bakery!</p>
        <p>Please come again.</p>
        <?php if (!empty($order['customer_name'])): ?>
        <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customer_name']); ?></p>
        <?php endif; ?>
    </div>
<?php
    $html = ob_get_clean();
    
    echo json_encode([
        'success' => true,
        'html' => $html,
        'order' => [
            'id' => $order_id,
            'date' => date('F d, Y', strtotime($order['order_date'])),
            'total' => $order['total_amount'],
            'payment' => $payment_amount,
            'change' => $change
        ]
    ]);
    
} else {
    
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>