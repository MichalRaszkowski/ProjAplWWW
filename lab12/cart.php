<?php
// cart.php
require_once('cfg.php');
session_start();

// Inicjalizacja koszyka jeśli nie istnieje
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function addToCart($conn, $product_id, $quantity = 1)
{
    // Sprawdź czy produkt istnieje i pobierz jego dane
    $stmt = $conn->prepare("SELECT * FROM produkty WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();

        // Sprawdź dostępność
        if ($product['ilosc_magazyn'] < $quantity) {
            return "Przepraszamy, dostępnych jest tylko " . $product['ilosc_magazyn'] . " sztuk tego produktu.";
        }

        // Oblicz cenę brutto
        $cena_brutto = $product['cena_netto'] * (1 + $product['podatek_vat'] / 100);

        // Sprawdź czy produkt już jest w koszyku
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'id' => $product_id,
                'name' => $product['tytul'],
                'price_netto' => $product['cena_netto'],
                'price_brutto' => $cena_brutto,
                'vat' => $product['podatek_vat'],
                'quantity' => $quantity
            ];
        }
        return "Produkt dodany do koszyka.";
    }
    return "Nie znaleziono produktu.";
}

function removeFromCart($product_id)
{
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return "Produkt usunięty z koszyka.";
    }
    return "Produkt nie znajduje się w koszyku.";
}

function updateCartQuantity($product_id, $quantity)
{
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity > 0) {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            return "Ilość zaktualizowana.";
        } else {
            return removeFromCart($product_id);
        }
    }
    return "Produkt nie znajduje się w koszyku.";
}

function getCartTotal()
{
    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price_brutto'] * $item['quantity'];
    }
    return $total;
}

function showCart()
{
    if (empty($_SESSION['cart'])) {
        return '<div class="alert alert-info">Twój koszyk jest pusty.</div>';
    }

    $output = '<div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produkt</th>
                            <th>Cena jednostkowa</th>
                            <th>Ilość</th>
                            <th>Suma</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>';

    foreach ($_SESSION['cart'] as $product_id => $item) {
        $suma = $item['price_brutto'] * $item['quantity'];
        $output .= '<tr>
                        <td>' . htmlspecialchars($item['name']) . '</td>
                        <td>' . number_format($item['price_brutto'], 2) . ' zł<br>
                            <small class="text-muted">netto: ' . number_format($item['price_netto'], 2) . ' zł</small></td>
                        <td>
                            <form method="post" class="d-flex" style="max-width: 150px;">
                                <input type="hidden" name="action" value="update">
                                <input type="hidden" name="product_id" value="' . $product_id . '">
                                <input type="number" name="quantity" value="' . $item['quantity'] . '" min="1" class="form-control form-control-sm">
                                <button type="submit" class="btn btn-sm btn-secondary ms-1">✓</button>
                            </form>
                        </td>
                        <td>' . number_format($suma, 2) . ' zł</td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="action" value="remove">
                                <input type="hidden" name="product_id" value="' . $product_id . '">
                                <button type="submit" class="btn btn-danger btn-sm">Usuń</button>
                            </form>
                        </td>
                    </tr>';
    }

    $output .= '</tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Suma całkowita:</strong></td>
                        <td><strong>' . number_format(getCartTotal(), 2) . ' zł</strong></td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>';

    return $output;
}

// Obsługa akcji
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    switch ($action) {
        case 'add':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            $message = addToCart($conn, $product_id, $quantity);
            break;
        case 'remove':
            $message = removeFromCart($product_id);
            break;
        case 'update':
            $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
            $message = updateCartQuantity($product_id, $quantity);
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koszyk - Sklep internetowy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="shop.php">Sklep</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        Koszyk (<?php echo count($_SESSION['cart']); ?>)
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <h1>Koszyk</h1>

        <?php
        if (isset($message)) {
            echo '<div class="alert alert-info">' . $message . '</div>';
        }
        echo showCart();
        ?>

        <div class="mt-3">
            <a href="shop.php" class="btn btn-secondary">Powrót do sklepu</a>
            <?php if (!empty($_SESSION['cart'])): ?>
                <a href="#" class="btn btn-primary">Przejdź do kasy</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>