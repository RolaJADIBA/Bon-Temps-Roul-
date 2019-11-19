<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('nom',TextType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Le nom est obligatoire.'
                ])
            ]
        ])
        ->add('prenom',TextType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Le prenom est obligatoire.'
                ])
            ]
        ])
        ->add('email', EmailType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'L\'email est obligatoire.'
                ])
            ]
        ])
        ->add('objet', TextType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrez un objet'
                ])
            ]
        ])
        ->add('message', TextareaType::class, [
            'constraints' => [
                new NotBlank([
                    'message' => 'Veuillez entrez un message'
                ])
            ]
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
