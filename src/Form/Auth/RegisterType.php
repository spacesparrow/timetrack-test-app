<?php

declare(strict_types=1);

namespace App\Form\Auth;

use App\Entity\User;
use App\Service\AuthService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class RegisterType extends AbstractType
{
    /** @var AuthService  */
    private AuthService $authService;

    /**
     * RegisterType constructor.
     * @param AuthService $authService
     */
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\Email(),
                        new Constraints\Length(['min' => User::MIN_EMAIL_LENGTH, 'max' => User::MAX_EMAIL_LENGTH])
                    ],
                    'documentation' => [
                        'type' => 'string',
                        'example' => 'example@domain.com'
                    ]
                ]
            )
            ->add(
                'password',
                PasswordType::class,
                [
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\Length(['min' => User::MIN_PASSWORD_LENGTH, 'max' => User::MAX_PASSWORD_LENGTH])
                    ],
                    'documentation' => [
                        'type' => 'string',
                        'example' => 'somepassword'
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
            'data_class' => User::class,
            'constraints' => [
                new Constraints\Callback([$this, 'validate'])
            ]
        ]);
    }

    /**
     * @param User $user
     * @param ExecutionContextInterface $context
     */
    public function validate(User $user, ExecutionContextInterface $context): void
    {
        if ($this->authService->checkEmailUsed($user->getEmail())) {
            $context->buildViolation('This email is used by another user')
                ->atPath('email')
                ->addViolation();
        }
    }
}
