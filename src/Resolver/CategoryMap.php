<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Appointments;
use Knp\Component\Pager\PaginatorInterface;


class CategoryMap
{
    function index()
    {
        $page = 1;
        $size = 10;

        $entityManager = $this->getDoctrine()->getManager();
        $response = array();
        $repository = $this->getDoctrine()->getRepository(Category::class);
      
        foreach($paginator->paginate($entityManager->getRepository(Category::class)->findAll(), $page, $size) as $category)
        {
            $response[] = [
                "id"=>$category->getId(),
                "name"=>$category->getName(),
                "slug"=>$category->getSlug()
            ];
        }

        return $response;

    }


}