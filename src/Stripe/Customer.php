<?php
namespace App\Stripe;

use App\App\App;
use App\Message\Message;

class Customer
{
    static public function all()
    {
        \Stripe\Stripe::setApiKey("sk_test_fbfPWCoJ4JJcwdeLh6qsndV8");
        return \Stripe\Customer::all();
    }

    static public function alreadyExist($id)
    {
        $select = App::$db->prepare('SELECT * FROM user WHERE id = :id');
        $requete = $select->execute(['id' => $id]);
        $user = $select->fetch(\PDO::FETCH_ASSOC);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Erreur lors de la requête MYSQL');
            return false;
        }

        if($user['id_stripe'] != NULL){
            return true;
        }

        return false;
    }

    static public function asCard($id)
    {
        $select = App::$db->prepare('SELECT id_stripe FROM user WHERE id = :id');
        $select->execute(['id' => $id]);

        $id_stripe = $select->fetch(\PDO::FETCH_ASSOC);

        return $id_stripe['id_stripe'];
    }
}