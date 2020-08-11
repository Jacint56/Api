<?php

namespace App\Entity;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentLikeRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Entity(repositoryClass=CommentLikeRepository::class)
 */
class CommentLike
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=user::class, cascade={"persist", "remove"})
     */
    private $liker;

    /**
     * @ORM\ManyToOne(targetEntity=comment::class, cascade={"persist", "remove"})
     */
    private $comment;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $available;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAvailable(): ?bool
    {
        return $this->available;
    }

    public function setAvailable(bool $available): self
    {
        $this->available = $available;

        return $this;
    }

    public function getLiker(): ?user
    {
        return $this->liker;
    }

    public function setLiker(?user $liker): self
    {
        $this->liker = $liker;

        return $this;
    }

    public function getComment(): ?comment
    {
        return $this->comment;
    }

    public function setComment(?comment $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
