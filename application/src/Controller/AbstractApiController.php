<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractApiController extends AbstractController
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    /**
     * @param string $content
     * @param string $status
     * @param int $code
     * @return JsonResponse
     */
    public function getResponse(string $content, string $status = self::STATUS_SUCCESS, int $code = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse(['status'  => $status, 'content' => $content], $code);
    }

    /**
     * Get array of constraint violations messages. Prepares validation errors for api response.
     *
     * @param  ConstraintViolationListInterface $violations
     * @return array
     */
    protected function getConstraintViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        /* @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $constraint = $violation->getConstraint();

            /*
             * We try to use payload property path before take it from getPropertyPath
             */
            $propertyPath = $violation->getConstraint()->payload['propertyPath'] ?? $violation->getPropertyPath();

            $errors[$propertyPath] = [
                'code'    => $constraint->payload['code'] ?? $violation->getMessage(),
                'message' => $violation->getMessage()
            ];
        }

        return $errors;
    }

    /**
     * Response view for request data validation violations
     *
     * @param  ConstraintViolationListInterface $violations
     * @return JsonResponse
     */
    protected function getValidationErrorResponse(ConstraintViolationListInterface $violations): JsonResponse
    {
        return $this->getResponse($this->getConstraintViolations($violations), self::STATUS_ERROR, Response::HTTP_BAD_REQUEST);
    }
}
