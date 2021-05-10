<?php

namespace App\Form\Task;

use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class CreateTaskType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'title',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\Length(['min' => Task::MIN_TITLE_LENGTH, 'max' => Task::MAX_TITLE_LENGTH])
                    ],
                    'documentation' => [
                        'type' => 'string',
                        'example' => 'First ever task'
                    ]
                ]
            )
            ->add(
                'comment',
                TextType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\Length(['min' => Task::MIN_COMMENT_LENGTH, 'max' => Task::MAX_COMMENT_LENGTH])
                    ],
                    'documentation' => [
                        'type' => 'string',
                        'example' => 'First ever task for me in the system'
                    ]
                ]
            )
            ->add('timeSpent',
                IntegerType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\PositiveOrZero()
                    ],
                    'documentation' => [
                        'type' => 'integer',
                        'example' => 15
                    ]
                ]
            );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
