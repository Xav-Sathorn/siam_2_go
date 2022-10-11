<?php

namespace App\Controller;

use App\Cart\CartService;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'cart_add', requirements: ['id' => '\d+'])] //sécurise la route
    public function add($id, ProductRepository $productRepository, CartService $cartService): Response
    {
        //0. Sécurisation: est-ce que le produit existe?
        $product = $productRepository->find($id);

        if (!$product) {
            throw $this->createNotFoundException("Le produit $id n'existe pas !");
        }

        $cartService->add($id);

        $this->addFlash('success', "Le produit a bien été ajouté au panier");

        //$session->remove('cart');
        //dd($session->get('cart'));

        return $this->redirectToRoute('product_show', [
            'category_slug' => $product->getCategory()->getSlug(),
            'slug' => $product->getSlug()
        ]);
    }

    #[Route('/cart', name: 'cart_show')]
    public function show(CartService $cartService)
    {
        $detailedCart = $cartService->getDetailledCartItems();

        $total = $cartService->getTotal();

        return $this->render('cart/index.html.twig', [
            'items' => $detailedCart,
            'total' => $total
        ]);
    }
}
