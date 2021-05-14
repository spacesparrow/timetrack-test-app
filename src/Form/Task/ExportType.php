<?php

declare(strict_types=1);

namespace App\Form\Task;

use App\DTO\TasksExportDTO;
use App\Service\Export\TasksExportServiceInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ExportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $allowedTypes = TasksExportServiceInterface::ALLOWED_TYPES;
        $builder
            ->add(
                'type',
                ChoiceType::class,
                [
                    'required' => true,
                    'choices' => [$allowedTypes],
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                    'documentation' => [
                        'type' => 'string',
                        'example' => TasksExportServiceInterface::TYPE_PDF,
                        'enum' => $allowedTypes,
                    ],
                ]
            )
            ->add(
                'start_date',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                    'documentation' => [
                        'type' => 'string',
                        'example' => '2021-05-08',
                        'format' => 'date',
                    ],
                ]
            )
            ->add(
                'end_date',
                DateType::class,
                [
                    'widget' => 'single_text',
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                    ],
                    'documentation' => [
                        'type' => 'string',
                        'example' => '2021-05-09',
                        'format' => 'date',
                    ],
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TasksExportDTO::class,
            'constraints' => [
                new Constraints\Callback([$this, 'validate']),
            ],
        ]);
    }

    public function validate(TasksExportDTO $exportDTO, ExecutionContextInterface $context): void
    {
        if ($exportDTO->getStartDate() > $exportDTO->getEndDate()) {
            $context->buildViolation("Start date can't be after end date")
                ->atPath('start_date')
                ->addViolation();
        }
    }
}
