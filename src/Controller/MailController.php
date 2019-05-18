<?php
namespace App\Controller;

use App\App\App;
use App\Groupe\Groupe;
use App\Offer\Offer;
use App\Payment\Payment;
use App\Router\Router;
use App\Stripe\Customer;
use App\Stripe\Stripe;
use App\User\User;

class MailController extends AbstractController
{
    static public function invitationGroupe() : void
    {
        self::twig(
            'mail/invitation-groupe.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom']),
                'SOLUTIONS' => urldecode($_GET['solutions']),
                'TOTAL' => urldecode($_GET['total']),
                'ID_GROUPE' => urldecode($_GET['id_groupe']),
                'TOKEN' => urldecode($_GET['token'])
            ]
        );
    }

    static public function creationGroupe() : void
    {
        self::twig(
            'mail/creation-groupe.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom']),
                'SOLUTIONS' => urldecode($_GET['solutions']),
                'TOTAL' => urldecode($_GET['total']),
                'ID_GROUPE' => urldecode($_GET['id_groupe']),
                'TOKEN' => urldecode($_GET['token'])
            ]
        );
    }

    static public function activationGroupe() : void
    {
        self::twig(
            'mail/activation-groupe.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom'])
            ]
        );
    }

    static public function desactivationGroupe() : void
    {
        self::twig(
            'mail/desactivation-groupe.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom'])
            ]
        );
    }

    static public function rejoindreGroupe() : void
    {
        self::twig(
            'mail/rejoindre-groupe.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom'])
            ]
        );
    }

    static public function quitteGroupe() : void
    {
        self::twig(
            'mail/quitte-groupe.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom'])
            ]
        );
    }

    static public function inscription() : void
    {
        self::twig(
            'mail/inscription.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom']),
                'EMAIL' => urldecode($_GET['email'])
            ]
        );
    }

    static public function prelevement() : void
    {
        self::twig(
            'mail/prelevement.html',
            [
                'PRENOM' => urldecode($_GET['prenom']),
                'NOM' => urldecode($_GET['nom']),
                'PRIX' => urldecode($_GET['prix'])
            ]
        );
    }

}