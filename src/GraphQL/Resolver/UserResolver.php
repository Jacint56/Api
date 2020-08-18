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

use Symfony\Component\HttpFoundation\Request;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\AuthorizationHeaderTokenExtractor;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use PhpParser\Node\Expr\Cast\String_;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Constraints\Url;

class UserResolver implements ResolverInterface, AliasedInterface
{
    private $em;
    private $paginator;
    private $jwt;

    public function __construct(JWTEncoderInterface $jwt, EntityManager $em, PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->paginator = $paginator;
        $this->jwt = $jwt;
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
        $authorizationHeader = $_SERVER['HTTP_AUTHORIZATION'];
        $token = substr($authorizationHeader, 7);

        if(empty($token)){
            throw new \GraphQL\Error\UserError('Can\'t find token!');
            exit();
        }

        $data = $this->jwt->decode($token);

        $users = $this->em->getRepository(User::class)->findBy(
            array(
                'userName' => $data['username'])
        );

        if(empty($users)){
            throw new \GraphQL\Error\UserError('Invalid token!');
            exit();
        }

        $user = $users[0];
        if($user->getAvailable())
        {
            return $user;
        }
    }

    public static function getAliases(): array
    {
        return array(
            "resolve" => "User",
            "list" => "allUsers",
            "tokenResolver" => "UserFromToken"
        );
    }

}