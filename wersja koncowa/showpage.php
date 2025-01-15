<?php
include 'cfg.php';
?>

<!DOCTYPE html>
<html lang="pl">

<head>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <meta http-equiv="Content-Language" content="pl" />
    <meta name="Author" content="Michał Raszkowski" />
    <title>Robert Kubica</title>

    <link rel="stylesheet" href="css/nawigacja.css">

    <?php
    if (!isset($_GET['idp']) || $_GET['idp'] == 'glowna') {
        echo '<link rel="stylesheet" href="css/index.css">';
    }

    if (isset($_GET['idp'])) {
        if ($_GET['idp'] == 'nowosci') $pageCssFile = 'css/nowosci.css';
        if ($_GET['idp'] == 'kariera') $pageCssFile = 'css/kariera.css';
        if ($_GET['idp'] == 'inne') $pageCssFile = 'css/karieraInne.css';
        if ($_GET['idp'] == 'galeria') $pageCssFile = 'css/galeria.css';
        if ($_GET['idp'] == 'kontakt') $pageCssFile = 'css/kontakt.css';
        if ($_GET['idp'] == 'filmy') $pageCssFile = 'css/filmy.css';
    }

    if (isset($pageCssFile)) {
        echo '<link rel="stylesheet" href="' . $pageCssFile . '">';
    }
    ?>
</head>

<body>
    <header>
        <div class="inner">
            <div class="logo">
                <img src="img/88.jpg" alt="logo 88">
            </div>

            <nav>
                <ul>
                    <li><a href="showpage.php?idp=glowna" class="button">Strona Główna</a></li>
                    <li><a href="showpage.php?idp=nowosci" class="button">Nowości</a></li>
                    <li><a href="showpage.php?idp=kariera" class="button">Kariera w F1</a></li>
                    <li><a href="showpage.php?idp=inne" class="button">Kariera w innych seriach</a></li>
                    <li><a href="showpage.php?idp=galeria" class="button">Galeria</a></li>
                    <li><a href="showpage.php?idp=filmy" class="button">Filmy</a></li>
                    <li><a href="shop.php" class="button">Sklep</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main class="background-container">
        <div class="text-box">
            <?php
            $pageAlias = isset($_GET['idp']) ? $_GET['idp'] : 'glowna';

            $stmt = $conn->prepare("SELECT page_title, page_content, status FROM moja_strona WHERE alias = ? LIMIT 1");
            $stmt->bind_param("s", $pageAlias);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                if ((int)$row['status'] === 0) {
                    http_response_code(404);
                    echo "<h1>Strona nieaktywna</h1>";
                    exit;
                }

                echo "<h1>" . htmlspecialchars($row['page_title']) . "</h1>";
                echo $row['page_content'];
            } else {
                http_response_code(404);
                echo "<h1>Błąd 404</h1>";
                echo "<p>Nie znaleziono strony.</p>";
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
    </main>

    <footer>
        <a href="contact.php">Kontakt</a>
    </footer>
</body>

</html>