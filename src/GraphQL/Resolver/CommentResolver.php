<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\CommentLike;
use Doctrine\ORM\EntityManager;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;

class Response{
    public $id;
    public $poster;
    public $content;
    public $likes;
}

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
        $comment = $this->em->getRepository(Comment::class)->find($args["id"]);

        $response = new Response;
        $response -> id = $comment -> getId();
        $response -> content = $comment -> getContent();
        $response -> poster = $comment -> getPoster();
        $response -> post = $comment -> getPost();

        $where = array();
        $where["comment"] = $comment;
        $likes = $this->em->getRepository(CommentLike::class)->findBy(
            $where
        );
        $result = $this->paginator->paginate(
            $likes,
            1,
            1
        );
        $response -> likes = $result->getTotalItemCount();

        if($comment->getAvailable())
        {
            return $response;
        }
    }
    /*
{
  Comment(id: 1) {
    id
    content
    post {
      title
    }
    poster {
      userName
    }
    likes
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
        
        $comments = $this->em->getRepository(Comment::class)->findBy(
            $where,
            array($column => $order)
        );
        $limit = $args["limit"];
        if($args["limit"] == 0)
        {
            $limit = 1;
        }
        $result = $this->paginator->paginate(
            $comments,
            $args["page"],
            $limit
        );

        $where = array();
        $responses = array();
        if($args["limit"] == 0){
            foreach($comments as $source){
                $response = new Response;
                $response -> id = $source -> getId();
                $response -> content = $source -> getContent();
                $response -> poster = $source -> getPoster();
                $response -> post = $source -> getPost();

                $whereL["comment"] = $source;
                $likes = $this->em->getRepository(CommentLike::class)->findBy(
                    $whereL
                );
                $likes = $this->paginator->paginate(
                    $likes,
                    1,
                    1
                );
                $response -> likes = $likes->getTotalItemCount();

                $responses[] = $response;
            }
        }

        else{
            foreach($result as $source){
                $response = new Response;
                $response -> id = $source -> getId();
                $response -> content = $source -> getContent();
                $response -> poster = $source -> getPoster();
                $response -> post = $source -> getPost();

                $whereL["comment"] = $source;
                $likes = $this->em->getRepository(CommentLike::class)->findBy(
                    $whereL
                );
                $likes = $this->paginator->paginate(
                    $likes,
                    1,
                    1
                );
                $response -> likes = $likes->getTotalItemCount();

                $responses[] = $response;
            }
        }

        return [
            "comments" => $responses,
            "total" => $result->getTotalItemCount()
        ];
    }
    /*
{
  allComments(limit: 3, page: 1) {
    total
    comments {
      id
      content
      post {
        title
      }
      poster {
        userName
      }
      likes
    }
  }
}

*/

    public static function getAliases(): array
    {
        return array(
            "resolve" => "Comment",
            "list" => "allComments"
        );
    }

}