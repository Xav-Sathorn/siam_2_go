<?php

namespace App\Cart;

use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\RequestStack;



class CartService
{
    protected $requestStack;
    protected $productRepository;

    public function __construct(RequestStack $requestStack, ProductRepository $productRepository)
    {
        $this->requestStack = $requestStack;
        $this->productRepository = $productRepository;
    }
    public function add(int $id)
    {
        $cart = $this->requestStack->getSession()->get('cart', []);

        if (array_key_exists($id, $cart)) {
            $cart[$id]++;
        } else {
            $cart[$id] = 1;
        }

        $this->requestStack->getSession()->set('cart', $cart);
    }

    public function getTotal(): int
    {
        $total = 0;
        foreach ($this->requestStack->getSession()->get('cart', []) as $id => $qty) {

            $product = $this->productRepository->find($id);

            if (!$product) {
                continue;
            }

            $total += $product->getPrice() * $qty;
        }

        return $total;
    }

    public function getDetailledCartItems(): array
    {

        $detailedCart = [];


        foreach ($this->requestStack->getSession()->get('cart', []) as $id => $qty) {

            $product = $this->productRepository->find($id);

            if (!$product) {
                continue;
            }

            $detailedCart[] = new CartItem($product, $qty);
        }
        return $detailedCart;
    }
}
