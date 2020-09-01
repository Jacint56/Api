<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\CommentLike;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query\AST\Functions\LengthFunction;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;

class Response{
    public $id;
    public $poster;
    public $content;
    public $likes;
    public $created;
    public $updated;
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

        $conn = $this->em->getConnection();
        $sql = '
            SELECT created_at FROM comment
            WHERE comment.id = :Id
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['Id' => $args['id']]);
        foreach($stmt->fetchAll() as $data){
            $response -> created = ($data['created_at']);
        }

        $conn = $this->em->getConnection();
        $sql = '
            SELECT updated_at FROM comment
            WHERE comment.id = :Id
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute(['Id' => $args['id']]);
        foreach($stmt->fetchAll() as $data){
            $response -> updated = ($data['updated_at']);
        }

        $where = array(
            "comment" => $comment,
            "available" => true
        );
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
    function sorter($value1, $value2)
    {
        if($value1-> likes == $value2-> likes)
        {
            return 0;
        }
        if($value1-> likes < $value2-> likes)
        {
            return -1;
        }
        else
        {
            return 1;
        }
    }

    public function list(Argument $args)
    {
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

                $conn = $this->em->getConnection();
                $sql = '
                    SELECT created_at FROM comment
                    WHERE comment.id = :Id 
                    ';
                $stmt = $conn->prepare($sql);
                $stmt->execute(['Id' => $source -> getId()]);
                foreach($stmt->fetchAll() as $data){
                    $response -> created = ($data['created_at']);
                }
        
                $conn = $this->em->getConnection();
                $sql = '
                    SELECT updated_at FROM comment
                    WHERE comment.id = :Id
                    ';
                $stmt = $conn->prepare($sql);
                $stmt->execute(['Id' => $source -> getId()]);
                foreach($stmt->fetchAll() as $data){
                    $response -> updated = ($data['updated_at']);
                }

                $whereL = array(
                    "comment" => $source,
                    "available" => true
                );
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
                //$response -> slug = $source -> getSlug();

                $conn = $this->em->getConnection();
                $sql = '
                    SELECT created_at FROM comment
                    WHERE comment.id = :Id
                    ';
                $stmt = $conn->prepare($sql);
                $stmt->execute(['Id' => $args['id']]);
                foreach($stmt->fetchAll() as $data){
                    $response -> created = ($data['created_at']);
                }
        
                $conn = $this->em->getConnection();
                $sql = '
                    SELECT updated_at FROM comment
                    WHERE comment.id = :Id
                    ';
                $stmt = $conn->prepare($sql);
                $stmt->execute(['Id' => $args['id']]);
                foreach($stmt->fetchAll() as $data){
                    $response -> updated = ($data['updated_at']);
                }
                
                $whereL = array(
                    "comment" => $source,
                    "available" => true
                );
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
        
        for($i=0; $i< count($responses)-1;$i++)
        {
            if($responses[$i] )
            if($responses[$i] -> likes < $responses[$i+1] -> likes)
            {
                $value = $responses[$i];
                $responses[$i] = $responses[$i+1];
                $responses[$i+1]= $value;
                $i=0;
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