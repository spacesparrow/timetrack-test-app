<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\Table(name="tasks")
 */
class Task
{
    public const MIN_TITLE_LENGTH = 5;
    public const MAX_TITLE_LENGTH = 255;
    public const MIN_COMMENT_LENGTH = 10;
    public const MAX_COMMENT_LENGTH = 255;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Exclude()
     */
    private User $user;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255)
     */
    private string $comment;

    /**
     * @Assert\NotBlank()
     * @Assert\PositiveOrZero()
     * @ORM\Column(type="integer", name="time_spent")
     */
    private int $timeSpent;

    /**
     * @Assert\DateTime()
     * @ORM\Column(type="datetime", name="created_date")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @OA\Property(example="2021-05-08")
     */
    private ?DateTime $createdDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @return $this
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    /**
     * @return $this
     */
    public function setTimeSpent(int $timeSpent): self
    {
        $this->timeSpent = $timeSpent;

        return $this;
    }

    public function getCreatedDate(): ?DateTime
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?DateTime $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    public function getFormattedDate(): string
    {
        return $this->createdDate->format('d/m/Y');
    }

    public function toExportArray(): array
    {
        return [
            $this->id,
            $this->getFormattedDate(),
            $this->title,
            $this->comment,
            $this->timeSpent,
        ];
    }
}
