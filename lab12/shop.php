<?php
// shop.php - główna strona sklepu
session_start();
require_once('cfg.php'); // plik z połączeniem do bazy

function wyswietlKategorie($conn)
{
    echo '<div class="categories-sidebar">';
    echo '<h3>Kategorie</h3>';

    // Pobierz kategorie główne
    $query = "SELECT id, nazwa FROM kategorie WHERE matka = 0 ORDER BY nazwa";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        echo '<ul class="list-unstyled">';
        while ($row = $result->fetch_assoc()) {
            echo '<li class="category-item">';
            echo '<a href="?kategoria=' . $row['id'] . '">' . htmlspecialchars($row['nazwa']) . '</a>';

            // Pobierz podkategorie
            $stmt = $conn->prepare("SELECT id, nazwa FROM kategorie WHERE matka = ?");
            $stmt->bind_param("i", $row['id']);
            $stmt->execute();
            $subcategories = $stmt->get_result();

            if ($subcategories->num_rows > 0) {
                echo '<ul class="list-unstyled ml-3">';
                while ($sub = $subcategories->fetch_assoc()) {
                    echo '<li><a href="?kategoria=' . $sub['id'] . '">' . htmlspecialchars($sub['nazwa']) . '</a></li>';
                }
                echo '</ul>';
            }
            echo '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}

function wyswietlProdukty($conn, $kategoria_id = null)
{
    if ($kategoria_id) {
        // Najpierw pobierz wszystkie podkategorie dla wybranej kategorii
        $sql = "WITH RECURSIVE category_tree AS (
            -- Kategoria początkowa
            SELECT id, nazwa
            FROM kategorie
            WHERE id = ?
            
            UNION ALL
            
            -- Dodaj podkategorie
            SELECT k.id, k.nazwa
            FROM kategorie k
            INNER JOIN category_tree ct ON k.matka = ct.id
        )
        SELECT p.*, k.nazwa as kategoria_nazwa 
        FROM produkty p 
        LEFT JOIN kategorie k ON p.kategoria_id = k.id 
        WHERE p.kategoria_id IN (SELECT id FROM category_tree)
        ORDER BY p.tytul";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $kategoria_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Jeśli nie wybrano kategorii, pokaż wszystkie produkty
        $sql = "SELECT p.*, k.nazwa as kategoria_nazwa 
                FROM produkty p 
                LEFT JOIN kategorie k ON p.kategoria_id = k.id 
                ORDER BY p.tytul";
        $result = $conn->query($sql);
    }

    if ($result->num_rows > 0) {
        echo '<div class="row">';
        while ($row = $result->fetch_assoc()) {
            $cena_brutto = $row['cena_netto'] * (1 + $row['podatek_vat'] / 100);

            echo '<div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="' . ($row['zdjecie_url'] ?: 'placeholder.jpg') . '" 
                             class="card-img-top" 
                             alt="' . htmlspecialchars($row['tytul']) . '"
                             style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title">' . htmlspecialchars($row['tytul']) . '</h5>
                            <p class="card-text text-muted">' . htmlspecialchars($row['kategoria_nazwa']) . '</p>
                            <p class="card-text">
                                <strong>Cena: ' . number_format($cena_brutto, 2) . ' zł</strong><br>
                                <small class="text-muted">netto: ' . number_format($row['cena_netto'], 2) . ' zł</small>
                            </p>
                            <a href="product.php?id=' . $row['id'] . '" class="btn btn-primary">Szczegóły</a>
                        </div>
                    </div>
                </div>';
        }
        echo '</div>';
    } else {
        echo '<p class="alert alert-info">Brak produktów w tej kategorii.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sklep</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .categories-sidebar {
            background: rgb(161, 211, 170);
            padding: 15px;
            border-radius: 5px;
        }

        .category-item {
            margin-bottom: 10px;
        }

        .category-item ul {
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="shop.php">Sklep</a>
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
        <div class="row">
            <div class="col-md-3">
                <?php wyswietlKategorie($conn); ?>
            </div>
            <div class="col-md-9">
                <?php
                $kategoria_id = isset($_GET['kategoria']) ? intval($_GET['kategoria']) : null;
                wyswietlProdukty($conn, $kategoria_id);
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>