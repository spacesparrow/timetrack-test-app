<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TaskRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass=TaskRepository::class)
 * @ORM\Table(name="tasks")
 * @Serializer\ExclusionPolicy("all")
 */
class Task
{
    use TimestampableEntity;

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
     * @Serializer\Expose()
     */
    private int $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="tasks")
     * @ORM\JoinColumn(nullable=false)
     */
    private User $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private string $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private string $comment;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", name="time_spent")
     * @Serializer\Expose()
     */
    private int $timeSpent;

    /**
     * @var DateTime $createdAt
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Serializer\Expose()
     * @Serializer\Type("DateTime")
     */
    protected $createdAt;

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
     * @param DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }
}
