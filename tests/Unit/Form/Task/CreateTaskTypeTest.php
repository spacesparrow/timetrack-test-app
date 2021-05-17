<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Task;

use App\Entity\Task;
use App\Form\Task\CreateTaskType;
use DateTime;
use Exception;
use Faker\Factory;
use Generator;
use Nelmio\ApiDocBundle\Form\Extension\DocumentationExtension;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Constraints;

class CreateTaskTypeTest extends TypeTestCase
{
    /**
     * @covers \App\Form\Task\CreateTaskType::buildForm
     */
    public function testBuildForm(): void
    {
        $createTaskType = new CreateTaskType();

        $builderMock = $this->createMock(FormBuilderInterface::class);
        $builderMock->expects(static::exactly(4))
            ->method('add')
            ->withConsecutive(
                ['title', TextType::class],
                ['comment', TextType::class],
                ['timeSpent', IntegerType::class],
                ['createdDate', DateType::class],
            )
            ->willReturnSelf();

        $createTaskType->buildForm($builderMock, []);
    }

    /**
     * @covers \App\Form\Task\CreateTaskType::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $createTaskType = new CreateTaskType();

        $optionsResolverMock = $this->createMock(OptionsResolver::class);
        $optionsResolverMock->expects(static::once())
            ->method('setDefaults')
            ->with([
                'data_class' => Task::class,
                'constraints' => [new Constraints\Callback([$createTaskType, 'validate'])]
            ])
            ->willReturnSelf();

        $createTaskType->configureOptions($optionsResolverMock);
    }

    /**
     * @covers       \App\Form\Task\CreateTaskType
     * @dataProvider dataProviderForSubmitValidDataTesting
     * @throws Exception
     */
    public function testSubmitValidData(string $title, string $comment, int $timeSpent, string $createdDate): void
    {
        $taskActual = new Task();
        $taskExpected = new Task();
        $taskExpected->setTitle($title);
        $taskExpected->setComment($comment);
        $taskExpected->setTimeSpent($timeSpent);
        $taskExpected->setCreatedDate(new DateTime($createdDate));

        $form = $this->factory->create(CreateTaskType::class, $taskActual);
        $form->submit([
            'title' => $title,
            'comment' => $comment,
            'timeSpent' => $timeSpent,
            'createdDate' => $createdDate
        ]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isSubmitted());
        static::assertTrue($form->isValid());
        static::assertEquals($taskExpected, $taskActual);
    }

    public function dataProviderForSubmitValidDataTesting(): Generator
    {
        $faker = Factory::create();

        yield [$faker->text, $faker->paragraph, $faker->randomDigitNotNull, $faker->date()];
        yield [$faker->text, $faker->paragraph, $faker->randomDigitNotNull, $faker->date()];
        yield [$faker->text, $faker->paragraph, $faker->randomDigitNotNull, $faker->date()];
        yield [$faker->text, $faker->paragraph, $faker->randomDigitNotNull, $faker->date()];
    }

    /**
     * @covers       \App\Form\Task\CreateTaskType
     * @dataProvider dataProviderForSubmitInvalidDataTesting
     */
    public function testSubmitInvalidData(
        string $title,
        string $comment,
        int $timeSpent,
        string $createdDate,
        array $expectedMessages
    ): void {
        $taskActual = new Task();

        $form = $this->factory->create(CreateTaskType::class, $taskActual);
        $form->submit([
            'title' => $title,
            'comment' => $comment,
            'timeSpent' => $timeSpent,
            'createdDate' => $createdDate
        ]);

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
        $faker = Factory::create();

        yield [
            'some',
            $faker->paragraph,
            $faker->randomDigitNotNull,
            $faker->date(),
            ['This value is too short. It should have 5 characters or more.']
        ];
        yield [
            $faker->text,
            'some',
            $faker->randomDigitNotNull,
            $faker->date(),
            ['This value is too short. It should have 10 characters or more.']
        ];
        yield [$faker->text, $faker->paragraph, -5, $faker->date(), ['This value should be either positive or zero.']];
        yield [
            $faker->text,
            $faker->paragraph,
            $faker->randomDigitNotNull,
            (new DateTime('tomorrow'))->format('Y-m-d'),
            ['Created date can not be greater that today']
        ];
    }

    protected function getExtensions(): array
    {
        $createTaskType = new CreateTaskType();

        $validator = Validation::createValidatorBuilder()
            ->setMetadataFactory(new LazyLoadingMetadataFactory(new StaticMethodLoader()))
            ->getValidator();

        return [
            new PreloadedExtension([$createTaskType], []),
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