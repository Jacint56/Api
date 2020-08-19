<?php

namespace App\GraphQL\Resolver;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\PostLike;
use Doctrine\ORM\EntityManager;

use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\ResolverInterface;

use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Validator\Constraints\Length;


class PostResponse{
    public $id;
    public $poster;
    public $content;
    public $likes;
    public $comments;
}


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

        

        $response = new PostResponse;
        $response -> id = $post -> getId();
        $response -> content = $post -> getContent();
        $response -> title = $post -> getTitle();
        $response -> poster = $post -> getPoster();

        $where = array();
        $where["post"] = $post;
        $comments = $this->em->getRepository(Comment::class)->findBy(
            $where
        );
        $result = $this->paginator->paginate(
            $comments,
            1,
            1
        );
        $response -> comments = $result->getTotalItemCount();
        
        $where = array();
        $where["post"] = $post;
        $likes = $this->em->getRepository(PostLike::class)->findBy(
            $where
        );
        $result = $this->paginator->paginate(
            $likes,
            1,
            1
        );
        $response -> likes = $result->getTotalItemCount();

        if($post->getAvailable())
        {
            return $response;
        }
    }
    /*
    {
        post(id: 5) {
          id
          title
          content
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
        $posts = $this->em->getRepository(Post::class)->findBy(
            $where,
            array($column => $order)
        );
        $limit = $args["limit"];
        if($args["limit"] == 0)
        {
            $limit = 1;
        }
        $result = $this->paginator->paginate(
            $posts,
            $args["page"],
            $limit
        );
        if($args["limit"] == 0)
        {
            foreach($posts as $source){
                $response = new PostResponse;
                $response -> id = $source -> getId();
                $response -> content = $source -> getContent();
                $response -> title = $source -> getTitle();
                $response -> poster = $source -> getPoster();

                $whereL["post"] = $source;
                $likes = $this->em->getRepository(PostLike::class)->findBy(
                    $whereL
                );
                $likes = $this->paginator->paginate(
                    $likes,
                    1,
                    1
                );
                $response -> likes = $likes->getTotalItemCount();

                $comments = $this->em->getRepository(PostLike::class)->findBy(
                    $whereL
                );
                 $comments= $this->paginator->paginate(
                    $comments,
                    1,
                    1
                );
                $response -> comments = $comments->getTotalItemCount();
                
                $responses[] = $response;
            }
        }

        else{
            foreach($result as $source){
                $response = new PostResponse;
                $response -> id = $source -> getId();
                $response -> content = $source -> getContent();
                $response -> poster = $source -> getPoster();
                $response -> post = $source -> getPost();

                $whereL["post"] = $source;
                $likes = $this->em->getRepository(PostLike::class)->findBy(
                    $whereL
                );
                $likes = $this->paginator->paginate(
                    $likes,
                    1,
                    1
                );
                $response -> likes = $likes->getTotalItemCount();

                $comments = $this->em->getRepository(PostLike::class)->findBy(
                    $whereL
                );
                 $comments= $this->paginator->paginate(
                    $comments,
                    1,
                    1
                );
                $response -> comments = $comments->getTotalItemCount();

                $responses[] = $response;
            }
        }
        return [
            "posts" => $responses,
            "total" => $result->getTotalItemCount()
        ];
    }
    /*
    {
  allPosts(limit: 0, page: 1) {
    posts {
      id
      title
      slug
      content
      poster {
        id
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
            "resolve" => "Post",
            "list" => "allPosts"
        );
    }

}