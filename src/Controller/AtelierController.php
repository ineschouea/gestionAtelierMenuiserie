<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\DateType;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\Session\Session;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Client;
use App\Entity\Facture;
use App\Entity\LigneCommande;
use App\Entity\Produit;
use App\Entity\User;
use App\Entity\Contact;

use App\Repository\ProduitRepository;
use App\Repository\FactureRepository;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Repository\ContactRepository;

class AtelierController extends Controller
{
    /**
     * @Route("/", name="atelier")
     */
    public function index(): Response
    {
        return $this->render('atelier/index.html.twig', [
            'controller_name' => 'AtelierController',
        ]);
    }

     /**
     * @Route("/listeProd", name="listeProd")
     */
    public function liste(Request $request, ProduitRepository $repo): Response
    {
        $Allproduits=$repo->findAll();
         // Paginate the results of the query
         $produits = $this->get('knp_paginator')->paginate(
            // Doctrine Query, not results
            $Allproduits,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );

        return $this->render('atelier/produits.html.twig', [
            'controller_name' => 'AtelierController',
            'produits' => $produits

        ]);
    }
   

     /**
     * @Route("/listeProdAdmin", name="listeProdAdmin")
     */
    public function listeAdmin(Request $request, ProduitRepository $repo): Response
    {
        $Allproduits=$repo->findAll();
         // Paginate the results of the query
         $produits = $this->get('knp_paginator')->paginate(
            // Doctrine Query, not results
            $Allproduits,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );

        return $this->render('atelier/produitsAdmin.html.twig', [
            'controller_name' => 'AtelierController',
            'produits' => $produits

        ]);
    }
    /**
     * @Route("/des/{id}", name="desc")
     */
    public function description(Produit $produit): Response
    {
        return $this->render('atelier/description.html.twig', [
            'produit' => $produit
        ]);
    }

    /**
     * @Route("/creation", name="creation")
     */
    public function creer(Request $request )
    {
        $produit=new Produit();
       $form= $this->createFormBuilder($produit)
               ->add('titre')
               ->add('prix')
               ->add('description',TextareaType::class)
               ->add('image',FileType::class)
               ->getForm();
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){
           $file=$produit->getImage();
           $fileName=md5(uniqid()).'.'.$file->guessExtension();
           $produit->setImage($fileName);
           try {
            $file->move(
                $this->getParameter('upload_directory'),
                $fileName
            );
        } catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }  
           $entityManager=$this->getDoctrine()->getManager();
           $entityManager->persist($produit);
           $entityManager->flush();
           
           return $this->redirectToRoute('creation');
       }

       
       return $this->render('atelier/creation.html.twig', [
           'formProd' => $form->createView(),
           'produit' => $produit,
           'controller_name' => 'AtelierController'
       ]);
    }
    /**
     * @Route("/modification/{id}", name="modification")
     */
    public function modifier(Request $request, Produit $produit): Response
    {
       
       $form= $this->createFormBuilder($produit)
               ->add('titre')
               ->add('prix')
               ->add('description',TextareaType::class)
               ->getForm();
       $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){
           
           $entityManager=$this->getDoctrine()->getManager();
           $entityManager->persist($produit);
           $entityManager->flush();
           
           return $this->redirectToRoute('listeProdAdmin');
       }

       
       return $this->render('atelier/modification.html.twig', [
           'formProd' => $form->createView(),
           'produit' => $produit,
           'controller_name' => 'AtelierController'
       ]);
    }
    /**
     * @Route("/suppression/{id}", name="suppression")
     */
    public function supprimer(Request $request, Produit $produit): Response
    {
            
            $entityManager=$this->getDoctrine()->getManager();
            $entityManager->remove($produit);
            $entityManager->flush();
            return $this->redirectToRoute('listeProdAdmin');
        

        
        return $this->render('atelier/produitsAdmin.html.twig', [

        ]);
    }

     /**
     * @Route("/commander/{id}", name="commander")
     */
    public function commander(Request $request, $id,FactureRepository $repo )
    {
       $facture=$repo->find($id);
       $ligneCommande=new LigneCommande();
       $form= $this->createFormBuilder($ligneCommande)
               ->add('produit',EntityType::class,[
                   'class'=>Produit::class,
                   'choice_label' => 'titre'
               ]) 
               ->add('qte') 
             
            ->add('qte')
               ->getForm();
       $form->handleRequest($request);
 
        if($form->isSubmitted() && $form->isValid()){
            $prix=$ligneCommande->getProduit()->getPrix()*$ligneCommande->getQte();
            $ligneCommande->setPrix($prix);  
            $ligneCommande->setFacture($facture);        
            $entityManager=$this->getDoctrine()->getManager();
            $entityManager->persist($ligneCommande);
            $entityManager->flush();
            $facture->addLigneCommande($ligneCommande);
            $facture->setPrixTotal($facture->getPrixTotal()+$prix);
            $entityManager->persist($facture);
            $entityManager->flush();
            return $this->redirectToRoute('commander',['id'=>$facture->getId()]);

        }
        
       
       return $this->render('atelier/commande.html.twig', [
           'formCmd' =>$form->createView(),
           'controller_name' => 'AtelierController'
       ]);
    }
   /**
     * @Route("/creerCmd", name="creerCmd")
     */
    public function creerCmd(Request $request )
    {
        $client=new Client();
        $form= $this->createFormBuilder($client)
                    ->add('cin')
                    ->add('nom')
                    ->add('dateDeNaissance',DateType::class)
                    ->add('adresse')
                    ->add('tel')
                    ->getForm();
        $form->handleRequest($request);

       $prixTotal=0;
        
        if($form->isSubmitted() && $form->isValid()){
            
            $entityManager=$this->getDoctrine()->getManager();
            $entityManager->persist($client);
            $entityManager->flush();

        $facture=new Facture();
        $facture->setPrixTotal($prixTotal);
        $facture->setClient($client);
        $entityManager=$this->getDoctrine()->getManager();
        $entityManager->persist($facture);
        $entityManager->flush();
        return $this->redirectToRoute('commander', ['id'=> $facture->getId()]);

       }
       
       return $this->render('atelier/cmd.html.twig', [
           'form' =>$form->createView(),
           'controller_name' => 'AtelierController'
       ]);
    } 
   /**
     * @Route("/listeCmd", name="listeCmd")
     */
    public function listeCmd(Request $request, FactureRepository $repo): Response
    {
        $AllFactures=$repo->findAll();
         // Paginate the results of the query
         $factures = $this->get('knp_paginator')->paginate(
            // Doctrine Query, not results
            $AllFactures,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );

        return $this->render('atelier/listeCmds.html.twig', [
            'controller_name' => 'AtelierController',
            'factures' => $factures

        ]);
    }  
    /**
     * @Route("/listeClt", name="listeClt")
     */
    public function listeClt(Request $request, ClientRepository $repo): Response
    {
        $AllClients=$repo->findAll();
         // Paginate the results of the query
         $clients = $this->get('knp_paginator')->paginate(
            // Doctrine Query, not results
            $AllClients,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );

        return $this->render('atelier/listeClt.html.twig', [
            'controller_name' => 'AtelierController',
            'clients' => $clients

        ]);
    } 
     /**
     * @Route("/admin", name="admin")
     */
    public function admin(Request $request, UserRepository $repo): Response
    {
        

    $Allusers=$repo->findAll();
         // Paginate the results of the query
         $users = $this->get('knp_paginator')->paginate(
            // Doctrine Query, not results
            $Allusers,
            // Define the page parameter
            $request->query->getInt('page', 1),
            // Items per page
            5
        );
    

        return $this->render('atelier/admin.html.twig', [
            'controller_name' => 'AtelierController',
            'users' => $users

        ]);
      
  
      
        
    }
    /**
     * @Route("/suppUser/{id}", name="suppUser")
     */
    public function suppUser(Request $request, $id): Response
    {
        $user=new User();
         $repo =$this->getDoctrine()->getRepository(User::class);
         $user= $repo->find($id);
    
            
            $entityManager=$this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
            return $this->redirectToRoute('admin');
        

        
        return $this->render('access/delete.html.twig', [
            'conv' => $conv

        ]);
    }
     /**
     * @Route("/modifUser/{id}", name="modifUser")
     */
    public function modifUser(Request $request, User $user): Response
    { $form= $this->createFormBuilder($user)

        ->add('email',EmailType::class)
        ->add('password')
        ->getForm();
$form->handleRequest($request);
if($form->isSubmitted() && $form->isValid()){
    
    $entityManager=$this->getDoctrine()->getManager();
    $entityManager->persist($user);
    $entityManager->flush();
    
    return $this->redirectToRoute('admin');
}


return $this->render('atelier/modifUser.html.twig', [
    'formUser' => $form->createView(),
    'user' => $user,
    'controller_name' => 'AtelierController'
]);
           
            }
        
      /**
     * @Route("/ajoutUser", name="ajoutUser")
     */
    public function ajoutUser(Request $request): Response
    {
        $user=new User();
        $form= $this->createFormBuilder($user)
        ->add('email')
        ->add('password')
        ->getForm();
$form->handleRequest($request);
if($form->isSubmitted() && $form->isValid()){
    
    $entityManager=$this->getDoctrine()->getManager();
    $entityManager->persist($user);
    $entityManager->flush();
    
    return $this->redirectToRoute('admin');
}


return $this->render('atelier/ajoutUser.html.twig', [
    'formUser' => $form->createView(),
    'user' => $user,
    'controller_name' => 'AtelierController'
]);
           
           
}
    /**
     * @Route("/listecontact", name="listecontact")
     */
    public function contact(Request $request,ContactRepository $repo): Response
    {
       
        $AllContacts=$repo->findAll();
        // Paginate the results of the query
        $contacts = $this->get('knp_paginator')->paginate(
           // Doctrine Query, not results
           $AllContacts,
           // Define the page parameter
           $request->query->getInt('page', 1),
           // Items per page
           5
       );

       return $this->render('atelier/contact.html.twig', [
           'controller_name' => 'AtelierController',
           'contacts' => $contacts

       ]);
        
    }
    /**
     * @Route("/suppContact/{id}", name="suppContact")
     */
    public function suppContact(Request $request, $id): Response
    {
        $contact=new Contact();
         $repo =$this->getDoctrine()->getRepository(Contact::class);
         $contact= $repo->find($id);
    
            
            $entityManager=$this->getDoctrine()->getManager();
            $entityManager->remove($contact);
            $entityManager->flush();
            return $this->redirectToRoute('listecontact');
        

        
        return $this->render('atelier/delete.html.twig');
    }
    
    /**
     * @Route("/ajouterContact", name="ajouterContact")
     */
    public function ajoutContact(Request $request): Response
    {
        $contact=new Contact();
        $form= $this->createFormBuilder($contact)
        ->add('nom')
        ->add('prenom')
        ->add('email',EmailType::class)
        ->add('tel')
        ->add('objet')
        ->add('msg',TextareaType::class)

        ->getForm();
$form->handleRequest($request);
if($form->isSubmitted() && $form->isValid()){
    
    $entityManager=$this->getDoctrine()->getManager();
    $entityManager->persist($contact);
    $entityManager->flush();
    
    return $this->redirectToRoute('atelier');
}


return $this->render('atelier/ajoutContact.html.twig', [
    'form' => $form->createView(),
    'controller_name' => 'AtelierController'
]);
           
           
} 
  /**
     * @Route("/administration", name="administration")
     */
    public function administration(Request $request): Response
    {
        
          return $this->render('atelier/administration.html.twig');
    } 

     /**
     * @Route("/error", name="error")
     */
    public function error()
    {
         
        return $this->render('atelier/error.html.twig');
    } 
 }
