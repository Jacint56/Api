<?php

namespace App\Entity;

use App\Entity\Game;
use App\Repository\RoomRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
/**
 * @ORM\Entity(repositoryClass=RoomRepository::class)
 */
class Room
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=game::class, cascade={"persist", "remove"})
     */
    private $game;

     /**
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     * @Gedmo\Slug(fields={"id"})
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPrivate;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $available;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGame(): ?game
    {
        return $this->game;
    }

    public function setGame(?game $game): self
    {
        $this->game = $game;

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

    public function getIsPrivate(): ?bool
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }
}
