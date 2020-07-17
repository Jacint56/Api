<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;

class CommentResolver implements ResolverInterface, AliasedInterface
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
        $post = $this->em->getRepository(Comment::class)->find($args["id"]);
        if($post->getAvailable())
        {
            return $post;
        }
    }
    /*
    {
  Comment(id: 2) {
    id
    content
    post {
      title
    }
    poster {
      userName
    }
  }
}
*/

    public function list(Argument $args)
    {
        $posts = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;

        if(!empty($args["post"]))
        {
            $where["post"] = $this->em->getRepository(Post::class)->find($args["post"]);
        }

        if(!empty($args["poster"]))
        {
            $where["poster"] = $this->em->getRepository(User::class)->find($args["poster"]);
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
        
        $comments = $this->paginator->paginate(
            $this->em->getRepository(Comment::class)->findBy(
                $where,
                array($column => $order)
            ),
            $args["page"],
            $args["limit"]
        );
        
        return [
            "comments" => $comments,
            "total" =>$comments->getTotalItemCount()
        ];
    }
    /*
    {
  post(id: 1) {
    id
    title
    slug
    content
    poster {
      id
      userName
    }
  }
  allComments(limit: 10, page: 1, post: 1) {
    total
    comments {
      id
      content
      poster {
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
            "resolve" => "Comment",
            "list" => "allComments",
        );
    }

}