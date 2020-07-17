<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Room;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;

class UserResolver implements ResolverInterface, AliasedInterface
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
        $user = $this->em->getRepository(User::class)->find($args["id"]);
        if($user->getAvailable())
        {
            return $user;
        }
    }
    /*
    {
  user(id: 3) {
    id
    userName
    slug
    password
    email
    room{
        name
        game{
            name
        }
    }
  }
}
*/

    public function list(Argument $args)
    {
        $users = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;

        if(!empty($args["room"]))
        {
            $where["room"] = $this->em->getRepository(Room::class)->find($args["room"]);
        }

        if(!empty($args["userName"]))
        {
            $where["userName"] = $args["userName"];
        }

        if(!empty($args["stats"]))
        {
            $where["stats"] = $args["stats"];
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
        
        $users = $this->paginator->paginate(
            $this->em->getRepository(User::class)->findBy(
                $where,
                array($column => $order)
            ),
            $args["page"],
            $args["limit"]
        );
        
        return [
            "users" => $users,
            "total" =>$users->getTotalItemCount()
        ];
    }

    public static function getAliases(): array
    {
        return array(
            "resolve" => "User",
            "list" => "allUsers",
        );
    }

}