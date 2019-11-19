<?php

namespace App\Form;

use App\Entity\Articles;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use FOS\CKEditorBundle\Form\Type\CKEditorType;

class ArticlesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Le titre de l\'article est obligatoire.'
                    ])
                ]
            ])
            ->add('image', FileType::class, [
                'required' => false,
                // 'data_class' à null systématiquement si le FileType est utilisé
                'data_class' => null,
                'constraints' => [
                    new Image([
                        'maxSize' => '10M',
                        'mimeTypes' => [
                            'image/png', 'image/jpeg', 'image/gif'
                        ]
                    ])
                ] 
            ])
            ->add('contenu', CKEditorType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir le contenu de votre article.'
                    ])
                ]
            ])
            ->add('description', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez saisir un résumé pour votre Articles'
                    ])
                ]
            ])
            // ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Articles::class,
        ]);
    }
}
