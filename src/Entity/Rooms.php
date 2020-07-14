<?php

namespace App\Entity;

use App\Repository\RoomsRepository;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RoomsRepository::class)
 */
class Rooms
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $isPrivate;

    /**
     * @ORM\ManyToOne(targetEntity=games::class, cascade={"persist", "remove"})
     */
    private $game;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsPrivate(): ?string
    {
        return $this->isPrivate;
    }

    public function setIsPrivate(?bool $isPrivate): self
    {
        $this->isPrivate = $isPrivate;

        return $this;
    }

    public function getGame(): ?games
    {
        return $this->category;
    }

    public function setGame(?games $game): self
    {
        $this->game = $game;

        return $this;
    }
}
