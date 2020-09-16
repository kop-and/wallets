<?php
declare(strict_types=1);

namespace App\Controller;

use App\Managers\WalletManager;
use App\Repository\WalletRepository;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Swagger\Annotations as SWG;

/**
 * Class WalletController
 * @package App\Controller
 * @Route("/api/walletss")
 */
class WalletController extends AbstractApiController
{
    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/")
     * @SWG\Tag(name="wallets")
     *
     * @param WalletRepository $repository
     * @return JsonResponse
     */
    public function getWalletsAction(
        WalletRepository $repository
    ): JsonResponse
    {
        return new JsonResponse($repository->findAll());
    }

    /**
     * @SWG\Post(
     *      summary="Transaction between wallets",
     *      @SWG\Parameter(
     *          name="body",
     *          description="",
     *          in="body",
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="fromWallet", type="int", example=""),
     *              @SWG\Property(property="toWallet", type="int", example=""),
     *              @SWG\Property(property="amount", type="int", example="")
     *          )
     *      ),
     * @SWG\Response(response=200, description="Success")
     * )
     * @SWG\Tag(name="wallets")
     * @Route("/transaction", methods={"POST"})
     *
     * @param Request $request
     * @param WalletManager $walletManager
     * @return JsonResponse
     * @throws \Throwable
     */
    public function transactionWalletsAction(
        Request $request,
        WalletManager $walletManager
    ): JsonResponse
    {
        try {
            $walletManager->transferAmount(
                $request->request->get('fromWallet'),
                $request->request->get('toWallet'),
                $request->request->get('amount')
            );
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['success' => true]);
    }
}
