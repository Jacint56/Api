<?php

namespace App\GraphQL\Mutation;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\GraphQL\Resolver\CategoryResolver;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class CommentMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(Argument $args)
    {
        $comment = new Comment();

        $comment->setPoster($this->em->getRepository(User::class)->find($args["comment"]["poster"]));
        $comment->setPost($this->em->getRepository(Post::class)->find($args["comment"]["post"]));
        $comment->setContent($args["comment"]["content"]);

        $comment->setAvailable(true);

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }
    /*
    mutation {
  createComment(comment: {poster: 3, post: 3, content: "A jobb kéz nem tuggya, a bal mit csinál."}) {
    id
    content
    poster {
      id
      userName
    }
  }
}
*/
    public function update(Argument $args)
    {
        $post = $this->em->getRepository(Comment::class)->find($args["id"]);

        if(!empty($args["comment"]["content"]))
        {
            $post->setContent($args["comment"]["content"]);
        }

        $this->em->flush();

        return $post;

    }
    /*
    mutation {
  updateComment(comment: {content: "Gondolkodok, tehát vagyok."}, id: 5) {
    id
    content
    poster {
      id
      userName
    }
  }
}
*/

    public function delete(Argument $args)
    {
        $comment = $this->em->getRepository(Comment::class)->find($args["id"]);

        $comment->setAvailable(false);

        $this->em->flush();

        return 1;

    }
    /*
    mutation {
  deleteComment(id: 5) {
    id
    content
    poster {
      id
      userName
    }
  }
}
*/

    public static function getAliases(): array
    {
        return array(
            "create" => "createComment",
            "update" => "updateComment",
            "delete" => "deleteComment"
            );
    }
}