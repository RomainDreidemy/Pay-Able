<?php
namespace App\FakeData;


use App\App\App;

class FakeGroupe extends FakeData
{

    public function generateData()
    {
        $this->createMany(500,'group', function ($index){
            $insert = App::$db->prepare('INSERT INTO groupe(name, token, statut, taille)
                                          VALUES(:name, :token, 0, :taille)');

            $taille = $this->faker->numberBetween(2,4);
            $insert->execute([
                'name' => $this->faker->userName,
                'token' => App::GenerateToken(),
                'taille' => $taille,
            ]);
            $id_group = App::$db->lastInsertId();



            for($i=0; $i < $this->faker->numberBetween(1,$taille); $i++) {
                $select = App::$db->prepare('SELECT * FROM user_group WHERE id_user = :id_user AND id_group = :id_group');
                $select->execute([
                    'id_user' => $this->getReference('user'),
                    'id_group' => $id_group
                ]);

                if($select->rowCount() < 1){
                  $insert = App::$db->prepare('INSERT INTO user_group(id_user, id_group, role) VALUES(:id_user, :id_group, :role)');
                  $insert->execute([
                      'id_user' => $this->getReference('user'),
                      'id_group' => $id_group,
                      'role' => ($i === 0) ? 1 : 0
                  ]);
                }


                if($i+1 === $taille){
                    $update = App::$db->prepare('UPDATE groupe SET statut = 1, next_prelevement = :next_prelevement WHERE id_group = :id_group');
                    $update->execute([
                        'id_group' => $id_group,
                        'next_prelevement' => $this->faker->dateTimeInInterval('now', '+ 30 days')->format('Y-m-d')
                    ]);
                }
            }


                for($i=1; $i <= $this->faker->numberBetween(1,4); $i++){

                  $insert = App::$db->prepare('INSERT INTO offer_group(id_offer, id_group)
                                            VALUES(:id_offer, :id_group)');
                  $insert->execute([
                      'id_offer' => $i,
                      'id_group' => $id_group
                  ]);
                }


            return $id_group;
        });



    }
}
