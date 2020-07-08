<?php

// src/Resolver/MyResolverMap.php
namespace App\Resolver;

use Overblog\GraphQLBundle\Resolver\ResolverMap;

class MyResolverMap extends ResolverMap
{
   protected function map()
   {
        return [
            'Query' => [
                self::RESOLVE_FIELD => function($value, ArgumentInterface $args, \ArrayObject $context, ResolveInfo $info){
                    return (int)$args['id'];
                }
            ],
            'Name' => [
                self::RESOLVE_TYPE => function ($value) {
                    return isset($value['user']) ? $value['user'] : null;
                },
            ]
        ];
    }
}