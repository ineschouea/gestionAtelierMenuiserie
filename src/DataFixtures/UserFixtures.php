<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;


class UserFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
       $user1= new User();
       $user1->setNom("Wael")
            ->setPrenom("Mahmoudi")
            ->setEmail("wael.mahmoudi@gmail.com")
            ->setPassword("admin")
            ->setRole("admin");
       $manager->persist($user1); 
       $manager->flush();

       $user2= new User();
       $user2->setNom("Amal")
            ->setPrenom("Charef")
            ->setEmail("amal.charef@gmail.com")
            ->setPassword("user1")
            ->setRole("user1");
       $manager->persist($user2); 
       $manager->flush();

       $user2= new User();
       $user2->setNom("Haroun")
            ->setPrenom("Salhi")
            ->setEmail("haroun.salhi@gmail.com")
            ->setPassword("user2")
            ->setRole("user2");
       $manager->persist($user2); 
       $manager->flush();
    }
}
