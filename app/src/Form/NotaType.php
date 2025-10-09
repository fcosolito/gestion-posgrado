<?php

namespace App\Form;

use App\Entity\DocumentacionNota;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('valor')
            ->add('descripcion')
            ->add('fechaCarga')
            ->add('inscripcionEdicion', EntityType::class, [
                'class' => InscripcionEdicion::class,
                'choice_label' => 'id',
            ])
            ->add('documentacionNota', EntityType::class, [
                'class' => DocumentacionNota::class,
                'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Nota::class,
        ]);
    }
}
