<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class CategoryController extends AbstractController
{
    /**
     * @Route("/category", name="category")
     */
    public function createController() : Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $category = new Category();
        $category->setName("RTS");
        $category->setSlug("aszom");

        $entityManager->persist($category);
        $entityManager->flush();
        return new Response('Done: '.$category->getId());
    }
}
