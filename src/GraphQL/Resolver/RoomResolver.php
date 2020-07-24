<?php

namespace App\GraphQL\Resolver;

use App\Entity\Room;
use App\Entity\Game;
use Doctrine\ORM\EntityManager;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;

class RoomResolver implements ResolverInterface, AliasedInterface
{
    private $em;
    private $paginator;

    public function __construct(EntityManager $em, PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->paginator = $paginator;
    }

    public function resolve(Argument $args)
    {
        $room = $this->em->getRepository(Room::class)->find($args["id"]);
        if($room->getAvailable())
        {
            return $room;
        }
    }
    /*
    {
  room(id: 1) {
    id
    name
    slug
    game {
      name
    }
  }
}
*/

    public function list(Argument $args)
    {
        $rooms = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;
        $where["isPrivate"] = false;

        if(!empty($args["name"]))
        {
            $where["name"] = $args["name"];
        }

        if(!empty($args["column"]))
        {
            if(substr($args["column"], 0, 1) == '-')
            {
                $column = substr($args["column"], 1);
                $order = "DESC";
            }
            else
            {
                $column = $args["column"];
            }
        }
        
        $rooms = $this->paginator->paginate(
            $this->em->getRepository(Room::class)->findBy(
                $where,
                array($column => $order)
            ),
            $args["page"],
            $args["limit"]
        );
        
        return [
            "rooms" => $rooms,
            "total" =>$rooms->getTotalItemCount()
        ];
    }
    /*
    {
  allRooms(limit: 10, page: 1) {
    total
    rooms {
      id
      name
      slug
      game {
        name
      }
    }
  }
}
*/

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Room",
            "list" => "allRooms"
        );
    }

}