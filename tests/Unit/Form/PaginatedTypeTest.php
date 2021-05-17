<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\DTO\PaginatedRequestDTO;
use App\Form\PaginatedType;
use Generator;
use Nelmio\ApiDocBundle\Form\Extension\DocumentationExtension;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validation;

class PaginatedTypeTest extends TypeTestCase
{
    /**
     * @covers \App\Form\PaginatedType::buildForm
     */
    public function testBuildForm(): void
    {
        $paginatedType = new PaginatedType();

        $builderMock = $this->createMock(FormBuilderInterface::class);
        $builderMock->expects(static::once())
            ->method('add')
            ->with(...[self::equalTo('page'), self::equalTo(IntegerType::class)])
            ->willReturnSelf();

        $paginatedType->buildForm($builderMock, []);
    }

    /**
     * @covers \App\Form\PaginatedType::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $paginatedType = new PaginatedType();

        $optionsResolverMock = $this->createMock(OptionsResolver::class);
        $optionsResolverMock->expects(static::once())
            ->method('setDefaults')
            ->with(['data_class' => PaginatedRequestDTO::class])
            ->willReturnSelf();

        $paginatedType->configureOptions($optionsResolverMock);
    }

    /**
     * @covers       \App\Form\PaginatedType
     * @dataProvider dataProviderForSubmitValidDataTesting
     */
    public function testSubmitValidData(?int $page): void
    {
        $paginatedRequestDTOActual = new PaginatedRequestDTO();
        $paginatedRequestDTOExpected = new PaginatedRequestDTO();
        $paginatedRequestDTOExpected->setPage($page);

        $form = $this->factory->create(PaginatedType::class, $paginatedRequestDTOActual);
        $form->submit(['page' => $page]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isSubmitted());
        static::assertTrue($form->isValid());
        static::assertEquals($paginatedRequestDTOExpected, $paginatedRequestDTOActual);
    }

    public function dataProviderForSubmitValidDataTesting(): Generator
    {
        yield [1];
        yield [2];
        yield [15];
        yield [100];
        yield [7];
        yield [null];
    }

    /**
     * @covers       \App\Form\PaginatedType
     * @dataProvider dataProviderForSubmitInvalidDataTesting
     * @param int|float $page
     * @param string $expectedMessage
     */
    public function testSubmitInvalidData($page, string $expectedMessage): void
    {
        $paginatedRequestDTOActual = new PaginatedRequestDTO();

        $form = $this->factory->create(PaginatedType::class, $paginatedRequestDTOActual);
        $form->submit(['page' => $page]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isSubmitted());
        static::assertFalse($form->isValid());

        foreach ($form->getErrors(true) as $actualMessage) {
            static::assertSame($expectedMessage, $actualMessage->getMessage());
        }
    }

    public function dataProviderForSubmitInvalidDataTesting(): Generator
    {
        yield [0, 'This value should be positive.'];
        yield [2.5, 'This value is not valid.'];
        yield [-22, 'This value should be positive.'];
        yield [-15, 'This value should be positive.'];
        yield [-100, 'This value should be positive.'];
        yield [-7, 'This value should be positive.'];
        yield [-1, 'This value should be positive.'];
    }

    protected function getExtensions(): array
    {
        $paginatedType = new PaginatedType();

        $validator = Validation::createValidatorBuilder()
            ->setMetadataFactory(new LazyLoadingMetadataFactory(new StaticMethodLoader()))
            ->getValidator();

        return [
            new PreloadedExtension([$paginatedType], []),
            new ValidatorExtension($validator)
        ];
    }

    protected function getTypeExtensions(): array
    {
        return [
            new DocumentationExtension()
        ];
    }
}