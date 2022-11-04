<?php 

namespace App\Controller\Purchase;

use App\Cart\CartService;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


class PurchasePaymentSuccessController extends AbstractController{
    
    #[Route('/purchase/terminate/{id}', name: 'purchase_payment_success')]
    #IsGranted('ROLE_USER', message: "Vous devez être connecté pour accéder à vos commandes !")]

    public function success($id, PurchaseRepository $purchaseRepository, 
                            EntityManagerInterface $em,
                            CartService $cartService){
        //1. Je récupère la commande
        $purchase = $purchaseRepository->find($id);

        if(
            !$purchase || 
            ($purchase && $purchase->getUser() !== $this->getUser()) || 
            ($purchase && $purchase->getStatus() === Purchase::STATUS_PAID)){
            $this->addFlash('waring', "La commande n'existe pas");
            return $this->redirectToRoute("purchase_index");
        }

        //2. Je la fait passer au status Payee (paid)
        $purchase->setStatus(Purchase::STATUS_PAID);
        $em->flush();

        //3. Je vide le panier
        //$cartService->empty();

        //4.Je redirige avec un flash vers la liste des commandes

        $this->addFlash('success', "La commande a été payée est confirmée !");
        return $this->redirectToRoute("purchase_index");

    }
}
