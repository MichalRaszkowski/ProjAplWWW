<?php
    $nr_indeksu = '169357';
    $nrGrupy = '3';

    echo 'Michal Raszkowski ' . $nr_indeksu . ' grupa ' . $nrGrupy . '<br /><br />';
    echo 'Zastosowanie metody include() <br />';
    include('header.php');
    echo 'zastosowanie metody require_once() <br/>';
    require_once('header.php');
    

    function foo(){
        global $color;
        include 'vars.php';
        echo 'a ' . $color . ' ' . $fruit;
    }
    foo();
    echo "a $color <br/>";

    $a=10;
    $b=5;
    if($a>$b){
        echo 'a is bigger than b';
    }
    else{
        echo 'a is not bigger than b';
    }

    echo 'Pętla while i for<br />';
$i = 0;
while ($i < 5) {
    echo $i . '<br />';
    $i++;
}
for ($j = 0; $j < 5; $j++) {
    echo $j . '<br />';
}

echo 'Typy zmiennych $_GET, $_POST, $_SESSION<br />';
session_start();
$_SESSION['zmienna'] = 'sesja';
echo '$_SESSION zmienna: ' . $_SESSION['zmienna'] . '<br />';

    ?>


