<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Room;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Security;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;

use Symfony\Component\Security\Core\Security;

class UserResolver implements ResolverInterface, AliasedInterface
{
    private $em;
    private $paginator;
    private $jwt;
    private $security;

    public function __construct(JWTEncoderInterface $jwt, EntityManager $em, PaginatorInterface $paginator, Security $security)
    {
        $this->em = $em;
        $this->paginator = $paginator;
        $this->jwt = $jwt;
        $this->security = $security;
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
        
        $users = $this->em->getRepository(User::class)->findBy(
            $where,
            array($column => $order)
        );
        
        $limit = $args["limit"];
        if($args["limit"] == 0)
        {
            $limit = 1;
        }
        $result = $this->paginator->paginate(
            $users,
            $args["page"],
            $limit
        );
        if($args["limit"] == 0)
        {
            return [
                "users" => $users,
                "total" => $result->getTotalItemCount()
            ];
        }
        return [
            "users" => $result,
            "total" => $result->getTotalItemCount()
        ];
    }

    public function tokenResolver(Argument $args)
    {
        return $this->security->getUser();
    }
    /*
    {
  userFromToken {
    id
    userName
    password
  }
}
*/
//Kell, hogy legyen token, csak postmanbő működik.

    public static function getAliases(): array
    {
        return array(
            "resolve" => "User",
            "list" => "allUsers",
            "tokenResolver" => "UserFromToken"
        );
    }

}