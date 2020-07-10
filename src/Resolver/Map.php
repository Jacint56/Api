<?php

// src/Resolver/ResolverMap.php
namespace App\Resolver;

use GraphQL\Error\Error;
use GraphQL\Language\AST\StringValueNode;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Utils;
use Overblog\GraphQLBundle\Definition\Argument;
use Overblog\GraphQLBundle\Resolver\ResolverMap;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Appointments;
use Knp\Component\Pager\PaginatorInterface;

use App\Controller\CategoryController;

class Map extends ResolverMap
{
    function map()
    {
        return [
            'Query' => [
                self::RESOLVE_FIELD => function ($value, Argument $args, \ArrayObject $context, ResolveInfo $info) {
                    if ('Category' === $info->fieldName) {
                        
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