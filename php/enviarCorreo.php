<?php

//Import PHPMailer classes into the global namespace
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require '../phpMailer/src/Exception.php';
require '../phpMailer/src/PHPMailer.php';
require '../phpMailer/src/SMTP.php';

//Create an instance; passing `true` enables exceptions
$mail = new PHPMailer(true);

$errorMSG = "";

// NAME
if (empty($_POST["userName"])) {
    $errorMSG = "Nombre es requerido ";
} else {
    $name = $_POST["UserName"];
}

// EMAIL
if (empty($_POST["UserEmail"])) {
    $errorMSG .= "Email es requerido";
} else {
    $email = $_POST["UserEmail"];
}

// Phone Number
if (empty($_POST["subject"])) {
    $errorMSG .= "Número de telefono es requerido ";
} else {
    $phone_number = $_POST["subject"];
}

// MESSAGE
if (empty($_POST["message"])) {
    $errorMSG .= "Mensaje es requerido ";
} else {
    $message = $_POST["message"];
}

// reCaptcha
if (empty($_POST['g-recaptcha-response'])) {
    $errorMSG .= "captcha es requerida";
} else {
    $captcha = $_POST['g-recaptcha-response'];
}


if ($errorMSG == "") {
    $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify", false, $context), true);
    // initiate the curl request
    $request = curl_init();

    curl_setopt($request, CURLOPT_URL, "https://www.google.com/recaptcha/api/siteverify");
    curl_setopt($request, CURLOPT_POST, 1);
    curl_setopt(
        $request,
        CURLOPT_POSTFIELDS,
        "secret=6LcL6JolAAAAAOwnrsXlUZpcZTUl7NHkQ3SB2GXz&response=" . $captcha
    );

    // catch the response
    curl_setopt($request, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($request);
    curl_close($request);
    if ($response['success'] == false) {
        echo 'Ocurrió un error con el captcha, por favor vuelve a actualizar la página';
    } else {

        // prepare email body text
        $Body = "";
        $Body .= "<b>Nombre: </b>";
        $Body .= $name;
        $Body .= "\n <br>";
        $Body .= "<b>Correo electrónico: </b>";
        $Body .= $email;
        $Body .= "\n <br>";
        $Body .= "<b>Asunto: Nueva solicitud de contacto</b>";
        $Body .= "\n <br>";
        $Body .= "<b>Número de telefono: </b>";
        $Body .= $subject;
        $Body .= "\n <br>";
        $Body .= "<b>Mensaje: </b>";
        $Body .= $message;
        $Body .= "\n <br>";
        $Body .= "Este correo se ha enviado desde la página web de Gimax";

        //street_address
        // send email
        // $success = mail($EmailTo, $Subject, $Body);

        // redirect to success page
        try {
            //Server settings
            $mail->SMTPDebug = SMTP::DEBUG_SERVER;                   //Enable verbose debug output
            $mail->isSMTP(); //Send using SMTP
            $mail->Host = 'sukra.hosting-mexico.net'; //Set the SMTP server to send through
            $mail->SMTPAuth = true; //Enable SMTP authentication
            $mail->Username = 'webpage@gigaone.mx'; //SMTP username
            $mail->Password = ''; //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable implicit TLS encryption
            // $mail->Port = 587; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            $mail->Port = 465; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom('webpage@gimax.mx', 'Formulario de contacto');
            // $mail->addAddress('leilani.arreola@wifmax.com ', 'Dpto. Ventas'); //Add a recipient
            $mail->addAddress('atención_a_clientes@GIGAONE.MX', 'Atención a Clientes | GIGAONE'); //Add a recipient
            $mail->addCC('nahimrgz@gmail.com');

            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->CharSet = 'UTF-8';
            $mail->Subject = 'Nueva solicitud de cotización';
            $mail->Body = $Body;
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            echo '<script type="text/javascript">
                        alert("Correo enviado correctamente, pronto un ejecutivo se comunicará con usted");
                        window.location.href="https://gigaone.mx";
                  </script>';

        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    }

} else {
    if ($errorMSG == "") {
        echo "Something went wrong :(";
    } else {
        echo $errorMSG;
    }
}