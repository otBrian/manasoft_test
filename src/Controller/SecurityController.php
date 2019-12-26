<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AuthFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('user_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $this->createForm(AuthFormType::class);

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error,'authForm' => $form->createView()]);
    }

    /**
     * @Route("/custom_login", name="custom_login")
     */
    public function customLogin(Request $request): Response
    {
        $form = $this->createForm(AuthFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $password = $form->get('password')->getData();
            $this->forward('App\Controller\SecurityController::login', [
                '_email' => $email,
                '_password' => $password,
            ]);

        }

        return $this->render('security/login.html.twig', ['authForm' => $form->createView()]);

    }
    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        return $this->redirectToRoute('app_login');
    }

    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(AuthFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $email = $form->get('plainPassword')->getData();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            return $this->redirectToRoute('user_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
