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

class HomeController extends AbstractController
{
    static public function home() : void
    {
        self::twig(
            'home.html',
            [
                'HTML_TITLE' => "Accueil | Pay-Able",
            ]
        );
    }

    static public function choice() : void
    {
        self::twig(
            'choice.html',
            [
                'HTML_TITLE' => "Créer/Rejoindre un groupe | Pay-Able",
            ]
        );
    }

    static public function createGroup1() : void
    {
        if($_POST && isset($_POST['nom'])){
            if(Groupe::etape1($_POST['nom'])){
                header('location:' . Router::buildPath('creerGroupe2'));
                die;
            }
        }

        self::twig(
            'creer-groupe-1.html',
            [
                'HTML_TITLE' => "Choisie un nom | Pay-Able",
                'PREREMPLISSAGE_NOM' => $_SESSION['createGroupe']['nom'] ?? ''
            ]
        );
    }

    static public function createGroup2() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
            die;
        }

        if(!isset($_SESSION['createGroupe']['nom'])){
            header('location:' . Router::buildPath('creerGroupe1'));
            die;
        }

        if($_POST && isset($_POST['envoie'])){
            if(Groupe::etape2($_POST['solution'] ?? [], intval($_POST['nbperson']) ?? [])){
                header('location:' . Router::buildPath('creerGroupe3'));
                die;
            }
        }

//        Récupération des solution

        $select = App::$db->query('SELECT * FROM offer LIMIT 0,4');
        $offers = $select->fetchAll(\PDO::FETCH_ASSOC);

        self::twig(
            'creer-groupe-2.html',
            [
                'HTML_TITLE' => "Choisie une solution | Pay-Able",
                'OFFERS' => $offers
            ]
        );

    }

    static public function createGroup3() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
        }

        if(!isset($_SESSION['createGroupe']['solutions']) || !isset($_SESSION['createGroupe']['taille'])){
            header('location:' . Router::buildPath('creerGroupe2'));
            die;
        }

        if($_POST && isset($_POST['envoie'])){
            if(Groupe::etape3($_POST['email'] ?? [])){
                header('location:' . Router::buildPath('creerGroupe4'));
            }
        }

        self::twig(
            'creer-groupe-3.html',
            [
                'HTML_TITLE' => "Invite des personnes | Pay-Able",
                'NB_PERSON' => $_SESSION['createGroupe']['taille'] - 1
            ]
        );
    }

    static public function createGroup4() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
        }

        if(!isset($_SESSION['createGroupe']['invitations'])){
            header('location:' . Router::buildPath('creerGroupe3'));
            die;
        }

        foreach ($_SESSION['createGroupe']['solutions'] as $solution){
            $solutions[] = Offer::info($solution);
        }

        $total = 0;
        foreach ($solutions as $solution){
            $total = $total + $solution['price'];
        }

        self::twig(
            'creer-groupe-4.html',
            [
                'HTML_TITLE' => "Récapitulatif | Pay-Able",
                'INFO_GROUPE' => $_SESSION['createGroupe'],
                'SOLUTIONS' => $solutions ?? '',
                'TOTAL' => $total
            ]
        );
    }

    static public function createGroup5() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
        }

        if(!isset($_SESSION['createGroupe']['invitations'])) {
            header('location:' . Router::buildPath('creerGroupe3'));
            die;
        }

        //       Si l'utilisateur à déja mis sa carte
        $asCard = Customer::asCard($_SESSION['user']['id']);


//        Si l'utilisateur à mis sa carte
        if($_POST && isset($_POST['stripeToken'])){
            if(Stripe::newCustomer($_POST['stripeToken'])){
                if(Groupe::creation()){
                    header('location:' . Router::buildPath('accueil2'));
                    die;
                }
            }
        }

//        Si il avait déjà une carte d'enregistré
        if($_GET && isset($_GET['action']) && $_GET['action'] == 'active'){
            if(Groupe::creation()){
                header('location:' . Router::buildPath('accueil2'));
                die;
            }
        }



        self::twig(
            'creer-groupe-5.html',
            [
                'HTML_TITLE' => "Payment | Pay-Able",
                'ASCARD' => $asCard
            ]
        );
    }

    static public function joinGroup()
    {
        if($_POST && isset($_POST['rejoindre'])){
            if(Groupe::check($_POST['id_group'])){
                header('location:' . Router::buildPath('rejoindre2'));
                die;
            }
        }

        if(isset($_GET['id']) && isset($_GET['token'])){
            $id = $_GET['id'];
            $token = $_GET['token'];
            $remplissage = "PAY-ABLE_{$id}_{$token}";
        }

        if(isset($_SESSION['rejoindreGroupe'])){
            $remplissage = $_SESSION['rejoindreGroupe']['id'];
        }


        self::twig(
            'rejoindre.html',
            [
                'HTML_TITLE' => "Rejoindre un groupe | Pay-Able",
                'REMPLISSAGE_ID' => $remplissage ?? ''
            ]
        );
    }

    static public function joinGroupCard()
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
            die;
        }

        if(!Groupe::check($_SESSION['rejoindreGroupe']['id'] ?? '')){
            header('location:' . Router::buildPath('rejoindre'));
            die;
        }

        $asCard = Customer::asCard($_SESSION['user']['id']);

        if($_POST && isset($_POST['stripeToken'])){
            if(Groupe::rejoindre($_SESSION['rejoindreGroupe']['id'])){
                header('location:' . Router::buildPath('profil'));
                die;
            }
        }

        //        Si il avait déjà une carte d'enregistré
        if($_GET && isset($_GET['action']) && $_GET['action'] == 'active'){
            if(Groupe::rejoindre($_SESSION['rejoindreGroupe']['id'])){
                header('location:' . Router::buildPath('profil'));
                die;
            }
        }


        self::twig(
            'rejoindre-2.html',
            [
                'HTML_TITLE' => "Ajout de la carte bancaire | Pay-Able",
                'ID_GROUPE' => $_SESSION['rejoindreGroupe']['id'],
                'ASCARD' => $asCard
            ]
        );
    }

    static public function login() : void
    {
        if($_GET && isset($_GET['logout'])){
            User::deconexion();
            header('location:connexion');
            die;
        }

        if(isset($_SESSION['user'])){
            header('location:' . Router::buildPath( 'accueil2'));
            die;
        }

        if($_POST && isset($_POST['connexion'])){
            if(User::connexion($_POST['email'], $_POST['password'])){
                if(isset($_SESSION['redirect']) && !empty(trim($_SESSION['redirect']))){
                    header('location:' . Router::buildPath($_SESSION['redirect']));
                    unset($_SESSION['redirect']);
                    die;
                }
                header('location:' . Router::buildPath('choix'));
            }
        }

        self::twig(
            'login.html',
            [
                'HTML_TITLE' => "Connexion | Pay-Able",
            ]
        );
    }

    static public function signIn() : void
    {
        if($_POST && isset($_POST['signin'])){
            if(User::inscription($_POST)){
                header('location:' . Router::buildPath('connexion'));
                die;
            }
        }

        $infos = [
            'name' => $_POST['name'] ?? '',
            'surname' => $_POST['surname'] ?? '',
            'email' => $_POST['email'] ?? '',
            'emailVerify' => $_POST['emailVerify'] ?? '',
            'password' => $_POST['password'] ?? '',
            'birth_date' => $_POST['birth_date'] ?? '',
            'postal_code' => $_POST['postal_code'] ?? '',
            'phone_number' => $_POST['phone_number'] ?? ''
        ];

        self::twig(
            'inscription.html',
            [
                'HTML_TITLE' => "Inscription | Pay-Able",
                'INFOS' => $infos
            ]
        );
    }

    static public function error404() : void
    {
        self::twig(
            'error/404.html',
            [
                'html_title' => 'Framework | 404 error',
                'title' => '404 error !',
                'accroche' => 'La page n\'existe pas',
            ]);
    }

    static public function profil() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
            die;
        }

        $user = User::info($_SESSION['user']['id']);
        $groupes = User::groupPerUser($_SESSION['user']['id']);
        $lastPayment = Payment::fiveLastPayment($_SESSION['user']['id']);

        self::twig(
            'profil/profil.html',
            [
                'HTML_TITLE' => 'Profil de ' . $user['surname'] . ' ' . $user['name'] . ' | Pay-Able',
                'INFOS' => $user,
                'GROUPES' => $groupes,
                'PRELEVEMENTS' => $lastPayment,
            ]);

    }

    static public function modifierPassword() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
            die;
        }

        if($_POST && isset($_POST['modifier'])){
            App::Debug($_POST);
            User::modifierPassword($_POST, $_SESSION['user']['id']);
        }

        self::twig(
            'profil/modifier-password.html',
            [
                'HTML_TITLE' => 'Changer de mot de passe | Pay-Able'
            ]);

    }

    static public function modifierCarte() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
            die;
        }

        if($_POST && isset($_POST['stripeToken'])){
            if(!empty($_SESSION['user']['id_stripe'])){
                 if(Stripe::changeCard($_POST['stripeToken'], $_SESSION['user']['id_stripe'])){
                     header('location:' . Router::buildPath('profil'));
                     die;
                 }
            }else{
                if(Stripe::ajoutCard($_POST['token'])){
                    header('location:' . Router::buildPath('profil'));
                    die;
                }
            }
        }


        self::twig(
            'profil/modifier-carte.html',
            [
                'HTML_TITLE' => 'Changer de carte bancaire | Pay-Able'
            ]);

    }

    static public function modifierProfil() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
            die;
        }

        if($_POST && isset($_POST['modifier'])){
            if(User::modifier($_POST,$_SESSION['user']['id'])){
                header('location:' . App::URL . '/profil/modifier');
                die;
            }
        }

        $user = User::info($_SESSION['user']['id']);



        self::twig(
            'profil/modifier.html',
            [
                'HTML_TITLE' => 'Profil de ' . $user['surname'] . ' ' . $user['name'] . ' | Pay-Able',
                'INFOS' => $user,
                'TEST' => Router::buildPath('modifierProfil')
            ]);

    }

    static public function viewGroup() : void
    {
        if(!User::isConnect()){
            header('location:' . Router::buildPath('connexion'));
            die;
        }

        if(!isset($_GET['id'])){
            header('location:' . Router::buildPath('profil'));
            die;
        }

//        Vérifie si l'utilisateur est dans le group
        if(!Groupe::inGroup($_GET['id'], $_SESSION['user']['id'])){
            header('location:' . Router::buildPath('profil'));
            die;
        }

        if(isset($_GET['action'])){
            if($_GET['action'] == 'active'){
                Groupe::activation($_GET['id'], $_SESSION['user']['id']);
            }elseif($_GET['action'] == 'desactive'){
                Groupe::desactivation($_GET['id'], $_SESSION['user']['id']);
            }elseif($_GET['action'] == 'leave'){
                if(Groupe::quitter($_GET['id'], $_SESSION['user']['id'])){
                    header('location:' . Router::buildPath('profil'));
                    die;
                }
            }
        }

//        Récupération des infos du groupe
        $groupe = Groupe::info($_GET['id']);

        if(count($groupe) == 0){
            header('location:' . Router::buildPath('profil'));
            die;
        }

//        Récupération des solutions dans le groupe
        $offers = Groupe::offerInGroup($_GET['id']);

//        Récupération des personnes du groupe
        $users = Groupe::userInGroup($_GET['id']);

//        Si l'utilisateur est propriétaire du groupe
        $role = User::isPropriGroup($_SESSION['user']['id'], $_GET['id']);

        self::twig(
            'profil/viewGroup.html',
            [
                'HTML_TITLE' => 'Gestion du groupe | Pay-Able',
                'GROUPE' => $groupe,
                'OFFERS' => $offers,
                'USERS' => $users,
                'ROLE' => $role
            ]);
    }

}