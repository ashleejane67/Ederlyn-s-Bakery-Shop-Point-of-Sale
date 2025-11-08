<?php

include('connect.php');

if (isset($_POST['save'])) {
    $product_name = mysqli_escape_string($con, $_POST['product_name']);
    $stock = mysqli_escape_string($con, $_POST['stock']);
    $price = mysqli_escape_string($con, $_POST['price']);

    
    $upload_dir = "uploads/"; 
    $image_path = "";

    if (isset($_FILES['upload_image']) && $_FILES['upload_image']['error'] == 0) {
        $filename = uniqid() . "_" . basename($_FILES['upload_image']['name']);
        $destination = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['upload_image']['tmp_name'], $destination)) {
            $image_path = $destination;
        } else {
            echo "<script>alert('Failed to upload image.')</script>";
            echo "<script>window.open('add-products.php','_self')</script>";
            exit; 
        }
    }

    $sql = "INSERT INTO products(name, quantity_in_stock, price, image_path) VALUES ('$product_name', '$stock', '$price', '$image_path')";
    $result = $con->query($sql);

    if ($result) {
        echo "<script>alert('Successfully Added!')</script>";
        echo "<script>window.open('inventory.php','_self')</script>";
    } else {
        echo "Error query: " . $con->error;
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add Product</title>
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            height: 100vh;
            background-color: #e6f2ff;
        }

        .sidebar {
            width: 250px;
            background: #4a90e2;
            color: white;
            padding: 20px;
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
        }

        .sidebar ul li {
            padding: 15px;
            cursor: pointer;
            border-radius: 5px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
        }

        .sidebar ul li i {
            margin-right: 10px;
            font-size: 18px;
            width: 24px;
            text-align: center;
        }

        .sidebar ul li:hover,
        .sidebar ul li.active {
            background: #2c6db5;
        }

        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #e6f2ff;
            border-bottom: 1px solid #ccc;
        }

        .brand {
            color: #990000;
            font-size: 20px;
            font-weight: bold;
        }

        .search-container {
            flex: 0 1 400px;
        }

        .search-input {
            width: 100%;
            padding: 8px 15px;
            border: none;
            border-radius: 20px;
            background-color: #c79440;
            color: white;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .admin-btn {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
        }

        .admin-icon {
            background-color: #333;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .content-area {
            display: flex;
            flex: 1;
            overflow: hidden;
            justify-content: center;
            align-items: center;
        }

        .modal-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2); 
            width: 90%;
            max-width: 400px;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            border-bottom: 1px solid #eee; 
            padding-bottom: 10px;
        }

        .modal-header h2 {
            color: #333;
            font-size: 22px; 
            margin: 0;
        }

        .close-btn {
            background: none;
            border: none;
            color: #aaa;
            font-size: 28px;
            cursor: pointer;
            opacity: 0.7; 
            transition: opacity 0.2s ease;
        }

        .close-btn:hover {
            opacity: 1;
        }

        .form-group {
            margin-bottom: 20px; 
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500; 
            color: #495057;
            font-size: 14px;
        }

        .form-control {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da; 
            border-radius: 6px; 
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.2s ease-in-out; 
        }

        .form-control:focus {
            border-color: #80bdff;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25); 
        }

        .btn-add {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            transition: background-color 0.2s ease;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); 
        }

        .btn-add:hover {
            background-color: #0056b3;
        }

        .upload-image {
            margin-top: 15px; 
        }

        .upload-image label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #495057;
            font-size: 14px;
        }

        .upload-image input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 16px;
            box-sizing: border-box;
        }
    </style>
</head>

<body>
    <div class="main-content">
        <div class="header">
            <h1 class="brand">Add Product</h1>
        </div>
        <div class="content-area">
            <div class="modal-bg">
                <div class="modal">
                    <div class="modal-header">
                        <h2>Add New Product</h2>
                        <button type="button" class="close-btn"
                            onclick="window.location.href='inventory.php'">&times;</button>
                    </div>
                    <form method="POST" action="" enctype="multipart/form-data"> <div class="form-group">
                            <label for="product_name">Product Name:</label>
                            <input type="text" class="form-control" name="product_name"
                                placeholder="e.g., Chocolate Cake" required>
                        </div>
                        <div class="form-group">
                            <label for="stock">Stock:</label>
                            <input type="text" class="form-control" name="stock" placeholder="e.g., 20pcs"
                                required>
                        </div>
                        <div class="form-group">
                            <label for="price">Price:</label>
                            <input type="number" class="form-control" name="price" placeholder="e.g., 50.00"
                                step="0.01" required>
                        </div>
                        <div class="form-group upload-image">
                            <label for="upload_image">Upload Image:</label>
                            <input type="file" class="form-control" name="upload_image">
                        </div>
                        <button type="submit" name="save" class="btn-add">Add Product</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>