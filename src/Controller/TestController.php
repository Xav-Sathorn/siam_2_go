<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestController extends AbstractController
{
    #[Route('/', methods: ['GET', 'POST'])]
    public function index()
    {
        dd("Ca fonctionne");
    }


    #[Route('/test/{age<\d+>?0}', methods: ['GET', 'POST'])]
    public function test(Request $request, $age)
    {

        return new Response("Vous avez $age ans !");
    }
}
