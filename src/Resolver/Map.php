<?php
// src/Resolver/ResolverMap.php
namespace App\Resolver;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Resolver\ResolverMap;
use Symfony\Component\HttpFoundation\JsonResponse;

class Map extends ResolverMap
{
    
    public function map()
    {
        return [
            'Query' => [
                'categories' => function ($value, Argument $args)
                {
                    //dump($value);
                    return array();
                }
            ]
        ];
    }
}