<?php

namespace App\Controller;

use App\Entity\Account;
use App\Managers\AccountManager;
use App\Form\AccountCreateType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api/users")
 */
class UserController extends AbstractApiController
{

    /**
     * @var EntityManagerInterface $em
     */
    private $em;
    /**
     * @var SerializerInterface $serializer
     */
    private $serialize;

    /**
     * @var AccountManager $accountManager
     */
    private $accountManager;

    public function _constructor(SerializerInterface $serialize, AccountManager $accountManager, EntityManagerInterface $em)
    {
        $this->serialize = $serialize;
        $this->accountManager = $accountManager;
        $this->em = $em;
    }

    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/{id}")
     * @SWG\Tag(name="users")
     *
     * @param User $user
     * @return User
     */
    public function getUserAction(User $user)
    {
        return $user;
    }

    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/")
     * @SWG\Tag(name="users")
     *
     * @param UserRepository $repository
     * @return Response
     */
    public function getUsersAction(UserRepository $repository)
    {
        return $users = $repository->findAll();

        $list = $this->serialize->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(['userList'])->setSerializeNull(true)
        );

        return new Response($list, Response::HTTP_OK);
    }

    /**
     *
     * @SWG\Post(
     *      summary="Add account to user",
     *      @SWG\Parameter(
     *          name="body",
     *          description="",
     *          in="body",
     *          @Model(type=\App\Form\AccountCreateType::class)
     *      ),
     * @SWG\Response(response=200, description="Success")
     * )
     * @SWG\Tag(name="users")
     * @Route("/{id}/account", methods={"POST"})
     *
     * @param Request $request
     * @param User $user
     * @return Account|JsonResponse
     */
    public function addAccountAction(Request $request, User $user)
    {
        $form = $this->createForm(AccountCreateType::class, new Account());

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->getFormErrorResponse($form);
        }

        $account = $form->getData();
        $account->setUser($user);
        $this->getDoctrine()->getManager()->persist($account);
        $this->getDoctrine()->getManager()->flush();

        return $account;
        //return $manager->createAccount($form->getData(), $user);
    }
}
