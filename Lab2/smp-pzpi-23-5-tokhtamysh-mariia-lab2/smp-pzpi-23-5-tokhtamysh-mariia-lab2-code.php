<?php

class Restrictions
{
    public const MIN_AGE = 7;
    public const MAX_AGE = 150;

    public const MIN_QUANTITY = 0;
    public const MAX_QUANTITY = 99;

    public const MAX_TITLE_LENGTH_IN_BILL = 20;
}

class Product
{
    private string $title;
    private float $cost;

    public function __construct(string $title, float $cost)
    {
        $this->title = $title;
        $this->cost = $cost;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCost(): float
    {
        return $this->cost;
    }
}

class Cart
{
    private array $items = [];

    public function addProduct(int $productId, int $quantity): void
    {
        if ($quantity <= 0) {
            unset($this->items[$productId]);
            echo "Товар видалено з кошика.\n";
        } else {
            $currentQuantity = $this->items[$productId] ?? 0;
            $newQuantity = $currentQuantity + $quantity;

            if ($newQuantity > Restrictions::MAX_QUANTITY) {
                echo "ПОМИЛКА! Загальна кількість не може перевищувати " . Restrictions::MAX_QUANTITY . ".\n";
            } else {
                $this->items[$productId] = $newQuantity;
            }
        }
    }


    public function getItems(): array
    {
        return $this->items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}



class UserProfile
{
    private ?string $name = null;
    private ?int $age = null;

    public function setName(string $name): bool
    {
        $isValid = preg_match("/^[А-Яа-яЁёЇїІіЄєҐґA-Za-z'’\- ]+$/u", $name);
        $hasLetters = preg_match("/[А-Яа-яЁёЇїІіЄєҐґA-Za-z]/u", $name);
        if ($isValid && $hasLetters) {
            $this->name = $name;
            return true;
        }
        return false;
    }

    public function setAge(int $age): bool
    {
        if ($age >= Restrictions::MIN_AGE && $age <= Restrictions::MAX_AGE) {
            $this->age = $age;
            return true;
        }
        return false;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getAge(): ?int
    {
        return $this->age;
    }
}

class Store
{
    private array $products = [];

    private Cart $cart;
    private UserProfile $userProfile;
    private string $productFile;

    public function __construct()
    {
        $this->cart = new Cart();
        $this->userProfile = new UserProfile();

        $this->productFile = __DIR__ . DIRECTORY_SEPARATOR . 'product.json';
        $this->loadProductsFromFile();

    }


    private function loadProductsFromFile(): void
    {
        $jsonFile = __DIR__ . DIRECTORY_SEPARATOR . 'product.json';

        if (!file_exists($jsonFile)) {
            die("Помилка: Файл продуктів {$jsonFile} не знайдено\n");
        }

        $jsonData = file_get_contents($jsonFile);
        $productArray = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            die("Помилка при розборі JSON: " . json_last_error_msg() . "\n");
        }

        foreach ($productArray as $id => $data) {
            if (isset($data['name'], $data['price'])) {
                $this->products[(int)$id] = new Product($data['name'], (float)$data['price']);
            }
        }
    }


    public function run(): void
    {
        while (true) {
            $this->showMainMenu();
            $input = trim(fgets(STDIN));
            echo "\n";

            switch ($input) {
                case "1":
                    $this->selectProducts();
                    break;
                case "2":
                    $this->displayFinalBill();
                    break;
                case "3":
                    $this->configureUserProfile();
                    break;
                case "0":
                    echo "До побачення!\n";
                    return;
                default:
                    echo "ПОМИЛКА! Перевірте введення і спробуйте ще раз.\n";
            }

            echo "\n";
        }
    }

    private function showMainMenu(): void
    {
        echo "################################\n";
        echo "# ПРОДОВОЛЬЧИЙ МАГАЗИН \"ВЕСНА\" #\n";
        echo "################################\n";
        echo "1 Вибрати товари\n";
        echo "2 Отримати підсумковий рахунок\n";
        echo "3 Налаштувати свій профіль\n";
        echo "0 Вийти з програми\n";
        echo "Введіть команду: ";
    }

    private function getLongestTitleLength(array $products): int
    {
        $max = 0;
        foreach ($products as $product) {
            preg_match_all('/./us', $product->getTitle(), $chars);
            $length = count($chars[0]);
            if ($length > $max) {
                $max = $length;
            }
        }
        return $max;
    }

    private function printProductList(array $products, int $maxLen): void
    {
        echo "\n№  НАЗВА" . str_repeat(" ", $maxLen - 5 + 2) . "ЦІНА\n";
        foreach ($products as $id => $product) {
            preg_match_all('/./us', $product->getTitle(), $chars);
            $padding = str_repeat(" ", $maxLen - count($chars[0]) + 2);
            printf("%-2d %s%s%.2f\n", $id, $product->getTitle(), $padding, $product->getCost());
        }
        echo "   -----------\n";
        echo "0  ПОВЕРНУТИСЯ\n";
    }

    private function handleProductSelection(int $productId, int $maxLen): void
    {
        $product = $this->products[$productId];
        echo "Вибрано: {$product->getTitle()}\n";
        echo "Введіть кількість, штук: ";
        $amount = (int)trim(fgets(STDIN));

        $this->cart->addProduct($productId, $amount);

        $this->printCartContents($maxLen);
    }
    
    private function printCartContents(int $maxLen): void
    {
        echo "У КОШИКУ:\nНАЗВА" . str_repeat(" ", $maxLen - 5 + 2) . "КІЛЬКІСТЬ\n";
        foreach ($this->cart->getItems() as $productId => $quantity) {
            $title = $this->products[$productId]->getTitle();
            preg_match_all('/./us', $title, $chars);
            $padding = str_repeat(" ", $maxLen - count($chars[0]) + 2);
            echo $title . $padding . $quantity . "\n";
        }
        echo "\n";
    }

    private function selectProducts(): void
    {
        $products = $this->products;
        $maxLen = $this->getLongestTitleLength($products);

        while (true) {
            $this->printProductList($products, $maxLen);

            echo "Виберіть товар: ";
            $input = trim(fgets(STDIN));

            if (!ctype_digit($input)) {
                echo "ПОМИЛКА! НЕОБХІДНО ВВЕСТИ ЧИСЛО\n\n";
                continue;
            }

            $selectedId = (int)$input;

            if ($selectedId === 0) {
                break;
            }

            if (!isset($products[$selectedId])) {
                echo "ПОМИЛКА! ВКАЗАНО НЕПРАВИЛЬНИЙ НОМЕР ТОВАРУ\n";
                continue;
            }

            $this->handleProductSelection($selectedId, $maxLen);
        }
    }

    private function displayFinalBill(): void
    {
        $basket = $this->cart->getItems();
        if (empty($basket)) {
            echo "КОШИК ПОРОЖНІЙ\n";
            return;
        }

        $nameW = max(5, $this->getLongestTitleLength($this->products));
        $priceW = 4;
        $qtyW = 9;
        $sumW = 8;

        foreach ($basket as $k => $qty) {
            $cost = $this->products[$k]->getCost();
            $total = $qty * $cost;

            $priceW = max($priceW, strlen((string)$cost));
            $qtyW = max($qtyW, strlen((string)$qty));
            $sumW = max($sumW, strlen((string)$total));
        }

        echo "№  НАЗВА"
            . str_repeat(" ", $nameW - 5 + 2)
            . "ЦІНА" . str_repeat(" ", $priceW - 4 + 2)
            . "КІЛЬКІСТЬ" . str_repeat(" ", $qtyW - 9 + 2)
            . "ВАРТІСТЬ\n";

        $line = 1;
        $sumAll = 0;

        foreach ($basket as $k => $qty) {
            $title = $this->products[$k]->getTitle();
            $cost = $this->products[$k]->getCost();
            $subtotal = $qty * $cost;
            $sumAll += $subtotal;

            preg_match_all('/./us', $title, $chars);
            $namePad = str_repeat(" ", $nameW - count($chars[0]) + 2);
            $costPad = str_repeat(" ", $priceW - strlen((string)$cost) + 2);
            $qtyPad = str_repeat(" ", $qtyW - strlen((string)$qty) + 2);

            echo "$line  $title$namePad$cost$costPad$qty$qtyPad$subtotal\n";
            $line++;
        }

        echo "РАЗОМ ДО СПЛАТИ: $sumAll\n";
    }


    private function configureUserProfile(): void
    {
        while (true) {
            echo "Ваше імʼя: ";
            $username = trim(fgets(STDIN));
            if ($this->userProfile->setName($username)) {
                break;
            }
            echo "ПОМИЛКА! Імʼя повинно містити хоча б одну літеру\n";
        }

        while (true) {
            echo "Ваш вік: ";
            $ageStr = trim(fgets(STDIN));
            if (!is_numeric($ageStr)) {
                echo "ПОМИЛКА! Вік має бути числом\n";
                continue;
            }
            $age = (int)$ageStr;
            if ($this->userProfile->setAge($age)) {
                break;
            }
            echo "ПОМИЛКА! Вік має бути в межах від " . Restrictions::MIN_AGE . " до " . Restrictions::MAX_AGE . " років\n";
        }
        echo "\n";
    }

}

$store = new Store();
$store->run();
