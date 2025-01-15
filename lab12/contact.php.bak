<?php
include 'cfg.php'; // Jeśli potrzebujesz połączenia z bazą danych

// Funkcja: PokazKontakt
function PokazKontakt()
{
    return '
    <h2>Formularz kontaktowy</h2>
    <form method="post" action="?action=send_contact">
        <label for="name">Imię:</label><br>
        <input type="text" id="name" name="name" required><br>
        <label for="email">E-mail:</label><br>
        <input type="email" id="email" name="email" required><br>
        <label for="message">Wiadomość:</label><br>
        <textarea id="message" name="message" rows="5" required></textarea><br>
        <button type="submit">Wyślij</button>
    </form>

    <h2>Przypomnienie hasła</h2>
    <form method="post" action="?action=remind_password">
        <label for="admin_email">Podaj e-mail admina:</label><br>
        <input type="email" id="admin_email" name="admin_email" required><br>
        <button type="submit">Przypomnij hasło</button>
    </form>
    ';
}

// Funkcja: WyslijMailKontakt
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function WyslijMailKontakt($name, $email, $message)
{
    require 'vendor/autoload.php'; // Autoload PHPMailer

    $mail = new PHPMailer(true);

    try {
        // Konfiguracja serwera SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp-mail.outlook.com'; // Adres serwera SMTP
        $mail->SMTPAuth = true;
        $mail->Username = '169357@student.uwm.edu.pl'; // Twój email
        $mail->Password = 'Marchewkowe123';    // Hasło do emaila
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Nadawca i odbiorca
        $mail->setFrom('169357@student.uwm.edu.pl', $name); // E-mail i imię nadawcy
        $mail->addAddress('169357@student.uwm.edu.pl', 'Admin'); // E-mail odbiorcy (np. admina)

        // Treść wiadomości
        $mail->isHTML(true);
        $mail->Subject = 'Formularz kontaktowy';
        $mail->Body = "<p><b>Imie:</b> $name</p>
                       <p><b>Email:</b> $email</p>
                       <p><b>Wiadomosc:</b></p>
                       <p>$message</p>";

        $mail->send();
        echo "Wiadomość została wysłana.";
    } catch (Exception $e) {
        echo "Wysłanie wiadomości nie powiodło się. Błąd: {$mail->ErrorInfo}";
    }
}


// Funkcja: PrzypomnijHaslo
function PrzypomnijHaslo($adminEmail)
{
    require 'vendor/autoload.php';

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'smtp-mail.outlook.com'; // Adres serwera SMTP
        $mail->SMTPAuth = true;
        $mail->Username = '169357@student.uwm.edu.pl'; // Twój email
        $mail->Password = 'Marchewkowe123';    // Hasło do emaila
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('169357@student.uwm.edu.pl', 'Admin');
        $mail->addAddress($adminEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Przypomnienie hasła';
        $mail->Body = "<p>Twoje haslo do panelu admina to: <b>haslo123</b></p>";

        $mail->send();
        echo "Hasło zostało wysłane na podany e-mail.";
    } catch (Exception $e) {
        echo "Nie udało się wysłać hasła. Błąd: {$mail->ErrorInfo}";
    }
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['action']) && $_GET['action'] === 'send_contact') {
        // Obsługa formularza kontaktowego
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $message = $_POST['message'] ?? '';

        if ($name && $email && $message) {
            WyslijMailKontakt($name, $email, $message);
        } else {
            echo "Wszystkie pola są wymagane w formularzu kontaktowym.";
        }
    } elseif (isset($_GET['action']) && $_GET['action'] === 'remind_password') {
        // Obsługa przypomnienia hasła
        $adminEmail = $_POST['admin_email'] ?? '';

        if ($adminEmail) {
            PrzypomnijHaslo($adminEmail);
        } else {
            echo "Proszę podać adres e-mail admina.";
        }
    }
} else {
    echo PokazKontakt(); // Wyświetlenie formularza
}
