<?php
require_once('cfg.php');
session_start();

function wyswietlProdukt($conn, $id)
{
    $stmt = $conn->prepare("
        SELECT p.*, k.nazwa as kategoria_nazwa 
        FROM produkty p 
        LEFT JOIN kategorie k ON p.kategoria_id = k.id 
        WHERE p.id = ? 
        LIMIT 1
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $cena_brutto = $product['cena_netto'] * (1 + $product['podatek_vat'] / 100);

        return '
        <div class="row">
            <div class="col-md-6">
                <img src="' . ($product['zdjecie_url'] ?: 'placeholder.jpg') . '" 
                     class="img-fluid" 
                     alt="' . htmlspecialchars($product['tytul']) . '">
            </div>
            <div class="col-md-6">
                <h1>' . htmlspecialchars($product['tytul']) . '</h1>
                <p class="text-muted">Kategoria: ' . htmlspecialchars($product['kategoria_nazwa']) . '</p>
                
                <div class="price-box my-4">
                    <h2 class="text-primary">' . number_format($cena_brutto, 2) . ' zł</h2>
                    <small class="text-muted">
                        Cena netto: ' . number_format($product['cena_netto'], 2) . ' zł<br>
                        VAT: ' . $product['podatek_vat'] . '%
                    </small>
                </div>
                
                <div class="status-box mb-4">
                    <p>Status: <span class="badge bg-' .
            ($product['status_dostepnosci'] == 'dostępny' ? 'success' : ($product['status_dostepnosci'] == 'na zamówienie' ? 'warning' : 'danger')) .
            '">' . htmlspecialchars($product['status_dostepnosci']) . '</span></p>
                    <p>Dostępna ilość: ' . $product['ilosc_magazyn'] . ' szt.</p>
                </div>
                
                <div class="description-box mb-4">
                    <h3>Opis produktu</h3>
                    <p>' . nl2br(htmlspecialchars($product['opis'])) . '</p>
                </div>
                
                <div class="additional-info">
                    <p>Gabaryt: ' . htmlspecialchars($product['gabaryt']) . '</p>
                </div>

                <form method="post" action="cart.php" class="mt-3">
                    <input type="hidden" name="action" value="add">
                    <input type="hidden" name="product_id" value="' . $product['id'] . '">
                    <div class="input-group mb-3" style="max-width: 200px;">
                        <input type="number" name="quantity" value="1" min="1" class="form-control">
                        <button type="submit" class="btn btn-primary">Dodaj do koszyka</button>
                    </div>
                </form>
            </div>
        </div>';
    } else {
        return '<div class="alert alert-danger">Produkt nie został znaleziony.</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sklep internetowy - Produkt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="shop.php">Sklep internetowy</a>
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        Koszyk (<?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?>)
                    </a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <?php
        if (isset($_GET['id'])) {
            echo wyswietlProdukt($conn, intval($_GET['id']));
        } else {
            echo '<div class="alert alert-danger">Nie wybrano produktu.</div>';
        }
        ?>
        <div class="mt-4">
            <a href="shop.php" class="btn btn-secondary">Powrót do sklepu</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>