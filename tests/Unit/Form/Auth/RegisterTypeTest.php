<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form\Auth;

use App\Entity\User;
use App\Form\Auth\RegisterType;
use App\Service\AuthService;
use App\Tests\Traits\ReflectionTrait;
use Faker\Factory;
use Generator;
use Nelmio\ApiDocBundle\Form\Extension\DocumentationExtension;
use PHPUnit\Framework\MockObject\MockObject;
use ReflectionException;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Validation;

class RegisterTypeTest extends TypeTestCase
{
    use ReflectionTrait;

    /** @var AuthService|MockObject */
    private $authServiceMock;

    /**
     * @covers \App\Form\Auth\RegisterType::__construct
     * @throws ReflectionException
     */
    public function testConstruct(): void
    {
        $registerTypeForm = new RegisterType($this->authServiceMock);

        static::assertInstanceOf(
            AuthService::class,
            $this->getNonPublicPropertyValue($registerTypeForm, 'authService')
        );
    }

    /**
     * @covers \App\Form\Auth\RegisterType::buildForm
     */
    public function testBuildForm(): void
    {
        $registerType = new RegisterType($this->authServiceMock);

        $builderMock = $this->createMock(FormBuilderInterface::class);
        $builderMock->expects(static::exactly(2))
            ->method('add')
            ->withConsecutive(
                ['email', EmailType::class],
                ['password', PasswordType::class],
            )
            ->willReturnSelf();

        $registerType->buildForm($builderMock, []);
    }

    /**
     * @covers \App\Form\Auth\RegisterType::configureOptions
     */
    public function testConfigureOptions(): void
    {
        $registerType = new RegisterType($this->authServiceMock);

        $optionsResolverMock = $this->createMock(OptionsResolver::class);
        $optionsResolverMock->expects(static::once())
            ->method('setDefaults')
            ->with([
                'data_class' => User::class,
                'constraints' => [new Constraints\Callback([$registerType, 'validate'])]
            ])
            ->willReturnSelf();

        $registerType->configureOptions($optionsResolverMock);
    }

    /**
     * @covers       \App\Form\Auth\RegisterType
     * @dataProvider dataProviderForSubmitValidDataTesting
     */
    public function testSubmitValidData(string $email, string $password): void
    {
        $userActual = new User();
        $userExpected = new User();
        $userExpected->setEmail($email);
        $userExpected->setPassword($password);

        $form = $this->factory->create(RegisterType::class, $userActual);
        $form->submit(['email' => $email, 'password' => $password]);

        static::assertTrue($form->isSynchronized());
        static::assertTrue($form->isSubmitted());
        static::assertTrue($form->isValid());
        static::assertEquals($userExpected, $userActual);
    }

    public function dataProviderForSubmitValidDataTesting(): Generator
    {
        $faker = Factory::create();

        yield [$faker->email, $faker->password];
        yield [$faker->email, $faker->password];
        yield [$faker->email, $faker->password];
        yield [$faker->email, $faker->password];
    }

    /**
     * @covers       \App\Form\Auth\RegisterType
     * @dataProvider dataProviderForSubmitInvalidDataTesting
     */
    public function testSubmitInvalidData(
        string $email,
        string $password,
        array $expectedMessages,
        ?string $otherEmail = null
    ): void {
        $userActual = new User();

        if ($otherEmail) {
            $this->authServiceMock->expects(static::once())
                ->method('checkEmailUsed')
                ->with(...[$email])
                ->willReturn(true);
        }

        $form = $this->factory->create(RegisterType::class, $userActual);
        $form->submit(['email' => $email, 'password' => $password]);

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

        yield ['string', $faker->password, ['This value is not a valid email address.']];
        yield [
            $faker->paragraph . $faker->paragraph . $faker->email,
            $faker->password,
            ['This value is too long. It should have 180 characters or less.']
        ];
        yield [$faker->email, 'str', ['This value is too short. It should have 6 characters or more.']];
        yield [$faker->email, $faker->paragraph, ['This value is too long. It should have 50 characters or less.']];
        yield [$faker->email, $faker->password, ['This email is used by another user'], $faker->email];
    }

    protected function getExtensions(): array
    {
        $createTaskType = new RegisterType($this->authServiceMock);

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

    protected function setUp(): void
    {
        $this->authServiceMock = $this->createMock(AuthService::class);

        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->authServiceMock);
    }
}