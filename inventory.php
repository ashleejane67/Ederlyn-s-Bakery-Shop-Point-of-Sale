<?php
include('connect.php');


$error = "";


if (!$con) {
    $error = "Database connection error: " . mysqli_connect_error();
} else {
    
    $sql_select_products = "SELECT product_id, name, quantity_in_stock FROM products ORDER BY name ASC";
    $result_select_products = $con->query($sql_select_products);

    
    if (!$result_select_products) {
        $error = "Error fetching products: " . $con->error;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Inventory</title>
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

        .main-content header {
            background-color: #e6f2ff;
            padding: 15px 20px;
            border-bottom: 1px solid #ccc;
        }

        .main-content header h2 {
            margin: 0;
            color: #333;
        }

        .inventory-container {
            padding: 20px;
            overflow-y: auto;
            max-height: calc(100vh - 60px);
        }

        .inventory-header {
            font-size: 30px;
            margin-bottom: 20px;
            color: #333;
        }

        .inventory-actions {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .inventory-button {
            padding: 10px 15px;
            background-color: #4a90e2;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .inventory-button:hover {
            background-color: #2c6db5;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #d9eaff;
            border-radius: 8px;
            overflow: hidden;
        }

        .inventory-table th {
            background-color: #4a90e2;
            color: white;
            padding: 15px;
            text-align: center;
        }

        .inventory-table td {
            padding: 15px;
            border-bottom: 1px solid #b4d4ff;
            text-align: center;
        }

        .inventory-table tr:nth-child(even) {
            background-color: #cfe5ff;
        }

        .action-button {
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            margin: 0 5px;
            font-size: 14px;
            display: inline-block;
        }

        .edit-button {
            background-color: #2196F3;
        }

        .delete-button {
            background-color: #ff6b6b;
        }

        .edit-button:hover {
            background-color: #0b7dda;
        }

        .delete-button:hover {
            background-color: #e55353;
        }

        .in-stock {
            background-color: #00e676;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }

        .low-stock {
            background-color: #ffb74d;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }

        .out-of-stock {
            background-color: #ff5252;
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            display: inline-block;
        }

        .error-message {
            background-color: #ff5252;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .no-data {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
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

            .inventory-table th:nth-child(3),
            .inventory-table td:nth-child(3) {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Dashboard</h2>
        <ul>
            <li><i class="fas fa-chart-bar"></i> <a href="dashboard.php">Dashboard</a></li>
            <li><i class="fas fa-shopping-cart"></i> <a href="order.php">Order</a></li>
            <li class="active"><i class="fas fa-cubes"></i> <a href="inventory.php">Inventory</a></li>
            <li><i class="fas fa-sign-out-alt"></i> <a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header>
            <h2>Edelyn's Bakery Inventory</h2>
        </header>
        <div class="inventory-container">
            <div class="inventory-header">
                <h1>Product Management</h1>
            </div>
            <div class="inventory-actions">
                <a href="add-products.php" class="inventory-button">Add New Product</a>
            </div>
            <?php if ($error): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php else: ?>
                <table class="inventory-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Stock</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_select_products && $result_select_products->num_rows > 0) {
                            while($row = $result_select_products->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>".$row['name']."</td>";
                                echo "<td>".$row['quantity_in_stock']."</td>";
                                echo "<td class='stock-status'>";
                                if ($row['quantity_in_stock'] <= 0) {
                                    echo "<span class='out-of-stock'>Out of Stock</span>";
                                } elseif ($row['quantity_in_stock'] <= 10) {
                                    echo "<span class='low-stock'>Low Stock</span>";
                                } else {
                                    echo "<span class='in-stock'>In Stock</span>";
                                }
                                echo "</td>";
                                echo "<td class='action-links'>";
                                echo "<a href='edit-products.php?id=".$row['product_id']."' class='action-button edit-button'>Update</a> ";
                                echo "<a href='delete-products.php?id=".$row['product_id']."' class='action-button delete-button' onclick=\"return confirm('Are you sure you want to delete this product?')\">Delete</a>";
                                echo "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='4' class='no-data'>No products found in inventory.</td></tr>";
                        }

                        
                        if ($result_select_products) {
                            $result_select_products->free();
                        }

                        
                        if ($con) {
                            $con->close();
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
</body>
</html>