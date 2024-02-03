<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class ModifUserFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label'=>"Nom d'utilisateur :"
            ])
            ->add('email', TextType::class, [
                'label'=>"Adresse mail :"
            ])
            ->add('password', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'label'=>"Mot de passe :",
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'required' => false,
                'constraints' => [
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('isAdmin', CheckboxType::class, [
                'label'=>"L'utilisateur est admin ?",
                'required'=>false,
                'mapped' => false,
                'data' => $options['is_admin']
            ])
        ;
    }

    // public function configureOptions(OptionsResolver $resolver): void
    // {
    //     $resolver->setDefaults([
    //         'data_class' => User::class,
    //     ]);
    // }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // ...,
            'is_admin' => false,
        ]);

        // you can also define the allowed types, allowed values and
        // any other feature supported by the OptionsResolver component
        $resolver->setAllowedTypes('is_admin', 'bool');
    }
}
