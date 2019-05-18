<?php
namespace App\FakeData;


use Faker\Factory;

abstract class FakeData
{
    private static $referencesIndex = [];
    protected $faker;

    public function __construct()
    {
        $this->faker = Factory::create('fr_FR');
    }


    abstract public function generateData();

    protected function createMany(int $nb, string $name, callable $function){
        for ($i = 0; $i < $nb; $i++){
            self::$referencesIndex[$name][] = $function($i);
        }
    }

    protected function getReference(string $name) : int
    {
        if (!isset(self::$referencesIndex[$name])) {
            throw new \Exception("Clé invalide");
        }
        return $this->faker->randomElement(self::$referencesIndex[$name]);
    }
}