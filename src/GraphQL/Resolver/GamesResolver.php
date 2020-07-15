<?php

namespace App\GraphQL\Resolver;

use App\Entity\Category;
use App\Entity\Games;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;

class GamesResolver implements ResolverInterface, AliasedInterface
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
        return $this->em->getRepository(Games::class)->find($args["id"]);
    }

    public function list(Argument $args)
    {
        $games = array();
        $where = array();
        $column = "id";
        $order = "ASC";

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
        
        $categories = $this->paginator->paginate(
            $this->em->getRepository(Games::class)->findBy(
                $where,
                array($column => $order)
            ),
            $args["page"],
            $args["limit"]
        );
        
        return [
            "games" => $games,
            "total" =>$categories->getTotalItemCount()
        ];
    }


    public static function getAliases(): array
    {
        return array(
            "resolve" => "Game",
            "list" => "allGames",
        );
    }

}