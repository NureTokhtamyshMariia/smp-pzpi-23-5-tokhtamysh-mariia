<?php

$DB_FILE = __DIR__ . '/db1.db';
session_start();

function eco_init_database()
{
    global $DB_FILE;
    $db = new SQLite3($DB_FILE);

    $result = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='products'");
    if (!$result->fetchArray()) {
        $db->exec('
            CREATE TABLE products (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                price DECIMAL(10,2) NOT NULL
            )
        ');

        $db->exec('
            CREATE TABLE cart_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                session_id TEXT NOT NULL,
                product_id INTEGER NOT NULL,
                quantity INTEGER NOT NULL DEFAULT 1,
                FOREIGN KEY (product_id) REFERENCES products(id)
            )
        ');

        $db->exec("INSERT INTO products (name, price) VALUES
            ('Cotton Eco Bag', 120),
            ('Organic Basil Seeds', 35),
            ('Bamboo Toothbrush', 55),
            ('Home Compost Bin (10L)', 320),
            ('Natural Lavender Soap', 65),
            ('Coconut Fiber Pot', 40),
            ('Loofah Sponge Soap', 45)
        ");
    }

    return $db;
}


function eco_get_all_products()
{
    $db = eco_get_database();

    $result = $db->query('SELECT * FROM products ORDER BY name COLLATE NOCASE ASC');

    $products = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $products[$row['id']] = $row;
    }

    return $products;
}



function eco_get_database()
{
    static $db = null;
    if ($db === null) {
        $db = eco_init_database();
    }
    return $db;
}

function eco_get_cart_contents()
{
    $db = eco_get_database();
    $session_id = session_id();

    $query = $db->prepare('
        SELECT c.id, 
            c.product_id, 
            c.quantity, 
            p.name, 
            p.price 
        FROM cart_items c
        JOIN products p ON c.product_id = p.id
        WHERE c.session_id = :session_id
        ORDER BY p.name ASC
    ');
    $query->bindValue(':session_id', $session_id, SQLITE3_TEXT);
    $result = $query->execute();

    $cart_items = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $cart_items[] = $row;
    }

    return $cart_items;
}


function eco_add_product_to_cart($product_id, $quantity)
{
    $quantity = (int)$quantity;
    if ($quantity < 1) {

        return;
    }
    if ($quantity > 100) {
        $quantity = 100; 
    }

    $db = eco_get_database();
    $session_id = session_id();

    $query = $db->prepare('
        SELECT id, quantity FROM cart_items 
        WHERE session_id = :session_id AND product_id = :product_id
    ');
    $query->bindValue(':session_id', $session_id, SQLITE3_TEXT);
    $query->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
    $result = $query->execute();

    $existing_item = $result->fetchArray(SQLITE3_ASSOC);

    if ($existing_item) {
        $new_quantity = $existing_item['quantity'] + $quantity;
        if ($new_quantity > 100) {
            $new_quantity = 100;
        }

        $query = $db->prepare('
            UPDATE cart_items 
            SET quantity = :quantity 
            WHERE id = :id
        ');
        $query->bindValue(':quantity', $new_quantity, SQLITE3_INTEGER);
        $query->bindValue(':id', $existing_item['id'], SQLITE3_INTEGER);
        $query->execute();
    } else {
       
        $query = $db->prepare('
            INSERT INTO cart_items (session_id, product_id, quantity)
            VALUES (:session_id, :product_id, :quantity)
        ');
        $query->bindValue(':session_id', $session_id, SQLITE3_TEXT);
        $query->bindValue(':product_id', $product_id, SQLITE3_INTEGER);
        $query->bindValue(':quantity', $quantity, SQLITE3_INTEGER);
        $query->execute();
    }
}


function eco_modify_cart_item($item_id, $quantity)
{
    $db = eco_get_database();

    if ($quantity <= 0) {
        $query = $db->prepare('DELETE FROM cart_items WHERE id = :id');
        $query->bindValue(':id', $item_id, SQLITE3_INTEGER);
    } else {
        $query = $db->prepare('UPDATE cart_items SET quantity = :quantity WHERE id = :id');
        $query->bindValue(':quantity', $quantity, SQLITE3_INTEGER);
        $query->bindValue(':id', $item_id, SQLITE3_INTEGER);
    }

    $query->execute();
}

function eco_calculate_cart_total()
{
    $cart_items = eco_get_cart_contents();
    $total = 0;

    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    return $total;
}
