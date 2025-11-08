<?php
session_start();
require 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


$products = [];
$res = $conn->query("SELECT product_id, name, price, image_path, quantity_in_stock FROM products");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    echo "Error fetching products: " . $conn->error;
}

$operator_username = $_SESSION['username'] ?? 'N/A';


$orderItems = $_SESSION['order_items'] ?? [];


$totalAmount = 0;
foreach ($orderItems as $item) {
    $totalAmount += $item['quantity'] * $item['price'];
}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Ederlyn's Bakery POS - Order</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background: #4a90e2;
            color: white;
            padding: 20px;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            font-size: 22px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar ul li {
            padding: 15px;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .sidebar ul li i {
            margin-right: 10px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar ul li:hover, .sidebar ul li.active {
            background: #2c6db5;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .main-content {
            margin-left: 250px;
            width: calc(100% - 250px);
            height: 100vh;
            overflow-y: auto;
        }

        
        .order-dashboard {
            display: flex;
            height: 100%;
        }

        .menu-container {
            flex: 2;
            display: flex;
            flex-wrap: wrap;
            overflow-y: auto;
            padding: 20px;
            gap: 15px;
            justify-content: flex-start; 
            align-items: flex-start;
        }

        .menu-header {
            width: 100%;
            text-align: center;
            margin-bottom: 20px;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }

        .menu-item {
            background: #fff;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            width: 160px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }

        
        .menu-item.out-of-stock {
            background: #f0f0f0;
            opacity: 0.7;
            cursor: not-allowed;
            position: relative;
        }

        
        .out-of-stock-label {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            text-align: center;
            background-color: rgba(255, 0, 0, 0.7);
            color: white;
            padding: 5px 0;
            font-weight: bold;
            transform: rotate(-15deg);
        }

        .menu-item img {
            max-width: 100%;
            height: auto;
            border-radius: 4px;
            margin-bottom: 8px;
            max-height: 100px;
            object-fit: cover;
        }

        .menu-item h3 {
            font-size: 18px;
            margin: 8px 0;
            color: #333;
        }

        .menu-item .price {
            color: #4a90e2;
            font-weight: bold;
            font-size: 16px;
        }

        .order-sidebar {
            flex: 1;
            background: #f9f9f9;
            padding: 10px;
            border-left: 1px solid #ddd;
            display: flex;
            flex-direction: column;
        }

        .order-info {
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }

        .order-metadata {
            margin-top: 8px;
            font-size: 14px;
        }

        .order-metadata p {
            margin: 5px 0;
        }

        .current-order {
            flex: 1;
            overflow-y: auto;
        }

        .order-items {
            list-style: none;
            padding: 0;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .order-item-image {
            max-height: 50px;
            max-width: 50px;
            margin-right: 8px;
        }

        .order-item-details {
            flex: 1;
        }

        .order-item-name {
            font-weight: bold;
            margin-right: 5px;
        }

        .order-item-quantity {
            display: flex;
            align-items: center;
        }

        .quantity-btn {
            background: none;
            border: none;
            font-size: 16px;
            padding: 5px;
            cursor: pointer;
            color: #007BFF; 
        }

        .quantity-value {
            margin: 0 5px;
            font-size: 16px; 
            font-weight: bold; 
            min-width: 20px; 
            text-align: center; /
        }

        .remove-btn {
            background: none;
            border: none;
            color: #d32f2f;
            cursor: pointer;
        }

        .order-total {
            padding-top: 10px;
            border-top: 1px solid #ddd;
            text-align: right;
        }

        .place-order-section {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }

        .place-order-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .place-order-section input {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .btn-place-order {
            background-color: #4caf50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }

        .order-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .btn-delete {
            background-color: #f44336;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        #no-items-message {
            text-align: center;
            color: #888;
            padding: 10px;
        }

        #receipt-modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 5px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .close-button {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-button:hover {
            color: #333;
        }

        
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px dashed #ccc;
        }
        
        .receipt-header h2 {
            margin: 0;
            color: #4a90e2;
        }
        
        .receipt-details {
            margin-bottom: 15px;
        }

        .receipt-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .receipt-items th {
            background-color: #4a90e2; 
            color: white;
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .receipt-items td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .receipt-total {
            text-align: right;
            font-weight: bold;
            font-size: 18px;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
        }
        
        .receipt-payment {
            text-align: right;
            margin-top: 10px;
        }
        
        .receipt-footer {
            text-align: center;
            margin-top: 20px;
            font-style: italic;
            color: #666;
        }
        
        .receipt-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }
        
        .btn-view-receipt {
            background-color: #4a90e2;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .btn-new-order {
            background-color: #4caf50;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
                padding: 10px;
            }

            .sidebar h2, .sidebar ul li span {
                display: none;
            }

            .main-content {
                margin-left: 70px;
                width: calc(100% - 70px);
            }

            .inventory-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><i class="fas fa-chart-bar"></i> <a href="dashboard.php">Dashboard</a></li>
            <li class="active"><i class="fas fa-shopping-cart"></i> <a href="order.php">Order</a></li>
            <li><i class="fas fa-cubes"></i> <a href="inventory.php">Inventory</a></li>
            <li><i class="fas fa-sign-out-alt"></i> <a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="order-dashboard">
            <div class="menu-container">
                <h2 class="menu-header">MENU</h2>
                <?php if (count($products) > 0) : ?>
                    <?php foreach ($products as $product) : ?>
                        <?php $outOfStock = $product['quantity_in_stock'] <= 0; ?>
                        <div class="menu-item <?php echo $outOfStock ? 'out-of-stock' : ''; ?>" 
                             <?php if (!$outOfStock): ?>
                                onclick="addItemToOrder(<?php echo $product['product_id']; ?>, '<?php echo htmlspecialchars($product['name']); ?>', <?php echo $product['price']; ?>, '<?php echo htmlspecialchars($product['image_path']); ?>')"
                             <?php endif; ?>>
                            <?php if ($outOfStock): ?>
                                <div class="out-of-stock-label">OUT OF STOCK</div>
                            <?php endif; ?>
                            <?php if ($product['image_path']) : ?>
                                <img src="<?php echo htmlspecialchars($product['image_path']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            <?php else : ?>
                                <img src="placeholder.png" alt="Placeholder">
                            <?php endif; ?>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="price">₱<?php echo number_format($product['price'], 2); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <p>No products available.</p>
                <?php endif; ?>
            </div>

            <div class="order-sidebar">
                <div class="order-info">
                    <h2>Current Order</h2>
                    <div class="order-metadata">
                        <p><strong>Order ID:</strong> <span id="order-id">#<?php echo time(); ?></span></p>
                        <p><strong>Operator:</strong> <?php echo htmlspecialchars($operator_username); ?></p>
                    </div>
                    
                </div>

                <div class="current-order">
                    <ul class="order-items" id="order-items-list">
                        <li id="no-items-message" style="<?php echo empty($orderItems) ? '' : 'display:none;'; ?>">No items in order.</li>
                        <?php if (!empty($orderItems)) : ?>
                            <?php foreach ($orderItems as $key => $item) : ?>
                                <li class="order-item" data-product-id="<?php echo $item['id']; ?>">
                                    <?php if ($item['image']) : ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="order-item-image">
                                    <?php else : ?>
                                        <img src="placeholder_small.png" alt="Placeholder" class="order-item-image">
                                    <?php endif; ?>
                                    <div class="order-item-details">
                                        <span class="order-item-name"><?php echo htmlspecialchars($item['name']); ?></span>
                                        <div class="order-item-quantity">
                                            <button class="quantity-btn" onclick="changeQuantity(<?php echo $item['id']; ?>, -1)">−</button>
                                            <span class="quantity-value"><?php echo $item['quantity']; ?></span>
                                            <button class="quantity-btn" onclick="changeQuantity(<?php echo $item['id']; ?>, 1)">+</button>
                                        </div>
                                    </div>
                                    <button class="remove-btn" onclick="removeItem(<?php echo $item['id']; ?>)">Remove</button>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>
                    
                    <div class="order-total">
                        <strong>Total: ₱<span id="total-amount"><?php echo number_format($totalAmount, 2); ?></span></strong>
                    </div>
                </div>

                <div class="place-order-section">
                    <label for="payment-input">Payment Amount:</label>
                    <input type="number" id="payment-input" min="0" value="0">
                    <p><strong>Change: ₱<span id="change"><?php echo number_format(0, 2); ?></span></strong></p>
                    <button class="btn-place-order" onclick="placeOrder()">Place Order</button>
                </div>

                <div class="order-actions">
                    <button class="btn-delete" onclick="clearOrder()">Clear Order</button>
                </div>
            </div>
        </div>

        <div id="receipt-modal" class="modal">
            <div class="modal-content">
                <span class="close-button" onclick="closeReceiptModal()">&times;</span>
                <div id="receipt-content">
                    
                </div>
            </div>
        </div>

    </div>

    <script>
        let orderItems = <?php echo json_encode($orderItems); ?>;
        let totalAmount = <?php echo $totalAmount; ?>;
        const orderList = document.getElementById('order-items-list');
        const payableAmountSpan = document.getElementById('total-amount');
        const changeAmountSpan = document.getElementById('change');
        const noItemsMessage = document.getElementById('no-items-message');
        const paymentInput = document.getElementById('payment-input');
        const receiptModal = document.getElementById('receipt-modal');
        const receiptContent = document.getElementById('receipt-content');

        function updateOrderDisplay() {
            orderList.innerHTML = '';
            totalAmount = 0;
            if (orderItems.length === 0) {
                noItemsMessage.style.display = 'block';
            } else {
                noItemsMessage.style.display = 'none';
                orderItems.forEach(item => {
                    const listItem = document.createElement('li');
                    listItem.classList.add('order-item');
                    listItem.dataset.productId = item.id;
                    listItem.innerHTML = `
                        ${item.image ? `<img src="${item.image}" alt="${item.name}" class="order-item-image">` : '<img src="placeholder_small.png" alt="Placeholder" class="order-item-image">'}
                        <div class="order-item-details">
                            <span class="order-item-name">${item.name}</span>
                            <div class="order-item-quantity">
                                <button class="quantity-btn" onclick="changeQuantity(${item.id}, -1)">−</button>
                                <span class="quantity-value">${item.quantity}</span>
                                <button class="quantity-btn" onclick="changeQuantity(${item.id}, 1)">+</button>
                            </div>
                        </div>
                        <button class="remove-btn" onclick="removeItem(${item.id})">Remove</button>
                    `;
                    orderList.appendChild(listItem);
                    totalAmount += item.price * item.quantity;
                });
            }
            payableAmountSpan.textContent = totalAmount.toFixed(2);
            calculateChange();
        }

        function calculateChange() {
            const payment = parseFloat(paymentInput.value) || 0;
            const change = payment - totalAmount;
            changeAmountSpan.textContent = change.toFixed(2);
        }

        function addItemToOrder(productId, name, price, image) {
            const existingItem = orderItems.find(item => item.id === productId);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                orderItems.push({ id: productId, name: name, price: price, image: image, quantity: 1 });
            }
            updateOrderDisplay();
        }

        function changeQuantity(productId, change) {
            const itemIndex = orderItems.findIndex(item => item.id === productId);
            if (itemIndex > -1) {
                orderItems[itemIndex].quantity += change;
                if (orderItems[itemIndex].quantity < 1) {
                    orderItems.splice(itemIndex, 1);
                }
                updateOrderDisplay();
            }
        }

        function removeItem(productId) {
            orderItems = orderItems.filter(item => item.id !== productId);
            updateOrderDisplay();
        }

        function clearOrder() {
            orderItems = [];
            updateOrderDisplay();
        }

        function placeOrder() {
            if (orderItems.length === 0) {
                alert('Please add items to the order.');
                return;
            }
            
            const payment = parseFloat(document.getElementById('payment-input').value) || 0;
            
            if (payment < totalAmount) {
                alert('Payment is insufficient.');
                return;
            }

            
            const orderData = {
                place_order: true,
                payment_amount: payment,
                order: orderItems.map(item => ({
                    product_id: item.id,
                    quantity: item.quantity,
                    price: item.price
                })),
                operator: '<?php echo $operator_username; ?>'
            };

           
            fetch('1place-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(orderData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
                    showReceiptModal(data.order_id, payment);
                } else {
                    alert('Error placing order: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An unexpected error occurred while placing the order.');
            });
        }

        function showReceiptModal(orderId, paymentAmount) {
            
            const today = new Date();
            const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
            const formattedDate = today.toLocaleDateString('en-US', dateOptions);
            
            
            const change = paymentAmount - totalAmount;
            
            
            let receiptHTML = `
                <div class="receipt-header">
                    <h2>Ederlyn's Bakery</h2>
                    <p>Order Receipt</p>
                    <p>Date: ${formattedDate}</p>
                </div>
                
                <div class="receipt-details">
                    <p><strong>Order ID:</strong> #${orderId}</p>
                    <p><strong>Operator:</strong> <?php echo $operator_username; ?></p>
                </div>
                
                <table class="receipt-items">
                    <thead>
                        <tr style="background-color: #4a90e2; color: white;">
                            <th>Product</th>
                            <th>Price</th>
                            <th>Qty</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            
            orderItems.forEach(item => {
                const subtotal = item.price * item.quantity;
                receiptHTML += `
                    <tr>
                        <td>${item.name}</td>
                        <td>₱${item.price.toFixed(2)}</td>
                        <td>${item.quantity}</td>
                        <td>₱${subtotal.toFixed(2)}</td>
                    </tr>
                `;
            });
            
            
            receiptHTML += `
                    </tbody>
                </table>
                
                <div class="receipt-total">
                    <p>Total: ₱${totalAmount.toFixed(2)}</p>
                </div>
                
                <div class="receipt-payment">
                    <p>Payment: ₱${paymentAmount.toFixed(2)}</p>
                    <p>Change: ₱${change.toFixed(2)}</p>
                </div>
                
                <div class="receipt-footer">
                    <p>Thank you for your purchase at Ederlyn's Bakery!</p>
                </div>
                
                <div class="receipt-actions">
                    <button class="btn-view-receipt" onclick="viewFullReceipt(${orderId})">Print Receipt</button>
                    <button class="btn-new-order" onclick="closeReceiptModal()">New Order</button>
                </div>
            `;
            
            
            receiptContent.innerHTML = receiptHTML;
            
            
            receiptModal.style.display = 'block';
            
            
            clearOrder();
            paymentInput.value = 0;
            calculateChange();
        }

        function closeReceiptModal() {
            receiptModal.style.display = 'none';
        }

        function viewFullReceipt(orderId) {
            
            window.location.href = `order_dashboard.php?order_id=${orderId}`;
        }

        
        window.onclick = function(event) {
            if (event.target == receiptModal) {
                closeReceiptModal();
            }
        }

        
        paymentInput.addEventListener('input', calculateChange);

        updateOrderDisplay();
    </script>
</body>
</html>