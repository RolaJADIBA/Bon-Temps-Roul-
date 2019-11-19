<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuilliez saisir une adresse email.'
                    ])
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'type' => PasswordType::class,
                'mapped' => false,
                'first_options'  => ['label' => 'Password', 'attr' =>['class' => 'form-control']],
                'second_options' => ['label' => 'Repeat Password', 'attr' =>['class' => 'form-control']],
               
                
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])

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
            ->add('adresse',TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'adresse est obligatoire.'
                    ])
                ]
            ])
            ->add('ville',TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'La ville est obligatoire.'
                    ])
                ]
            ])
            ->add('code_postal',TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le code postal est obligatoire.'
                    ])
                ]
            ])
            ->add('tel',TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le téléphone est obligatoire.'
                    ])
                ]
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
