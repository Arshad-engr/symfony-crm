<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType; 
use Symfony\Component\Form\Extension\Core\Type\EmailType; 
use Symfony\Component\Form\Extension\Core\Type\SubmitType; 
use Symfony\Component\Form\Extension\Core\Type\DateType; 
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\TextareaType; 
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TaskType extends AbstractType
{
    
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
          
        $builder
        ->add('name', TextType::class, [
            'attr' => ['class' => 'form-control form-control','placeholder'=>'Enter Nam of the task'],
            'label' => false,
            'required' => true,  // Make sure 'name' is required
        ])
        ->add('description', TextareaType::class,[
            'attr' => ['class' => 'form-control form-control','placeholder'=>'Enter Description'],
            'label' => false,
            'constraints' => [
                new NotBlank([
                    'message' => 'Please enter description',
                ]),
            ],
        ])
        ->add('user', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'email', // Display the 'email' of the user in the dropdown
            'placeholder' => 'Select a User',
            'label' => 'User',
            'attr' => ['class' => 'form-control'],
            'required' => false,
        ])
        ->add('add', SubmitType::class, [
            'attr' => ['class' => 'btn btn-primary btn-user btn-block'],
            'label' => 'Submit',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
   
}
