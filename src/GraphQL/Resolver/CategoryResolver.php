<?php

namespace App\GraphQL\Resolver;

use App\Entity\Category;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;

class CategoryResolver implements ResolverInterface, AliasedInterface
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
        return $this->em->getRepository(Category::class)->find($args["id"]);
    }

    public function list(Argument $args)
    {
        $categories = array();
        if(empty($args["name"]))
        {
            $categories = $this->paginator->paginate($this->em->getRepository(Category::class)->findAll(), $args["page"], $args["limit"]);
        }
        else
        {
            $categories = $this->paginator->paginate($this->em->getRepository(Category::class)->findBy(["name"=>$args["name"]]), $args["page"], $args["limit"]);
        }
        return [
            "categories" => $categories
        ];
    }
    public function counterF(Argument $args){
        $categories = array();
        if(empty($args["name"]))
        {
            $categories = $this->em->getRepository(Category::class)->findAll();
        }
        else
        {
            $categories = $this->em->getRepository(Category::class)->findBy(["name"=>$args["name"]]);
        }
        return [
            "number" => count($categories)
        ];
    }

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Category",
            "list" => "allCategories",
            "counterF" => "counter"
        );
    }

}