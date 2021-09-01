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



   /**********
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

        $this->addFlash("plus","L'équipe ".$e->getNom()." a été créée !");

        return $this->redirectToRoute("accueil");
    }

    /**
     * @Route("/delTeam/{id}", name="delEquipe")
     */
    public function delEquipe(Equipe $e, EntityManagerInterface $em): Response
    {
        if (!empty($e->getPersonnes())) {
            foreach ($e->getPersonnes() as $p) {
                $e->removePersonne($p);
            }
        }
        
        $em->remove($e);
        $em->flush();

        $this->addFlash("moins","L'équipe ".$e->getNom()." a été effacée !");

        return $this->redirectToRoute("accueil");
    }



   /**********
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
            $equipe = $er->find($req->get("team"));
            $p->addEquipe($equipe);
            $this->addFlash("plus","L'utilisateur ".$p->getNom()." ".$p->getPrenom()." a rejoint l'entreprise et a intégré l'équipe ".$equipe->getNom()."!");
        } else {
            $this->addFlash("plus","L'utilisateur ".$p->getNom()." ".$p->getPrenom()." a rejoint l'entreprise !");
        }

        $em->persist($p);
        $em->flush();

        

        return $this->redirectToRoute("accueil");
    }

    /**
     * @Route("/addPersoEquipe/{personne}/{equipe}", name="addPersoEquipe")
     */
    public function addPersoEquipe(Personne $personne, Equipe $equipe, EntityManagerInterface $em): Response
    {
        $personne->addEquipe($equipe);

        $em->persist($personne);
        $em->flush();

        $this->addFlash("plus","L'utilisateur ".$personne->getNom()." ".$personne->getPrenom()." a rejoint l'équipe ".$equipe->getNom()." !");

        return $this->json(['info'=>'ok']);
    }

    /**
     * @Route("/delPersoEquipe/{personne}/{equipe}", name="delPersoEquipe")
     */
    public function delPersoEquipe(Personne $personne,Equipe $equipe, EntityManagerInterface $em): Response
    {
        $personne->removeEquipe($equipe);

        $em->persist($personne);
        $em->flush();

        $this->addFlash("moins","L'utilisateur ".$personne->getNom()." ".$personne->getPrenom()." a quitté l'équipe ".$equipe->getNom()." !");

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

        $this->addFlash("moins","L'utilisateur ".$p->getNom()." ".$p->getPrenom()." a quitté l'entreprise !");

        return $this->redirectToRoute("accueil");
    }
}