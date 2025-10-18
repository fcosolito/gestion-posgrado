<?php

namespace App\Form;

use App\Entity\Carrera;
use App\Entity\Curso;
use App\Entity\PerteneceA;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PerteneceAType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('esElectivo', CheckboxType::class, [
                'label' => '¿Es electivo?',
                'required' => false,
            ])
            ->add('carrera', EntityType::class, [
                'class' => Carrera::class,
                'choice_label' => 'nombre',
            ])
            ->add('curso', EntityType::class, [
                'class' => Curso::class,
                'choice_label' => 'nombre',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PerteneceA::class,
        ]);
    }
}
