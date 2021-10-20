<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Produit;
use App\Entity\Client;
use App\Entity\Facture;
use App\Entity\LigneCommande;


class ProduitFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker= \Faker\Factory::create('fr_FR');

        //5 clients
    $img=0;
     for($i = 1; $i <= 5; $i++){
         $client=New Client();
         $client->setCin($faker->unique()->randomNumber($nbDigits = 8))
                ->setNom($faker->name)
                ->setDateDeNaissance($faker->dateTimeBetween('-50 years'))
                ->setAdresse($faker->address)
                ->setTel($faker->e164PhoneNumber);
                $manager->persist($client);

          //pour chaque client entre 1 et 4 factures
          for($j = 1; $j <= mt_rand(1,4); $j++){
              $facture=new Facture();
              $prixTotal=0;
             //pour chaque facture entre 1 et 6 lc
              for($k = 1; $k <= mt_rand(1,6); $k++){
                $ligneCommande=new LigneCommande();
                  $img+=1;
                $produit= new Produit();
                $produit->setTitre($faker->sentence())
                    ->setDescription($faker->paragraph(5))
                    ->setPrix($faker->randomFloat($nbMaxDecimals = NULL, $min = 0, $max = NULL) // 48.8932
                    )
                    ->setImage("$img.jpg");
               $manager->persist($produit);

               $ligneCommande->setProduit($produit)
                             ->setqte($faker->numberBetween($min = 1, $max = 100));
                    $prix=$ligneCommande->getQte()*$ligneCommande->getProduit()->getPrix();
                $ligneCommande->setPrix($prix);
                $ligneCommande->setFacture($facture);
                $manager->persist($ligneCommande);
                $prixTotal= $prixTotal+$prix;
              }

              $facture->setPrixTotal($prix);
              $facture->setClient($client);
              $manager->persist($facture);
          }


        }

       $manager->flush();    
}
}
