<?php
// src/Form/PagoType.php

namespace App\Form;

use App\Entity\Pago;
use App\Entity\PagoCuota;
use App\Entity\Comprobante;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\File;


class PagoType extends AbstractType
{
     public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('monto', NumberType::class, [
                'label' => 'Monto Total del Pago',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => '0.00',
                    'step' => '0.01',
                    'min' => '0',
                    'id' => 'pago_monto'
                ],
                'constraints' => [
                    new NotBlank(message: 'El monto es obligatorio')
                ]
            ])
            ->add('fechaPago', DateType::class, [
                'label' => 'Fecha de Pago',
                'widget' => 'single_text',
                'attr' => [
                    'class' => 'form-control',
                    'id' => 'pago_fecha'
                ],
                'constraints' => [
                    new NotBlank(message: 'La fecha de pago es obligatoria')
                ]
            ])
            ->add('cuotasSeleccionadas', HiddenType::class, [
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'id' => 'cuotas-seleccionadas',
                ]
            ])
            ->add('archivoComprobante', FileType::class, [
                'label' => 'Comprobante de Pago',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.pdf,.jpg,.jpeg,.png',
                    'id' => 'pago_comprobante'
                ],
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: [
                            'application/pdf',
                            'image/jpeg',
                            'image/png',
                            'image/jpg'
                        ],
                        mimeTypesMessage: 'Por favor suba un archivo válido (PDF, JPEG, PNG)',
                    )
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Pago::class,
        ]);
    }
}