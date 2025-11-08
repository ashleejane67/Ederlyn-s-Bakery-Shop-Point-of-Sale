<?php

include('connect.php');

if (isset($_GET['id'])) {
    $product_id = mysqli_escape_string($con, $_GET['id']);

    
    $sql_select = "SELECT name FROM products WHERE product_id='$product_id'";
    $result_select = $con->query($sql_select);

    if ($result_select && $result_select->num_rows > 0) {
        $row = $result_select->fetch_assoc();
        $product_name = htmlspecialchars($row['name']); 
    } else {
        echo "<p>Product not found.</p>";
        exit();
    }
} else {
    echo "<p>Invalid product ID.</p>";
    exit();
}

if (isset($_POST['delete'])) {
    $delete_id = mysqli_escape_string($con, $_POST['product_id']);
    $sql_delete = "DELETE FROM products WHERE product_id='$delete_id'";
    if ($con->query($sql_delete) === TRUE) {
        echo "<script>alert('Product deleted successfully!')</script>";
        echo "<script>window.location.href = 'inventory.php';</script>"; 
        exit();
    } else {
        echo "Error deleting product: " . $con->error;
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delete Product</title>
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

        .confirmation-container {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            text-align: center;
            width: 90%;
            max-width: 450px;
        }

        .confirmation-container h2 {
            color: #4682B4; 
            margin-bottom: 20px;
            font-size: 24px;
        }

        .confirmation-message {
            color: #555;
            margin-bottom: 25px;
            font-size: 16px;
        }

        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .button {
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
        }

        .yes-button {
            background-color: #ADD8E6; 
            color: #191970; 
        }

        .no-button {
            background-color: #B0C4DE; 
            color: #2F4F4F; 
        }

        .yes-button:hover {
            background-color: #87CEEB; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transform: scale(1.02);
        }

        .no-button:hover {
            background-color: #778899; 
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            transform: scale(1.02);
        }
    </style>
</head>

<body>

    <div class="confirmation-container">
        <h2 style="margin-top: 0;">Delete Product</h2>
        <p class="confirmation-message">Are you sure you want to delete <strong><?php echo $product_name; ?></strong>?</p>
        <form method="post" class="button-container">
            <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
            <button type="submit" name="delete" class="button yes-button">Yes</button>
            <button type="button" class="button no-button" onclick="window.location.href='inventory.php'">No</button>
        </form>
    </div>

</body>

</html>