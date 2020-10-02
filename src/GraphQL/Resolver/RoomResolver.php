<?php

namespace App\GraphQL\Resolver;

use App\Entity\Room;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Exception;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;

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
        
        $rooms = $this->em->getRepository(Room::class)->findBy(
            $where,
            array($column => $order)
        );
        $limit = $args["limit"];
        if($args["limit"] == 0)
        {
            $limit = 1;
        }
        $result = $this->paginator->paginate(
            $rooms,
            $args["page"],
            $limit
        );
        if($args["limit"] == 0)
        {
            return [
                "rooms" => $rooms,
                "total" => $result->getTotalItemCount()
            ];
        }
        return [
            "rooms" => $result,
            "total" => $result->getTotalItemCount()
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

    public function read(Argument $args)
    {
        $room = $this->em->getRepository(Room::class)->find($args["id"]);
        if (empty($room) || !($room->getAvailable())) {
            throw new \GraphQL\Error\UserError('This room doesn\'t exist!');
            exit();
        }

        $filePath = "rooms/" . $room->getId() . ".txt";

        try{
            $myfile = fopen($filePath, "r");
            $chat = fread($myfile,filesize($filePath));
        }
        catch(Exception $err){
            throw new \GraphQL\Error\UserError("Can't read room conversation!");
            exit();
        }
        finally{}

        $count = (int)(substr_count($chat, "\n") / 4);

        $offset = 0;
        for ($i = 0; $i < 4 * $args['limit']; $i++){
            $temp = stripos($chat, "\n", $offset) + 1;
            if ($temp < $offset){
                break;
            }
            $offset = $temp;
        }

        $chat = substr($chat, 0, $offset);

        $messages = array();
        $chat = explode("\n", $chat);

        if($args["limit"] > $count){
            $max = $count;
        }
        else{
            $max = $args["limit"];
        }
        for ($i = 0; $i < $max * 4; $i += 2) { 
            $sender = $this->em->getRepository(User::class)->find($chat[$i]);
            array_push(
                $messages,
                [
                    "sender" => $sender,
                    "date" => $chat[++$i],
                    "content" => $chat[++$i]
                ]
            );
        }

        $result = [
            "messages" => $messages,
            "total" => $count,
            "room" => $room
        ];
        return $result;

    }

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Room",
            "list" => "allRooms",
            "read" => "ReadRoom"
        );
    }

}