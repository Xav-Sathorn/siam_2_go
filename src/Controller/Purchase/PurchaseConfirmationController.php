<?php

namespace App\Controller\Purchase;

use App\Cart\CartService;
use App\Entity\Purchase;
use App\Entity\PurchaseItem;
use App\Form\CartConfirmationType;
use App\Purchase\PurchasePersister;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

class PurchaseConfirmationController extends AbstractController
{
    protected $formFactory;
    protected $router;
    protected $security;
    protected $cartService;
    protected $requestStack;
    protected $em;
    protected $persister;

    public function __construct(
        FormFactoryInterface $formFactory,
        RouterInterface $router,
        Security $security,
        CartService $cartService,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        PurchasePersister $persister
    ) {
        $this->formFactory = $formFactory;
        $this->router = $router;
        $this->security = $security;
        $this->cartService = $cartService;
        $this->em = $em;
        $this->persister = $persister;
    }

    #[Route('/purchase/confirm', name: 'purchase_confirm')]
    public function confirm(Request $request): Response
    {
        //1. Lire les données du formulaire
        //->FormFactoryInterface / Request
        $form = $this->formFactory->create(CartConfirmationType::class);


        $form->handleRequest($request);
        //2. SI le formulaire n'a pas été soumis :  dégager
        if (!$form->isSubmitted()) {
            $this->requestStack->getCurrentRequest()->getSession();
            $this->addFlash('warning', 'Vous devez remplir le formulaire de confirmation');
            //Message flash + redirection (FlashBagInterface)
            // $flashBag->add('warning', 'Vous devez remplir le formulaire de confirmation');
            return new RedirectResponse($this->router->generate('cart_show'));
        }
        // //3. Si je ne suis pas connecté : dégager (Security)
        // $user = $this->security->getUser();

        // if (!$user) {
        //     throw new AccessDeniedException("Vous devez être connecté pour confirmer une commande");
        // }

        //4. Si il n'y a pas de produits dans mon panier : dégager (CartService)
        $cartItems = $this->cartService->getDetailledCartItems();

        if (count($cartItems) === 0) {

            $this->addFlash('waring', 'Vous ne pouvez pas confirmer une commande avec un panier vide');
            return new RedirectResponse($this->router->generate('cart_show'));
        }

        //5. Nous allons créer une purchase
        /** @var Purchase */
        $purchase = $form->getData();

        $this->persister->strorePurchase($purchase);

        // //6. Nous allons la lier avec l'utilisation actuellement connecté (Security)
        // $purchase->setUser($user)
        //     ->setPurchasedAt(new DateTime())
        //     ->setTotal($this->cartService->getTotal());

        // $this->em->persist($purchase);

        // //7. Nous allons la lier avec les produits dans le panier (cartService)

        // foreach ($this->cartService->getDetailledCartItems() as $cartItem) {
        //     $purchaseItem = new PurchaseItem;
        //     $purchaseItem->setPurchase($purchase)
        //         ->setProduct($cartItem->product)
        //         ->setProductName($cartItem->product->getName())
        //         ->setProductPrice($cartItem->product->getPrice())
        //         ->setQuantity($cartItem->qty)
        //         ->setTotal($cartItem->getTotal());

        //     $this->em->persist($purchaseItem);
        // }

        //8. Nous allons enregistrer la commande (EntityManagerInterface)
        $this->em->flush();

        // $this->cartService->epmty();

        // $this->addFlash('success', 'La commande a bien été enregistrée !');

        return new RedirectResponse($this->router->generate('purchase_payment_form', [
            'id' => $purchase->getId()
        ]));
    }
}
