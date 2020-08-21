<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\PostLike;
use Doctrine\ORM\EntityManager;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;

class PostLikeResolver implements ResolverInterface, AliasedInterface
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
        $like = $this->em->getRepository(PostLike::class)->find($args["id"]);
        if($like->getAvailable())
        {
            return $like;
        }
    }
/*{
  postLike(id:3)
  {
    id
    liker
    {
      id
      userName
      email
    }
    post
    {
      id
      title
      content
    }
  }
} */
    public function list(Argument $args)
    {
        $likes = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;

        if(!empty($args["post"]))
        {
            $where["post"] = $this->em->getRepository(Post::class)->find($args["post"]);
        }

        if(!empty($args["liker"]))
        {
            $where["liker"] = $this->em->getRepository(User::class)->find($args["liker"]);
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
        
        $likes = $this->em->getRepository(PostLike::class)->findBy(
            $where,
            array($column => $order)
        );
        $limit = $args["limit"];
        if($args["limit"] == 0)
        {
            $limit = 1;
        }
        $result = $this->paginator->paginate(
            $likes,
            $args["page"],
            $limit
        );
        if($args["limit"] == 0)
        {
            return array(
                "postLikes" => $likes,
                "total" => $result->getTotalItemCount()
            );
        }
        return array(
            "postLikes" => $result,
            "total" => $result->getTotalItemCount()
        );
    }

/*{
  allPostLikes(limit:0 page:1 post:1 )
  {
    postLikes
    {
      id
      liker {
        id
        userName
      }
      post {
        id
        title
        content
      }
    }
  }
} */

    public static function getAliases(): array
    {
        return array(
            "resolve" => "PostLike",
            "list" => "allPostLikes"
        );
    }

}