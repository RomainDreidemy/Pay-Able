<?php

namespace App\Mailer;


use App\Offer\Offer;

class Mailer
{
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

        foreach ($emails as $email){

            $to      = $email;
            $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' vous invite à rejoindre un groupe Pay-Able';
            $message = $messageMail;

            // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
            $headers = array(
                'From' => 'PAY-ABLE <contact@pay-able.fr>',
                'To' => $email,
                'X-Mailer' => 'PHP/' . phpversion(),
                'MIME-Version' => '1.0',
                'Content-type' =>  'text/html; charset=utf-8'
            );
            // Envoi

            if(!mail($to, $subject, $message, $headers)){
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

            $to      = $email;
            $subject = 'Confirmation de la création de votre groupe Pay-Able';
            $message = $messageMail;

            // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
            $headers = array(
                'From' => 'PAY-ABLE <contact@pay-able.fr>',
                'To' => $email,
                'X-Mailer' => 'PHP/' . phpversion(),
                'MIME-Version' => '1.0',
                'Content-type' =>  'text/html; charset=utf-8'
            );
            // Envoi

            if(!mail($to, $subject, $message, $headers)){
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


        $to = $email;
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a activer le groupe';
        $message = $messageMail;

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers = array(
            'From' => 'PAY-ABLE <contact@pay-able.fr>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=utf-8'
        );

        // Envoi
        if (!mail($to, $subject, $message, $headers)) {
            return false;
        }
    }

    static public function desactivationGroupe(string $email)
    {
        $lien = 'https://pay-able.fr/mail/desactivation-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']);

        $messageMail = file_get_contents($lien);


        $to = $email;
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a désactivé le groupe';
        $message = $messageMail;

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers = array(
            'From' => 'PAY-ABLE <contact@pay-able.fr>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=utf-8'
        );

        // Envoi
        if (!mail($to, $subject, $message, $headers)) {
            return false;
        }
    }

    static public function rejoindreGroupe(string $email)
    {
        $lien = 'https://pay-able.fr/mail/rejoindre-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']);

        $messageMail = file_get_contents($lien);

        $to = $email;
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a rejoint le groupe';
        $message = $messageMail;

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers = array(
            'From' => 'PAY-ABLE <contact@pay-able.fr>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=utf-8'
        );

        // Envoi
        if (!mail($to, $subject, $message, $headers)) {
            return false;
        }
    }

    static public function quitteGroupe(string $email)
    {
        $lien = 'https://pay-able.fr/mail/quitte-groupe?prenom=' .
            urlencode($_SESSION['user']['surname']) .
            '&nom=' . urlencode($_SESSION['user']['name']);

        $messageMail = file_get_contents($lien);

        $to = $email;
        $subject = $_SESSION['user']['name'] . ' ' . $_SESSION['user']['surname'] . ' a quitté le groupe';
        $message = $messageMail;

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers = array(
            'From' => 'PAY-ABLE <contact@pay-able.fr>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=utf-8'
        );

        // Envoi
        if (!mail($to, $subject, $message, $headers)) {
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


        $to = $email;
        $subject = 'Merci de vous être inscrit sur Pay-Able';
        $message = $messageMail;

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers = array(
            'From' => 'PAY-ABLE <contact@pay-able.fr>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=utf-8'
        );

        // Envoi
        if (!mail($to, $subject, $message, $headers)) {
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


        $to = $email;
        $subject = 'Reçu du prélèvement Pay-Able';
        $message = $messageMail;

        // Pour envoyer un mail HTML, l'en-tête Content-type doit être défini
        $headers = array(
            'From' => 'PAY-ABLE <contact@pay-able.fr>',
            'X-Mailer' => 'PHP/' . phpversion(),
            'MIME-Version' => '1.0',
            'Content-type' => 'text/html; charset=utf-8'
        );

        // Envoi
        if (!mail($to, $subject, $message, $headers)) {
            return false;
        }
    }

}