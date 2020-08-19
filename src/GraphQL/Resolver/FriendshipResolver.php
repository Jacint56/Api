<?php

namespace App\GraphQL\Resolver;

use App\Entity\Friendship;
use Doctrine\ORM\EntityManager;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;

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
  friendship(id: 1) {
    id
    sender {
      id
      userName
    }
    reciver {
      id
      userName
    }
    status
  }
}

*/

    public function list(Argument $args)
    {
        $friendship = array();
        $sender = array();
        $reciver = array();
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

        if(!empty($args["user"]))
        {
            $sender["sender"]["id"] = $args["user"];
            $reciver["reciver"]["id"] = $args["user"];
            $friendship = 
                array_merge(
                    $this->em->getRepository(Friendship::class)->findBy(
                        array_merge(
                            $where,
                            $sender
                        ),
                        array($column => $order)
                    ),
                    $this->em->getRepository(Friendship::class)->findBy(
                        array_merge(
                            $where,
                            $reciver
                        ),
                        array($column => $order)
                    )
                )
            ;
        }
        else
        {
            $friendship = 
                $this->em->getRepository(Friendship::class)->findBy(
                    $where,
                    array($column => $order)
                )
            ;
        }
        $limit = $args["limit"];
        if($args["limit"] == 0)
        {
            $limit = 1;
        }
        $result = $this->paginator->paginate(
            $friendship,
            $args["page"],
            $limit
        );
        if($args["limit"] == 0)
        {
            return [
                "friendship" => $friendship,
                "total" => $result->getTotalItemCount()
            ];
        }
        return [
            "friendship" => $result,
            "total" => $result->getTotalItemCount()
        ];
    }
    /*
    {
  allFriendships(limit: 10, page: 1, user: 3) {
    total
    friendship {
      id
      sender {
        id
        userName
      }
      reciver {
        id
        userName
      }
    }
  }
}

*/

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Friendship",
            "list" => "allFriendships"
        );
    }

}