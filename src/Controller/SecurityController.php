<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\LoginType;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class SecurityController extends AbstractController
{
    #[Route('/login', name: 'security_login')]
    public function login(AuthenticationUtils $utils): Response
    {
        $form = $this->createForm(LoginType::class);


        return $this->render('security/login.html.twig', [
            'formView' => $form->createView(),
            'error' => $utils->getLastAuthenticationError()
        ]);
    }
    #[Route('/logout', name: 'security_logout')]
    public function logout()
    {
        return $this->redirect($this->generateUrl('security_logout'));
    }

    #[Route('/inscription', 'security_registration', methods: ['GET', 'POST'])]
    
    public function registration( UserPasswordHasherInterface $passwordHasher, Request $request, EntityManagerInterface $em) : Response {
        $user = new User();
        $user->setRoles(['ROLE_USER']);

        

        $form = $this->createForm(RegistrationType::class, $user);
        
        $form->handleRequest($request);

        
        if($form->isSubmitted() &&$form->isValid()){
            
            $user = $form->getData();

           // dd($user->getPassword());
            
            $mdp = $passwordHasher->hashPassword($user, $user->getPassword());
            
            $user->setPassword($mdp);
            
            $this->addFlash('success', 'Votre compte a bien été créé !');

            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('security_login');
        }


        return $this->render('security/registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
