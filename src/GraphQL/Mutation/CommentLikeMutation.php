<?php

namespace App\GraphQL\Mutation;

use App\Entity\User;
use App\Entity\Comment;
use App\Entity\CommentLike;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class CommentLikeMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(Argument $args)
    {
        if(!empty($this->em->getRepository(CommentLike::class)->findBy(
            array(
                "liker" => $args["commentLike"]["liker"],
                "comment" => $args["commenttLike"]["comment"]
            )
        )))
        {
            throw new \GraphQL\Error\UserError('This like already exists!');
            exit();
        }

        $like = new CommentLike();

        $like->setLiker($this->em->getRepository(User::class)->find($args["commentLike"]["liker"]));
        $like->setComment($this->em->getRepository(Comment::class)->find($args["commentLike"]["comment"]));

        $like->setAvailable(true);

        $this->em->persist($like);
        $this->em->flush();

        return $like;
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

    public function delete(Argument $args)
    {
        $like = $this->em->getRepository(CommentLike::class)->find($args["id"]);
        if(!empty($like) && $like->getAvailable())
        {
            if ($this->em->getRepository(User::class)->find($args["editor"])==$like->getLiker())
            {
                $like->setAvailable(false);
                $this->em->flush();
                return true;
            }
            else
            {
                throw new \GraphQL\Error\UserError('This like is not yours!');
            }
        }
        throw new \GraphQL\Error\UserError('Shit! Something is wrong');
    }
/*
mutation {
  deleteCommentLike(id: 3, editor: 2)
}
*/
    public static function getAliases(): array
    {
        return array(
            "create" => "createCommentLike",
            "delete" => "deleteCommentLike"
        );
    }
}