<?php

namespace App\Mailer;
use App\App\App;
use App\Message\Message;
use App\Offer\Offer;
use PHPMailer\PHPMailer\PHPMailer;

class Mailer
{
    static private function smtpMailer($to, $subject, $body) {
        $mail = new PHPMailer();  // Cree un nouvel objet PHPMailer
        $mail->CharSet = 'UTF-8';
        $mail->IsSMTP(); // active SMTP
        $mail->SMTPDebug = 0;  // debogage: 1 = Erreurs et messages, 2 = messages seulement
        $mail->SMTPAuth = true;  // Authentification SMTP active
        $mail->SMTPSecure = 'ssl'; // Gmail REQUIERT Le transfert securise
        $mail->Host = 'smtp.gmail.com';
        $mail->Port = 465;
        $mail->Username = 'payable.projet@gmail.com';
        $mail->Password = 'etxzh93r';
        $mail->SetFrom('payable.projet@gmail.com', 'Pay-Able');
        $mail->Subject = $subject;
        $mail->msgHTML($body);
        $mail->AddAddress($to);
        if(!$mail->Send()) {
            Message::add(Message::MSG_ERROR, 'Envoi du mail impossible ' . $mail->ErrorInfo);
            return 'Mail error: '.$mail->ErrorInfo;
        } else {
            return true;
        }
    }

    static public function invitationGroupe(array $emails,$id_group,$token) : bool
    {

        foreach ($_SESSION['createGroupe']['solutions'] as $solution){
            $solutions[] = Offer::info($solution);
        }

        foreach ($solutions as $solution){
            $nomSolution[] = $solution['name'];
        }

        $nomSolution = implode(',%20', $nomSolution);

        $total = 0;
        foreach ($solutions as $solution){
            $total = $total + $solution['price'];
        }


        $lien = 'https://pay-able.fr/mail/invitation-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']) .
            '&solutions=' . urlencode($nomSolution) .
            '&total=' . urlencode(($total/$_SESSION['createGroupe']['taille'])) .
            '&id_groupe=' . urlencode($id_group) .
            '&token=' . urlencode($token);


        $messageMail = file_get_contents($lien);
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' vous invite à rejoindre un groupe Pay-Able';

        foreach ($emails as $email){


            // Envoi
            if(!self::smtpMailer($email, $subject, $messageMail)){
                return false;
            }
        }

        return true;
    }

    static public function creationGroupe(string $email,$id_group,$token) : bool
    {

        foreach ($_SESSION['createGroupe']['solutions'] as $solution){
            $solutions[] = Offer::info($solution);
        }

        foreach ($solutions as $solution){
            $nomSolution[] = $solution['name'];
        }

        $nomSolution = implode(',%20', $nomSolution);

        $total = 0;
        foreach ($solutions as $solution){
            $total = $total + $solution['price'];
        }


        $lien = 'https://pay-able.fr/mail/creation-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']) .
            '&solutions=' . urlencode($nomSolution) .
            '&total=' . urlencode(($total/$_SESSION['createGroupe']['taille'])) .
            '&id_groupe=' . urlencode($id_group) .
            '&token=' . urlencode($token);


        $messageMail = file_get_contents($lien);
        $subject = 'Confirmation de la création de votre groupe Pay-Able';

        // Envoi
        if(!self::smtpMailer($email, $subject, $messageMail)){
            return false;
        }


        return true;
    }

    static public function activationGroupe(string $email)
    {
        $lien = 'https://pay-able.fr/mail/activation-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']);

        $messageMail = file_get_contents($lien);
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a activer le groupe';


        $to = $email;
        $message = $messageMail;

        // Envoi
        if(!self::smtpMailer($email, $subject, $messageMail)){
            return false;
        }
    }

    static public function desactivationGroupe(string $email)
    {
        $lien = 'https://pay-able.fr/mail/desactivation-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']);

        $messageMail = file_get_contents($lien);
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a désactivé le groupe';

        // Envoi
        if(!self::smtpMailer($email, $subject, $messageMail)){
            return false;
        }
    }

    static public function rejoindreGroupe(string $email)
    {
        $lien = 'https://pay-able.fr/mail/rejoindre-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']);

        $messageMail = file_get_contents($lien);
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a rejoint le groupe';
        // Envoi
        if(!self::smtpMailer($email, $subject, $messageMail)){
            return false;
        }
    }

    static public function quitteGroupe(string $email)
    {
        $lien = 'https://pay-able.fr/mail/quitte-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']);

        $messageMail = file_get_contents($lien);
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a quitté le groupe';

        // Envoi
        if(!self::smtpMailer($email, $subject, $messageMail)){
            return false;
        }
    }

    static public function inscription(string $email)
    {
        $lien = 'https://pay-able.fr/mail/inscription?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']) .
            '&email=' . urlencode($email);

        $messageMail = file_get_contents($lien);
        $subject = 'Merci de vous être inscrit sur Pay-Able';

        // Envoi
        if(!self::smtpMailer($email, $subject, $messageMail)){
            return false;
        }
    }

    static public function prelevement(string $email, $prix)
    {
        $lien = 'https://pay-able.fr/mail/prelevement?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']) .
            '&prix=' . urlencode($prix);

        $messageMail = file_get_contents($lien);
        $subject = 'Reçu du prélèvement Pay-Able';


        // Envoi
        if(!self::smtpMailer($email, $subject, $messageMail)){
            return false;
        }
    }

}