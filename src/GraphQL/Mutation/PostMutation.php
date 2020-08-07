<?php

namespace App\GraphQL\Mutation;

use App\Entity\User;
use App\Entity\Post;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

class PostMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(Argument $args)
    {
        $post = new Post();
        //$post->setPoster
        $post->setTitle($args["post"]["title"]);
        $post->setPoster($this->em->getRepository(User::class)->find($args["post"]["poster"]));
        $post->setContent($args["post"]["content"]);

        $post->setAvailable(true);

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }
    /*
    mutation {
  createPost(post: {poster: 3, content: "Fasz", title: "Rád gondótam"}) {
    id
    title
    slug
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
        $post = $this->em->getRepository(Post::class)->find($args["id"]);
        
        if(!empty($post) && $post->getAvailable())
        {
          if ($this->em->getRepository(User::class)->find($args["editor"])==$post->getPoster()) {
              if (!empty($args["post"]["content"])) {
                  $post->setContent($args["post"]["content"]);
              }

              if (!empty($args["post"]["title"])) {
                  $post->setTitle($args["post"]["title"]);
              }

              $this->em->flush();

              return $post;
          }
          else
          {
            throw new \GraphQL\Error\UserError('This post is not yours!');
          }
        }
        //return null;
        throw new \GraphQL\Error\UserError('This post does not exist');
    }
    /*
    mutation {
  updatePost(id: 2 post: {content: "Péló"}) {
    id
    title
    slug
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
        $post = $this->em->getRepository(Post::class)->find($args["id"]);
        if(!empty($post) && $post->getAvailable())
        {
            $post->setAvailable(false);
            $this->em->flush();
            return true;
        }
        throw new \GraphQL\Error\UserError('');
    }
    /*
    mutation {
  deletePost(id: 2)
}
*/

    public static function getAliases(): array
    {
        return array(
            "create" => "createPost",
            "update" => "updatePost",
            "delete" => "deletePost"
            );
    }
}

