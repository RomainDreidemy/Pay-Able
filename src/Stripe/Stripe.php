<?php

namespace App\Stripe;

use App\App\App;
use App\Groupe\Groupe;
use App\Mailer\Mailer;
use App\Message\Message;
use App\Offer\Offer;
use App\User\User;

class Stripe
{
    public $privateKey = "sk_test_fbfPWCoJ4JJcwdeLh6qsndV8";

    static public function newCustomer(string $token) : bool
    {
        \Stripe\Stripe::setApiKey("sk_test_fbfPWCoJ4JJcwdeLh6qsndV8");
        $customer = \Stripe\Customer::create([
            "description" => "{$_SESSION['user']['name']} {$_SESSION['user']['surname']}",
            "email" => $_SESSION['user']['email'],
            "source" => $token
        ]);


        $update = App::$db->prepare('UPDATE user SET id_stripe = :id_stripe WHERE id = :id_user');
        $requete = $update->execute([
            'id_stripe' => $customer->id,
            'id_user' => $_SESSION['user']['id']
        ]);

        //      Si la requête n'a pas fonctionné
        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }
        return true;
    }

    static public function payment()
    {
//        Récupération de tous les utilisateurs
        $today = date("Y-m-d");
        $usersPayment = [];

//        Récupération des groupes dont le prélèvement doit être effectué aujourdhui
        $select = App::$db->query('SELECT id_group, taille FROM groupe WHERE next_prelevement <= CURDATE()');
        $groupes = $select->fetchAll(\PDO::FETCH_ASSOC);

        foreach($groupes as $groupe){
//            Récupération des utilisateurs qui sont dans les groupes à prélever
            $users = Groupe::userInGroup($groupe['id_group']);

//            Récupération des utilisateurs qui sont dans les groupes à prélever
            $offers = Groupe::offerInGroup($groupe['id_group']);

//            Calcul du prix
            $prix = 0;
            foreach ($offers as $offer){
                $prix = $prix + $offer['price'];
            }
            $prix = ($prix / $groupe['taille']) * 100;

            foreach ($users as $user){

                if(isset($error)){
                    unset($error);
                }

                try {

                    \Stripe\Stripe::setApiKey("sk_test_fbfPWCoJ4JJcwdeLh6qsndV8");

                    \Stripe\Charge::create([
                        "amount" => $prix,
                        "currency" => "eur",
                        "customer" => $user['id_stripe'],
                        "description" => "Charge for " . $user['id']
                    ]);

                } catch(\Stripe\Error\Card $e) {
                    // Since it's a decline, \Stripe\Error\Card will be caught
                    $body = $e->getJsonBody();
                    $error = $body['error']['message'];

                } catch (\Stripe\Error\RateLimit $e) {
                    $body = $e->getJsonBody();
                    $error = $body['error']['message'];
                } catch (\Stripe\Error\InvalidRequest $e) {
                    $body = $e->getJsonBody();
                    $error = $body['error']['message'];
                } catch (\Stripe\Error\Authentication $e) {
                    $body = $e->getJsonBody();
                    $error = $body['error']['message'];
                } catch (\Stripe\Error\ApiConnection $e) {
                    $body = $e->getJsonBody();
                    $error = $body['error']['message'];
                } catch (\Stripe\Error\Base $e) {
                    $body = $e->getJsonBody();
                    $error = $body['error']['message'];
                } catch (Exception $e) {
                    $body = $e->getJsonBody();
                    $error = $body['error']['message'];
                }

//                Insertion du prélèvement dans la base de donnée
                $select = App::$db->prepare('INSERT INTO prelevement(id_user, prix, date, error) VALUES(:id_user, :prix, CURDATE(), :error)');
                $select->execute([
                    'id_user' => $user['id'],
                    'prix' => ($prix / 100),
                    'error' => $error ?? 'ok'
                ]);
                Mailer::prelevement($user['email'], $prix / 100);
            }

//            Changement de la date de next_prelevement si il n'y a pas d'erreur
            if(!isset($error)){
                $update = App::$db->prepare('UPDATE groupe SET next_prelevement = DATE_ADD(next_prelevement, INTERVAL 1 MONTH) WHERE id_group = :id_group');
                $update->execute([
                    'id_group' => $groupe['id_group']
                ]);

            }
        }
    }

    static public function changeCard($token, $id_stripe) : bool
    {
            \Stripe\Stripe::setApiKey("sk_test_fbfPWCoJ4JJcwdeLh6qsndV8");

            \Stripe\Customer::update(
                $id_stripe,
                [
                    'source' => $token,
                ]
            );

            Message::add(Message::MSG_SUCCESS, 'La carte a été changé');
            return true;
    }

    static public function ajoutCard($token) : bool
    {
        \Stripe\Stripe::setApiKey("sk_test_fbfPWCoJ4JJcwdeLh6qsndV8");
        $customer = \Stripe\Customer::create([
            "description" => "{$_SESSION['user']['name']} {$_SESSION['user']['surname']}",
            "email" => $_SESSION['user']['email'],
            "source" => $token
        ]);

        Message::add(Message::MSG_SUCCESS, 'La carte a été ajouté');
        return true;
    }

}