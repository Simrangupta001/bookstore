<?php
include 'config.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('location:login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Add to cart
if (isset($_POST['add_to_cart'])) {
    $pro_name     = mysqli_real_escape_string($conn, $_POST['product_name']);
    $pro_price    = (float) $_POST['product_price'];
    $pro_quantity = max(1, (int) $_POST['product_quantity']); // ensure >= 1
    $pro_image    = mysqli_real_escape_string($conn, $_POST['product_image']);

    // Check if product is already in cart
    $stmt = $conn->prepare("SELECT * FROM cart WHERE name = ? AND user_id = ?");
    $stmt->bind_param("si", $pro_name, $user_id);
    $stmt->execute();
    $check = $stmt->get_result();

    if ($check->num_rows > 0) {
        $message[] = 'Already added to cart!';
    } else {
        $stmt = $conn->prepare("INSERT INTO cart (user_id, name, price, quantity, image) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isdss", $user_id, $pro_name, $pro_price, $pro_quantity, $pro_image);

        if ($stmt->execute()) {
            $message[] = 'Product added to cart!';
        } else {
            $message[] = 'Failed to add product. Try again!';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Shop Page</title>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom Styles -->
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="home.css">
</head>
<body>
  
<?php include 'user_header.php'; ?>

<section class="products_cont">
  <div class="pro_box_cont">
    <?php
    $select_products = $conn->query("SELECT * FROM products");

    if ($select_products->num_rows > 0) {
        while ($fetch_products = $select_products->fetch_assoc()) {
    ?>
        <form action="" method="post" class="pro_box">
          <img src="./uploaded_img/<?php echo htmlspecialchars($fetch_products['image']); ?>" alt="">
          <h3><?php echo htmlspecialchars($fetch_products['name']); ?></h3>
          <p>Rs. <?php echo number_format($fetch_products['price'], 2); ?>/-</p>
        
          <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($fetch_products['name']); ?>">
          <input type="number" name="product_quantity" min="1" value="1">
          <input type="hidden" name="product_price" value="<?php echo $fetch_products['price']; ?>">
          <input type="hidden" name="product_image" value="<?php echo htmlspecialchars($fetch_products['image']); ?>">

          <button type="submit" name="add_to_cart" class="product_btn">Add to Cart</button>
        </form>
    <?php
        }
    } else {
        echo '<p class="empty">No Products Added Yet!</p>';
    }
    ?>
  </div>
</section>

<?php include 'footer.php'; ?>

<script src="https://kit.fontawesome.com/eedbcd0c96.js" crossorigin="anonymous"></script>
<script src="script.js"></script>

</body>
</html>
