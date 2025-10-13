<?php

namespace App\Form;

use App\Entity\Curso;
use App\Entity\Edicion;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EdicionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('precio')
            ->add('fechaInicio')
            ->add('fechaFin')
            ->add('nombre')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Edicion::class,
        ]);
    }
}
