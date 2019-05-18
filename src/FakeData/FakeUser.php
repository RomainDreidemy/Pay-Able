<?php
namespace App\FakeData;


use App\App\App;

class FakeUser extends FakeData
{

    public function generateData()
    {
        $this->createMany(500,'user', function ($index){
            $insert = App::$db->prepare('INSERT INTO user(surname, name, birth_date, email, password, postal_code, phone_number, id_stripe, admin)
                                          VALUES(:surname, :name, :birth_date, :email, :password, :postal_code, :phone_number, :id_stripe, :admin)');
            $insert->execute([
                'surname' => $this->faker->firstName,
                'name' => $this->faker->lastName,
                'birth_date' => $this->faker->dateTimeBetween('-50 years', '-20 years')->format('Y-m-d'),
                'email' => 'faker-test' . $index . '@gmail.com',
                'password' => password_hash('fakerTest', PASSWORD_DEFAULT),
                'postal_code' => $this->faker->postcode,
                'phone_number' => 658957690,
                'id_stripe' => 'cus_Ev8pMKNvVZw021',
                'admin' => 0
            ]);
            return App::$db->lastInsertId();
        });

        $this->createMany(1,'userAdmin', function ($index){
            $insert = App::$db->prepare('INSERT INTO user(surname, name, birth_date, email, password, postal_code, phone_number, id_stripe, admin)
                                          VALUES(:surname, :name, :birth_date, :email, :password, :postal_code, :phone_number, :id_stripe, :admin)');
            $insert->execute([
                'surname' => 'Romain',
                'name' => 'Dreidemy',
                'birth_date' => '1999-06-19',
                'email' => 'dreidemyromain@gmail.com',
                'password' => password_hash('admin', PASSWORD_DEFAULT),
                'postal_code' => $this->faker->postcode,
                'phone_number' => 658957690,
                'id_stripe' => 'cus_EqcsoD7kraFsox',
                'admin' => 1
            ]);
            return App::$db->lastInsertId();
        });
    }
}
