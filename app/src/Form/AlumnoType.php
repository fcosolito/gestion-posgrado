<?php

namespace App\Form;

use App\Entity\Alumno;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AlumnoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, [
                'attr' => ['maxlength' => 255]
            ])
            ->add('apellido', TextType::class, [
                'attr' => ['maxlength' => 255]
            ])
            ->add('dni', NumberType::class, [
                'attr' => ['maxlength' => 11]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => ['maxlength' => 255]
            ])
            ->add('telefono', NumberType::class, [
                'required' => false,
                'attr' => ['maxlength' => 255]
            ])
            ->add('tituloGrado', TextType::class, [
                'required' => false,
                'attr' => ['maxlength' => 255]
            ])
            ->add('fechaNacimiento', DateType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Alumno::class,
        ]);
    }
}
