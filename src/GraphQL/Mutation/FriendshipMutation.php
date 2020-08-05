<?php

namespace App\GraphQL\Mutation;

use App\Entity\Friendship;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\CssSelector\Exception\InternalErrorException;

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
        $friendship->setSender($this->em->getRepository(User::class)->find($args["friendship"]["sender"]));
        $friendship->setReciver($this->em->getRepository(User::class)->find($args["friendship"]["reciver"]));
        $friendship->setStatus(false);

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
    public function accept(Argument $args)
    {
        $friendship = $this->em->getRepository(Friendship::class)->find($args["id"]);
        
        if($friendship->getAvailable())
        {
            $friendship->setStatus(true);
            $this->em->flush();
            return $friendship;
        }
    }

   
    public function delete(Argument $args)
    {
        $friendship = $this->em->getRepository(Friendship::class)->find($args["id"]);
        if(!empty($friendship) && $friendship->getAvailable())
        {
            $friendship->setAvailable(false);
            $this->em->flush();
            return true;
        }
        throw new \GraphQL\Error\UserError('Shit! Something is wrong');
    }
    /*
    mutation {
  deleteFriendship(id: 1)
}

*/

    public static function getAliases(): array
    {
        return array(
            "create" => "createFriendship",
            "accept" => "acceptFriendship",
            "delete" => "deleteFriendship"
            );
    }
}