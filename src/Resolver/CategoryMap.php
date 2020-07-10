<?php

namespace App\Resolver;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

use App\Entity\Appointments;
use Knp\Component\Pager\PaginatorInterface;


class CategoryMap extends AbstractController
{
    function index()
    {

        $entityManager = $this->getDoctrine()->getManager();
        $response = array();
      
        foreach($entityManager->getRepository(Category::class)->findAll() as $category)
        {
            $response[$category->getId()] = [
                "id"=>$category->getId(),
                "name"=>$category->getName(),
                "slug"=>$category->getSlug()
            ];
        }

        return $response;

    }


}