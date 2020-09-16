<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Wallet;
use App\Managers\WalletManager;
use FOS\RestBundle\Controller\Annotations\Get;
use App\Entity\User;
use App\Repository\UserRepository;
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
     * @return JsonResponse
     */
    public function getUserAction(User $user): JsonResponse
    {
        return new JsonResponse($user);
    }

    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/")
     * @SWG\Tag(name="users")
     *
     * @param UserRepository $repository
     * @return JsonResponse
     */
    public function getUsersAction(
        UserRepository $repository
    ): JsonResponse
    {
        return new JsonResponse($repository->findAll());
    }

    /**
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
     * @return JsonResponse
     */
    public function addWalletAction(
        Request $request,
        User $user,
        WalletManager $walletManager,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $wallet = new Wallet();
        $wallet->setNumber($request->request->get('number'));

        $errors = $validator->validate($wallet);

        if (count($errors) > 0) {
            $errorsString = (string) $errors;

            return new JsonResponse($errorsString, Response::HTTP_METHOD_NOT_ALLOWED);
        }

        try {
            $newWallet = $walletManager->createWallet($wallet, $user);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_METHOD_NOT_ALLOWED);
        }

        return new JsonResponse($newWallet);
    }
}
