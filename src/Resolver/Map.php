<?php

// src/Resolver/ResolverMap.php
namespace App\Resolver;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

class Map extends ResolverMap
{
    public function map()
    {
        return [
            'Query' => [
                self::RESOLVE_FIELD => function ($value, Argument $args, \ArrayObject $context, ResolveInfo $info) {
                    if ('category' === $info->fieldName) {
                        $category = CategoryMap::index();
                        $id = (int) $args['id'];
                        if (isset($category[$id])) {
                            return $category[$id];
                        }
                    }
                    return null;
                }
            ]
        ];
    }
}