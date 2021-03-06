<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Produit;
use App\Entity\Categorie;
use App\Repository\UserRepository;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class SwapController extends AbstractController

{

    /**
     * @Route("/{categorie}", name="home", methods={"GET"})
     */

    public function home(ProduitRepository $produitRepository, CategorieRepository $categorieRepository, $categorie=null): Response
    {
        $hasCategorie = [];
        if (isset($categorie)) {
            $hasCategorie["categorie"] = $categorie;
        }
        return $this->render('swap/home.html.twig', [
            'title' => "App SwapGift",
            'categorieActive' => $categorie,
            'categories' => $categorieRepository->findAll(),
            'produits' => $produitRepository->findBy($hasCategorie, ['CreeLe'=> 'DESC'], 12),
        ]);
    }

    /**
     * @Route("/swap/fiche_produit/{id}", name="fiche_produit")
     */
        public function ficheProduit(Produit $produits) {
            dump($produits);
            return $this->render('swap/fiche_produit.html.twig', [
                'title'    => "App SwapGift",
                'produit'  => $produits,
            ]);
        }

    /**
     * @Route("/swap/creer", name="creer")
     * @Route("/swap/produit/{id}/edit", name="editer")
     */
    public function creer(Request $request, Produit $produit = null) {
        $em = $this->getDoctrine()->getManager();

        if(!$produit) {
            $produit = new Produit();
        }
        $repo = $this->getDoctrine()->getRepository(Categorie::class);
        $categories = $repo->findAll();
       
        
        $form = $this->createFormBuilder($produit)
                     ->add('titre')
                     ->add('description')
                     ->add('categorie', ChoiceType::class, [
                        new Categorie('Femme'),
                        new Categorie('Homme'),
                        new Categorie('Enfant'),
                        new Categorie('Maison'),
                        new Categorie('Media/Loisir'),
                        new Categorie('Autre'),
                    ])
                     ->add('valeur')
                     ->add('image', FileType::class)
                     ->add('ajouter', SubmitType::class)
                     ->getForm();
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$produit->getId()) {
                $produit->setCreeLe(new \DateTime());
            }
            $em->persist($produit);
            $em->flush();
            return $this->redirectToRoute('swap');
        }

        return $this->render('produit/creer.html.twig', [
            'monFormulaire' => $form->createView(),
            'edition' => ($produit->getId() !== null) ? true : false
        ]);
    }
}
