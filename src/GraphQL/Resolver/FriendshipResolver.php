<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Friendship;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;

class FriendshipResolver implements ResolverInterface, AliasedInterface
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
        $friendship = $this->em->getRepository(Friendship::class)->find($args["id"]);
        
        if($friendship->getAvailable())
        {
            return $friendship;
        }
    }
    /*
    {
  friendship(id: 3) {
    id
    user1 {
      id
      userName
      slug
      room
      {
        id
        slug
        game
        {
          id
          name
          slug
          category
          {
            id
            name
            slug
          }
        }
        name
      }
      password
      email
      stats
    }
    user2 {
      id
      userName
      slug
      room
      {
        id
        slug
        game
        {
          id
          name
          slug
          category
          {
            id
            name
            slug
          }
        }
        name
      }
      password
      email
      stats
    }
    status
  }
}
*/

    public function list(Argument $args)
    {
        $friendship = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;

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
        if (!empty($args["user"])) {
            $where["user1"]["id"] = $args["user"];
            $friendship = $this->paginator->paginate(
                $this->em->getRepository(Friendship::class)->findBy(
                    $where,
                    array($column => $order)
                ),
                $args["page"],
                $args["limit"]
            );
        }
        
        return [
            "friendship" => $friendship,
            "total" =>$friendship->getTotalItemCount()
        ];
    }

    /*
    {
  allFriendships(limit: 10, page: 1,user:2) {
    total
    friendship {
      id
      user1 {
        id
        userName
      }
      user2 {
        id
        userName
        
      }
      status
    }
  }
}


*/

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Friendship",
            "list" => "allFriendships",
        );
    }

}