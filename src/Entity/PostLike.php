<?php

namespace App\Entity;

use App\Entity\Post;
use App\Entity\User;
use App\Repository\PostLikeRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Entity(repositoryClass=PostLikeRepository::class)
 */
class PostLike
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
     * @ORM\ManyToOne(targetEntity=post::class, cascade={"persist", "remove"})
     */
    private $post;

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

    public function getPost(): ?post
    {
        return $this->post;
    }

    public function setPost(?post $post): self
    {
        $this->post = $post;

        return $this;
    }
}
