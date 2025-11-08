<?php
session_start();
require 'config.php';
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}


$res = $conn->query("SELECT SUM(total_amount) as total_sales_amount FROM orders");
if ($res) {
    $row = $res->fetch_assoc();
    $total_sales_amount = $row['total_sales_amount'] ?? 0;
} else {
    echo "Error fetching total sales amount: " . $conn->error;
    $total_sales_amount = 0;
}


$res = $conn->query("SELECT SUM(oi.quantity) as total_products_sold 
                    FROM order_item oi");
if ($res) {
    $row = $res->fetch_assoc();
    $total_products_sold = $row['total_products_sold'] ?? 0;
} else {
    echo "Error fetching total products sold: " . $conn->error;
    $total_products_sold = 0;
}


$res = $conn->query("SELECT COUNT(*) as total_products_count FROM products");
if ($res) {
    $row = $res->fetch_assoc();
    $total_products = $row['total_products_count'] ?? 0;
} else {
    echo "Error fetching total product count: " . $conn->error;
    $total_products = 0;
}


$res = $conn->query("SELECT COUNT(*) as low_stock_count FROM products WHERE quantity_in_stock <= 10");
if ($res) {
    $row = $res->fetch_assoc();
    $low_stock = $row['low_stock_count'] ?? 0;
} else {
    echo "Error fetching low stock count: " . $conn->error;
    $low_stock = 0;
}


$current_month = date('m');
$current_year = date('Y');
$monthly_data = [];

if (isset($_GET['show_monthly_report']) && $_GET['show_monthly_report'] == 'true') {
    
    $report_month = isset($_GET['month']) ? intval($_GET['month']) : intval($current_month);
    $report_year = isset($_GET['year']) ? intval($_GET['year']) : intval($current_year);
    

    $monthly_query = $conn->query("SELECT 
                                    DATE(order_date) as sale_date,
                                    SUM(total_amount) as daily_total,
                                    COUNT(order_id) as order_count
                                FROM orders
                                WHERE MONTH(order_date) = $report_month 
                                AND YEAR(order_date) = $report_year
                                GROUP BY DATE(order_date)
                                ORDER BY sale_date ASC");
    
    if ($monthly_query) {
        while ($day_data = $monthly_query->fetch_assoc()) {
            $monthly_data[$day_data['sale_date']] = [
                'daily_total' => $day_data['daily_total'],
                'order_count' => $day_data['order_count']
            ];
        }
    }
    
    
    $month_total_query = $conn->query("SELECT 
                                        SUM(total_amount) as month_total,
                                        COUNT(order_id) as month_orders
                                    FROM orders
                                    WHERE MONTH(order_date) = $report_month 
                                    AND YEAR(order_date) = $report_year");
    
    if ($month_total_query) {
        $month_totals = $month_total_query->fetch_assoc();
        $month_total_sales = $month_totals['month_total'] ?? 0;
        $month_total_orders = $month_totals['month_orders'] ?? 0;
    } else {
        $month_total_sales = 0;
        $month_total_orders = 0;
    }
    
    
    $month_name = date('F', mktime(0, 0, 0, $report_month, 1, $report_year));
}


$daily_sales = [];
if (isset($_GET['view_date'])) {
    $view_date = $_GET['view_date'];
    
    if (preg_match("/^\d{4}-\d{2}-\d{2}$/", $view_date)) {
        
        $daily_query = $conn->query("SELECT 
                                    o.order_id, 
                                    o.order_date, 
                                    o.total_amount, 
                                    o.payment_amount, 
                                    o.change_amount,
                                    a.username as sales_by
                                FROM orders o
                                LEFT JOIN admin a ON o.admin_id = a.admin_id
                                WHERE DATE(o.order_date) = '$view_date'
                                ORDER BY o.order_date DESC");
        
        if ($daily_query) {
            while ($order = $daily_query->fetch_assoc()) {
                
                $order_id = $order['order_id'];
                $products_query = $conn->query("SELECT p.name as product_name, oi.quantity, p.product_id,
                                              oi.price_at_sale
                                           FROM order_item oi 
                                           JOIN products p ON oi.product_id = p.product_id 
                                           WHERE oi.order_id = $order_id");
                
                $products = [];
                if ($products_query) {
                    while ($product = $products_query->fetch_assoc()) {
                        $products[] = $product;
                    }
                }
                
                $order['products'] = $products;
                $daily_sales[] = $order;
            }
        }
        
        
        $daily_summary_query = $conn->query("SELECT 
                                            COUNT(order_id) as total_orders,
                                            SUM(total_amount) as total_revenue
                                        FROM orders
                                        WHERE DATE(order_date) = '$view_date'");
        
        if ($daily_summary_query) {
            $daily_summary = $daily_summary_query->fetch_assoc();
            $daily_order_count = $daily_summary['total_orders'] ?? 0;
            $daily_revenue = $daily_summary['total_revenue'] ?? 0;
        } else {
            $daily_order_count = 0;
            $daily_revenue = 0;
        }
    }
}


$recent_sales = [];
$res = $conn->query("SELECT o.order_id, o.order_date as transaction_date, 
                           o.total_amount as amount, o.payment_amount, o.change_amount,
                           a.username as sales_by
                      FROM orders o
                      LEFT JOIN admin a ON o.admin_id = a.admin_id
                      ORDER BY o.order_date DESC LIMIT 5");

if ($res) {
    while ($row = $res->fetch_assoc()) {
        
        $order_id = $row['order_id'];
        $products_query = $conn->query("SELECT p.name as product_name, oi.quantity, p.product_id
                                       FROM order_item oi 
                                       JOIN products p ON oi.product_id = p.product_id 
                                       WHERE oi.order_id = $order_id");
        
        $products = [];
        if ($products_query) {
            while ($product = $products_query->fetch_assoc()) {
                $products[] = $product;
            }
        }
        
        $row['products'] = $products;
        $recent_sales[] = $row;
    }
} else {
    echo "Error fetching recent sales data: " . $conn->error;
}


$sold_products = [];
if(isset($_GET['show_product_sales']) && $_GET['show_product_sales'] == 'true') {
    
    $res = $conn->query("SELECT p.product_id, p.name as product_name, SUM(oi.quantity) as total_sold
                         FROM order_item oi
                         JOIN products p ON oi.product_id = p.product_id
                         GROUP BY p.product_id
                         ORDER BY total_sold DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $sold_products[] = $row;
        }
    } else {
        echo "Error fetching product sales data: " . $conn->error;
    }
}


$products = [];
$res = $conn->query("SELECT * FROM products");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $products[] = $row;
    }
} else {
    echo "Error fetching product data: " . $conn->error;
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Ederlyn's Bakery POS Dashboard</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        
        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
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

        
        .dashboard {
            flex-grow: 1;
            padding: 20px;
        }

        .stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat-box {
            width: 30%;
            padding: 20px;
            background: #d9eaff;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .stat-box h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #333;
        }

        .stat-box span {
            font-size: 24px;
            font-weight: bold;
            color: #4a90e2;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        th,
        td {
            padding: 12px 15px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background: #4a90e2;
            color: white;
            font-weight: bold;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .product-links {
            margin-top: 10px;
            font-size: 14px;
        }

        .product-links a {
            margin: 0 5px;
            color: #007bff;
            text-decoration: none;
        }

        .product-links a:hover {
            text-decoration: underline;
        }

        
        .clickable-stat {
            cursor: pointer;
            transition: transform 0.2s;
        }

        .clickable-stat:hover {
            transform: scale(1.05);
        }

        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 25px;
            border: 1px solid #ddd;
            width: 60%;
            max-width: 600px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .modal-header h2 {
            margin: 0;
            color: #4a90e2;
            font-size: 22px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.2s;
        }

        .close:hover,
        .close:focus {
            color: #333;
            text-decoration: none;
        }

        
        .monthly-report-container {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 20px;
            display: <?php echo isset($_GET['show_monthly_report']) ? 'block' : 'none'; ?>;
        }
        
        .month-selector {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .month-selector select {
            padding: 8px 15px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background-color: white;
            font-size: 16px;
        }
        
        .month-selector button {
            padding: 8px 15px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        
        .month-selector button:hover {
            background-color: #2c6db5;
        }
        
        .month-summary {
            background-color: #d9eaff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .month-summary h3 {
            margin: 0;
            color: #333;
            font-size: 20px;
        }
        
        .month-summary .total-value {
            font-size: 24px;
            font-weight: bold;
            color: #4a90e2;
            margin: 10px 0;
        }
        
        .month-summary .order-count {
            font-size: 16px;
            color: #555;
        }
        
        .clickable-date {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
        }
        
        .clickable-date:hover {
            color: #0056b3;
        }
        
        
        .daily-sales-container {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 20px;
            display: <?php echo isset($_GET['view_date']) ? 'block' : 'none'; ?>;
        }
        
        .daily-summary {
            background-color: #d9eaff;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            display: flex;
            justify-content: space-around;
        }
        
        .daily-summary-item {
            flex: 1;
        }
        
        .daily-summary-item h4 {
            margin: 0;
            color: #555;
            font-size: 16px;
        }
        
        .daily-summary-item .value {
            font-size: 22px;
            font-weight: bold;
            color: #4a90e2;
            margin: 10px 0;
        }
        
        .back-button {
            display: inline-block;
            margin-bottom: 15px;
            padding: 8px 15px;
            background-color: #4a90e2;
            color: white;
            border-radius: 4px;
            text-decoration: none;
        }
        
        .back-button i {
            margin-right: 5px;
        }
        
        .back-button:hover {
            background-color: #2c6db5;
        }

        
        .product-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .product-item {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 16px;
        }
        
        .product-item:last-child {
            border-bottom: none;
        }
        
        .product-item:hover {
            background-color: #f5f9ff;
        }
        
        .product-name {
            font-weight: 600;
            color: #333;
            flex-grow: 1;
        }
        
        .product-quantity {
            color: #4a90e2;
            font-weight: 600;
            background: #eaf2fd;
            padding: 5px 12px;
            border-radius: 15px;
            margin-left: 15px;
        }
        
        
        .product-sales-container {
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            margin-top: 20px;
            display: <?php echo isset($_GET['show_product_sales']) ? 'block' : 'none'; ?>;
        }
        
        .multiple-products {
            color: #007bff;
            cursor: pointer;
            text-decoration: underline;
            font-weight: 500;
        }
        
        .multiple-products:hover {
            color: #0056b3;
        }
        
        
        .products-total {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #eee;
            text-align: right;
            font-weight: 600;
            font-size: 16px;
        }
        
        
        .product-details-scrollable {
            max-height: 350px;
            overflow-y: auto;
        }
        
        
        .change-highlight {
            color: #28a745;
            font-weight: bold;
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
            
            .modal-content {
                width: 90%;
                margin: 15% auto;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li class="active"><i class="fas fa-chart-bar"></i> <a href="dashboard.php">Dashboard</a></li>
            <li><i class="fas fa-shopping-cart"></i> <a href="order.php">Order</a></li>
            <li><i class="fas fa-cubes"></i> <a href="inventory.php">Inventory</a></li>
            <li><i class="fas fa-sign-out-alt"></i> <a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="dashboard">
            <div class="stats">
                <div class="stat-box clickable-stat" id="total-sales-box" onclick="toggleMonthlyReport()">
                    <h2>Total Sale Amount</h2>
                    <span>₱<?php echo number_format($total_sales_amount, 2); ?></span>
                    <div class="product-links">
                        <a href="#">Click to view monthly sales report</a>
                    </div>
                </div>
                <div class="stat-box clickable-stat" id="total-products-box" onclick="toggleProductSales()">
                    <h2>Total Products Sales</h2>
                    <span><?php echo number_format($total_products_sold); ?> items</span>
                    <div class="product-links">
                        <a href="#">Click to view product sales details</a>
                    </div>
                </div>
                <div class="stat-box">
                    <h2>Total Products</h2>
                    <span>
                        <?php echo $total_products; ?>
                        <br>
                        Low Stock: <?php echo $low_stock; ?>
                    </span>
                </div>
            </div>

            
            <div class="monthly-report-container" id="monthly-report-section">
                <h2>Monthly Sales Report</h2>
                
                <div class="month-selector">
                    <form action="" method="GET">
                        <input type="hidden" name="show_monthly_report" value="true">
                        <select name="month">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php if (isset($report_month) && $report_month == $m) echo 'selected'; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $m, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <select name="year">
                            <?php 
                            $current_year = date('Y');
                            for ($y = $current_year - 2; $y <= $current_year; $y++): 
                            ?>
                                <option value="<?php echo $y; ?>" <?php if (isset($report_year) && $report_year == $y) echo 'selected'; ?>>
                                    <?php echo $y; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <button type="submit">View Report</button>
                    </form>
                </div>
                
                <div class="month-summary">
                    <h3>Sales Summary for <?php echo isset($month_name) ? $month_name . ' ' . $report_year : ''; ?></h3>
                    <div class="total-value">₱<?php echo isset($month_total_sales) ? number_format($month_total_sales, 2) : '0.00'; ?></div>
                    <div class="order-count">Total Orders: <?php echo isset($month_total_orders) ? $month_total_orders : '0'; ?></div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Total Orders</th>
                            <th>Total Sales</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($monthly_data) && !empty($monthly_data)): ?>
                            <?php foreach ($monthly_data as $date => $data): ?>
                                <tr>
                                    <td><?php echo date('F j, Y (l)', strtotime($date)); ?></td>
                                    <td><?php echo $data['order_count']; ?> orders</td>
                                    <td>₱<?php echo number_format($data['daily_total'], 2); ?></td>
                                    <td>
                                        <a href="?view_date=<?php echo $date; ?>" class="clickable-date">View Details</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4">No sales data available for this month</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            
            <div class="daily-sales-container" id="daily-sales-section">
                <?php if (isset($_GET['view_date'])): ?>
                    <a href="?show_monthly_report=true&month=<?php echo date('m', strtotime($_GET['view_date'])); ?>&year=<?php echo date('Y', strtotime($_GET['view_date'])); ?>" class="back-button">
                        <i class="fas fa-arrow-left"></i> Back to Monthly Report
                    </a>
                    
                    <h2>Daily Sales: <?php echo date('F j, Y (l)', strtotime($_GET['view_date'])); ?></h2>
                    
                    <div class="daily-summary">
                        <div class="daily-summary-item">
                            <h4>Total Orders</h4>
                            <div class="value"><?php echo isset($daily_order_count) ? $daily_order_count : '0'; ?></div>
                        </div>
                        <div class="daily-summary-item">
                            <h4>Total Revenue</h4>
                            <div class="value">₱<?php echo isset($daily_revenue) ? number_format($daily_revenue, 2) : '0.00'; ?></div>
                        </div>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th>Order Time</th>
                                <th>Products</th>
                                <th>Amount</th>
                                <th>Payment</th>
                                <th>Change</th>
                                <th>Sales By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($daily_sales)): ?>
                                <?php foreach ($daily_sales as $order): ?>
                                    <tr>
                                        <td><?php echo date('h:i A', strtotime($order['order_date'])); ?></td>
                                        <td>
                                            <?php 
                                            if (count($order['products']) > 0):
                                                if (count($order['products']) == 1):
                                                    $product = $order['products'][0];
                                                    echo htmlspecialchars($product['product_name']) . ' (' . $product['quantity'] . ' pcs)';
                                                else:
                                            ?>
                                                    <span class="multiple-products" onclick="showProductDetails(<?php echo htmlspecialchars(json_encode($order['products'])); ?>, '<?php echo htmlspecialchars($order['order_date']); ?>')">
                                                        <?php echo count($order['products']); ?> products - Click to view
                                                    </span>
                                            <?php
                                                endif;
                                            else:
                                                echo 'N/A';
                                            endif;
                                            ?>
                                        </td>
                                        <td>₱<?php echo number_format($order['total_amount'], 2); ?></td>
                                        <td>₱<?php echo number_format($order['payment_amount'] ?? $order['total_amount'], 2); ?></td>
                                        <td class="change-highlight">₱<?php echo number_format($order['change_amount'] ?? 0, 2); ?></td>
                                        <td><?php echo htmlspecialchars($order['sales_by'] ?? 'N/A'); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">No sales data available for this date</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            
            <div class="product-sales-container" id="product-sales-section">
                <h2>Product Sales Summary</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Total Quantity Sold</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (is_countable($sold_products) && count($sold_products) > 0) : ?>
                            <?php 
                            $verified_total = 0;
                            foreach ($sold_products as $product) : 
                                $verified_total += $product['total_sold'];
                            ?>
                                <tr><tr>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo number_format($product['total_sold']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr style="font-weight: bold; background-color: #d9eaff;">
                                <td>Total</td>
                                <td><?php echo number_format($verified_total); ?></td>
                            </tr>
                        <?php else : ?>
                            <tr>
                                <td colspan="2">No product sales data available</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <h3>Recent Transactions</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Products</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Change</th>
                        <th>Sales By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_sales as $sale): ?>
                        <tr>
                            <td><?php echo date('M j, Y h:i A', strtotime($sale['transaction_date'])); ?></td>
                            <td>
                                <?php 
                                if (count($sale['products']) > 0):
                                    if (count($sale['products']) == 1):
                                        $product = $sale['products'][0];
                                        echo htmlspecialchars($product['product_name']) . ' (' . $product['quantity'] . ' pcs)';
                                    else:
                                ?>
                                        <span class="multiple-products" onclick="showProductDetails(<?php echo htmlspecialchars(json_encode($sale['products'])); ?>, '<?php echo htmlspecialchars($sale['transaction_date']); ?>')">
                                            <?php echo count($sale['products']); ?> products - Click to view
                                        </span>
                                <?php
                                    endif;
                                else:
                                    echo 'N/A';
                                endif;
                                ?>
                            </td>
                            <td>₱<?php echo number_format($sale['amount'], 2); ?></td>
                            <td>₱<?php echo number_format($sale['payment_amount'] ?? $sale['amount'], 2); ?></td>
                            <td class="change-highlight">₱<?php echo number_format($sale['change_amount'] ?? 0, 2); ?></td>
                            <td><?php echo htmlspecialchars($sale['sales_by'] ?? 'N/A'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="productsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-title">Order Details</h2>
                <span class="close">&times;</span>
            </div>
            <div id="modal-date"></div>
            <div class="product-details-scrollable">
                <ul class="product-list" id="modal-products">
                    
                </ul>
            </div>
            <div class="products-total" id="modal-total"></div>
        </div>
    </div>

    <script>
        
        var modal = document.getElementById("productsModal");
        
        
        var span = document.getElementsByClassName("close")[0];
        
        
        function showProductDetails(products, orderDate) {
            
            const dateObj = new Date(orderDate);
            const formattedDate = dateObj.toLocaleString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: 'numeric',
                minute: 'numeric',
                hour12: true
            });
            
            document.getElementById("modal-date").innerHTML = "Order Date: " + formattedDate;
            
            
            const productsList = document.getElementById("modal-products");
            productsList.innerHTML = "";
            
            
            let totalItems = 0;
            let orderTotal = 0;
            
            products.forEach(product => {
                const item = document.createElement("li");
                item.className = "product-item";
                
                
                const nameSpan = document.createElement("span");
                nameSpan.className = "product-name";
                nameSpan.textContent = product.product_name;
                
                
                const quantitySpan = document.createElement("span");
                quantitySpan.className = "product-quantity";
                quantitySpan.textContent = product.quantity + " pcs";
                
                
                let priceElement = "";
                if (product.price_at_sale) {
                    const priceSpan = document.createElement("span");
                    priceSpan.className = "product-price";
                    priceSpan.textContent = "₱" + parseFloat(product.price_at_sale).toFixed(2);
                    item.appendChild(priceSpan);
                    
                    
                    const subtotal = product.quantity * product.price_at_sale;
                    orderTotal += subtotal;
                }
                
                
                item.appendChild(nameSpan);
                item.appendChild(quantitySpan);
                
               
                productsList.appendChild(item);
                
                
                totalItems += parseInt(product.quantity);
            });
            
            
            if (orderTotal > 0) {
                document.getElementById("modal-total").textContent = "Total: ₱" + orderTotal.toFixed(2) + " (" + totalItems + " items)";
                document.getElementById("modal-total").style.display = "block";
            } else {
                document.getElementById("modal-total").textContent = "Total Items: " + totalItems;
                document.getElementById("modal-total").style.display = "block";
            }
            
            
            modal.style.display = "block";
        }
        
        
        span.onclick = function() {
            modal.style.display = "none";
        }
        
        
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
        
        
        function toggleMonthlyReport() {
            window.location.href = "?show_monthly_report=true";
        }
        
        
        function toggleProductSales() {
            window.location.href = "?show_product_sales=true";
        }
    </script>
</body>

</html>