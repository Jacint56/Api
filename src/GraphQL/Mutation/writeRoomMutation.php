<?php

namespace App\GraphQL\Mutation;

use App\Entity\Room;
use Doctrine\ORM\EntityManager;
use Exception;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class writeRoomMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function write(Argument $args)
    {
        $room = $this->em->getRepository(Room::class)->find($args["id"]);
        if (empty($room) || !($room->getAvailable())) {
            throw new \GraphQL\Error\UserError('This room doesn\'t exist!');
            exit();
        }

        $message = 
            "\n" .
            $args["writer"] .
            "\n" .
            $args["date"] .
            "\n" .
            $args["message"] .
            "\n";

        $filePath = "rooms/" . $room->getId() . ".txt";

        $chat = "";
        try{
            $myfile = fopen($filePath, "r");
            $chat = fread($myfile,filesize($filePath));
        }
        catch(Exception $err){}
        finally{}

        $chat = $message . $chat;;

        $stdout = fopen($filePath, "w");
        fwrite($stdout, $chat);

        //Legutolsó üzenet
        return true;

    }
    /*
    mutation{
    writeRoom(id:3, writer:4, date:"21-25-1926", message:"anyad")
}
*/

    public static function getAliases(): array
    {
        return array(
            "write" => "writeRoom"
        );
    }
}