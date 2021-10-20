<?php

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Form\RegistrationType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Repository\UserRepository;

class SecurityController extends AbstractController
{
    /**
     * @Route("/inscriptionAdmin", name="security_registration")
     */
    public function registrationAdmin(Request $request , UserPasswordEncoderInterface $encoder): Response
    {
       $user=new User();
      
       $form=$this->createFormBuilder($user)
                  ->add('userName')
                  ->add('email')
                  ->add('password',PasswordType::class)
                  ->add('confirm_password',PasswordType::class)
                  ->add('role',ChoiceType::class, [
                    'choices' => [
                        'Admin' => '["ROLE_ADMIN"]',
                        'Client' => '["ROLE_USER"]'
                    ]
                ])
                  ->getForm();
       $form->handleRequest($request);

       if($form->isSubmitted() && $form->isValid()){

        $hash = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($hash);
        
           
        $manager=$this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('security_login');

       }


        return $this->render('security/registrationAdmin.html.twig', [
            'form' => $form->createView()
        ]);
    }
     /**
     * @Route("/inscriptionClient", name="client_registration")
     */
    public function registrationClient(Request $request , UserPasswordEncoderInterface $encoder): Response
    {
       $user=new User();
       $form=$this->createFormBuilder($user)
                  ->add('userName')
                  ->add('email')
                  ->add('password',PasswordType::class)
                  ->add('confirm_password',PasswordType::class)
                  ->getForm();
                  $form->handleRequest($request);
       if($form->isSubmitted() && $form->isValid()){

        $hash = $encoder->encodePassword($user, $user->getPassword());
        $user->setPassword($hash);
        $user->setRole('["ROLE_CLIENT"]');
           
        $manager=$this->getDoctrine()->getManager();
        $manager->persist($user);
        $manager->flush();
        return $this->redirectToRoute('client_registration');

       }


        return $this->render('security/registrationClient.html.twig', [
            'form' => $form->createView()
        ]);
    }
        /**
     * @Route("/connexion", name="security_login")
     */
    public function login(Request $request, UserRepository $repo)
    {
        
        $email = $request->request->get('_username');

       

        return $this->render('security/login.html.twig',[
            'email' => $email
        ]);
    }

        /**
     * @Route("/deconnexion", name="security_logout")
     */
    public function logout()
    {
        $session->clear();

    }

}

