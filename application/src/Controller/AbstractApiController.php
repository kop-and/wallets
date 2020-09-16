<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractApiController extends AbstractController
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_ERROR = 'error';

    /**
     *
     * @param $content
     * @param string $status
     * @param $code
     * @return JsonResponse
     */
    public function getResponse($content, $status = self::STATUS_SUCCESS, $code = Response::HTTP_OK): JsonResponse
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

    /**
     * @param Request $request
     * @param array $fields
     * @return array
     */
    protected function getParamsFromRequest(Request $request, $fields = []): array
    {
        $params = [];
        foreach ($fields as $field) {
            if ($value = $request->get($field)) {
                $params[$field] = $value;
            }
        }

        return $params;
    }

    /**
     * @param mixed $objects
     * @return string
     */
    protected function serialize($objects): string
    {
        return $this->container->get('jms_serializer')->serialize($objects, 'json');
    }
}
