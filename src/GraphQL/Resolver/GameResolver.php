<?php

namespace App\GraphQL\Resolver;

use App\Entity\Category;
use App\Entity\Game;
use Doctrine\ORM\EntityManager;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;

class GameResolver implements ResolverInterface, AliasedInterface
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
        $game = $this->em->getRepository(Game::class)->find($args["id"]);
        if($game->getAvailable())
        {
            return $game;
        }
    }
    /*
    {
  game(id: 4) {
    id
    name
    slug
    category {
      id
      name
      slug
    }
  }
}

    */

    public function list(Argument $args)
    {
        $games = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;

        if(!empty($args["name"]))
        {
            $where["name"] = $args["name"];
        }

        if(!empty($args["category"]))
        {
            $where["category"] = $args["category"];
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
        
        $games = $this->em->getRepository(Category::class)->findBy(
            $where,
            array($column => $order)
        );
        $limit = $args["limit"];
        if($args["limit"] == 0)
        {
            $limit = 1;
        }
        $result = $this->paginator->paginate(
            $games,
            $args["page"],
            $limit
        );
        if($args["limit"] == 0)
        {
            return [
                "games" => $games,
                "total" => $result->getTotalItemCount()
            ];
        }
        return [
            "games" => $result,
            "total" => $result->getTotalItemCount()
        ];
    }
    /*
    {
  allGames(limit: 10, page: 1) {
    games {
      id
      name
      slug
      category {
        id
        name
        slug
      }
    }
    total
  }
}

    */

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Game",
            "list" => "allGames"
        );
    }

}