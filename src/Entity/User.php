<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 */
class User
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $userName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $passwd;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

     /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     * @Gedmo\Slug(fields={"userName"})
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $available;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $stats;

    /**
     * @ORM\ManyToOne(nullable=true, targetEntity=room::class, cascade={"persist", "remove"})
     */
    private $room;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserName(): ?string
    {
        return $this->userName;
    }

    public function setUserName(?string $userName): self
    {
        $this->userName = $userName;

        return $this;
    }

    public function getRoom(): ?room
    {
        return $this->room;
    }

    public function setRoom(?room $room): self
    {
        $this->room = $room;

        return $this;
    }
    public function getSlug(): ?string
    {
        return $this->slug;
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

    public function getPassWord(): ?string
    {
        return $this->passwd;
    }

    public function setPassWord(?string $passwd): self
    {
        $this->passwd = $passwd;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getStats(): ?bool
    {
        return $this->stats;
    }

    public function setStats(bool $stats): self
    {
        $this->stats = $stats;

        return $this;
    }
}
