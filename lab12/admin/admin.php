<?php
session_start();
include '../cfg.php';

function FormularzLogowania($error = '')
{
  $wynik = '
    <div class="logowanie">
      <h1 class="heading">Panel CMS:</h1>
      <div class="logowanie">
        <form method="post" name="LoginForm" enctype="multipart/form-data" action="' . htmlspecialchars($_SERVER['PHP_SELF']) . '">
          <table class="logowanie">
            <tr><td class="log4_t">[login]</td><td><input type="text" name="login_email" class="logowanie" /></td></tr>
            <tr><td class="log4_t">[haslo]</td><td><input type="password" name="login_pass" class="logowanie" /></td></tr>
            <tr><td>&nbsp;</td><td><input type="submit" name="x1_submit" class="logowanie" value="zaloguj"/></td></tr>
          </table>
        </form>
        ' . ($error ? '<p style="color:red;">' . $error . '</p>' : '') . '
      </div>
    </div>
    ';
  return $wynik;
}

function DodajNowaPodstrone($conn)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_submit'])) {
    // Pobierz dane z formularza
    $title = $_POST['page_title'] ?? '';
    $content = $_POST['page_content'] ?? '';
    $status = isset($_POST['active']) ? 1 : 0;

    // Generowanie aliasu (tytuł z dodanym "1" na końcu)
    $alias = $title . '1';

    // Wstaw dane do bazy z aliasem
    $stmt = $conn->prepare("INSERT INTO moja_strona (page_title, page_content, status, alias) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $title, $content, $status, $alias);

    if ($stmt->execute()) {
      echo "<p>Podstrona została pomyślnie dodana.</p>";
    } else {
      echo "<p>Błąd podczas dodawania podstrony: " . $stmt->error . "</p>";
    }
    $stmt->close();
  }

  // Formularz dodawania nowej podstrony
  echo '<h2>Dodaj nową podstronę</h2>
        <form method="post">
          <label for="active">Aktywna:</label>
          <input type="checkbox" name="active" id="active">
          <br>
          <label for="page_title">Tytuł strony:</label>
          <input type="text" name="page_title" id="page_title" required>
          <br>
          <label for="page_content">Treść strony:</label>
          <textarea name="page_content" id="page_content" rows="5" required></textarea>
          <br>
          <input type="submit" name="add_submit" value="Dodaj podstronę">
        </form>';
}

// Dodaj link do formularza dodawania w funkcji ListaPodstron
function ListaPodstron($conn)
{
  $wynik = '<h2>Lista podstron</h2>';
  $wynik .= '<a href="?action=add" class="button">Dodaj nową podstronę</a>';
  $wynik .= '<table border="1" cellspacing="0" cellpadding="5">
              <tr>
                  <th>ID</th>
                  <th>Tytuł</th>
                  <th>Opcje</th>
              </tr>';

  $query = "SELECT id, page_title FROM moja_strona ORDER BY id ASC LIMIT 100";
  $result = $conn->query($query);

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      $wynik .= '<tr>';
      $wynik .= '<td>' . htmlspecialchars($row['id']) . '</td>';
      $wynik .= '<td>' . htmlspecialchars($row['page_title']) . '</td>';
      $wynik .= '<td>
                        <a href="?action=edit&id=' . $row['id'] . '">Edytuj</a> | 
                        <a href="?action=delete&id=' . $row['id'] . '" onclick="return confirm(\'Czy na pewno chcesz usunąć tę podstronę?\')">Usuń</a>
                     </td>';
      $wynik .= '</tr>';
    }
  } else {
    $wynik .= '<tr><td colspan="3">Brak podstron w bazie danych.</td></tr>';
  }

  $wynik .= '</table>';
  return $wynik;
}


function EdytujPodstrone($conn, $id)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Pobierz dane z formularza
    $status = isset($_POST['active']) ? 1 : 0; // Checkbox
    $title = $_POST['title'] ?? ''; // Tytuł strony
    $content = $_POST['content'] ?? ''; // Treść strony
    $id = (int) $_POST['id']; // Identyfikator podstrony

    // Zaktualizuj bazę danych
    $stmt = $conn->prepare("UPDATE moja_strona SET status = ?, page_title = ?, page_content = ? WHERE id = ?");
    $stmt->bind_param("issi", $status, $title, $content, $id);

    if ($stmt->execute()) {
      echo "<p>Dane strony zostały pomyślnie zaktualizowane.</p>";
    } else {
      echo "<p>Błąd podczas aktualizacji: " . $stmt->error . "</p>";
    }
    $stmt->close();
  }

  // Pobierz aktualne dane podstrony z bazy
  $stmt = $conn->prepare("SELECT page_title, page_content, status FROM moja_strona WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Wyświetl formularz edycji
    echo '<form method="post">
            <input type="hidden" name="id" value="' . htmlspecialchars((string) $id) . '">
            <label for="active">Aktywna:</label>
            <input type="checkbox" name="active" id="active" ' . ($row['status'] ? 'checked' : '') . '>
            <br>
            <label for="title">Tytuł strony:</label>
            <input type="text" name="title" id="title" value="' . htmlspecialchars($row['page_title']) . '" required>
            <br>
            <label for="content">Treść strony:</label>
            <textarea name="content" id="content" rows="5" required>' . htmlspecialchars($row['page_content']) . '</textarea>
            <br>
            <input type="submit" name="submit" value="Zapisz zmiany">
          </form>';
  } else {
    echo "<p>Nie znaleziono podstrony o podanym ID.</p>";
  }

  $stmt->close();
}

function UsunPodstrone($conn, $id)
{
  $query = "DELETE FROM moja_strona WHERE id = ? LIMIT 1";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo '<p>Podstrona została usunięta.</p>';
  } else {
    echo '<p>Nie udało się usunąć podstrony.</p>';
  }
  $stmt->close();
}
if (isset($_POST['x1_submit'])) {
  $email = $_POST['login_email'] ?? '';
  $password = $_POST['login_pass'] ?? '';

  if ($email === $login && $password === $pass) {
    $_SESSION['logged_in'] = true;
    header('Location: admin.php');
    exit;
  } else {
    echo FormularzLogowania('Nieprawidłowy login lub hasło.');
    exit;
  }
}

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
  echo FormularzLogowania();
  exit;
}

if (isset($_GET['action'])) {
  $action = $_GET['action'];
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  if ($action === 'delete') {
    UsunPodstrone($conn, $id);
  } elseif ($action === 'edit') {
    EdytujPodstrone($conn, $id);
  } elseif ($action === 'add') {
    DodajNowaPodstrone($conn);
  }
}

echo ListaPodstron($conn);


function DodajKategorie($conn)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_category'])) {
    // Pobierz dane z formularza
    $nazwa = $_POST['category_name'] ?? '';
    $matka = $_POST['parent_category'] ?? 0; // Jeśli kategoria główna, to matka = 0

    // Jeśli matka nie jest 0, sprawdź, czy istnieje w bazie
    if ($matka != 0) {
      $stmt = $conn->prepare("SELECT id FROM kategorie WHERE id = ? LIMIT 1");
      $stmt->bind_param("i", $matka);
      $stmt->execute();
      $result = $stmt->get_result();
      if ($result->num_rows === 0) {
        echo "<p>Błąd: Wybrana kategoria nadrzędna nie istnieje.</p>";
        $stmt->close();
        return;
      }
      $stmt->close();
    }

    // Wstaw dane do bazy
    $stmt = $conn->prepare("INSERT INTO kategorie (nazwa, matka) VALUES (?, ?)");
    $stmt->bind_param("si", $nazwa, $matka);

    if ($stmt->execute()) {
      echo "<p>Kategoria została pomyślnie dodana.</p>";
    } else {
      echo "<p>Błąd podczas dodawania kategorii: " . $stmt->error . "</p>";
    }
    $stmt->close();
  }

  // Formularz dodawania kategorii
  echo '<h2>Dodaj kategorię</h2>
        <form method="post">
            <label for="category_name">Nazwa kategorii:</label>
            <input type="text" name="category_name" id="category_name" required>
            <br>
            <label for="parent_category">Kategoria nadrzędna:</label>
            <select name="parent_category" id="parent_category">
                <option value="0">Brak</option>';

  // Pobierz wszystkie główne kategorie do wyboru
  $query = "SELECT id, nazwa FROM kategorie WHERE matka = 0";
  $result = $conn->query($query);

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo '<option value="' . htmlspecialchars($row['id']) . '">' . htmlspecialchars($row['nazwa']) . '</option>';
    }
  }
  echo '</select>
        <br>
        <input type="submit" name="add_category" value="Dodaj kategorię">
        </form>';
}

function EdytujKategorie($conn, $id)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_category'])) {
    // Pobierz dane z formularza
    $nazwa = $_POST['category_name'] ?? '';
    $matka = $_POST['parent_category'] ?? 0;

    // Zaktualizuj kategorię w bazie
    $stmt = $conn->prepare("UPDATE kategorie SET nazwa = ?, matka = ? WHERE id = ? LIMIT 1");
    $stmt->bind_param("sii", $nazwa, $matka, $id);

    if ($stmt->execute()) {
      echo "<p>Kategoria została pomyślnie zaktualizowana.</p>";
    } else {
      echo "<p>Błąd podczas aktualizacji kategorii: " . $stmt->error . "</p>";
    }
    $stmt->close();
  }

  // Pobierz aktualne dane kategorii
  $stmt = $conn->prepare("SELECT nazwa, matka FROM kategorie WHERE id = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    // Formularz edytowania kategorii
    echo '<form method="post">
              <input type="hidden" name="id" value="' . htmlspecialchars((string) $id) . '">
              <label for="category_name">Nazwa kategorii:</label>
              <input type="text" name="category_name" id="category_name" value="' . htmlspecialchars($row['nazwa']) . '" required>
              <br>
              <label for="parent_category">Kategoria nadrzędna:</label>
              <select name="parent_category" id="parent_category">
                  <option value="0">Brak</option>';

    // Pobierz główne kategorie
    $query = "SELECT id, nazwa FROM kategorie WHERE matka = 0";
    $result = $conn->query($query);
    if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
        echo '<option value="' . htmlspecialchars($row['id']) . '" ' . ($row['id'] == $row['matka'] ? 'selected' : '') . '>' . htmlspecialchars($row['nazwa']) . '</option>';
      }
    }
    echo '</select>
              <br>
              <input type="submit" name="edit_category" value="Zapisz zmiany">
            </form>';
  } else {
    echo "<p>Nie znaleziono kategorii o podanym ID.</p>";
  }

  $stmt->close();
}
function UsunKategorie($conn, $id)
{
  $query = "DELETE FROM kategorie WHERE id = ? LIMIT 1";
  $stmt = $conn->prepare($query);
  $stmt->bind_param("i", $id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo '<p>Kategoria została usunięta.</p>';
  } else {
    echo '<p>Nie udało się usunąć kategorii.</p>';
  }
  $stmt->close();
}

function PokazKategorie($conn)
{
  echo '<h2>Lista kategorii</h2>';
  // Dodaj przycisk do formularza dodawania kategorii
  echo '<a href="?action=add_cat" class="btn btn-primary">Dodaj kategorię</a><br>';

  // Pobierz wszystkie kategorie główne
  $query = "SELECT id, nazwa FROM kategorie WHERE matka = 0 ORDER BY nazwa";
  $result = $conn->query($query);

  echo '<ul>';

  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo '<li>' . htmlspecialchars($row['nazwa']) .
        ' <a href="?action=edit_cat&id=' . $row['id'] . '" class="btn btn-warning">Edytuj</a>' .
        ' <a href="?action=delete_cat&id=' . $row['id'] . '" class="btn btn-danger" onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">Usuń</a>';

      // Pobierz podkategorie
      $stmt = $conn->prepare("SELECT id, nazwa FROM kategorie WHERE matka = ?");
      $stmt->bind_param("i", $row['id']);
      $stmt->execute();
      $subcategories = $stmt->get_result();

      if ($subcategories->num_rows > 0) {
        echo '<ul>';
        while ($sub = $subcategories->fetch_assoc()) {
          echo '<li>' . htmlspecialchars($sub['nazwa']) .
            ' <a href="?action=edit_cat&id=' . $sub['id'] . '" class="btn btn-warning">Edytuj</a>' .
            ' <a href="?action=delete_cat&id=' . $sub['id'] . '" class="btn btn-danger" onclick="return confirm(\'Czy na pewno chcesz usunąć tę kategorię?\')">Usuń</a>' .
            '</li>';
        }
        echo '</ul>';
      }

      echo '</li>';
    }
  } else {
    echo '<li>Brak kategorii głównych w bazie.</li>';
  }

  echo '</ul>';
}

// Zmodyfikowany ostatni if
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  if ($action === 'add_cat') {
    DodajKategorie($conn);
  } elseif ($action === 'edit_cat') {
    EdytujKategorie($conn, $id);
  } elseif ($action === 'delete_cat') {
    UsunKategorie($conn, $id);
  }
}
PokazKategorie($conn);

function DodajProdukt($conn)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    // Pobierz dane z formularza
    $tytul = $_POST['tytul'] ?? '';
    $opis = $_POST['opis'] ?? '';
    $data_wygasniecia = $_POST['data_wygasniecia'] ?? NULL;
    $cena_netto = $_POST['cena_netto'] ?? 0;
    $podatek_vat = $_POST['podatek_vat'] ?? 23;
    $ilosc_magazyn = $_POST['ilosc_magazyn'] ?? 0;
    $status_dostepnosci = $_POST['status_dostepnosci'] ?? 'niedostępny';
    $kategoria_id = $_POST['kategoria_id'] ?? NULL;
    $gabaryt = $_POST['gabaryt'] ?? 'średni';
    $zdjecie_url = $_POST['zdjecie_url'] ?? '';

    // Przygotuj zapytanie INSERT
    $query = "INSERT INTO produkty (tytul, opis, data_wygasniecia, cena_netto, podatek_vat, 
                                    ilosc_magazyn, status_dostepnosci, kategoria_id, gabaryt, zdjecie_url) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
      "sssddiisss",
      $tytul,
      $opis,
      $data_wygasniecia,
      $cena_netto,
      $podatek_vat,
      $ilosc_magazyn,
      $status_dostepnosci,
      $kategoria_id,
      $gabaryt,
      $zdjecie_url
    );

    if ($stmt->execute()) {
      echo "<p class='alert alert-success'>Produkt został pomyślnie dodany.</p>";
    } else {
      echo "<p class='alert alert-danger'>Błąd podczas dodawania produktu: " . $stmt->error . "</p>";
    }
    $stmt->close();
  }

  // Formularz dodawania produktu
  echo '<h2>Dodaj nowy produkt</h2>
  <form method="post" class="form">
      <div class="form-group">
          <label for="tytul">Tytuł produktu:</label>
          <input type="text" name="tytul" id="tytul" class="form-control" required>
      </div>

      <div class="form-group">
          <label for="opis">Opis:</label>
          <textarea name="opis" id="opis" class="form-control" rows="5"></textarea>
      </div>

      <div class="form-group">
          <label for="data_wygasniecia">Data wygaśnięcia:</label>
          <input type="date" name="data_wygasniecia" id="data_wygasniecia" class="form-control">
      </div>

      <div class="form-group">
          <label for="cena_netto">Cena netto:</label>
          <input type="number" step="0.01" name="cena_netto" id="cena_netto" class="form-control" required>
      </div>

      <div class="form-group">
          <label for="podatek_vat">VAT (%):</label>
          <input type="number" step="0.01" name="podatek_vat" id="podatek_vat" class="form-control" value="23" required>
      </div>

      <div class="form-group">
          <label for="ilosc_magazyn">Ilość w magazynie:</label>
          <input type="number" name="ilosc_magazyn" id="ilosc_magazyn" class="form-control" required>
      </div>

      <div class="form-group">
          <label for="status_dostepnosci">Status dostępności:</label>
          <select name="status_dostepnosci" id="status_dostepnosci" class="form-control">
              <option value="dostępny">Dostępny</option>
              <option value="niedostępny">Niedostępny</option>
              <option value="na zamówienie">Na zamówienie</option>
              <option value="wyprzedany">Wyprzedany</option>
          </select>
      </div>

      <div class="form-group">
          <label for="kategoria_id">Kategoria:</label>
          <select name="kategoria_id" id="kategoria_id" class="form-control">';

  // Pobierz kategorie z bazy
  $query = "SELECT id, nazwa FROM kategorie ORDER BY nazwa";
  $result = $conn->query($query);
  if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
      echo '<option value="' . $row['id'] . '">' . htmlspecialchars($row['nazwa']) . '</option>';
    }
  }

  echo '</select>
      </div>

      <div class="form-group">
          <label for="gabaryt">Gabaryt:</label>
          <select name="gabaryt" id="gabaryt" class="form-control">
              <option value="mały">Mały</option>
              <option value="średni">Średni</option>
              <option value="duży">Duży</option>
              <option value="nietypowy">Nietypowy</option>
          </select>
      </div>

      <div class="form-group">
          <label for="zdjecie_url">URL zdjęcia:</label>
          <input type="text" name="zdjecie_url" id="zdjecie_url" class="form-control">
      </div>

      <button type="submit" name="add_product" class="btn btn-primary">Dodaj produkt</button>
  </form>';
}

function EdytujProdukt($conn, $id)
{
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_product'])) {
    // Pobierz dane z formularza
    $tytul = $_POST['tytul'] ?? '';
    $opis = $_POST['opis'] ?? '';
    $data_wygasniecia = $_POST['data_wygasniecia'] ?? NULL;
    $cena_netto = $_POST['cena_netto'] ?? 0;
    $podatek_vat = $_POST['podatek_vat'] ?? 23;
    $ilosc_magazyn = $_POST['ilosc_magazyn'] ?? 0;
    $status_dostepnosci = $_POST['status_dostepnosci'] ?? 'niedostępny';
    $kategoria_id = $_POST['kategoria_id'] ?? NULL;
    $gabaryt = $_POST['gabaryt'] ?? 'średni';
    $zdjecie_url = $_POST['zdjecie_url'] ?? '';

    // Aktualizuj dane w bazie
    $query = "UPDATE produkty SET 
                  tytul = ?, 
                  opis = ?, 
                  data_wygasniecia = ?, 
                  cena_netto = ?, 
                  podatek_vat = ?,
                  ilosc_magazyn = ?, 
                  status_dostepnosci = ?, 
                  kategoria_id = ?, 
                  gabaryt = ?, 
                  zdjecie_url = ?
               WHERE id = ? LIMIT 1";

    $stmt = $conn->prepare($query);
    $stmt->bind_param(
      "sssddiisssi",
      $tytul,
      $opis,
      $data_wygasniecia,
      $cena_netto,
      $podatek_vat,
      $ilosc_magazyn,
      $status_dostepnosci,
      $kategoria_id,
      $gabaryt,
      $zdjecie_url,
      $id
    );

    if ($stmt->execute()) {
      echo "<p class='alert alert-success'>Produkt został zaktualizowany.</p>";
    } else {
      echo "<p class='alert alert-danger'>Błąd podczas aktualizacji produktu: " . $stmt->error . "</p>";
    }
    $stmt->close();
  }

  // Pobierz dane produktu
  $stmt = $conn->prepare("SELECT * FROM produkty WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $product = $result->fetch_assoc();

    // Formularz edycji produktu (podobny do formularza dodawania)
    echo '<h2>Edytuj produkt</h2>
      <form method="post" class="form">
          <div class="form-group">
              <label for="tytul">Tytuł produktu:</label>
              <input type="text" name="tytul" id="tytul" class="form-control" value="' . htmlspecialchars($product['tytul']) . '" required>
          </div>

          <div class="form-group">
              <label for="opis">Opis:</label>
              <textarea name="opis" id="opis" class="form-control" rows="5">' . htmlspecialchars($product['opis']) . '</textarea>
          </div>

          <div class="form-group">
              <label for="data_wygasniecia">Data wygaśnięcia:</label>
              <input type="date" name="data_wygasniecia" id="data_wygasniecia" class="form-control" value="' . htmlspecialchars($product['data_wygasniecia']) . '">
          </div>

          <div class="form-group">
              <label for="cena_netto">Cena netto:</label>
              <input type="number" step="0.01" name="cena_netto" id="cena_netto" class="form-control" value="' . htmlspecialchars($product['cena_netto']) . '" required>
          </div>

          <div class="form-group">
              <label for="podatek_vat">VAT (%):</label>
              <input type="number" step="0.01" name="podatek_vat" id="podatek_vat" class="form-control" value="' . htmlspecialchars($product['podatek_vat']) . '" required>
          </div>

          <div class="form-group">
              <label for="ilosc_magazyn">Ilość w magazynie:</label>
              <input type="number" name="ilosc_magazyn" id="ilosc_magazyn" class="form-control" value="' . htmlspecialchars($product['ilosc_magazyn']) . '" required>
          </div>

          <div class="form-group">
              <label for="status_dostepnosci">Status dostępności:</label>
              <select name="status_dostepnosci" id="status_dostepnosci" class="form-control">
                  <option value="dostępny"' . ($product['status_dostepnosci'] == 'dostępny' ? ' selected' : '') . '>Dostępny</option>
                  <option value="niedostępny"' . ($product['status_dostepnosci'] == 'niedostępny' ? ' selected' : '') . '>Niedostępny</option>
                  <option value="na zamówienie"' . ($product['status_dostepnosci'] == 'na zamówienie' ? ' selected' : '') . '>Na zamówienie</option>
                  <option value="wyprzedany"' . ($product['status_dostepnosci'] == 'wyprzedany' ? ' selected' : '') . '>Wyprzedany</option>
              </select>
          </div>

          <div class="form-group">
              <label for="kategoria_id">Kategoria:</label>
              <select name="kategoria_id" id="kategoria_id" class="form-control">';

    // Pobierz kategorie
    $query = "SELECT id, nazwa FROM kategorie ORDER BY nazwa";
    $categories = $conn->query($query);
    if ($categories->num_rows > 0) {
      while ($row = $categories->fetch_assoc()) {
        echo '<option value="' . $row['id'] . '"' .
          ($row['id'] == $product['kategoria_id'] ? ' selected' : '') . '>' .
          htmlspecialchars($row['nazwa']) . '</option>';
      }
    }

    echo '</select>
          </div>

          <div class="form-group">
              <label for="gabaryt">Gabaryt:</label>
              <select name="gabaryt" id="gabaryt" class="form-control">
                  <option value="mały"' . ($product['gabaryt'] == 'mały' ? ' selected' : '') . '>Mały</option>
                  <option value="średni"' . ($product['gabaryt'] == 'średni' ? ' selected' : '') . '>Średni</option>
                  <option value="duży"' . ($product['gabaryt'] == 'duży' ? ' selected' : '') . '>Duży</option>
                  <option value="nietypowy"' . ($product['gabaryt'] == 'nietypowy' ? ' selected' : '') . '>Nietypowy</option>
              </select>
          </div>

          <div class="form-group">
              <label for="zdjecie_url">URL zdjęcia:</label>
              <input type="text" name="zdjecie_url" id="zdjecie_url" class="form-control" value="' . htmlspecialchars($product['zdjecie_url']) . '">
          </div>

          <button type="submit" name="edit_product" class="btn btn-primary">Zapisz zmiany</button>
      </form>';
  } else {
    echo "<p class='alert alert-danger'>Nie znaleziono produktu o podanym ID.</p>";
  }
  $stmt->close();
}

function UsunProdukt($conn, $id)
{
  $stmt = $conn->prepare("DELETE FROM produkty WHERE id = ? LIMIT 1");
  $stmt->bind_param("i", $id);

  if ($stmt->execute()) {
    echo "<p class='alert alert-success'>Produkt został usunięty.</p>";
  } else {
    echo "<p class='alert alert-danger'>Błąd podczas usuwania produktu: " . $stmt->error . "</p>";
  }
  $stmt->close();
}

function PokazProdukty($conn)
{
  echo '<h2>Lista produktów</h2>';
  echo '<a href="?action=add_product" class="btn btn-primary mb-3">Dodaj nowy produkt</a>';

  $query = "SELECT p.*, k.nazwa as kategoria_nazwa 
            FROM produkty p 
            LEFT JOIN kategorie k ON p.kategoria_id = k.id 
            ORDER BY p.tytul";
  $result = $conn->query($query);

  if ($result->num_rows > 0) {
    echo '<div class="table-responsive">
            <table class="table table-striped">
              <thead>
                  <tr>
                      <th>ID</th>
                      <th>Zdjęcie</th>
                      <th>Tytuł</th>
                      <th>Kategoria</th>
                      <th>Cena netto</th>
                      <th>VAT</th>
                      <th>Ilość</th>
                      <th>Status</th>
                      <th>Data wygaśnięcia</th>
                      <th>Akcje</th>
                  </tr>
              </thead>
              <tbody>';

    while ($row = $result->fetch_assoc()) {
      // Oblicz cenę brutto
      $cena_brutto = $row['cena_netto'] * (1 + $row['podatek_vat'] / 100);

      // Sprawdź status dostępności
      $status_class = '';
      switch ($row['status_dostepnosci']) {
        case 'dostępny':
          $status_class = 'text-success';
          break;
        case 'niedostępny':
          $status_class = 'text-danger';
          break;
        case 'na zamówienie':
          $status_class = 'text-warning';
          break;
        case 'wyprzedany':
          $status_class = 'text-secondary';
          break;
      }

      echo '<tr>
                  <td>' . $row['id'] . '</td>
                  <td>';

      if (!empty($row['zdjecie_url'])) {
        echo '<img src="' . htmlspecialchars($row['zdjecie_url']) . '" alt="' . htmlspecialchars($row['tytul']) . '" style="max-width: 50px;">';
      } else {
        echo 'Brak zdjęcia';
      }

      echo '</td>
                  <td>' . htmlspecialchars($row['tytul']) . '</td>
                  <td>' . htmlspecialchars($row['kategoria_nazwa'] ?? 'Brak kategorii') . '</td>
                  <td>' . number_format($row['cena_netto'], 2) . ' zł<br>
                      <small class="text-muted">Brutto: ' . number_format($cena_brutto, 2) . ' zł</small></td>
                  <td>' . $row['podatek_vat'] . '%</td>
                  <td>' . $row['ilosc_magazyn'] . '</td>
                  <td class="' . $status_class . '">' . htmlspecialchars($row['status_dostepnosci']) . '</td>
                  <td>' . ($row['data_wygasniecia'] ? htmlspecialchars($row['data_wygasniecia']) : 'Brak') . '</td>
                  <td>
                      <a href="?action=edit_product&id=' . $row['id'] . '" class="btn btn-warning btn-sm">Edytuj</a>
                      <a href="?action=delete_product&id=' . $row['id'] . '" class="btn btn-danger btn-sm" 
                         onclick="return confirm(\'Czy na pewno chcesz usunąć ten produkt?\')">Usuń</a>
                  </td>
              </tr>';
    }

    echo '</tbody></table></div>';
  } else {
    echo '<p class="alert alert-info">Brak produktów w bazie danych.</p>';
  }
}
if (isset($_GET['action'])) {
  $action = $_GET['action'];
  $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

  switch ($action) {
    case 'add_product':
      DodajProdukt($conn);
      break;
    case 'edit_product':
      EdytujProdukt($conn, $id);
      break;
    case 'delete_product':
      UsunProdukt($conn, $id);
      PokazProdukty($conn);
      break;
    default:
      PokazProdukty($conn);
  }
} else {
  PokazProdukty($conn);
}

$conn->close();
