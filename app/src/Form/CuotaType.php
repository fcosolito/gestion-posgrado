<?php

namespace App\Form;

use App\Entity\Cuota;
use App\Entity\InscripcionCarrera;
use App\Entity\InscripcionEdicion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CuotaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('numeroCuota')
            ->add('inscripcionCarrera', EntityType::class, [
                'class' => InscripcionCarrera::class,
                'choice_label' => 'id',
            ])
            ->add('inscripcionEdicion', EntityType::class, [
                'class' => InscripcionEdicion::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Cuota::class,
        ]);
    }
}
