<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Wallet;
use App\Managers\WalletManager;
use FOS\RestBundle\Controller\Annotations\Get;
use App\Entity\User;
use App\Repository\UserRepository;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api/users")
 */
class UserController extends AbstractApiController
{
    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/{id}")
     * @SWG\Tag(name="users")
     *
     * @param User $user
     * @return Response
     */
    public function getUserAction(User $user): Response
    {
        return new Response($user, Response::HTTP_OK);
    }

    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/")
     * @SWG\Tag(name="users")
     *
     * @param UserRepository $repository
     * @param SerializerInterface $serialize
     * @return Response
     */
    public function getUsersAction(
        UserRepository $repository,
        SerializerInterface $serialize
    ): Response
    {
        $users = $repository->findAll();

        $list = $serialize->serialize(
            $users,
            'json',
            SerializationContext::create()->setGroups(['userList'])->setSerializeNull(true)
        );

        return new Response($list, Response::HTTP_OK);
    }

    /**
     *
     * @SWG\Post(
     *      summary="Add wallet to user",
     *      @SWG\Parameter(
     *          name="body",
     *          description="",
     *          in="body",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="number", type="string", example="")
     *          )
     *      ),
     * @SWG\Response(response=200, description="Success")
     * )
     * @SWG\Tag(name="users")
     * @Route("/{id}/wallet", methods={"POST"})
     *
     * @param Request $request
     * @param User $user
     * @param WalletManager $walletManager
     * @param ValidatorInterface $validator
     * @return Response|Wallet
     */
    public function addWalletAction(
        Request $request,
        User $user,
        WalletManager $walletManager,
        ValidatorInterface $validator
    )
    {
        $wallet = new Wallet();
        $wallet->setNumber($request->request->get('number'));

        $errors = $validator->validate($wallet);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new Response($errorsString);
        }

        try {
            $updateUser = $walletManager->createWallet($wallet, $user);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        return $updateUser;
    }
}
