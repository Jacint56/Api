<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\CommentLike;
use Doctrine\ORM\EntityManager;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;

class CommentLikeResolver implements ResolverInterface, AliasedInterface
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
        $like = $this->em->getRepository(CommentLike::class)->find($args["id"]);
        if($like->getAvailable())
        {
            return $like;
        }
    }
    /*
{
  CommentLike(id: 1) {
    id
    liker {
      userName
    }
    comment {
      id
      content
      poster {
        userName
      }
      post {
        title
        content
        poster {
          userName
        }
      }
    }
  }
}

*/

    public function list(Argument $args)
    {
        $likes = array();
        $where = array();
        $column = "id";
        $order = "ASC";

        $where["available"] = true;

        if(!empty($args["comment"]))
        {
            $where["comment"] = $this->em->getRepository(Comment::class)->find($args["comment"]);
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
        
        $likes = $this->em->getRepository(CommentLike::class)->findBy(
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
            return [
                "commentLikes" => $likes,
                "total" => $result->getTotalItemCount()
            ];
        }
        return [
            "commentLikes" => $result,
            "total" => $result->getTotalItemCount()
        ];
    }
/*
{
  allCommentLikes(limit: 0, page: 1, liker: 1) {
    total
    commentLikes {
      id
      comment {
        id
        content
        poster {
          userName
        }
        post {
          title
          content
          poster {
            userName
          }
        }
      }
    }
  }
}
*/
    public static function getAliases(): array
    {
        return array(
            "resolve" => "CommentLike",
            "list" => "allCommentLikes"
        );
    }

}