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
        if ($args["friendship"]["sender"] != $args["friendship"]["reciver"]) {
          if (empty($this->em->getRepository(Friendship::class)->
              findby(
                array(
                  "sender"=>$args["friendship"]["sender"],
                  "reciver"=>$args["friendship"]["reciver"],
                  "available"=>true)))&&
              empty($this->em->getRepository(Friendship::class)->
              findby(
                array(
                  "reciver"=>$args["friendship"]["sender"],
                  "sender"=>$args["friendship"]["reciver"],
                  "available"=>true)))
            ) {
                    $friendship->setSender($this->em->getRepository(User::class)->find($args["friendship"]["sender"]));
                    $friendship->setReciver($this->em->getRepository(User::class)->find($args["friendship"]["reciver"]));
                    $friendship->setStatus(false);

                    $friendship->setAvailable(true);

                    $this->em->persist($friendship);
                    $this->em->flush();

                    return $friendship;
              }
              else
              {
                throw new \GraphQL\Error\Error("You are friends or this user sent a request to you!");
              }
        }
        else
        {
            throw new \GraphQL\Error\Error('You cannot send a friend request to yourself!');
        }
    }
    /*
    mutation {
  createFriendship(friendship: {sender: 4, reciver: 2}) {
    id
    sender {
      id
    }
    reciver {
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
    /*
    mutation{
      acceptFriendship(id:13)
      {
        sender {
          id
        }
        id
        reciver {
          id
        }
      }
    }
   */

    public function delete(Argument $args)
    {
        $friendship = $this->em->getRepository(Friendship::class)->find($args["id"]);
        if(!empty($friendship) && $friendship->getAvailable())
        {
            $friendship->setAvailable(false);
            $this->em->flush();
            return true;
        }
        throw new \GraphQL\Error\Error('Something is wrong');
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