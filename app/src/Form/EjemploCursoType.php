<?php

namespace App\Form;

use App\Entity\Curso;
use App\Entity\Dictado;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EjemploCursoType extends AbstractType
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('curso', ChoiceType::class, [
                'choices' => $options["cursos"],
                'choice_value' => "nombre",
                'choice_label' => function (?Curso $curso): string {
                    return $curso ? $curso->getNombre() : "";
                }
            ])
            /*
            ->add('dictado', EntityType::class, [
                'class' => Dictado::class,
                'choices' => [], 
                'choice_label' => 'nombre',
            ])
            */
            ->add('dictado', HiddenType::class, [
                'label' => false,
            ])
            ->add('inscribir', SubmitType::class)
        ;

        // Esto puede ser necesario si los forms fallan por validacion de symfony
        // Es codigo tomado del prototipo

        /*
        // Para actualizar los dictados del lado del servidor
        $formModifier = function (FormInterface $form, ?Curso $curso) {
            $dictados = $curso ? $this->entityManager->getRepository(Dictado::class)->findBy(['curso' => $curso]) : [];
            $form->add('dictado', EntityType::class, [
                'class' => Dictado::class,
                'choices' => $dictados, // ahora Symfony sabe qué ids son válidos
                'choice_label' => 'nombre',
                'placeholder' => $curso ? 'Seleccionar un dictado' : 'Seleccionar un curso primero',
            ]);
        };

        // Para cargar los dictados del curso seleccionado por defecto, no parece andar
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($formModifier) {
            $data = $event->getData();
            $curso = $data['curso'] ?? null;
            $formModifier($event->getForm(), $curso);
        });

        // Para actualizar la lista de dictados validos antes de que symfony procese la seleccion
        // Sin esto la lista de 'choices' esta vacia y symfony da un error de validacion porque
        // no encuentra el id
        $builder->get('curso')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($formModifier) {
            $curso = $event->getForm()->getData();
            $formModifier($event->getForm()->getParent(), $curso);
        });
        */
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "cursos" => [],
        ]);
    }
}