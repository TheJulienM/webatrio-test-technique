<?php

namespace App\Controller;

use App\Entity\Job;
use App\Entity\People;
use DateTimeInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\UuidV4;

class ApiController extends AbstractController
{

    #[Route('/new-people', name: 'app_api_newPeople', methods: "POST")]
    public function newPeople(SerializerInterface $serializer, Request $request, ManagerRegistry $doctrine): Response
    {

        // Récupération du corps (body) de la requête HTTP
        $requestBody = $request->toArray();

        try {
            // Tentative de création d'un objet DateTime à partir de la date de naissance fournie
            $birthdate = new \DateTime($requestBody["birthdate"]);
        } catch (\Exception $exception) {
            // En cas d'échec de la conversion, renvoyer une réponse d'erreur 400
            $status = 400;
            $json = ["status" => $status, "message" => "Mauvaise requête. Veuillez respecter ce format : 'Y-m-d'. Exemple : '2000-05-27'"];
            $response = $serializer->serialize($json, 'json');

            return new Response($response, $status, ["Content-Type" => "application/json"]);
        }

        // Récupération de la date actuelle
        $actualDate = new \DateTime('now');

        if (intval($actualDate->format('Y')) - intval($birthdate->format('Y')) < 150) {
            // Création d'un nouvel objet People
            $people = new People();
            $people->setBirthdate($birthdate);
            $people->setFirstname($requestBody["firstname"]);
            $people->setLastname($requestBody["lastname"]);
            $people->setUuid(new UuidV4());

            // Gestion de la persistance en base de données
            $manager = $doctrine->getManager();
            $manager->persist($people);
            $manager->flush();

            // Réponse de succès 201 en cas de création réussie
            $status = 201;
            $json = ["status" => $status, "message" => "Success ! People created", "people" => $people];
        } else {
            // Réponse d'erreur 400 si la personne a 150 ans ou plus
            $status = 400;
            $json = ["status" => $status, "message" => "Bad request. People is 150 years or older."];
        }

        // Sérialisation de la réponse en format JSON
        $response = $serializer->serialize($json, 'json');

        // Retour de la réponse avec le code HTTP et le type de contenu appropriés
        return new Response($response, $status, ["Content-Type" => "application/json"]);
    }

    #[Route('/add-job-people', name: 'app_api_addJobPeople', methods: "POST")]
    public function addJobPeople(SerializerInterface $serializer, Request $request, ManagerRegistry $doctrine): Response {

        // Récupération du corps (body) de la requête HTTP
        $requestBody = $request->toArray();

        // Création d'un nouvel objet Job
        $job = new Job();
        $job->setCompanyName($requestBody["companyName"]);
        $job->setPosition($requestBody["position"]);

        // Récupération de la date de fin du contrat du job
        $endDate = $requestBody["endDate"];

        try {
            // Tentative de création d'un objet DateTime à partir de la date de début de travail
            $startDate = new \DateTime($requestBody["startDate"]);
        }
        catch (\Exception $exception) {

            // En cas d'échec de la conversion, renvoyer une réponse d'erreur 400
            $status = 400;
            $json = ["status" => $status, "message" => "Bad request. Please respect this format : 'Y-m-d'. Example : '2000-05-27'"];
            $response = $serializer->serialize($json, 'json');

            return new Response($response, $status, ["Content-Type" => "application/json"]);
        }

        try {
            // Si on a une date de fin
            if($endDate) {
                // Tentative de création d'un objet DateTime à partir de la date de début de travail
                $endDate = new \DateTime($endDate);
            }
        }
        catch (\Exception $exception) {

            // En cas d'échec de la conversion, renvoyer une réponse d'erreur 400
            $status = 400;
            $json = ["status" => $status, "message" => "Bad request. Please respect this format : 'Y-m-d'. Example : '2000-05-27'"];
            $response = $serializer->serialize($json, 'json');

            return new Response($response, $status, ["Content-Type" => "application/json"]);
        }

        // Définition des dates de début et de fin pour l'objet Job
        $job->setStartDate($startDate);
        $job->setEndDate($endDate);
        // Récupération de l'objet People correspondant à l'UUID fourni
        // On vérifie que le UUID est valide et s'il ne l'est pas on renvoie une erreur
        try {
            $people = $doctrine->getRepository(People::class)->findOneBy(["uuid" => UuidV4::fromString($requestBody["peopleUuid"])]);
            if(!$people) {
                $status = 400;
                $json = ["status" => $status, "message" => "Bad request. People unknown. Please check the UUID."];
                $response = $serializer->serialize($json, 'json');
                return new Response($response, $status, ["Content-Type" => "application/json"]);
            }
        }
        catch (\Exception $exception) {
            $status = 400;
            $json = ["status" => $status, "message" => "Bad request. People unknown. Please check the UUID."];
            $response = $serializer->serialize($json, 'json');
            return new Response($response, $status, ["Content-Type" => "application/json"]);
        }

        $job->setPeople($people);

        $manager = $doctrine->getManager();
        $manager->persist($job);
        $manager->flush();

        $status = 201;
        $json = ["status" => $status, "message" => "Success : New job created !", "people" => $people, "job" => $job];

        $response = $serializer->serialize($json, 'json', ["groups" => "addJobPeople"]);

        return new Response($response, $status, ["Content-Type" => "application/json"]);

    }

    #[Route('/peoples', name: 'app_api_getPeoples', methods: 'GET')]
    public function getPeoples(SerializerInterface $serializer, ManagerRegistry $doctrine): Response {
        $peoples = $doctrine->getRepository(People::class)->findAllOrderByLastname();
        $response = $serializer->serialize($peoples, 'json', ['groups' => 'getPeoples']);
        return new Response($response, 200, ["Content-Type" => "application/json"]);
    }

    #[Route('/company-members/{companyName?}', name: 'app_api_companyMembers', methods: 'GET')]
    public function companyMembers(SerializerInterface $serializer, ManagerRegistry $doctrine, ?string $companyName): Response {
        $peoples = $doctrine->getRepository(People::class)->findAllByCompany($companyName);
        $json = ["status" => 200, "companyName" => $companyName, "employees" => $peoples];
        $response = $serializer->serialize($json, 'json');
        return new Response($response, 200, ["Content-Type" => "application/json"]);
    }

}
