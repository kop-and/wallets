<?php
declare(strict_types=1);

namespace App\Controller;

use App\Managers\WalletManager;
use App\Repository\WalletRepository;
use FOS\RestBundle\Controller\Annotations\Get;
use App\Entity\Wallet;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
     * @param SerializerInterface $serialize
     * @return array|Response
     */
    public function getWalletsAction(
        WalletRepository $repository,
        SerializerInterface $serialize
    ): Response
    {
        $wallets = $repository->findAll();

        $list = $serialize->serialize(
            $wallets,
            'json',
            SerializationContext::create()->setGroups(['walletList'])->setSerializeNull(true)
        );

        return new Response($list, Response::HTTP_OK);
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
     * @param WalletRepository $walletRepository
     * @return JsonResponse
     */
    public function transactionWalletsAction(
        Request $request,
        WalletManager $walletManager,
        WalletRepository $walletRepository
    ): JsonResponse
    {
        /** @var Wallet $fromWallet */
        $fromWallet = $walletRepository->findOneBy(['id' => $request->request->get('fromWallet')]);
        if (!$fromWallet) {
            throw new NotFoundHttpException('From Wallet was not found');
        }
        /** @var Wallet $toWallet */
        $toWallet = $walletRepository->findOneBy(['id' => $request->request->get('toWallet')]);
        if (!$toWallet) {
            throw new NotFoundHttpException('To Wallet was not found');
        }

        try {
            $walletManager->transferAmount($fromWallet, $toWallet, $request->request->get('amount'));
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
