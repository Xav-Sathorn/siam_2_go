<?php

namespace App\Controller\Purchase;

use Stripe\Stripe;

use Stripe\PaymentIntent;
use App\Repository\PurchaseRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PurchasePaymentController extends AbstractController
{
    #[Route('/purchase/pay/{id}', name: 'purchase_payment_form')]
    public function showCardForm($id)
    {
        return $this->render('purchase/payment.html.twig', [
            'id' => $id
        ]);
    }

    #[Route('/purchase/pay/stripe/{id}', name: 'purchase_payment_form_stripe', methods: ['GET'])]
    public function getStripeIntent($id, PurchaseRepository $purchaseRepository): JsonResponse
    {
        Stripe::setApiKey('sk_test_51LAVnEEIp55pXkWxL40C6pITRjShzn1d4ybOjS3gHTm0ksRgSZ8G9vDliFhTZO3uT58q1hgASk2hrtRkrNEVlOX200sXYTOLvR');

        $purchase = $purchaseRepository->find($id);

        if (!$purchase) {
            return $this->redirectToRoute('cart_show');
        }


        $paymentIntent = PaymentIntent::create([
            'amount' => $purchase->getTotal(),
            'currency' => 'eur',
            'payment_method_types' => ['card'],
            'confirmation_method' => 'automatic',
        ]);
        // $output = [
        //     //"clientSecret" => $paymentIntent->client_secret,
        //     $paymentIntent
        // ];

       //dd($paymentIntent);

       
        return new JsonResponse('{"clientSecret" :"'.$paymentIntent->client_secret.'"}');
       
    }
};
