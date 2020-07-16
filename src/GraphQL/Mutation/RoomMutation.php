<?php

namespace App\GraphQL\Mutation;

use App\Entity\Room;
use App\Entity\Game;
use App\GraphQL\Resolver\RoomResolver;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class RoomMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(Argument $args)
    {
        
        $room = new Room();
        $room->setGame($this->em->getRepository(Game::class)->find($args["room"]["game"]));
        $room->setIsPrivate($args["room"]["isPrivate"]);
        $room->setAvailable(true);

        $this->em->persist($room);
        $this->em->flush();

        return $room;
    }
    /*
    mutation {
  createRoom(room: {game: 3, isPrivate: true}) {
    id
    slug
    isPrivate
  }
}

    */
      

    public function update(Argument $args)
    {
        $room = $this->em->getRepository(Room::class)->find($args["id"]);

        if(!empty($args["room"]["game"]))
        {
            $room->setGame($this->em->getRepository(Game::class)->find($args["room"]["game"]));
        }
        if(!empty($args["room"]["isPrivate"]))
        {
            $room->setIsPrivate($args["room"]["isPrivate"]);  
        }

        $this->em->flush();

        return $room;

    }


    /*
    mutation {
  updateRoom(room: {game: 6,isPrivate:false}, id: 2) {
    id
  }
}

    */

    public function delete(Argument $args)
    {
        $game = $this->em->getRepository(Room::class)->find($args["id"]);

        $game->setAvailable(false);

        $this->em->flush();

        return 1;

    }

    /*
    mutation {
  deleteRoom(id: 2) {
    id
  }
}
    */

    public static function getAliases(): array
    {
        return array(
            "create" => "createRoom",
            "update" => "updateRoom",
            "delete" => "deleteRoom"
            );
    }
}