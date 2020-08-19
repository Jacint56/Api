<?php

namespace App\GraphQL\Mutation;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\CommentLike;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;


class CommentLikeMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(Argument $args)
    {
      $data = $this->em->getRepository(CommentLike::class)->findBy(
        array(
            "liker" => $args["commentLike"]["liker"],
            "comment" => $args["commentLike"]["comment"]
        )
    );
        if(!empty($data))
        {
          if (!($data[0]->getAvailable())) {
              $data[0]->setAvailable(true);
          }
          else
          {
            $data[0]->setAvailable(false);
          }
          $this->em->flush();
          if ($data[0]->getAvailable()) {
              return $data[0];
          }
          else
          {
            return true;
          }
        }
        else
        {
          $like = new CommentLike();

          $like->setLiker($this->em->getRepository(User::class)->find($args["commentLike"]["liker"]));
          $like->setComment($this->em->getRepository(Comment::class)->find($args["commentLike"]["comment"]));
  
          $like->setAvailable(true);
  
          $this->em->persist($like);
          $this->em->flush();
  
          return $like;
        }
        
    }
    /*
    mutation {
      createCommentLike(commentLike: {liker: 1, comment: 2}) {
        id
        liker {
          userName
        }
        comment {
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
    public static function getAliases(): array
    {
        return array(
            "create" => "createCommentLike"
        );
    }
}