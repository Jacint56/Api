<?php

namespace App\GraphQL\Mutation;

use App\Entity\Friendship;
use App\Entity\User;
use App\GraphQL\Resolver\FriendshipResolver;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

class FriendshipMutation implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function create(Argument $args)
    {
        $friendship = new Friendship();
        $friendship->setUser1($this->em->getRepository(User::class)->find($args["friendship"]["user1"]));
        $friendship->setUser2($this->em->getRepository(User::class)->find($args["friendship"]["user2"]));
        $friendship->setStatus($args["friendship"]["status"]);

        $friendship->setAvailable(true);

        $this->em->persist($friendship);
        $this->em->flush();

        $friendship = new Friendship();
        $friendship->setUser2($this->em->getRepository(User::class)->find($args["friendship"]["user1"]));
        $friendship->setUser1($this->em->getRepository(User::class)->find($args["friendship"]["user2"]));
        $friendship->setStatus($args["friendship"]["status"]);

        $friendship->setAvailable(true);

        $this->em->persist($friendship);
        $this->em->flush();

        return $friendship;
    }
    /*
    mutation {
  createFriendship(friendship: {user1: 2, user2: 5, status: true}) {
    id
    user1 {
      id
    }
    user2 {
      id
    }
    status
  }
}

*/

   
    public function delete(Argument $args)
    {
        $friendship = $this->em->getRepository(Friendship::class)->find($args["id"]);

        $friendship->setAvailable(false);

        $this->em->flush();

        return 1;

    }
    /*
    mutation {
  deleteFriendship(id: 1) {
    id
    user {
      id
    }
    status
  }
}

*/

    public static function getAliases(): array
    {
        return array(
            "create" => "createFriendship",
            "delete" => "deleteFriendship"
            );
    }
}