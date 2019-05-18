<?php
/**
 * Created by PhpStorm.
 * User: Romain
 * Date: 20/04/2019
 * Time: 20:17
 */

namespace App\Payment;


use App\App\App;

class Payment
{
    static public function all() : array
    {
        return $payment = App::$db->query('SELECT * 
                                            FROM prelevement p,
                                                  user u
                                            WHERE u.id = p.id_user
                                            ORDER BY p.id_prelevement DESC')->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function demain() : array
    {
        $date = new \DateTime($groupe['next_prelevement']);
        $date->add(new \DateInterval('P1D')); //Où 'P1D' indique 'Période de 1 jour'

        $demain = App::$db->prepare('SELECT* FROM groupe WHERE next_prelevement = :next_prelevement');
        $demain->execute(['next_prelevement' => $date->format('Y-m-d')]);


        return $demain->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function fiveLastPayment(int $id) : array
    {
        $select = App::$db->prepare('SELECT * FROM prelevement WHERE id_user = :id_user ORDER BY date DESC LIMIT 0,5');
        $select->execute(['id_user' => $id]);

        if($select->rowCount() > 0){
            $lastPrelevement = $select->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $lastPrelevement ?? [];
    }

    static public function paymentPerUser(int $id) : array
    {
        $select = App::$db->prepare('SELECT * FROM prelevement WHERE id_user = :id');
        $select->execute(['id' => $id]);

        if($select->rowCount() !== 0){
            $payment = $select->fetchAll(\PDO::FETCH_ASSOC);
        }

        return $payment ?? [];
    }
}