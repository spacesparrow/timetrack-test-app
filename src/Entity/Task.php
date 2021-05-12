<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use OpenApi\Annotations as OA;

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
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Exclude()
     */
    private User $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private string $comment;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="time_spent")
     */
    private int $timeSpent;

    /**
     * @var DateTime|null $createdDate
     *
     * @ORM\Column(type="datetime", name="created_date")
     * @Serializer\Type("DateTime<'Y-m-d'>")
     * @OA\Property(example="2021-05-08")
     */
    private ?DateTime $createdDate;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     * @return $this
     */
    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getTimeSpent(): ?int
    {
        return $this->timeSpent;
    }

    /**
     * @param int $timeSpent
     * @return $this
     */
    public function setTimeSpent(int $timeSpent): self
    {
        $this->timeSpent = $timeSpent;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedDate(): ?DateTime
    {
        return $this->createdDate;
    }

    /**
     * @param DateTime|null $createdDate
     */
    public function setCreatedDate(?DateTime $createdDate): void
    {
        $this->createdDate = $createdDate;
    }

    /**
     * @return string
     */
    public function getFormattedDate(): string
    {
        return $this->createdDate->format('d/m/Y');
    }
}
