<?php

namespace App\Controller;

use App\Entity\Commission;
use App\Form\TransactionAccountType;
use App\Managers\AccountManager;
use App\Repository\AccountRepository;
use App\Form\AccountCreateType;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations\Delete;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\Put;
use App\Entity\Account;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @var SerializerInterface $serializer
     */
    private $serialize;

    public function _constructor(SerializerInterface $serialize)
    {
        $this->serialize = $serialize;
    }

    /**
     * @SWG\Response(response=200, description="Success")
     * @SWG\Response(response=404, description="Not Found")
     *
     * @Get(path="/")
     * @SWG\Tag(name="accounts")
     *
     * @param AccountRepository $repository
     * @return Response
     */
    public function getAccountsAction(AccountRepository $repository)
    {
        return $accounts = $repository->findAll();


        $list = $this->serialize->serialize(
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
     * @return array|JsonResponse
     */
    public function transactionAccountsAction(Request $request)
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
        /**
         * @var Commission $commission
         */
        $commission = $this->getDoctrine()->getRepository(Commission::class)->findOneBy(['type' => Commission::TYPE_TRANSACTION_USER]);

        if ($fromAccount->getAmount() < ($amountTransfer + $amountTransfer * $commission->getValue())) {
            throw new MethodNotAllowedHttpException([], 'The transfer amount is too large, there is not enough amount on the wallet');
        }

        $fromAccount->setAmount($fromAccount->getAmount() - ($amountTransfer + $amountTransfer * $commission->getValue()));
        $toAccount->setAmount($toAccount->getAmount() + $amountTransfer);

        $this->getDoctrine()->getManager()->persist($fromAccount);
        $this->getDoctrine()->getManager()->persist($toAccount);
        $this->getDoctrine()->getManager()->flush();

        return ['success' => true];
    }
}
