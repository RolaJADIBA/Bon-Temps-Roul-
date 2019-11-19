<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserEditPassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
        ->add('password', PasswordType::class,[
            'label' => 'Mot de passe actuel',
            'constraints' => [
            new NotBlank([
                'message' => 'Votre mot de passe actuel doit être saisi.'
            ])
        ]
        ])
        ->add('newpass', RepeatedType::class,[
            'type' => PasswordType::class,
            'first_options'  => ['label' => 'Nouveau mot de passe', 'attr' =>['class' => 'form-control']],
            'second_options' => ['label' => 'Confirmer nouveau mot de passe', 'attr' =>['class' => 'form-control']],
            'invalid_message' => 'Les nouveaux mot de passe ne correspondent pas.'

        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
