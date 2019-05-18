<?php
namespace App\Groupe;

use App\App\App;
use App\Mailer\Mailer;
use App\Message\Message;
use App\Stripe\Stripe;
use App\Stripe\Customer as CustomerStripe;
use App\User\User;

class Groupe
{
//    La création de groupe se passera en Session jusqu'à la finalisation et l'envoie dans la BDD


//    Méthode pour la première page de création de groupe
    static public function etape1($nom) : bool
    {
        //      Gestion de multiple erreurs
        $error = [
            "Le titre est vide" => empty(trim($nom))
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }

        $_SESSION['createGroupe']['nom'] = trim($nom);
        return true;
    }

//    Méthode pour ajouter les solutions

    static public function etape2(array $solutions, int $taille) : bool
    {
        //      Gestion de multiple erreurs
        $error = [
            "Sélectionner au moins une solution" => empty($solutions),
            "Selectionner la taille de votre groupe" => empty($taille)
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }

        $_SESSION['createGroupe']['solutions'] = $solutions;
        $_SESSION['createGroupe']['taille'] = $taille;

        return true;

    }

//    Méthode pour ajouter les personnes à inviter

    static public function etape3(array $emails) : bool
    {
        $_SESSION['createGroupe']['invitations'] = $emails;
        return true;
    }

    static public function check($id_group) : bool
    {
        $error = [
            "Veillez rentrer un identifiant" => empty(trim($id_group)),
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }

        $id_groupTab = explode('_', $id_group);
        $id_group2 = $id_groupTab[1];
        $token = $id_groupTab[2];

//        Si le groupe existe bien
        $select = App::$db->prepare('SELECT * FROM groupe WHERE id_group = :id_group AND token = :token');
        $requete = $select->execute([
            'id_group' => $id_group2,
            'token' => $token
        ]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

        if($select->rowCount() != 1){
            Message::add(Message::MSG_ERROR, 'Le groupe que vous essayez de rejoindre n\'existe pas');
            return false;
        }


//        Si l'utilisateur est déja dans le groupe
        $select = App::$db->prepare('SELECT * FROM user_group WHERE id_group = :id_group AND id_user = :id_user');
        $requete = $select->execute([
            'id_group' => $id_group2,
            'id_user' => $_SESSION['user']['id']
        ]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

        if($select->rowCount() >= 1){
            Message::add(Message::MSG_ERROR, 'Vous êtes déja dans ce groupe');
            return false;
        }


//        Si il y a de la place dans le groupe
        $select = App::$db->prepare('SELECT * FROM user_group WHERE id_group = :id_group');
        $requete = $select->execute([
            'id_group' => $id_group
        ]);


        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

        if($select->rowCount() > 3){ // A changer par rapport à la taille du groupe
            Message::add(Message::MSG_ERROR, 'Le groupe que vous essayez de rejoindre est plein');
            return false;
        }

        $_SESSION['rejoindreGroupe']['id'] = $id_group;
        return true;


    }

    static public function creation() : bool
    {
        //      Gestion de multiple erreurs
        $error = [
            "Le titre est vide" => empty(trim($_SESSION['createGroupe']['nom'])),
            "Pas de solution sélectionné" => count($_SESSION['createGroupe']['solutions']) == 0,
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }

//        Insertion dans la table "group"
        $token = App::GenerateToken();
        $insert = App::$db->prepare("INSERT INTO groupe (name, token, taille) VALUES(:name, :token, :taille)");
        $requete = $insert->execute([
            'name' => trim($_SESSION['createGroupe']['nom']),
            'token' => $token,
            'taille' => $_SESSION['createGroupe']['taille']
        ]);
//        Liaison de GROUP à OFFER
        $id_group = App::$db->lastInsertId();

        foreach ($_SESSION['createGroupe']['solutions'] as $solution){
            $insert = App::$db->prepare('INSERT INTO offer_group(id_offer, id_group) VALUES(:id_offer, :id_group)');
            $requete = $insert->execute([
                'id_offer' => $solution,
                'id_group' => $id_group
            ]);

            if(!$requete){
                Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
                return false;
            }
        }

//        Insersion de l'utilisateur de USER à GROUPE
        $insert = App::$db->prepare('INSERT INTO user_group (id_user, id_group, role) VALUES (:id_user, :id_group, 1)');
        $requete = $insert->execute([
            'id_user' => $_SESSION['user']['id'],
            'id_group' => $id_group
        ]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

//        Envoie des invitations par mail
        Mailer::invitationGroupe($_SESSION['createGroupe']['invitations'], $id_group, $token);
        Mailer::creationGroupe($_SESSION['user']['email'], $id_group, $token);


        unset($_SESSION['createGroupe']);

        Message::add(Message::MSG_SUCCESS, 'Votre groupe a été créé :)');
        return true;
    }


    static public function rejoindre($id_group) : bool
    {

        if(!self::check($id_group)){
            return false;
        }

        $id_group2 = explode('_', $id_group);
        $id_group2 = $id_group2[1];

        if(!CustomerStripe::alreadyExist($_SESSION['user']['id'])){
            Stripe::newCustomer($_POST['stripeToken']);
        }

//        Insertion de l'utilisateur dans le groupe
        $insert = App::$db->prepare('INSERT INTO user_group(id_user, id_group, role) VALUES(:id_user, :id_group, 0)');
        $requete = $insert->execute([
            'id_user' => $_SESSION['user']['id'],
            'id_group' => $id_group2
        ]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

//        Récupération du chef de groupe
        $select = App::$db->prepare('SELECT u.email FROM user u, user_group ug WHERE u.id = ug.id_user AND ug.id_group = :id_group AND ug.role = 1');
        $select->execute(['id_group' => $id_group2]);
        $user = $select->fetch(\PDO::FETCH_ASSOC);
        Mailer::rejoindreGroupe($user['email']);


        Message::add(Message::MSG_SUCCESS, 'Vous avez rejoin le groupe :)');
        return true;
    }

    static public function all() : array
    {
        return $groupe = App::$db->query('SELECT * FROM groupe')->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function info($id) : array
    {
        $select = App::$db->prepare('SELECT * FROM groupe WHERE id_group = :id');
        $requete = $select->execute(['id' => $id]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

        return $select->fetch(\PDO::FETCH_ASSOC) ?: [];
    }

    static public function userInGroup($id_group) : array
    {
        $select = App::$db->prepare('SELECT  * FROM user u, user_group ug WHERE u.id = ug.id_user AND ug.id_group = :id_group');
        $select->execute([
            'id_group' => $id_group
        ]);

        return $select->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function offerInGroup($id_group) : array
    {
        $select = App::$db->prepare('SELECT f.* FROM offer f, offer_group og WHERE f.id_offer = og.id_offer AND og.id_group = :id_group');
        $select->execute([
            'id_group' => $id_group
        ]);

        return $select->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function activation($id_group, $id_user) : bool
    {
//        Récupération du groupe
        $select_groupe = App::$db->prepare('SELECT * FROM groupe WHERE id_group = :id_group');
        $select_groupe->execute(['id_group' => $id_group]);
        $groupe = $select_groupe->fetch(\PDO::FETCH_ASSOC);
        $date = new \DateTime($groupe['next_prelevement']);
        $date->add(new \DateInterval('P1D')); //Où 'P1M' indique 'Période de 12 Mois'

//        Vérification de si l'utilisateur est bien propriétaire du groupe
        if(!User::isPropriGroup($id_user,$id_group)){
            Message::add(Message::MSG_ERROR, 'Vous devez être propriétaire du groupe pour l\'activer');
            return false;
        }

//        Récupération des personnes dans le groupe
        $users = Groupe::userInGroup($id_group);

//        Si le groupe est plein
        if($groupe['taille'] != count($users)){
            Message::add(Message::MSG_ERROR, 'Le groupe doit être plein pour être activé');
            return false;
        }


//        Envoie du mail
        foreach ($users as $u){
            Mailer::activationGroupe($u['email']);
        }

//        Passer le groupe en statut actif
        $update = App::$db->prepare('UPDATE groupe SET statut = 1, next_prelevement = :next_prelevement WHERE id_group = :id_group');
        $update->execute([
            'id_group' => $id_group,
            'next_prelevement' => $date->format('Y-m-d')
        ]);
        Message::add(Message::MSG_SUCCESS, 'Le groupe à été activée, les prélèvements automatique commenceront pour tous les membre du groupe');
        return true;
    }

    static public function desactivation($id_group, $id_user) : bool
    {
//        Récupération du groupe
        $select_groupe = App::$db->prepare('SELECT * FROM groupe WHERE id_group = :id_group');
        $select_groupe->execute(['id_group' => $id_group]);
        $groupe = $select_groupe->fetch(\PDO::FETCH_ASSOC);
        $users = Groupe::userInGroup($id_group);

//        Vérification de si l'utilisateur est bien propriétaire du groupe
        if(!User::isPropriGroup($id_user,$id_group)){
            Message::add(Message::MSG_ERROR, 'Vous devez être propriétaire du groupe pour le désactiver');
            return false;
        }

//        Envoie du mail
        foreach ($users as $u){
            Mailer::desactivationGroupe($u['email']);
        }


//        Passer le groupe en statut actif
        $update = App::$db->prepare('UPDATE groupe SET statut = 0, next_prelevement = NULL WHERE id_group = :id_group');
        $update->execute(['id_group' => $id_group]);
        Message::add(Message::MSG_SUCCESS, 'Le groupe à été désactivée, les prélèvements automatique s\'arrêteront pour tous les membre du groupe');
        return true;
    }

    static public function quitter($id_group, $id_user) : bool
    {
//        On change de propriétaire pour le groupe
        if(User::isPropriGroup($id_user,$id_group)){
            $newPropri = App::$db->prepare('SELECT id_user FROM user_group WHERE id_group = :id_group AND role != 1 LIMIT 1');
            $requete = $newPropri->execute(['id_group' => $id_group]);

            if($newPropri->rowCount() === 1){

                $newPropri = $newPropri->fetch(\PDO::FETCH_ASSOC);
                $update = App::$db->prepare('UPDATE user_group SET role = 1 WHERE id_user = :id_user AND id_group = :id_group');
                $requete = $update->execute([
                    'id_group' => $id_group,
                    'id_user' => $newPropri['id_user']
                ]);

                if(!$requete){
                    Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
                    return false;
                }
            }


        }

        //            Désactivation du groupe
        $update = App::$db->prepare('UPDATE groupe SET statut = 0 WHERE id_group = :id');
        $update->execute(['id' => $id_group]);

        $delete = App::$db->prepare('DELETE FROM user_group WHERE id_user = :id_user AND id_group = :id_group');

        $requete = $delete->execute([
            'id_group' => $id_group,
            'id_user' => $id_user
        ]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

        //        Récupération du chef de groupe
        $select = App::$db->prepare('SELECT u.email FROM user u, user_group ug WHERE u.id = ug.id_user AND ug.id_group = :id_group AND ug.role = 1');
        $select->execute(['id_group' => $id_group]);

        if($select->rowCount() !== 0){
            $user = $select->fetch(\PDO::FETCH_ASSOC);
            Mailer::quitteGroupe($user['email']);
        }


        Message::add(Message::MSG_SUCCESS, 'Vous avez quitté le groupe');
        return true;
    }

    static public function inGroup($id_group, $id_user) : bool
    {
        $select = App::$db->prepare('SELECT * FROM user_group WHERE id_group = :id_group AND id_user = :id_user');
        $select->execute([
            'id_group' => $id_group,
            'id_user' => $id_user
        ]);

        if($select->rowCount() != 1){
            return false;
        }


        return true;
    }

    static public function listGroupevide() : array
    {
        $groupes = self::all();
        $returnGroup = [];


        foreach ($groupes as $groupe){
            $select = App::$db->prepare('SELECT * FROM user_group WHERE id_group = :id_group');
            $select->execute(['id_group' => $groupe['id_group']]);


            if($select->rowCount() === 0){
                $returnGroup[] = $groupe;
            }
        }

        return $returnGroup;
    }

    static public function search(array $search) : array
    {
        unset($search['rechercher']);
        $condition = 'SELECT * FROM groupe WHERE';
        $search['id_group'] = explode('_', $search['id_group']);
        $search['id_group'] = $search['id_group'][1];

        foreach($search as $key => $value)
        {
            if(!empty($value) || $key === 'statut'){

                if($key === 'statut'){
                    if($value != 'all'){
                        $condition .= ' ' . $key . ' = ' . $value . ' AND';
                    }
                }else {
                    $condition .= ' ' . $key . " LIKE '%" . $value . "%' AND";
                }
            }
        }

        $position_espace = strrpos($condition, " "); //on récupère l'emplacement du dernier espace dans la chaine, pour ne pas découper un mot.
        $condition = substr($condition, 0, $position_espace);  //on découpe à la fin du dernier mot

        $select = App::$db->query($condition);

        $groupes = $select->fetchAll(\PDO::FETCH_ASSOC);

        return $groupes;
    }

}