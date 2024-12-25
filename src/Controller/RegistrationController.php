<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Form\FormError;
use App\Form\RegistrationType;
use App\Entity\User;
use Symfony\Component\String\Slugger\SluggerInterface;


class RegistrationController extends AbstractController
{
    private $slugger;

    // Inject SluggerInterface via constructor
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }
    #[Route('/registration', name: 'app_registration')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher,EntityManagerInterface $entityManager)
    {
        $user = new User();
        $form = $this->createForm(RegistrationType::class, $user);
       
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // Get the password and confirm_password values
            $password = $user->getPassword(); // from entity
            $confirmPassword = $form->get('confirm_password')->getData(); // from form (confirm password)
            // Compare passwords manually
            if ($password !== $confirmPassword) {
                // Add a form error if passwords do not match
                $form->get('confirm_password')->addError(new FormError('The password fields must match.'));
            }
            if ($form->isValid()) {
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $user->getPassword()
                );
                $user->setPassword($hashedPassword);
                // upload profile if provided
                $profileFile = $form->get('profile')->getData();
                if ($profileFile) {
                    $this->uploadProfile($form->get('profile')->getData(),$user);
                }
                
               // Persist the user data to the database
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Registration successful!');
            }
            
        }
        
        return $this->render('registration/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function uploadProfile($profile,$user)
    {
        $originalFilename = pathinfo($profile->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$profile->guessExtension();
        // Move the file to the directory where brochures are stored
        try {
            $profile->move($_ENV['PROFILE_DIRECTORY'], $newFilename);
        } catch (FileException $e) {
            dd ($e->getMessage());
        }
        // set profile fields
        $user->setProfile($newFilename);
        return true;
    }
}
