<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Spring Store</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
  <link rel="stylesheet" href="css/cart.css" />
   <link rel="stylesheet" href="css/product.css" />
</head>
<body>
<nav class="navbar">
  <a href="?page=home" class="nav-item">
    <i class="fa-solid fa-house"></i>
    <span>Home</span>
  </a>
  <div class="separator"></div>

  <a href="?page=about_us" class="nav-item">
    <i class="fa-solid fa-circle-info"></i>
    <span>About Us</span>
  </a>
  <div class="separator"></div>

  <a href="?page=product" class="nav-item">
    <i class="fa-solid fa-rectangle-list"></i>
    <span>Products</span>
  </a>
  <div class="separator"></div>

  <?php if (isset($_SESSION['username'])): ?>
    <a href="?page=basket" class="nav-item">
      <i class="fa-solid fa-cart-shopping"></i>
      <span>Cart</span>
    </a>
    <div class="separator"></div>

    <a href="?page=profile" class="nav-item">
      <i class="fa-solid fa-user"></i>
      <span>Profile</span>
    </a>
    <div class="separator"></div>

    <a href="?page=logout" class="nav-item">
      <i class="fa-solid fa-right-from-bracket"></i>
      <span>Logout</span>
    </a>
  <?php else: ?>
    <a href="?page=login" class="nav-item">
      <i class="fa-solid fa-right-to-bracket"></i>
      <span>Login</span>
    </a>
  <?php endif; ?>
</nav>

<main class="home-page">
