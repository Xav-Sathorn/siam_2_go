<?php

namespace App\Controller;


use App\Entity\Product;
use App\Entity\Category;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class ProductController extends AbstractController
{

    #[Route("/{slug}", name: "product_category", priority: -1)]


    public function category($slug, CategoryRepository $categoryRepository)
    {
        $category = $categoryRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$category) {
            throw $this->createNotFoundException("La catégorie demandée n'existe pas !");
        }


        return $this->render('product/category.html.twig', [
            'slug' => $slug,
            'category' => $category, 
            'image_product_directory' =>  $this->getParameter('image_product_web')
        ]);
    }

    #[Route("/{category_slug}/{slug}", name: "product_show", priority: -1)]


    public function show($slug, ProductRepository $productRepository)
    {

        $product = $productRepository->findOneBy([
            'slug' => $slug
        ]);

        if (!$product) {
            throw $this->createNotFoundException("Le produit demandée n'existe pas !");
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
            'image_product_directory' =>  $this->getParameter('image_product_web')
        ]);
    }

    #[Route("/admin/product/{id}/edit", name: "product_edit")]

    public function edit(
        $id,
        ProductRepository $productRepository,
        Request $request,
        EntityManagerInterface $em,
        ValidatorInterface $validator,
        SluggerInterface $slugger
    ) {

        $product = $productRepository->find($id);

        $form = $this->createForm(ProductType::class, $product);

        /* $form->setData($product); */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $mainPictureFile */
            $mainPictureFile = $form->get('mainPicture')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($mainPictureFile) {
                $originalFilename = pathinfo($mainPictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$mainPictureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $mainPictureFile->move(
                        $this->getParameter('image_product'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setMainPicture($newFilename);
            }

            $em->flush();

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
        }

        $formView = $form->createView();

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'formView' => $formView,
        ]);
    }

    #[Route("/admin/product/delete/{id}", name: "product_delete", requirements: ['id' => '\d+'])]
    
    public function deleteProduct($id, ProductRepository $productRepository, EntityManagerInterface $em, Request $request)
    {
        $product = $productRepository->find($id);

        //dd($product);

        $em->remove($product);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }


    #[Route("/admin/product/create", name: "product_create")]

    public function create(
        FormFactoryInterface $factory,
        Request $request,
        SluggerInterface $slugger,
        EntityManagerInterface $em
    ) {
        $product = new Product;

        $form = $this->createForm(ProductType::class, $product);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $mainPictureFile */
            $mainPictureFile = $form->get('mainPicture')->getData();

            // this condition is needed because the 'brochure' field is not required
            // so the PDF file must be processed only when a file is uploaded
            if ($mainPictureFile) {
                $originalFilename = pathinfo($mainPictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$mainPictureFile->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $mainPictureFile->move(
                        $this->getParameter('image_product'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents
                $product->setMainPicture($newFilename);
            }

            $product->setSlug(strtolower($slugger->slug($product->getName())));


            $em->persist($product);
            $em->flush();

            return $this->redirectToRoute('product_show', [
                'category_slug' => $product->getCategory()->getSlug(),
                'slug' => $product->getSlug()
            ]);
        }

        $formView = $form->createView();

        return $this->render('product/create.html.twig', [
            'formView' => $formView
        ]);
    }

}
