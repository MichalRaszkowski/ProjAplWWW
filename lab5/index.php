<!DOCTYPE html>
<html lang="pl">

<head>
  <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
  <meta http-equiv="Content-Language" content="pl" />
  <meta name="Author" content="Michał Raszkowski" />
  <title>Robert Kubica</title>

  <?php
  $cssFile = 'css/index.css';

  if (isset($_GET['idp'])) {
    if ($_GET['idp'] == 'nowosci') $cssFile = 'css/nowosci.css';
    if ($_GET['idp'] == 'kariera') $cssFile = 'css/kariera.css';
    if ($_GET['idp'] == 'karieraInne') $cssFile = 'css/karieraInne.css';
    if ($_GET['idp'] == 'galeria') $cssFile = 'css/galeria.css';
  }
  ?>

  <link rel="stylesheet" href="<?php echo $cssFile; ?>">
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
          <li><a href="filmy.html" class="button">Filmy</a></li>
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
      }

      if (file_exists($strona)) {
        include($strona);
      } else {
        echo 'Błąd: wybrana strona nie istnieje.';
      }
      ?>
    </div>
    <img src="img/robert.jpg" alt="Robert Kubica" class="background-image">
  </main>

  <footer>
    <a href="index.php?idp=kontakt">Kontakt</a>
  </footer>

</body>

</html>