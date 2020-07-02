<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class CategoryController extends AbstractController
{
    /**
     * @Route("/categories", methods={"POST"}, name="api_create_category")
     */
    function create(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $entityManager = $this->getDoctrine()->getManager();

        $category = new Category();
        $category->setName($content["name"]);

        $entityManager->persist($category);
        $entityManager->flush();

        return new JsonResponse([
            "id"=>$category->getId(),
            "name"=>$category->getName(),
            "slug"=>$category->getSlug()
        ]);
    }
    /**
     * @Route("/categories", methods={"PUT"}, name="api_create_category")
     */
    function update(Request $request)
    {
        $content = json_decode($request->getContent(), true);

        $entityManager = $this->getDoctrine()->getManager();
        $category = $entityManager->getRepository(Category::class)->find($content["id"]);

        $category->setName($content["name"]);

        $entityManager->flush();

        return new JsonResponse([
            "id"=>$category->getId(),
            "name"=>$category->getName(),
            "slug"=>$category->getSlug()
        ]);
    }
}
