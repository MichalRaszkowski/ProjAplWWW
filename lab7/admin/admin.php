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
            <tr><td class="log4_t">[email]</td><td><input type="text" name="login_email" class="logowanie" /></td></tr>
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
function ListaPodstron($conn)
{
  $wynik = '<h2>Lista podstron</h2>';
  $wynik .= '<table border="1" cellspacing="0" cellpadding="5">
              <tr>
                  <th>ID</th>
                  <th>Tytuł</th>
                  <th>Opcje</th>
              </tr>';

  $query = "SELECT id, page_title FROM moja_strona ORDER BY id ASC LIMIT 100"; // TIP 3
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
  $query = "UPDATE moja_strona SET page_title = ?, page_content = ?, alias = ?, status = ? WHERE id = ? LIMIT 1"; // TIP 3
  $stmt = $conn->prepare($query);

  $title = "Zaktualizowana podstrona";
  $content = "<p>To jest zaktualizowana treść podstrony.</p>";
  $alias = "zaktualizowana";
  $status = 1;

  $stmt->bind_param("sssii", $title, $content, $alias, $status, $id);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo '<p>Podstrona została zaktualizowana.</p>';
  } else {
    echo '<p>Nie udało się zaktualizować podstrony.</p>';
  }
  $stmt->close();
}


function DodajNowaPodstrone($conn)
{
  $query = "INSERT INTO moja_strona (page_title, page_content, alias, status) VALUES (?, ?, ?, ?) LIMIT 1"; // TIP 3
  $stmt = $conn->prepare($query);

  $title = "Nowa podstrona";
  $content = "<p>To jest treść nowej podstrony.</p>";
  $alias = "nowa";
  $status = 1;

  $stmt->bind_param("sssi", $title, $content, $alias, $status);
  $stmt->execute();

  if ($stmt->affected_rows > 0) {
    echo '<p>Nowa podstrona została dodana.</p>';
  } else {
    echo '<p>Nie udało się dodać podstrony.</p>';
  }
  $stmt->close();
}


function UsunPodstrone($conn, $id)
{
  $query = "DELETE FROM moja_strona WHERE id = ? LIMIT 1"; // TIP 3
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

$conn->close();
