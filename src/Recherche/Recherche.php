<?php

namespace App\Recherche;


use App\App\App;
use App\User\User;

class Recherche
{
    static public function rechercher(string $recherche)
    {

        if(!empty($recherche)){
            $tabRecherche = preg_split('~\s+~', $recherche);

            $stringRecherche = '*' . implode('* *', $tabRecherche) . '*';

            $select = App::$db->prepare("SELECT *, MATCH(name, surname, email, id_stripe) AGAINST(:recherche IN BOOLEAN MODE) AS result FROM user HAVING result ORDER BY result DESC");
            $select->execute(['recherche' => $stringRecherche]);

            return $select->fetchAll(\PDO::FETCH_ASSOC);
        }else{
            return User::all();
        }



    }
}