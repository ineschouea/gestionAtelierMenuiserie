<?php

namespace App\DataFixtures;
use App\Entity\Contact;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class ContactFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    { 
        for($i = 1; $i <= 10; $i++){
        $faker= \Faker\Factory::create('fr_FR');
        $contact=new Contact();
        $contact->setNom($faker->firstName())
                ->setPrenom($faker->LastName)
                ->setemail($faker->email  )
                ->setTel($faker->e164PhoneNumber)
                ->setObjet($faker->sentence())
                ->setMsg($faker->paragraph(7));
        $manager->persist($contact); 
        }
        $manager->flush();
    }
}
