<?php

namespace App\Entity;

use App\Entity\User;
use App\Repository\FriendshipRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=FriendshipRepository::class)
 */
class Friendship
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
    private $user1;


    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=user::class, cascade={"persist", "remove"})
     */

    private $user2;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $available;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser1(): ?user
    {
        return $this->user1;
    }

    public function setUser1(?user $user): self
    {
        $this->user1 = $user;

        return $this;
    }
    public function getUser2(): ?user
    {
        return $this->user2;
    }

    public function setUser2(?user $user): self
    {
        $this->user2 = $user;

        return $this;
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

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
