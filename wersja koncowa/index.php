<!DOCTYPE html>
<html lang="pl">

<head>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Content-Language" content="pl" />
  <meta name="Author" content="Michał Raszkowski" />
  <title>Robert Kubica</title>

  <?php
  echo '<link rel="stylesheet" href="css/nawigacja.css">';

  if (!isset($_GET['idp']) || $_GET['idp'] == 'glowna') {
    echo '<link rel="stylesheet" href="css/index.css">';
  }

  if (isset($_GET['idp'])) {
    if ($_GET['idp'] == 'nowosci') $pageCssFile = 'css/nowosci.css';
    if ($_GET['idp'] == 'kariera') $pageCssFile = 'css/kariera.css';
    if ($_GET['idp'] == 'karieraInne') $pageCssFile = 'css/karieraInne.css';
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
          <li><a href="index.php?idp=glowna" class="button">Strona Główna</a></li>
          <li><a href="index.php?idp=nowosci" class="button">Nowości</a></li>
          <li><a href="index.php?idp=kariera" class="button">Kariera w F1</a></li>
          <li><a href="index.php?idp=karieraInne" class="button">Kariera w innych seriach</a></li>
          <li><a href="index.php?idp=galeria" class="button">Galeria</a></li>
          <li><a href="index.php?idp=filmy" class="button">Filmy</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="background-container">
    <div class="text-box">
      <?php
      $strona = 'html/glowna.html';

      if (isset($_GET['idp'])) {
        if ($_GET['idp'] == 'nowosci') $strona = 'html/nowosci.html';
        if ($_GET['idp'] == 'kariera') $strona = 'html/kariera.html';
        if ($_GET['idp'] == 'karieraInne') $strona = 'html/karieraInne.html';
        if ($_GET['idp'] == 'galeria') $strona = 'html/galeria.html';
        if ($_GET['idp'] == 'kontakt') $strona = 'html/kontakt.html';
        if ($_GET['idp'] == 'filmy') $strona = 'html/filmy.html';
      }

      if (file_exists($strona)) {
        include($strona);
      } else {
        echo 'Błąd: wybrana strona nie istnieje.';
      }
      ?>
    </div>

    <?php if (!isset($_GET['idp']) || $_GET['idp'] == 'glowna') : ?>
      <img src="img/robert.jpg" alt="Robert Kubica" class="background-image">
    <?php endif; ?>
  </main>

  <?php if (!isset($_GET['idp']) || $_GET['idp'] == 'glowna') : ?>
    <footer>
      <a href="index.php?idp=kontakt">Kontakt</a>
    </footer>
  <?php endif; ?>
</body>

</html>