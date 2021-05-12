<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\AuthService;
use App\Tests\Traits\ReflectionTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\NativePasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoder;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\VarDumper\Cloner\Stub;

class AuthServiceTest extends TestCase
{
    use ReflectionTrait;

    /** @var UserRepository|MockObject */
    private $userRepositoryMock;

    /** @var UserPasswordEncoderInterface|MockObject|Stub */
    private $userPasswordEncoderMock;

    /**
     * @covers \App\Service\AuthService::__construct
     */
    public function testConstruct(): void
    {
        $authService = $this->getAuthService();

        static::assertInstanceOf(
            UserRepository::class,
            $this->getPropertyValue($authService, 'userRepository')
        );
        static::assertInstanceOf(
            UserPasswordEncoderInterface::class,
            $this->getPropertyValue($authService, 'userPasswordEncoder')
        );
    }

    /**
     * @covers       \App\Service\AuthService::checkEmailUsed
     * @dataProvider dataProviderForCheckEmailUsedTesting
     * @param string $email
     * @param bool $expected
     */
    public function testCheckEmailUsed(string $email, bool $expected): void
    {
        $this->userRepositoryMock->expects(static::once())
            ->method('__call')
            ->with(
                ...[
                    'findOneByEmail',
                    [$email]
                ]
            )
            ->willReturn($expected);

        static::assertSame(
            $expected,
            $this->getAuthService()->checkEmailUsed($email)
        );
    }

    /**
     * @return array[]
     */
    public function dataProviderForCheckEmailUsedTesting(): array
    {
        return [
            'used' => ['generalemail@domain.com', true],
            'not used' => ['someuniqueemail@domain.com', false]
        ];
    }

    /**
     * @covers \App\Service\AuthService::encodeUserPassword
     */
    public function testEncodeUserPassword(): void
    {
        $user = new User();
        $plainPassword = 'somepassword';
        $user->setPassword($plainPassword);

        $this->getAuthService()->encodeUserPassword($user);

        static::assertNotEquals(
            $plainPassword,
            $user->getPassword()
        );
    }

    /**
     * @return AuthService
     */
    private function getAuthService(): AuthService
    {
        return new AuthService(
            $this->userRepositoryMock,
            $this->userPasswordEncoderMock
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepositoryMock = $this->createMock(UserRepository::class);
        $this->userPasswordEncoderMock = new UserPasswordEncoder(
            new EncoderFactory(
                [User::class => new NativePasswordEncoder()]
            )
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown(): void
    {
        unset(
            $this->userRepositoryMock,
            $this->userPasswordEncoderMock
        );

        parent::tearDown();
    }
}