<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Tag;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/product")
 */
class ProductController extends AbstractController
{
    /**
     * @Route("/", name="product_index")
     */
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig');
    }



    /**
     * @Route("/list", name="product_list", methods={"GET"})
     */
    public function list(ProductRepository $productRepository): Response
    {
        return $this->render('product/list.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }


    /**
     * @Route("/list", name="product_searchtag", methods={"POST"})
     */
    public function searchtag(ProductRepository $productRepository, Request $request): Response
    {
        $value = $request->get('search');
        
        if ($value){
            $products = $productRepository->findAll();
            $ids = array();
            foreach ($products as $product){
                foreach ($product->getTags() as $tags){
                    if ($tags->getName() == $value) $ids[] = current($product);
                }                
            }
            return $this->render('product/list.html.twig', [
                'products' => $productRepository->findBy(array('id' => $ids)),
            ]);    
        }else{
            return $this->render('product/list.html.twig', [
                'products' => $productRepository->findAll(),
            ]);
        }               
    }


    /**
     * @Route("/addtag", name="product_addtag", methods={"GET","POST"})
     */
    public function addtag(Product $product)
    {
        $newTag = new Tag();
        $newTag->setName('');
        $product->getTags()->add($newTag);
        return $this;
    }


    /**
     * @Route("/new", name="product_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $product = new Product();

        $tag = new Tag();
        $tag->setName('');
        $product->getTags()->add($tag);

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Uploading image logic.
            $imagefile = $form['imagefile']->getData();

            // This condition is needed because the 'imagefile' field is not required.
            if ($imagefile) {
                $originalFilename = pathinfo($imagefile->getClientOriginalName(), PATHINFO_FILENAME);
                // This is needed to safely include the file name as part of the URL.
                $safeFilename = iconv('utf-8', 'us-ascii//TRANSLIT', $originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imagefile->guessExtension();

                // Move the file to the directory where images are stored
                try {
                    $imagefile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    echo $e->getMessage();
                    die();
                }

                // Update the 'image' property to store the image file name instead of its contents
                $product->setImage($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_show", methods={"GET"})
     */
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="product_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Product $product): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('product_list');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="product_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($product);
            $entityManager->flush();
        }

        return $this->redirectToRoute('product_list');
    }
}
