<?php
include('connect.php');


$upload_error = '';
$update_message = '';
$name = '';
$price = '';
$quantity_in_stock = '';
$current_image = '';


$upload_dir = "uploads/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


if (isset($_GET['id'])) {
    $product_id = mysqli_escape_string($con, $_GET['id']);
    $sql_products = "SELECT * FROM products WHERE product_id='$product_id'";
    $result_products = $con->query($sql_products);

    if ($result_products && $result_products->num_rows > 0) {
        $row_product = $result_products->fetch_assoc();
        $name = htmlspecialchars($row_product['name'], ENT_QUOTES);
        $price = htmlspecialchars($row_product['price'], ENT_QUOTES);
        $quantity_in_stock = htmlspecialchars($row_product['quantity_in_stock'], ENT_QUOTES);
        $current_image = $row_product['image_path'];
    } else {
        echo "<p class='error-message'>Product not found.</p>";
        exit();
    }
}


if (isset($_POST['update'])) {
    $name = mysqli_escape_string($con, $_POST['name']);
    $price = mysqli_escape_string($con, $_POST['price']);
    $quantity_in_stock = mysqli_escape_string($con, $_POST['quantity_in_stock']);
    $product_id = mysqli_escape_string($con, $_GET['id']);
    $image_path = $current_image; // Default to current image

    
    if (!empty($_FILES['image']['name'])) {
        $allowed_types = array('jpg', 'jpeg', 'png', 'gif');
        $image_name = basename($_FILES['image']['name']);
        $imageFileType = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
        $unique_filename = uniqid('IMG-', true) . '.' . $imageFileType;
        $target_file = $upload_dir . $unique_filename;

        
        $check = getimagesize($_FILES['image']['tmp_name']);
        if ($check !== false && in_array($imageFileType, $allowed_types)) {
            
            if ($current_image && file_exists($current_image) && strpos($current_image, 'http') !== 0) {
                @unlink($current_image);
            }

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            } else {
                $upload_error = "Error uploading image. Check directory permissions.";
            }
        } else {
            $upload_error = "Invalid image format. Only JPG, JPEG, PNG, and GIF files are allowed.";
        }
    }

    if (empty($upload_error)) {
        
        $sql = "UPDATE products SET name=?, price=?, quantity_in_stock=?, image_path=? WHERE product_id=?";
        $stmt = $con->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param("sddsi", $name, $price, $quantity_in_stock, $image_path, $product_id);
            $result = $stmt->execute();
            
            if ($result) {
                $update_message = "Product successfully updated!";
                
                
                $sql_products = "SELECT * FROM products WHERE product_id=?";
                $stmt_select = $con->prepare($sql_products);
                $stmt_select->bind_param("i", $product_id);
                $stmt_select->execute();
                $result_products = $stmt_select->get_result();
                
                if ($result_products && $result_products->num_rows > 0) {
                    $row_product = $result_products->fetch_assoc();
                    $name = htmlspecialchars($row_product['name'], ENT_QUOTES);
                    $price = htmlspecialchars($row_product['price'], ENT_QUOTES);
                    $quantity_in_stock = htmlspecialchars($row_product['quantity_in_stock'], ENT_QUOTES);
                    $current_image = $row_product['image_path'];
                }
                
            } else {
                $upload_error = "Error updating product: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $upload_error = "Error preparing statement: " . $con->error;
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Product</title>
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f0f8ff;
        }

        .edit-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            width: 90%;
            max-width: 500px;
        }

        .edit-container h2 {
            color: #4682B4;
            margin-bottom: 25px;
            font-size: 24px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
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
            border-color: #4682B4;
            outline: none;
        }

        .btn-update {
            background-color: #4CAF50;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.2s ease-in-out;
            width: 100%;
        }

        .btn-update:hover {
            background-color: #45a049;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
            font-size: 0.9em;
        }

        .success-message {
            color: green;
            margin-bottom: 10px;
            font-size: 0.9em;
        }

        .image-preview {
            text-align: center;
            margin-bottom: 15px;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.15);
        }

        .upload-image input[type="file"] {
            padding: 8px;
        }
        
        .back-button {
            background-color: #6c757d;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            margin-top: 15px;
            text-decoration: none;
            display: inline-block;
        }
        
        .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>

<body>
    <div class="edit-container">
        <h2>Edit Product</h2>

        <?php if (!empty($upload_error)): ?>
            <p class="error-message"><?php echo $upload_error; ?></p>
        <?php endif; ?>

        <?php if (!empty($update_message)): ?>
            <p class="success-message"><?php echo $update_message; ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name:</label>
                <input type="text" id="name" class="form-control" name="name" placeholder="Enter product name"
                    value="<?php echo $name; ?>" required>
            </div>

            <div class="form-group">
                <label for="price">Price:</label>
                <input type="number" id="price" class="form-control" name="price" placeholder="Enter price"
                    value="<?php echo $price; ?>" step="0.01" required>
            </div>

            <div class="form-group">
                <label for="quantity_in_stock">Quantity in Stock:</label>
                <input type="number" id="quantity_in_stock" class="form-control" name="quantity_in_stock"
                    placeholder="Enter quantity" value="<?php echo $quantity_in_stock; ?>" required>
            </div>

            <?php if ($current_image): ?>
                <div class="form-group image-preview">
                    <label>Current Image:</label>
                    <img src="<?php echo htmlspecialchars($current_image); ?>" alt="Current Product Image" onerror="this.src='uploads/placeholder.png';">
                </div>
            <?php endif; ?>

            <div class="form-group upload-image">
                <label for="image">Change Image:</label>
                <input type="file" class="form-control" name="image" accept="image/jpeg,image/png,image/gif,image/jpg">
                <small class="form-text text-muted">Only JPG, JPEG, PNG, and GIF files are allowed.</small>
            </div>

            <button type="submit" name="update" class="btn-update">Update Product</button>
            <a href="inventory.php" class="back-button" style="display: block; text-align: center; margin-top: 15px;">Back to Inventory</a>
        </form>
    </div>

</body>

</html>