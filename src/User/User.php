<?php
namespace App\User;

use App\App\App;
use App\Mailer\Mailer;
use App\Message\Message;
use App\Router\Router;

class User
{
    static public function isConnect() : bool
    {
        if(!isset($_SESSION) || !isset($_SESSION['user'])){
            $_SESSION['redirect'] = Router::getNameRoute();
            return false;
        }
        return true;
    }

    static public function isAdmin($id) : bool
    {
        if($id['admin'] == 1){
            return true;
        }
        return false;
    }

    static public function goRedirect() : string
    {
        $red = $_SESSION['redirect'];
        unset($_SESSION['redirect']);
        return $red;
    }

    static public function connexion(string $email, string $password) : bool
    {
        $error = [
            "Email incorrecte" => !preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $email),
            "Email ou mot de passe vide" => empty(trim($email)) || empty(trim($password))
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }


//      Recherche de la personne dans la base de donnée

        $select = App::$db->prepare('SELECT * FROM user WHERE email = :email');
        $requete = $select->execute([
            'email' => $email
        ]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Erreur lors de la requête MYSQL');
            return false;
        }

        if($select->rowCount() != 1){
            Message::add(Message::MSG_ERROR, 'l\'adresse email est incorrecte');
            return false;
        }

        $user = $select->fetch(\PDO::FETCH_ASSOC);

        if(!password_verify($password, $user['password'])){
            Message::add(Message::MSG_ERROR, 'Mot de passe incorrect');
            return false;
        }

        $_SESSION['user'] = $user;

        return true;
    }

    static public function deconexion() : void
    {
        unset($_SESSION['user']);
        unset($_SESSION['redirect']);

        Message::add(Message::MSG_SUCCESS, 'Vous avez été déconnecté');
    }

    static public function inscription(array $infos) : bool
    {
        unset($infos['signin']);

        // Si tous les informations sont fournies
        foreach ($infos as $info){
            if (empty(trim($info))){
                Message::add(Message::MSG_ERROR, 'Merci de remplir toutes les informations');
                return false;
            }
        }

//      Gestion de multiple erreurs
        $error = [
            "Le format de l'email est incorrect" => !preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $infos['email']),
            "Les emails ne sont pas identique" => $infos['email'] !== $infos['emailVerify'],
            "Le format du numéro de téléphone est incorrect" => strlen($infos['phone_number']) !== 9,
            "Le format du code postal est incorrect" => strlen($infos['postal_code']) !== 5
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }

//      Vérification que l'email est unique
        $select = App::$db->prepare('SELECT * FROM user WHERE email = :email');
        $select->execute([
            'email' => $infos['email']
        ]);

        if($select->rowCount() !== 0){
            Message::add(Message::MSG_ERROR, 'L\'email est déjà utilisé');
            return false;
        }

//      Hashage du mot de passe

        $infos['password'] = password_hash($infos['password'],PASSWORD_DEFAULT);

//      Insertion du nouvel utilisateur dans la base de donnée
        $select = App::$db->prepare('INSERT INTO user(surname, name, birth_date, email, password, postal_code, phone_number, admin) VALUES(:surname, :name, :birth_date, :email, :password, :postal_code, :phone_number, 0)');
        $requete = $select->execute([
            'surname' => $infos['surname'],
            'name' => $infos['name'],
            'birth_date' => $infos['birth_date'],
            'email' => $infos['email'],
            'password' => $infos['password'],
            'postal_code' => $infos['postal_code'],
            'phone_number' => $infos['phone_number']
        ]);

//      Si la requête n'a pas fonctionné
        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

//        Envoie de l'email
        Mailer::inscription($infos['email']);

//      Si tout c'est bien passé
        Message::add(Message::MSG_SUCCESS, 'Votre compte a été créé avec success');
        return true;
    }

    static public function ajoutAdmin(array $infos) : bool
    {
        unset($infos['signin']);

        // Si tous les informations sont fournies
        foreach ($infos as $info){
            if (empty(trim($info))){
                Message::add(Message::MSG_ERROR, 'Merci de remplir toutes les informations');
                return false;
            }
        }

//      Gestion de multiple erreurs
        $error = [
            "Le format de l'email est incorrect" => !preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $infos['email']),
            "Les emails ne sont pas identique" => $infos['email'] !== $infos['emailVerify'],
            "Le format du numéro de téléphone est incorrect" => strlen($infos['phone_number']) !== 9,
            "Le format du code postal est incorrect" => strlen($infos['postal_code']) !== 5
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }

//      Vérification que l'email est unique
        $select = App::$db->prepare('SELECT * FROM user WHERE email = :email');
        $select->execute([
            'email' => $infos['email']
        ]);

        if($select->rowCount() !== 0){
            Message::add(Message::MSG_ERROR, 'L\'email est déjà utilisé');
            return false;
        }

//      Hashage du mot de passe

        $infos['password'] = password_hash($infos['password'],PASSWORD_DEFAULT);

//      Insertion du nouvel utilisateur dans la base de donnée
        $select = App::$db->prepare('INSERT INTO user(surname, name, birth_date, email, password, postal_code, phone_number, admin) VALUES(:surname, :name, :birth_date, :email, :password, :postal_code, :phone_number, 1)');
        $requete = $select->execute([
            'surname' => $infos['surname'],
            'name' => $infos['name'],
            'birth_date' => $infos['birth_date'],
            'email' => $infos['email'],
            'password' => $infos['password'],
            'postal_code' => $infos['postal_code'],
            'phone_number' => $infos['phone_number']
        ]);

//      Si la requête n'a pas fonctionné
        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

//      Si tout c'est bien passé
        Message::add(Message::MSG_SUCCESS, 'Votre compte a été créé avec success');
        return true;
    }

    static public function modifier(array $infos, int $id) : bool
    {
        unset($infos['modifier']);

        // Si tous les informations sont fournies
        foreach ($infos as $info){
            if (empty(trim($info))){
                Message::add(Message::MSG_ERROR, 'Merci de remplir toutes les informations');
                return false;
            }
        }

//      Gestion de multiple erreurs
        $error = [
            "Le format de l'email est incorrect" => !preg_match('#^[\w.-]+@[\w.-]+\.[a-z]{2,6}$#i', $infos['email']),
            "Le format du numéro de téléphone est incorrect" => strlen($infos['phone_number']) !== 9,
            "Le format du code postal est incorrect" => strlen($infos['postal_code']) !== 5
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return false;
        }

//      Vérification que l'email est unique
        $select = App::$db->prepare('SELECT * FROM user WHERE email = :email AND id != :id');
        $select->execute([
            'email' => $infos['email'],
            'id' => $id
        ]);

        if($select->rowCount() !== 0){
            Message::add(Message::MSG_ERROR, 'L\'email est déjà utilisé');
            return false;
        }


//      Update de l'utilisateur dans la base de donnée
        $select = App::$db->prepare('UPDATE user SET surname = :surname, name = :name, birth_date = :birth_date, email = :email, postal_code = :postal_code, phone_number = :phone_number WHERE id = :id');
        $requete = $select->execute([
            'surname' => $infos['surname'],
            'name' => $infos['name'],
            'birth_date' => $infos['birth_date'],
            'email' => $infos['email'],
            'postal_code' => $infos['postal_code'],
            'phone_number' => $infos['phone_number'],
            'id' => $id
        ]);

//      Si la requête n'a pas fonctionné
        if(!$requete){
            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
            return false;
        }

//      Si tout c'est bien passé
        Message::add(Message::MSG_SUCCESS, 'La modification a été effectué avec success');
        return true;
    }

    static public function modifierPassword(array $infos, int $id) : bool
    {
        unset($infos['modifier']);

//       Vérification si tous les champs sont remplis
        foreach ($infos as $info){
            if(empty($info)){
                Message::add(Message::MSG_ERROR, 'Tous les champs doivent être remplis');
                return false;
            }
        }

//        Vérification si les champs sont identique
        if($infos['password'] !== $infos['passwordVerify']){
            Message::add(Message::MSG_ERROR, 'Les mots de passe doivent être identique');
            return false;
        }

//        Cryptage du mot de passe
        $password = password_hash($infos['password'], PASSWORD_DEFAULT);

//        Modification en base de donnée
        $update = App::$db->prepare('UPDATE user SET password = :password WHERE id = :id');
        $requete = $update->execute([
            'password' => $password,
            'id' => $id
        ]);

        if(!$requete){
            Message::add(Message::MSG_ERROR, 'La requête SQL à échoué');
            return false;
        }


        Message::add(Message::MSG_SUCCESS, 'Le mot de passe à été modifié avec succès');
        return true;
    }

    static public function suppression($id) : bool
    {
//        Récupération des groupes
        $select = App::$db->prepare('SELECT g.id_group AS id, ug.role AS role FROM groupe g, user_group ug WHERE g.id_group = ug.id_group AND ug.id_user = :id_user');
        $select->execute(['id_user' => $id]);

        if($select->rowCount() !== 0){
            $groupes = $select->fetchAll(\PDO::FETCH_ASSOC);
        }

//        Désactivation des groupes
        if(isset($groupes)){
            foreach ($groupes as $groupe){
                $update = App::$db->prepare('UPDATE groupe SET statut = 0, next_prelevement = NULL WHERE id_group = :id_group');
                $update->execute(['id_group' => $groupe['id']]);

                if($groupe['role'] == 1){
                    $newPropri = App::$db->prepare('SELECT id_user FROM user_group WHERE id_group = :id_group AND role != 1 LIMIT 1');
                    $requete = $newPropri->execute(['id_group' => $groupe['id']]);

                    if($newPropri->rowCount() === 1){

                        $newPropri = $newPropri->fetch(\PDO::FETCH_ASSOC);
                        $update = App::$db->prepare('UPDATE user_group SET role = 1 WHERE id_user = :id_user AND id_group = :id_group');
                        $requete = $update->execute([
                            'id_group' => $groupe['id'],
                            'id_user' => $newPropri['id_user']
                        ]);

                        if(!$requete){
                            Message::add(Message::MSG_ERROR, 'Problème lors de la requête MYSQL');
                            return false;
                        }
                    }
                }
            }
        }

//        Suppression de l'utilisateur
        $delete = App::$db->prepare('DELETE FROM user WHERE id = :id');
        $delete->execute(['id' => $id]);

        Message::add(Message::MSG_SUCCESS, 'L`\'utilisateur à été supprimé');
        return true;
    }

    static public function all() : array
    {
        return $users = App::$db->query('SELECT * FROM user')->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function info(int $id) : array
    {

        $error = [
            "Identifiant vide" => empty(trim($id))
        ];

        if(in_array(true, $error)){
            Message::add(Message::MSG_ERROR, array_search( true, $error));
            return [];
        }

        $select = App::$db->prepare('SELECT * FROM user WHERE id = :id');
        $select->execute(['id' => $id]);

        if($select->rowCount() != 1){
            Message::add(Message::MSG_ERROR, 'Identifiant inconnu');
            return [];
        }

        return $user = $select->fetch(\PDO::FETCH_ASSOC);
    }

    static public function groupPerUser(int $id_user) : array
    {
        $select = App::$db->prepare('SELECT g.* FROM groupe g, user_group ug WHERE g.id_group = ug.id_group AND ug.id_user = :id_user');
        $select->execute([
            'id_user' => $id_user
        ]);

        return $select->fetchAll(\PDO::FETCH_ASSOC);
    }

    static public function isPropriGroup(int $id_user, int $id_group) : bool
    {
        $select = App::$db->prepare('SELECT * FROM user_group WHERE id_user = :id_user AND id_group = :id_group');
        $select->execute([
            'id_group' => $id_group,
            'id_user' => $id_user
        ]);
        $user_group = $select->fetch(\PDO::FETCH_ASSOC);

        if($user_group['role'] == 1){
            return true;
        } else{
            return false;
        }
    }

    static public function search(array $search) : array
    {
        unset($search['rechercher']);
        $condition = 'SELECT * FROM user WHERE';
        foreach($search as $key => $value) {
            if (!empty($value) || $key === 'admin') {

                if ($key === 'admin') {
                    if ($value != 'all') {
                        $condition .= ' ' . $key . ' = ' . $value . ' AND';
                    }
                } else {
                    $condition .= ' ' . $key . " LIKE '%" . $value . "%' AND";
                }
            }
        }


        $position_espace = strrpos($condition, " "); //on récupère l'emplacement du dernier espace dans la chaine, pour ne pas découper un mot.
        $condition = substr($condition, 0, $position_espace);  //on découpe à la fin du dernier mot

        $select = App::$db->query($condition);

        $users = $select->fetchAll(\PDO::FETCH_ASSOC);

        return $users;
    }

    static public function changeCard($token) : bool
    {
        if (!empty($_SESSION['user']['id_stripe'])){
            $customer = \Stripe\Stripe::setApiKey("sk_test_fbfPWCoJ4JJcwdeLh6qsndV8");

            \Stripe\Customer::update(
                $_SESSION['user']['id_stripe'],
                [
                    'source' => $token,
                ]
            );

            return true;
        }else {
            \Stripe\Stripe::setApiKey("sk_test_fbfPWCoJ4JJcwdeLh6qsndV8");
            $customer = \Stripe\Customer::create([
                "description" => "{$_SESSION['user']['name']} {$_SESSION['user']['surname']}",
                "email" => $_SESSION['user']['email'],
                "source" => $token
            ]);
            return true;
        }
    }

}