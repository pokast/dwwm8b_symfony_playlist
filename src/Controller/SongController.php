<?php

namespace App\Controller;

use App\Entity\Song;
use App\Form\SongFormType;
use App\Repository\SongRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SongController extends AbstractController
{

    #[Route('/', name: 'song.index')]
    public function index(SongRepository $songRepository): Response
    {
        $songs = $songRepository->findAll();
        return $this->render('song/index.html.twig', compact('songs'));
    }

    /**
     * Cette méthode effectue 4 actions
     *          - Via la méthode GET, acceder à la page d'ajout d'un nouveau son sur laquelle on retrouve lz formulaire
     *          - Via la méthode POST, récuperer les données du formulaire, les traiter en fonction des différences contraintes de validation mises en place
     *          - Demander à SongRepository de realiser la requête d'insertion du son en base de données
     *          - Effectuer la redirection vers la page d'accueil accompagnée d'un message de succès
     * 
     * @param Request $request
     * @param SongRepository $songRepository
     * @return Response
     */
    #[Route('/create', name: 'song.create')]
    public function create(Request $request, SongRepository $songRepository) : Response
    {
        // Pour insérer un nouveau son en base de données

        // 1- Créer une nouvelle instance de type Song
        $song = new Song();

        /** 
         *  2- a) Créer le type de formulaire du son (symfony console make:form SongFormType)
         *  2- b) Demander à SongController de créer le formulaire en se basant sur :
         *  -- Le type de formulaire du son
         *  -- La nouvelle instance de type Song créée
        */  
        $form = $this->createForm(SongFormType::class, $song);

        // Le code de la ligne 49 à la ligne 62 prend effet si et seulement si les données du formulaire ont été envoyées
        // 4- Associer les données du formulaire à l'objet $form
        $form->handleRequest($request);
        
        /**
         * 5- Si le formulaire est soumis et que celui-ci est valide
         */
        if ( $form->isSubmitted() && $form->isValid() )
        {
            $data = $request->request->all();

            $score = round($data['song_form']['score'], 1);

            // 6- Initialiser manuellement les propriétés de l'objet dont les données ne proviennent pas du formulaire 
            $song->setScore($score);
            $song->setCreatedAt(new \DateTimeImmutable('now'));
            $song->setUpdatedAt(new \DateTimeImmutable('now'));

            /**
             * 7- Demander au gestionnaire des requêtes de la table "song" ->(SongRepository)
             * d'effectuer la requête d'insertion du nouveau son
             * Cette tâche est effectuée grâce au "EntityManager"
             */
            $songRepository->save($song, true);
            
            /**
             * 8- Afin d'indiquer à l'utilisateur que sa requête a été effectuée avec succès
             * préparer un message flash
             */
            $this->addFlash("success", "Le son a été ajouté avec succès.");

            /**
             * 9- Rediriger l'utilisateur vers la page d'accueil de l'application
             * afin qu'il puisse directement consulter la liste 
             */
            return $this->redirectToRoute("song.index");

        }

        /**
         * 3- Retourner la page d'ajout d'un nouveau son accompagnée de la partie visible du formulaire
         */
        return $this->render('song/create.html.twig', [
            "form" => $form->createView(),
            // "song" => $song
        ]);
    }


    #[Route('/edit/{id}', name: 'song.edit')]
    public function edit(Song $song, SongRepository $songRepository) : Response
    {
        $form = $this->createForm(SongFormType::class, $song);

        return $this->render("song/edit.html.twig", [
            "form" => $form->createView(),
            "song" => $song
        ]);

    }
}
