<?php

namespace App\Form;

use App\Entity\DocumentacionNota;
use App\Entity\InscripcionEdicion;
use App\Entity\Nota;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class NotaType extends AbstractType
{
    /*
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
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('valor', NumberType::class, [
                'label' => 'Nota',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '10',
                    'step' => '0.01',
                    'min' => '0',
                    'max' => '10'
                ]
            ])
            ->add('descripcion', TextType::class, [
                'label' => 'Descripción',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Descripción de la nota',
                    'maxlength' => '255'
                ]
            ])
            ->add('fechaCarga', DateType::class, [
                'label' => 'Fecha de carga',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control']
            ])
            ->add('archivo', FileType::class, [
                'label' => 'Documentación respaldatoria',
                'required' => false,
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.pdf,.jpg,.jpeg,.png,.doc,.docx'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        'mimeTypesMessage' => 'Por favor suba un archivo válido (PDF, Word, JPEG, PNG)',
                    ])
                ]
            ])
            ->add('inscripcionEdicion', EntityType::class, [
                'class' => InscripcionEdicion::class,
                'label' => false,
                'choice_label' => function(InscripcionEdicion $inscripcion) {
                    return sprintf(
                        '%s - %s',
                        $inscripcion->getEdicion()->getNombre(),
                        $inscripcion->getEdicion()->getCurso()->getNombre()
                    );
                },
                'attr' => ['style' => 'display: none;']
            ]);
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Nota::class,
        ]);
    }
}
