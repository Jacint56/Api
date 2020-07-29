<?php

namespace App\GraphQL\Mutation;

use Doctrine\ORM\EntityManager;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Definition\Resolver\AliasedInterface;
use Overblog\GraphQLBundle\Definition\Resolver\MutationInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\HttpClient\HttpClient;

class Login implements MutationInterface, AliasedInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
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
        return $content;

    }

    public static function getAliases(): array
    {
        return array(
            "login" => "login"
        );
    }
}