<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PagoSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('alumno', TextType::class, [
                'label' => 'Alumno',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Buscar por nombre o apellido...'
                ]
            ])
            ->add('monto_min', NumberType::class, [
                'label' => 'Monto Mínimo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Monto mínimo...',
                    'step' => '0.01'
                ]
            ])
            ->add('monto_max', NumberType::class, [
                'label' => 'Monto Máximo',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Monto máximo...',
                    'step' => '0.01'
                ]
            ])
            ->add('fecha_desde', DateType::class, [
                'label' => 'Fecha Desde',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'datepicker'
                ]
            ])
            ->add('fecha_hasta', DateType::class, [
                'label' => 'Fecha Hasta',
                'required' => false,
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'datepicker'
                ]
            ])
            ->add('buscar', SubmitType::class, [
                'label' => 'Buscar',
                'attr' => ['class' => 'btn btn-gris']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
            'method' => 'GET',
            'csrf_protection' => false,
        ]);
    }
}