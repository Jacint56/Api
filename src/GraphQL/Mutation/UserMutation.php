<?php

namespace App\GraphQL\Mutation;

use App\Entity\Game;
use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;

use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserMutation implements MutationInterface, AliasedInterface
{
    private $em;
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManager $em)
    {
        $this->em = $em;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function create(Argument $args)
    {
        if (!empty($this->em->getRepository(User::class)->findBy(Array("userName"=>$args["user"]["userName"])))) {
            throw new \GraphQL\Error\UserError('This username is exist!');
            exit();
        }
        if (!empty($this->em->getRepository(User::class)->findBy(Array("email"=>$args["user"]["email"])))) {
            throw new \GraphQL\Error\UserError('This email is exist!');
            exit();
        }
        $user = new User();
        $user->setUsername($args["user"]["userName"]);
        $user->setEmail($args["user"]["email"]);
        $user->setPassword($this->passwordEncoder->encodePassword(
            $user,
            $args["user"]["password"]
        ));

        $user->setRoles(['User']);
        $user->setAvailable(true);


        $this->em->persist($user);
        $this->em->flush();

        return $user;

    }
    /*
    mutation {
      createUser(user:{userName: "noobmaster69" password: "bbb" email: "noobmaster@gmail.com"}){
        id
        userName
        slug
        password
        email
      }
    }
    */

    public function update(Argument $args)
    {
        if (!empty($args["user"]["userName"]) && !empty($this->em->getRepository(User::class)->findBy(Array("userName"=>$args["user"]["userName"])))) {
            throw new \GraphQL\Error\UserError('This username is exist!');
            exit();
        }
        if (!empty($args["user"]["email"]) && !empty($this->em->getRepository(User::class)->findBy(Array("email"=>$args["user"]["email"])))) {
            throw new \GraphQL\Error\UserError('This email is exist!');
            exit();
        }
        else
        {
            $user = $this->em->getRepository(User::class)->find($args["id"]);
            if (!empty($user) && $user->getAvailable()) {
                if (!empty($args["user"]["userName"])) {
                    $user->setUsername($args["user"]["userName"]);
                }

                if (!empty($args["user"]["password"])) {
                    $user->setPassword($this->passwordEncoder->encodePassword(
                        $user,
                        $args["user"]["password"]
                    ));
                }
                if (!empty($args["user"]["email"])) {
                    $user->setEmail($args["user"]["email"]);
                }
                $this->em->flush();

                return $user;
            }
            throw new \GraphQL\Error\Error('This user does not exist!');
        }
    }
    /*
    mutation {
        updateUser(id: 1, user: {password: "asomizmus"}) {
          id
          userName
          slug
          password
        }
      }
      */
    

    public function delete(Argument $args)
    {
        $user = $this->em->getRepository(User::class)->find($args["id"]);

        if(!empty($user) && $user->getAvailable())
        {
            $user->setAvailable(false);
            $this->em->flush();
            return true;
        }
        throw new \GraphQL\Error\Error('Error!');
    }
    /*
    mutation {
      deleteUser(id: 2)
    }
    */    

    public static function getAliases(): array
    {
        return array(
            "create" => "createUser",
            "update" => "updateUser",
            "delete" => "deleteUser"
            );
    }
}