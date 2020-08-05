<?php

namespace App\GraphQL\Mutation;

use App\Entity\User;
use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\User\UserInterface;
use App\GraphQL\Resolver\UserResolver;
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
        /*
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,"http://localhost/api/login_check");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt(
            $ch, CURLOPT_POSTFIELDS,
            /*
            http_build_query(array(
                'username' => $args["userName"],
                'password' => $args["password"]
            *//*
            json_encode(array(
                'username' => $args["userName"],
                'password' => $args["password"]
            ))
        );
        $output = curl_exec($ch);
        curl_close ($ch);
        return $output;*/
/*
        $client = HttpClient::create([
            'headers' => [
                'Content-Type' => 'application/json',
            ]
        ]);
        $response = $client->request(
            'POST',
            'http://localhost/api/login_check',
            [
                'json' => [
                    'username' => $args['userName'],
                    'password' => $args["password"]
                ]
            ]
        );
        $content = $response->getContent();
        return $content;*/
/*
        $curl = curl_init();
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => "http://localhost/api/login_check",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>"{\r\n    \"username\": \"János\",\r\n    \"password\": \"asd\"\r\n}",
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            )
        );
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
        */
//        $JWTManager = $this->container->get('lexik_jwt_authentication.jwt_manager');
//        $where = array();
  //      $where["userName"] = $args["userName"];
        $users = Array();
        $users = $this->em->getRepository(User::class)->findBy(
            Array("userName" => $args["userName"])
        );
        $user = $users[0];
//        $user = $this->em->getRepository(User::class)->find($args["userName"]);
        if($user->getAvailable())
        {
            /*
            $password = $this->passwordEncoder->encodePassword(
                $user,
                $args["password"]
            );
            */
//            if($user->getPassword() == $password)
            if($this->passwordEncoder->isPasswordValid($user, $args["password"]))
            {
                return $this->jwtManager->create($user);
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