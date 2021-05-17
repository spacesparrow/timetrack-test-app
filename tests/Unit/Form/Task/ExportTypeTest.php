<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Task;

use App\DTO\TasksExportDTO;
use App\Form\Task\ExportType;
use DateTime;
use Exception;
use Generator;
use Nelmio\ApiDocBundle\Form\Extension\DocumentationExtension;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validation;

class ExportTypeTest extends TypeTestCase
{
    private const TYPE_PDF = 'pdf';
    private const TYPE_CSV = 'csv';
    private const TYPE_XLSX = 'xlsx';

    /**
     * @covers \App\Form\Task\ExportType::buildForm
     */
    public function testBuildForm(): void
    {
        $exportType = new ExportType();

        $builderMock = $this->createMock(FormBuilderInterface::class);
        $builderMock->expects(static::exactly(3))
            ->method('add')
            ->withConsecutive(
                [static::equalTo('type'), static::equalTo(ChoiceType::class)],
                [static::equalTo('start_date'), static::equalTo(DateType::class)],
                [static::equalTo('end_date'), static::equalTo(DateType::class)],
            )
            ->willReturnSelf();

        $exportType->buildForm($builderMock, []);
    }

    /**
     * @covers \App\Form\Task\ExportType::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $exportType = new ExportType();

        $optionsResolverMock = $this->createMock(OptionsResolver::class);
        $optionsResolverMock->expects(static::once())
            ->method('setDefaults')
            ->with(
                [
                    'data_class' => TasksExportDTO::class,
                    'constraints' => [new Constraints\Callback([$exportType, 'validate'])]
                ]
            )
            ->willReturnSelf();

        $exportType->configureOptions($optionsResolverMock);
    }

    /**
     * @covers       \App\Form\Task\ExportType
     * @dataProvider dataProviderForSubmitValidDataTesting
     * @throws Exception
     */
    public function testSubmitValidData(string $type, string $startDate, string $endDate): void
    {
        $tasksExportDTOActual = new TasksExportDTO();
        $tasksExportDTOExpected = new TasksExportDTO();
        $tasksExportDTOExpected->setType($type);
        $tasksExportDTOExpected->setStartDate(new DateTime($startDate));
        $tasksExportDTOExpected->setEndDate(new DateTime($endDate));

        $form = $this->factory->create(ExportType::class, $tasksExportDTOActual);
        $form->submit(['type' => $type, 'start_date' => $startDate, 'end_date' => $endDate]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isSubmitted());
        static::assertTrue($form->isValid());
        static::assertEquals($tasksExportDTOExpected, $tasksExportDTOActual);
    }

    public function dataProviderForSubmitValidDataTesting(): Generator
    {
        yield [self::TYPE_PDF, '2020-01-01', '2021-01-01'];
        yield [self::TYPE_XLSX, '2021-01-01', '2021-01-02'];
        yield [self::TYPE_CSV, '2021-01-02', '2021-01-02'];
    }

    /**
     * @covers       \App\Form\Task\ExportType
     * @dataProvider dataProviderForSubmitInvalidDataTesting
     * @throws Exception
     */
    public function testSubmitInvalidData(
        ?string $type,
        ?string $startDate,
        ?string $endDate,
        array $expectedMessages
    ): void {
        $tasksExportDTOActual = new TasksExportDTO();

        $form = $this->factory->create(ExportType::class, $tasksExportDTOActual);
        $form->submit(['type' => $type, 'start_date' => $startDate, 'end_date' => $endDate]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isSubmitted());
        static::assertFalse($form->isValid());
        $actualErrors = $form->getErrors(true);
        static::assertSame(count($expectedMessages), $actualErrors->count());
        $i = 0;

        foreach ($actualErrors as $actualMessage) {
            static::assertSame($expectedMessages[$i], $actualMessage->getMessage());
            $i++;
        }
    }

    public function dataProviderForSubmitInvalidDataTesting(): Generator
    {
        yield [
            'docx',
            '2020-01-01',
            '2021-01-01',
            [
                'This value is not valid.'
            ]
        ];
        yield [
            'xls',
            '2020-01-01',
            '2021-01-01',
            [
                'This value is not valid.'
            ]
        ];
        yield [
            'odt',
            '2020-01-01',
            '2021-01-01',
            [
                'This value is not valid.'
            ]
        ];
        yield [
            self::TYPE_PDF,
            '2020-01-02',
            '2020-01-01',
            [
                "Start date can't be after end date"
            ]
        ];
    }

    protected function getExtensions(): array
    {
        $exportType = new ExportType();

        $validator = Validation::createValidatorBuilder()
            ->setMetadataFactory(new LazyLoadingMetadataFactory(new StaticMethodLoader()))
            ->getValidator();

        return [
            new PreloadedExtension([$exportType], []),
            new ValidatorExtension($validator),
        ];
    }

    protected function getTypeExtensions(): array
    {
        return [
            new DocumentationExtension()
        ];
    }
}