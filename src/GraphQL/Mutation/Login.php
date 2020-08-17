<?php

namespace App\GraphQL\Mutation;

use App\Entity\User;
use App\Entity\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use App\GraphQL\Resolver\UserResolver;
//use Doctrine\Common\EventSubscriber;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class Login implements MutationInterface, AliasedInterface
{
    private $em;
    private $jwtManager;
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder, EntityManager $em, JWTTokenManagerInterface $jwtManager)
    {
        $this->em = $em;
        $this->jwtManager = $jwtManager;
        $this->passwordEncoder = $passwordEncoder;
    }

    public function login(Argument $args)
    {        
        $users = Array();
        $users = $this->em->getRepository(User::class)->findBy(
            Array("userName" => $args["userName"])
        );
        if (empty($users))
        {
            throw new \GraphQL\Error\Error('Wrong username or password!');
        }
        else
        {
            $user = $users[0];
            if (
                $user->getAvailable()
                &&
                $this->passwordEncoder->isPasswordValid($user, $args["password"])
            )
            {
                    //$asd = $this->em->getRepository(EventSubscriber::class)->onAuthenticationSuccess($this->jwtManager->create($user));
                    return $this->jwtManager->create($user);
            }
            else{
                throw new \GraphQL\Error\Error('Wrong username or password!');
            }
        }
    }

    public static function getAliases(): array
    {
        return array(
            "login" => "login"
        );
    }
}