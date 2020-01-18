<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use GuzzleHttp\Psr7\Response;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response as HTTP;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;


class UserController extends AbstractController
{
    /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/api/users", name="list_users",  methods={"GET"})

     */
    public function list(UserRepository $repo,SerializerInterface $serializer)
    {
        $users = $repo->findAll();
        $json = $serializer->serialize($users,'json');
        return new JsonResponse($json,HTTP::HTTP_OK,[],true);
    }

     /**
     * Link to this controller to start the "connect" process
     *
     * @Route("/api/create/user", name="create_user",  methods={"POST"})

     */
    public function create(UserPasswordEncoderInterface $passwordEncoder,Request $request,UserRepository $repo,SerializerInterface $serializer,LoggerInterface $logger)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $data = json_decode($request->getContent(), true);
      $email = $data['email'];
      $password = $data['password'];
      $fullName = $data['fullName'];
      $user = new User;
      $user->setEmail($email);
      $user->setPassword($passwordEncoder->encodePassword($user,$password));
      $user->setFullname($fullName);
      $user->setCreatedAt(new \DateTime());
      $entityManager->persist($user);
      $entityManager->flush();

    //   $user = $serializer->deserialize($data,User::class,'json');

       
    //     $user->setPassword($passwordEncoder->encodePassword($user,$user->getPassword()));
    //     $manager->persist($user);
    //     $manager->flush();

        return new JsonResponse("utilisateur crée",HTTP::HTTP_CREATED,[
            "location"=>"api/user/".$user->getId()
        ],true);
    }


}