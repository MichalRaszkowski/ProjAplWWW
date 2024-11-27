<?php
include 'cfg.php';

// Pobranie parametru strony
$pageAlias = isset($_GET['idp']) ? $_GET['idp'] : 'glowna';

// Przygotowanie zapytania
$stmt = $conn->prepare("SELECT page_title, page_content FROM moja_strona WHERE alias = ?");
$stmt->bind_param("s", $pageAlias);
$stmt->execute();

// Pobranie wyników
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<h1>" . htmlspecialchars($row['page_title']) . "</h1>";
    echo $row['page_content'];
} else {
    echo "<h1>Błąd</h1>";
    echo "<p>Nie znaleziono strony.</p>";
}

$stmt->close();
$conn->close();
