<?php

namespace App\Controller\Purchase;

use Stripe\Stripe;
use App\Entity\Purchase;

use Stripe\PaymentIntent;
use Symfony\Component\Mime\Address;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
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

    
    #[Route('/purchase/validation/{id}', name: 'payment_success', methods: ['GET'])]
    public function success_payment(MailerInterface $mailer, Purchase $purchase, EntityManagerInterface $em )
    {
        $purchase->setStatus('PAID');
        $email = (new TemplatedEmail())
        ->from(new Address('x.coenen.dev@gmail.com', 'Xav Mail Bot'))
        ->to('x.coenen.dev@gmail.com')
        ->subject('Please Confirm your Email')
        ->htmlTemplate('email/confirmation.html.twig')
        ->context([
            'name' => $purchase->getFullName(),
            'products' => $purchase->getPurchaseItems()
        ]);

        //Send Comfirm Email 
        $this->addFlash('success', "La commande a été payée est confirmée !");
        return $this->redirectToRoute("purchase_index");

        
        $em->flush();
        $mailer->send($email);

       return $this->render('purchase/payment.html.twig', []);
    }
};
