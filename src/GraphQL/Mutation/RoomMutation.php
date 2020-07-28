<?php

namespace App\GraphQL\Mutation;

use App\Entity\Room;
use App\Entity\Game;
use App\GraphQL\Resolver\GameResolver;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class RoomMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em, GameResolver $gameResolver)
    {
        $this->em = $em;
    }

    public function create(Argument $args)
    {
        $room = new Room();
        $room->setGame($this->em->getRepository(Game::class)->find($args["room"]["game"]));
        $room->setIsPrivate($args["room"]["isPrivate"]);
        $room->setName($args["room"]["name"]);

        $room->setAvailable(true);

        $this->em->persist($room);
        $this->em->flush();

        return $room;
    }
    /*
    mutation {
  createRoom(room: {game: 3, isPrivate: true, name: "Random Szoba név"}) {
    id
    slug
    game {
      id
      name
      category {
        id
        name
      }
    }
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

        if(!empty($args["room"]["name"]))
        {
            $room->setName($args["room"]["name"]);
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
  updateRoom(id: 1, room: {name: "Miháj Sumáher", game: 8}) {
    name
    id
    slug
    game {
      name
    }
  }
}
*/
      
    public function delete(Argument $args)
    {
        $room = $this->em->getRepository(Room::class)->find($args["id"]);
        if(!empty($room) && $room->getAvailable())
        {
            $room->setAvailable(false);
            $this->em->flush();
            return true;
        }
        return false;
    }
    /*
    mutation {
  deleteRoom(id: 9) {
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