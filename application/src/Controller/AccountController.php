<?php

namespace App\Controller;

use App\Form\TransactionAccountType;
use App\Managers\AccountManager;
use App\Repository\AccountRepository;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use App\Entity\Account;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

use Swagger\Annotations as SWG;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class AccountController
 * @package App\Controller
 * @Route("/api/accounts")
 */
class AccountController extends AbstractApiController
{
    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/")
     * @SWG\Tag(name="accounts")
     *
     * @param AccountRepository $repository
     * @param SerializerInterface $serialize
     * @return array|Response
     */
    public function getAccountsAction(AccountRepository $repository, SerializerInterface $serialize)
    {
        $accounts = $repository->findAll();


        $list = $serialize->serialize(
            $accounts,
            'json',
            SerializationContext::create()->setGroups(['accountList'])->setSerializeNull(true)
        );

        return new Response($list, Response::HTTP_OK);
    }

    /**
     *
     * @SWG\Put(
     *      summary="Transaction between accounts",
     *      @SWG\Parameter(
     *          name="body",
     *          description="",
     *          in="body",
     *          @Model(type=\App\Form\TransactionAccountType::class)
     *      ),
     * @SWG\Response(response=200, description="Success")
     * )
     * @SWG\Tag(name="accounts")
     * @Route("/transaction", methods={"PUT"})
     *
     * @param Request $request
     * @param AccountManager $accountManager
     * @return JsonResponse|Response
     */
    public function transactionAccountsAction(Request $request, AccountManager $accountManager)
    {
        /**
         * @var Account $fromAccount
         */
        $fromAccount = $this->getDoctrine()->getRepository(Account::class)->findOneBy(['id' => $request->get('fromAccount')]);
        if (!$fromAccount) {
            throw new NotFoundHttpException('From account was not found');
        }
        /**
         * @var Account $toAccount
         */
        $toAccount = $this->getDoctrine()->getRepository(Account::class)->findOneBy(['id' => $request->get('toAccount')]);
        if (!$toAccount) {
            throw new NotFoundHttpException('To account was not found');
        }
        $form = $this->createForm(TransactionAccountType::class, new Account());

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $this->getFormErrorResponse($form);
        }

        $amountTransfer = $request->get('amount');

        try {
            $accountManager->transferAmount($fromAccount, $toAccount, $amountTransfer);
        } catch (\Exception $exception) {
            return new JsonResponse($exception->getMessage(), Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['success' => true], Response::HTTP_OK);
    }
}
