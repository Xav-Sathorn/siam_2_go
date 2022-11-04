<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CategoryController extends AbstractController
{
    protected $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function renderMenuList()
    {
        //1. Aller chercher les catégories dans la base de données (repository)
        $categories = $this->categoryRepository->findAll();

        //2. Renvoyer le rendu HTML sous la forme d'une Response(this->render)
        return $this->render('category/_menu.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/admin/category/create', name: 'category_create')]
    public function create(Request $request, EntityManagerInterface $em, SluggerInterface $slugger)
    {
        $category = new Category;

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug(strtolower($slugger->slug($category->getSlug())));

            $em->persist($category);
            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        $formView = $form->createView();

        return $this->render('category/create.html.twig', [
            'category' => $category,
            'formView' => $formView
        ]);
    }

    #[Route('/admin/category/edit/{id}', name: 'category_edit')]
    // #[IsGranted("CAN_EDIT", subject: "id", message: "Vous n'êtes pas le propriétaire de cette catégorie")]
    public function edit(
        $id,
        CategoryRepository $categoryRepository,
        Request $request,
        EntityManagerInterface $em,
        Security $security,
        SluggerInterface $slugger
    ) {
        $category = $categoryRepository->find($id);

        if (!$category) {
            throw new NotFoundHttpException("Cette catégorie n'existe pas!");
        }

        $form = $this->createForm(CategoryType::class, $category);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setSlug(strtolower($slugger->slug($category->getSlug())));

            $em->flush();

            return $this->redirectToRoute('homepage');
        }

        $formView = $form->createView();

        return $this->render('category/edit.html.twig', [
            'category' => $category,
            'formView' => $formView
        ]);
    }

    #[Route("/admin/category/delete/{id}", name: "category_delete", requirements: ['id' => '\d+'])]
    
    public function deleteCategory($id, CategoryRepository $categoryRepository, EntityManagerInterface $em, Request $request)
    {
        // catégorie par défaut Accueil
        $defaultCategory = $categoryRepository->find("1");

        // on recherche la catégorie actuelle
        $category = $categoryRepository->find($id);

        // on recupére la liste des produits de la catégorie
        $productsCat = $category->getProducts();

        
        foreach($productsCat as $product)
        {
            // on affecte les produits à la catégorie par defaut (Accueil - 1)
            $product->setCategory($defaultCategory);
            $em->persist($product);
        }
       

        $em->remove($category);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}
