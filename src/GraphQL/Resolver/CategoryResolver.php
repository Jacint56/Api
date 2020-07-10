<?php

namespace App\GraphQL\Resolver;

use App\Entity\Category;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

class CategoryResolver implements ResolverInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function resolve(Argument $args)
    {
        return $this->em->getRepository(Category::class)->find($args["id"]);
    }

    public function list(Argument $args)
    {
        $categories = array();
        if(empty($args["name"]))
        {
            $categories = $this->em->getRepository(Category::class)->findBy([], [], $args["limit"], ($args["page"] - 1) * $args["limit"]);
        }
        else
        {
            $categories = $this->em->getRepository(Category::class)->findBy(["name"=>$args["name"]], [], $args["limit"], ($args["page"] - 1) * $args["limit"]);
        }
        return [
            "categories" => $categories
        ];
    }
    public static function getAliases(): array
    {
        return array(
            "resolve" => "Category",
            "list" => "allCategories"
        );
    }
}