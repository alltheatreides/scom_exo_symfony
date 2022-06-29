<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Form\PersonneType;
use App\Repository\PersonneRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[Route('/')]
class PersonneController extends AbstractController
{
    #[Route('/', name: 'app_personne_index', methods: ['GET'])]
    public function index(PersonneRepository $personneRepository): Response
    {

        // Query the personnes as an array of Objects
        $personnes = $personneRepository->findAll();

        // Create a clean array to store personnes data and change the date of birth to display their age
        $personnesWithAge = [];

        // Serializing in workable array format (Serialize dependency) the personne Objects
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, []);

        // Converts the Person Objects to arrays
        $personnesWithAge = $serializer->normalize($personnes, 'array');

        // Calculating the person age from current date time year and their input date of birth
        // New date time instance
        $currentDate = new DateTime();

        // Looping over the clean personnewithAge array and inserting the age in it
        foreach ($personnesWithAge as $key => $personne) {

            // Reserialize the date into a datetime object to use date_diff function and get the person age
            $reserializedDate = new DateTime();
            $reserializedDate->setTimestamp($personne['datedenaissance']['timestamp']);

            $personneAge = date_diff($currentDate, $reserializedDate)->format('%y');

            // Insert the age into the clean array
            $personnesWithAge[$key]['age'] = $personneAge;
        };

        return $this->render('personne/index.html.twig', [
            'personnes' => $personnesWithAge,
        ]);
    }

    #[Route('/new', name: 'app_personne_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PersonneRepository $personneRepository): Response
    {
        $personne = new Personne();
        $form = $this->createForm(PersonneType::class, $personne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personneRepository->add($personne, true);

            return $this->redirectToRoute('app_personne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('personne/new.html.twig', [
            'personne' => $personne,
            'form' => $form,
        ]);
    }

    #[Route('/{idpersonne}', name: 'app_personne_show', methods: ['GET'])]
    public function show(Personne $personne): Response
    {
        // Create a clean array to store personne data and change the date of birth to display their age
        $personneWithAge = [];

        // Serializing in workable array format (Serialize dependency) the personne Object
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, []);

        // Converts the Person Object to arrays
        $personneWithAge = $serializer->normalize($personne, 'array');

        // Calculating the person age from current date time year and their input date of birth
        // New date time instance
        $currentDate = new DateTime();

        // Reserialize the date into a datetime object to use date_diff function and get the person age
        $reserializedDate = new DateTime();
        $reserializedDate->setTimestamp($personneWithAge['datedenaissance']['timestamp']);

        $personneAge = date_diff($currentDate, $reserializedDate)->format('%y');

        // Insert the age into the clean array
        $personneWithAge['age'] = $personneAge;

        return $this->render('personne/show.html.twig', [
            'personne' => $personneWithAge,
        ]);
    }

    #[Route('/{idpersonne}/edit', name: 'app_personne_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Personne $personne, PersonneRepository $personneRepository): Response
    {
        $form = $this->createForm(PersonneType::class, $personne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $personneRepository->add($personne, true);

            return $this->redirectToRoute('app_personne_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('personne/edit.html.twig', [
            'personne' => $personne,
            'form' => $form,
        ]);
    }

    #[Route('/{idpersonne}', name: 'app_personne_delete', methods: ['POST'])]
    public function delete(Request $request, Personne $personne, PersonneRepository $personneRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $personne->getIdpersonne(), $request->request->get('_token'))) {
            $personneRepository->remove($personne, true);
        }

        return $this->redirectToRoute('app_personne_index', [], Response::HTTP_SEE_OTHER);
    }
}
