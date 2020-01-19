<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Psr7\Response;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response as HTTP;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;


class UserController extends AbstractController
{
    /**
     * 
     *
     * @Route("/api/users", name="list_users",  methods={"GET"})

     */
    public function list(UserRepository $repo, SerializerInterface $serializer)
    {
        return $this->json($repo->findAll(), HTTP::HTTP_OK, []);
    }

    /**
     * 
     *
     * @Route("/api/user/{id}", name="show_user",  methods={"GET"})

     */
    public function show(Request $request, User $user, SerializerInterface $serializer, UserRepository $repo)
    {
        
        return $this->json($user, HTTP::HTTP_OK, []);
    }
    /**
     * 
     *
     * @Route("/api/user", name="create_user",  methods={"POST"})

     */
    public function create(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, Request $request, UserRepository $repo, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');

        $user->setCreatedAt(new \DateTime());
        $user->setModifiedAt(new \DateTime());
        $user->setPassword($passwordEncoder->encodePassword($user, $user->getPassword()));
        $em->persist($user);
        $em->flush();

        return new JsonResponse("utilisateur crée", HTTP::HTTP_CREATED, [
            "location" => "api/user/" . $user->getId()
        ], true);
    }
    /**
     * 
     *
     * @Route("/api/user", name="update_user",  methods={"PUT"})

     */
    public function update(EntityManagerInterface $em, UserPasswordEncoderInterface $passwordEncoder, Request $request, UserRepository $repo, SerializerInterface $serializer, LoggerInterface $logger)
    {
        $data = $request->getContent();
        $user = $serializer->deserialize($data, User::class, 'json');
       $user= $em->find(User::class,$user->getId());

        if (empty($user)) {
            return new JsonResponse(['message' => 'User not found'], HTTP::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(User::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            // l'entité vient de la base, donc le merge n'est pas nécessaire.
            // il est utilisé juste par soucis de clarté
            $em->merge($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }
}
