<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Post;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;

class PostResolver implements ResolverInterface, AliasedInterface
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
        $post = $this->em->getRepository(Post::class)->find($args["id"]);
        if($post->getAvailable())
        {
            return $post;
        }
    }

    public function list(Argument $args)
    {
        $posts = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;

        if(!empty($args["title"]))
        {
            $where["title"] = $args["title"];
        }

        if(!empty($args["poster"]))
        {
            $where["poster"] = $this->em->getRepository(User::class)->find($args["id"]);
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
        
        $posts = $this->paginator->paginate(
            $this->em->getRepository(Post::class)->findBy(
                $where,
                array($column => $order)
            ),
            $args["page"],
            $args["limit"]
        );
        
        return [
            "posts" => $posts,
            "total" =>$posts->getTotalItemCount()
        ];
    }

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Post",
            "list" => "allPosts",
        );
    }

}