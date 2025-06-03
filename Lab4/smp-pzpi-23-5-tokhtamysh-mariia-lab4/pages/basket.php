<?php
require_once __DIR__ . '/../db/control.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'remove' && isset($_POST['item_id'])) {
            $cartItemId = (int)$_POST['item_id'];
            eco_modify_cart_item($cartItemId, 0);
        } elseif ($_POST['action'] === 'checkout') {
            $database = eco_get_database();
            $currentSessionId = session_id();
            $deleteStmt = $database->prepare('DELETE FROM cart_items WHERE session_id = :session_id');
            $deleteStmt->bindValue(':session_id', $currentSessionId, SQLITE3_TEXT);
            $deleteStmt->execute();

            $_SESSION['checkout_success'] = true;

            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    if (isset($_POST['item_id'], $_POST['quantity'])) {
        $item_id = (int)$_POST['item_id'];
        $quantity = (int)$_POST['quantity'];
        if ($item_id > 0 && $quantity > 0) {
           eco_add_product_to_cart($item_id, $quantity);

        }
    }
}

$items = eco_get_all_products();
$cartItemsList = eco_get_cart_contents();
$cartTotalAmount = eco_calculate_cart_total();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="../styles.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet" />
    <title>Spring Shop</title>
</head>
<body>


<main class="cart-page eco-container">
    <h1 class="eco-title">🛒 Your Cart</h1>

    <?php if (isset($_SESSION['checkout_success']) && $_SESSION['checkout_success']): ?>
        <div class="eco-success-box">
            <p>🌿 Thank you for shopping with us!</p>
            <a href="?page=product" class="eco-btn">Start New Shopping</a>
        </div>
        <?php unset($_SESSION['checkout_success']); ?>

    <?php elseif (!empty($cartItemsList)): ?>
        <div class="cart-table eco-card">
            <div class="cart-table__header eco-header-row">
                <div class="cart-table__cell">#</div>
                <div class="cart-table__cell">Item</div>
                <div class="cart-table__cell">Price</div>
                <div class="cart-table__cell">Qty</div>
                <div class="cart-table__cell">Total</div>
                <div class="cart-table__cell">Remove</div>
            </div>

            <?php foreach ($cartItemsList as $itemIndex => $cartItem): ?>
                <div class="cart-table__row <?php echo $itemIndex % 2 === 0 ? 'eco-row-even' : 'eco-row-odd'; ?>">
                    <div class="cart-table__cell"><?php echo $itemIndex + 1; ?></div>
                    <div class="cart-table__cell"><?php echo htmlspecialchars($cartItem['name']); ?></div>
                    <div class="cart-table__cell"><?php echo number_format($cartItem['price'], 2); ?> ₴</div>
                    <div class="cart-table__cell"><?php echo $cartItem['quantity']; ?></div>
                    <div class="cart-table__cell"><?php echo number_format($cartItem['price'] * $cartItem['quantity'], 2); ?> ₴</div>
                    <div class="cart-table__cell">
                        <form method="post" action="">
                            <input type="hidden" name="action" value="remove" />
                            <input type="hidden" name="item_id" value="<?php echo $cartItem['id']; ?>" />
                            <button type="submit" class="eco-btn-small"><i class="fa-solid fa-trash"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>

            <div class="cart-table__footer eco-footer-row">
                <div class="cart-table__cell">Total</div>
                <div class="cart-table__cell"></div>
                <div class="cart-table__cell"></div>
                <div class="cart-table__cell"></div>
                <div class="cart-table__cell"><strong><?php echo number_format($cartTotalAmount, 2); ?> ₴</strong></div>
                <div class="cart-table__cell"></div>
            </div>
        </div>

        <div class="cart-page__actions eco-action-buttons">
          <a href="?page=product" class="eco-btn">Cancel</a>
          <form method="post" action="">
              <input type="hidden" name="action" value="checkout" />
              <button type="submit" class="eco-btn">Checkout</button>
          </form>
        </div>

    <?php else: ?>
        <div class="cart-page__empty eco-empty-box">
            <p>Your cart is empty 🧺</p>
            <a href="product.php" class="eco-btn">Go to Products</a>
        </div>
    <?php endif; ?>
</main>

</body>
</html>
