<?php

namespace App\Form;

use App\DTO\PaginatedRequestDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class PaginatedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'page',
                IntegerType::class,
                [
                    'required' => false,
                    'constraints' => [
                        new Constraints\Positive(),
                    ],
                    'documentation' => [
                        'type' => 'integer',
                        'example' => 1,
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PaginatedRequestDTO::class,
        ]);
    }
}
