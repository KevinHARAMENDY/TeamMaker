<?php

namespace App\Controller;

use App\Entity\Equipe;
use App\Entity\Personne;
use App\Repository\EquipeRepository;
use App\Repository\PersonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{


    /**
     * @Route("/", name="accueil")
     */
    public function accueil(EquipeRepository $er, PersonneRepository $pr): Response
    {
        $equipes = $er->findAll();
        $personnes = $pr->findAll();
        return $this->render('main/accueil.html.twig', [
            "equipes" => $equipes,
            "personnes" => $personnes
        ]);
    }



    /*********
    * EQUIPE *
    **********/

    /**
     * @Route("/addTeam", name="ajoutEquipe")
     */
    public function ajoutEquipe(Request $req, EntityManagerInterface $em): Response
    {
        $e = new Equipe();
        $e->setNom($req->get("nom"));
        $em->persist($e);
        $em->flush();

        return $this->redirectToRoute("accueil");
    }

    /**
     * @Route("/delTeam/{id}", name="delEquipe")
     */
    public function delEquipe(Equipe $e, EntityManagerInterface $em): Response
    {
        foreach ($e->getPersonnes() as $p) {
            $e->removePersonne($p);
        }
        
        $em->remove($e);
        $em->flush();

        return $this->redirectToRoute("accueil");
    }



    /*********
    * PERSO  *
    **********/

    /**
     * @Route("/addPerso", name="ajoutPersonne")
     */
    public function ajoutPersonne(Request $req, EntityManagerInterface $em, EquipeRepository $er): Response
    {
        $p = new Personne();
        $p->setNom(strtoupper($req->get("nom")));
        $p->setPrenom(ucfirst(strtolower($req->get("prenom"))));

        if ($req->get("team") != 0) {
            //$equipe = $er->findBy(["id" => $req->get("team")]);
            $equipe = $er->find( $req->get("team"));
            $p->addEquipe($equipe);
        }

        $em->persist($p);
        $em->flush();

        return $this->redirectToRoute("accueil");
    }

    /**
     * @Route("/delPersoEquipe/{id}", name="delPersoEquipe")
     */
    public function delPersoEquipe(Personne $p, EntityManagerInterface $em): Response
    {
        if (!empty($p->getEquipes())) {
            foreach ($p->getEquipes() as $e) {
                $p->removeEquipe($e);
            }
        }
        
        $em->persist($p);
        $em->flush();

        return $this->redirectToRoute("accueil");
    }

    /**
     * @Route("/delPerso/{id}", name="delPerso")
     */
    public function delPerso(Personne $p, EntityManagerInterface $em): Response
    {
        if (!empty($p->getEquipes())) {
            foreach ($p->getEquipes() as $e) {
                $p->removeEquipe($e);
            }
        }
        
        $em->remove($p);
        $em->flush();

        return $this->redirectToRoute("accueil");
    }
}