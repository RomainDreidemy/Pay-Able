<?php
namespace App\Controller;

use App\App\App;
use App\FakeData\FakeData;
use App\FakeData\FakeGroupe;
use App\FakeData\FakeUser;
use App\Groupe\Groupe;
use App\Message\Message;
use App\Offer\Offer;
use App\Payment\Payment;
use App\Recherche\Recherche;
use App\Router\Router;
use App\Stripe\Stripe;
use App\User\User;

class AdminController extends AbstractController
{
    static public function securite() : void
    {
        if(!User::isConnect() || !User::isAdmin($_SESSION['user'])){
            header('location:' . Router::buildPath('accueil2'));
            die;
        }
    }

    static public function home() : void
    {
        self::securite();
//        Récupération des utilisateurs
        $users = User::all();
        self::twig(
            'backoffice/home.html',
            [
                'HTML_TITLE' => "Backoffice | Pay-Able",
                'USERS' => $users
            ]
        );
    }

    static public function userList() : void
    {
        self::securite();

//        Récupération des utilisateurs

        $users = User::all();
        self::twig(
            'backoffice/liste-utilisateurs.html',
            [
                'HTML_TITLE' => "Liste des utilisateurs | Pay-Able",
                'USERS' => $users
            ]
        );
    }

    static public function userUpdate() : void
    {
        self::securite();

        if(!isset($_GET["id"])){
            header('location:' . Router::buildPath('userList'));
            die;
        }

//        Récupération des utilisateurs
        $user = User::info($_GET['id']);
        if(sizeof($user) === 0){
            header('location:' . Router::buildPath('userList'));
            die;
        }

        $groupe = User::groupPerUser($_GET['id']);
        $payments = Payment::paymentPerUser($_GET['id']);

        if($_POST && isset($_POST['modifier'])){
            if(User::modifier($_POST, $_GET['id'])){
                header('location:' . App::URL . '/backoffice/utilisateurs/modification?id=' . $_GET['id']);
                die;
            }
        }

        if($_GET && isset($_GET['delete_verif'])){
            $delete_verif = "oui";
        }

        if($_GET && isset($_GET['action'])){
            if($_GET['action'] == 'suppression'){
                if(User::suppression($_GET['id'])){
                    header('location:' . App::URL . '/backoffice/utilisateurs/liste');
                    die;
                }

            }
        }
        self::twig(
            'backoffice/modification-utilisateurs.html',
            [
                'HTML_TITLE' => "Modification de l'utisateur n°" . $_GET['id'] . " | Pay-Able",
                'INFOS_USER' => $user,
                'GROUPES' => $groupe,
                'PAYMENTS' => $payments,
                'DELETE_VERIF' => $delete_verif ?? "non"
            ]
        );
    }

    static public function userSearch() : void
    {
        self::securite();

        if($_POST && isset($_POST['rechercher'])){
            $users = User::search($_POST);
        }

        self::twig(
            'backoffice/recherche-utilisateurs.html',
            [
                'HTML_TITLE' => "Recherche des utilisateurs | Pay-Able",
                'USERS' => $users ?? [],
                'POST' => $_POST ?: ['admin' => 'all']
            ]
        );
    }

    static public function userAddAdmin() : void
    {
        self::securite();

        if($_POST && isset($_POST['ajouter'])){
            User::ajoutAdmin($_POST);
        }

        self::twig(
            'backoffice/ajouter-admin.html',
            [
                'HTML_TITLE' => "Ajouter un nouvel admininstrateur | Pay-Able"
            ]
        );
    }

    static public function groupList() : void
    {
        self::securite();

//        Récupération des groupes
        $groupes = Groupe::all();

        self::twig(
            'backoffice/liste-groupes.html',
            [
                'HTML_TITLE' => "Liste des groupes | Pay-Able",
                'GROUPES' => $groupes
            ]
        );
    }

    static public function groupListVide() : void
    {
        self::securite();

//        Récupération des groupes
        $groupes = Groupe::listGroupevide();

        self::twig(
            'backoffice/liste-groupes-vide.html',
            [
                'HTML_TITLE' => "Liste des groupes | Pay-Able",
                'GROUPES' => $groupes
            ]
        );
    }

    static public function groupConsult() : void
    {
        self::securite();

        if(!isset($_GET["id"])){
            header('location:' . Router::buildPath('groupList'));
            die;
        }
//        Récupération des groupes
        $groupes = Groupe::info($_GET['id']);

        if(sizeof($groupes) === 0){
            header('location:' . Router::buildPath('groupList'));
            die;
        }

        $solutions = Groupe::offerInGroup($_GET['id']);
        $users = Groupe::userInGroup($_GET['id']);

        $prixTotal = 0;
        foreach ($solutions as $solution){
            $prixTotal = $prixTotal + $solution['price'];
        }

        self::twig(
            'backoffice/consultation-groupe.html',
            [
                'HTML_TITLE' => "Liste des groupes | Pay-Able",
                'INFOS_GROUPE' => $groupes,
                'SOLUTIONS' => $solutions,
                'USERS' => $users,
                'PRIX' => $prixTotal
            ]
        );
    }

    static public function groupSearch() : void
    {
        self::securite();

        if($_POST && isset($_POST['rechercher'])){
            $groupes = Groupe::search($_POST);
        }

        self::twig(
            'backoffice/recherche-groupes.html',
            [
                'HTML_TITLE' => "Recherche des groupes | Pay-Able",
                'GROUPES' => $groupes ?? [],
                'POST' => $_POST
            ]
        );
    }

    static public function logsPayment() : void
    {
        self::securite();

        $payment = Payment::all();

        self::twig(
            'backoffice/payment-logs.html',
            [
                'HTML_TITLE' => "Logs de payments | Pay-Able",
                'PAYMENTS' => $payment,
            ]
        );
    }

    static public function demainPayment() : void
    {
        self::securite();

        $payment = Payment::demain();

        self::twig(
            'backoffice/payment-demain.html',
            [
                'HTML_TITLE' => "Payments de demain | Pay-Able",
                'PAYMENTS' => $payment,
            ]
        );

    }

    static public function fakedata() : void
    {
        self::securite();

        $sql = file_get_contents(__DIR__ . '/../../config/sql24.sql');

        echo "Début du script sql";
        App::$db->exec($sql);
        echo "Fin du script sql";


        $fake = [
            FakeUser::class,
            FakeGroupe::class
        ];

        echo "Début de l'ajout des données";
        foreach ($fake as $f){
            $class = new $f();
            $class->generateData();
        }
        echo "Fin de l'ajout des données";
    }

    static public function cronCharge() : void
    {

        if(!isset($_GET['token']) || $_GET['token'] != '3565895723548'){
            header('location:' . Router::buildPath('accueil2'));
            die;
        }
        echo "Début des prélèvements <br>";

        Stripe::payment();

        echo "Fin des prélèvements <br>";
    }

    static public function cronCleaning() : void
    {
        if(!isset($_GET['token']) || $_GET['token'] != '3565895723548'){
            header('location:' . Router::buildPath('accueil2'));
            die;
        }
        echo "Début du cleaning <br>";

        $groupesVide = Groupe::listGroupevide();

        foreach ($groupesVide as $groupevide){
            $delete = App::$db->prepare('DELETE FROM groupe WHERE id_group = :id_group');
            $delete->execute([
                'id_group' => $groupevide['id_group']
            ]);
        }

        echo "Fin du cleaning <br>";
    }

    static public function ajaxRechercheUtilisateur() : void
    {
        self::json(Recherche::rechercher($_GET['recherche']));
    }

    static public function webhook() : void
    {
        echo "Début de la webhook";
        http_response_code(200); // Il faut renvoyer un code 200 pour indiquer à Stripe que le hook s'est bien lancé
// ...
        $input = file_get_contents('php://input');
        $received_event = json_decode($input); // On récupère l'évènement envoyé
// On peut ici mettre un filtre pour ne faire un traitement que pour certains évènements
        $event = \Stripe\Event::retrieve($received_event->id);

        App::Debug($event);
        App::Debug($_GET);
        echo "Fin de la webhook";
    }
}