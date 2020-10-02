<?php

namespace App\GraphQL\Resolver;

use App\Entity\Room;
use Doctrine\ORM\EntityManager;
use Exception;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class writeRoomResolver implements ResolverInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

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

        $offset = 0;
        for ($i = 0; $i < 4 * $args['limit']; $i++){
            $temp = stripos($chat, "\n", $offset) + 1;
            if ($temp < $offset){
                break;
            }
            $offset = $temp;
        }
        return substr($chat, 0, $offset);

    }
    /*
    {
    readRoom(limit: 1, id: 3)
}
*/

    public static function getAliases(): array
    {
        return array(
            "read" => "ReadRoom"
        );
    }
}