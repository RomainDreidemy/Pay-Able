<?php

namespace App\Offer;

use App\App\App;

class Offer
{
    static public function all() : array
    {
        return $offer = App::$db->query('SELECT * FROM offer')->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function info($id) : array
    {
        $offer = App::$db->prepare('SELECT * FROM offer WHERE id_offer = :id_offer');
        $offer->execute(['id_offer' => $id]);
        return $offer->fetch(\PDO::FETCH_ASSOC);
    }
}