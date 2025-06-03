<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include 'header.php';

$page = $_GET['page'] ?? 'home';

$isLoggedIn = isset($_SESSION['username']);

switch ($page) {
  case 'about_us':
        include 'pages/about_us.php';
        break;
    case 'profile':
        if ($isLoggedIn) {
            include 'pages/profile.php';
        } else {
            include 'pages/page404.php';
        }
        break;
    case 'product':
    case 'basket':
        if ($isLoggedIn) {
            include "pages/{$page}.php";
        } else {
            include 'pages/page404.php';
        }
        break;
    
    case 'login':
        include 'pages/login.php';
        break;
    case 'logout':
        include 'pages/logout.php';
        break;
    default:
        if ($page === 'home') {
            ?>
            <h1>🌿 Welcome to Spring Store – Nature in Every Detail</h1>
            <p>Discover eco-friendly products for your home, garden, and lifestyle.</p>

            <div class="home-highlights">
              <div class="highlight">
                <i class="fa-solid fa-leaf"></i>
                <p>Eco-friendly Selection</p>
              </div>
              <div class="highlight">
                <i class="fa-solid fa-truck-fast"></i>
                <p>Fast Delivery in Ukraine</p>
              </div>
              <div class="highlight">
                <i class="fa-solid fa-hand-holding-heart"></i>
                <p>Support Local Producers</p>
              </div>
            </div>

            <div class="home-page__buttons">
              <a href="main.php?page=products" class="btn">Start Shopping</a>
            </div>
            <?php
        } else {
            include 'pages/page404.php';
        }
        break;
}

include 'footer.php';
?>
