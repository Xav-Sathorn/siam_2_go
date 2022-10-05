<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HelloController extends AbstractController
{
    #[Route('/hello/{name?World}', name: 'hello-page', methods: ['GET', 'POST'])]
    public function hello(Request $request, $name)
    {
        return new Response("Hello $name");
    }
}
