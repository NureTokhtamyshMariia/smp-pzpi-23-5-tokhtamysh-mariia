<?php
require_once __DIR__ . '/../db/control.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
        $item_id = (int)$_POST['item_id'];
        $quantity = (int)$_POST['quantity'];

        if ($quantity > 0) {
            eco_add_product_to_cart($item_id, $quantity);  

            $items = eco_get_all_products(); 
            foreach ($items as $item) {
                if ($item['id'] === $item_id) {
                    $_SESSION['flash_messages'][] = "Item '{$item['name']}' added to cart!";
                    break;
                }
            }
        }
    }
}
$items = eco_get_all_products();  
;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="../styles.css" />
  <link rel="stylesheet" href="../css/product.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet" />
  <title>Spring Shop</title>
</head>
<body>
<div id="flash-messages-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; max-width: 300px;"></div>
<?php if (isset($_SESSION['flash_message'])): ?>
  <div id="flash-message">
    <?php
      echo htmlspecialchars($_SESSION['flash_message']);
      unset($_SESSION['flash_message']);
    ?>
  </div>
<?php endif; ?>

<nav class="navbar">
  <a href="../index.php" class="nav-item">
    <i class="fa-solid fa-house"></i>
    <span>Home</span>
  </a>
  <div class="separator"></div>
  <a href="#" class="nav-item">
    <i class="fa-solid fa-rectangle-list"></i>
    <span>Products</span>
  </a>
  <div class="separator"></div>
  <a href="basket.php" class="nav-item">
    <i class="fa-solid fa-cart-shopping"></i>
    <span>Cart</span>
  </a>
</nav>

<main class="products">
  <h1 class="products__title">🛍️ Choose Items</h1>
  <div class="products__grid">
    <?php foreach ($items as $item): ?>
    <div class="product-card">
      <form method="post" action="product.php" class="product-form">
        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>" />
        <div class="product-card__header">
          <div class="product-card__name"><?php echo htmlspecialchars($item['name']); ?></div>
          <div class="product-card__price"><?php echo number_format($item['price'], 2); ?> UAH</div>
        </div>
        <div class="product-card__input-row">
          <div class="product-card__input-group">
            <label for="quantity_<?php echo $item['id']; ?>" class="product-card__label">Quantity:</label>
            <input type="number" id="quantity_<?php echo $item['id']; ?>" name="quantity" value="1" min="1" max="99" class="product-card__input" />
          </div>
          <div class="product-card__submit">
            <button type="submit" class="product-card__button">Add to Cart</button>
          </div>
        </div>
      </form>
    </div>
    <?php endforeach; ?>
  </div>
  <div class="products__actions">
    <a href="basket.php" class="btn btn--primary">Go to Cart</a>
  </div>
</main>

<footer class="footer">
  <div class="footer__nav">
    <a href="../index.php" class="footer__link">Home</a> |
    <a href="#" class="footer__link">Items</a> |
    <a href="../pages/basket.php" class="footer__link">Cart</a> |
    <a href="../pages/about_us.php" class="footer__link">About us</a>
  </div>
</footer>

<script>
function showFlashMessage(message) {
  const container = document.getElementById('flash-messages-container');
  container.style.display = 'flex';

  const msg = document.createElement('div');
  msg.className = 'flash-message';
  msg.textContent = message;

  container.prepend(msg);

  msg.style.opacity = '0';
  msg.style.transform = 'translateX(100%)';
  setTimeout(() => {
    msg.style.transition = 'opacity 0.5s ease, transform 0.3s ease';
    msg.style.opacity = '1';
    msg.style.transform = 'translateX(0)';
  }, 10);

  setTimeout(() => {
    msg.style.opacity = '0';
    msg.style.transform = 'translateX(100%)';
    setTimeout(() => {
      msg.remove();
      if (container.children.length === 0) {
        container.style.display = 'none';
      }
    }, 500);
  }, 2000);
}

<?php if (!empty($_SESSION['flash_messages'])): ?>
  const messagesFromPHP = <?php echo json_encode($_SESSION['flash_messages']); ?>;
  <?php unset($_SESSION['flash_messages']); ?>
  
  messagesFromPHP.forEach(msg => {
    showFlashMessage(msg);
  });
<?php endif; ?>
</script>
</body>
</html>
