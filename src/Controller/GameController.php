<?php
namespace App\Controller;
use App\Entity\Game;
use App\Form\GameFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class GameController extends AbstractController
{
    #[Route('/game', name: 'app_game')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $game = $entityManager->getRepository(Game::class)->findAll();
        return $this->render('game/index.html.twig', [
            'games' => $game,
        ]);
    }

    #[Route('/game/view/{slug}', name: 'app_game_show')]
    public function show(string  $slug, EntityManagerInterface $entityManager): Response
    {
        $game = $entityManager->getRepository(Game::class)->findOneBy(['slug' => $slug]);
        return $this->render('game/show.html.twig', [
            'game' => $game,
        ]);
    }

    #[Route('/game/edit/{slug}', name: 'app_game_edit')]
    public function edit(EntityManagerInterface $entityManager, Game $game, Request $request, string $slug, SluggerInterface $slugger): Response
    {
        $game = $entityManager->getRepository(Game::class)->findOneBy(['slug' => $slug]);
        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($game->getName())->lower();
            $game->setSlug($slug);
            $entityManager->flush();
            return $this->redirectToRoute('app_game_show', ['slug' => $game->getSlug()]);
        }
        return $this->render('game/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/game/delete/{id}', name: 'app_game_delete')]
    public function delete(EntityManagerInterface $entityManager, Game $game): Response
    {
        $entityManager->remove($game);
        $entityManager->flush();
        return $this->redirectToRoute('app_game');
    }

    #[Route('/game/new', name: 'app_game_add')]
    public function new(EntityManagerInterface $entityManager, string $slug, SluggerInterface $slugger, Request $request): Response
    {
        $game = new Game();
        $form = $this->createForm(GameFormType::class, $game);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($game->getName())->lower();
            $game->setSlug($slug);
            $entityManager->persist($game);
            $entityManager->flush();
            return $this->redirectToRoute('app_game');
        }
        return $this->render('game/admin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route ('/game/admin', name: 'app_game_admin')]
    public function admin(EntityManagerInterface $entityManager, SluggerInterface $slugger, Request $request ): Response
    {
        $game = $entityManager->getRepository(Game::class)->findAll();
        $games = new Game();
        $form = $this->createForm(GameFormType::class, $games);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $slug = $slugger->slug($games->getName())->lower();
            $games->setSlug($slug);
            $entityManager->persist($games);
            $entityManager->flush();
            return $this->redirectToRoute('app_game_admin');
        }
        return $this->render('game/admin.html.twig', [
            'games' => $game,
            'form' => $form->createView(),
        ]);
    }



}
