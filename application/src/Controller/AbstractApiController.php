<?php
namespace App\Controller;

use FOS\RestBundle\View\View;
use FOS\RestBundle\Context\Context;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @return View
     */
    public function getResponse($content, $status = self::STATUS_SUCCESS, $code = Response::HTTP_OK)
    {
        $view = View::create(['status'  => $status, 'content' => $content], $code);
        $view->setContext(new Context());
        return $view;
    }

    /**
     * Get array of constraint violations messages. Prepares validation errors for api response.
     *
     * @param  ConstraintViolationListInterface $violations
     * @return array
     */
    protected function getConstraintViolations(ConstraintViolationListInterface $violations)
    {
        $errors = [];

        /* @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $constraint = $violation->getConstraint();

            /*
             * We try to use payload property path before take it from getPropertyPath
             */
            $propertyPath = isset($violation->getConstraint()->payload['propertyPath']) ?
                $violation->getConstraint()->payload['propertyPath'] :
                $violation->getPropertyPath();

            $errors[$propertyPath] = [
                'code'    => isset($constraint->payload['code']) ? $constraint->payload['code'] : $violation->getMessage(),
                'message' => $violation->getMessage()
            ];
        }

        return $errors;
    }

    /**
     * Response view for request data validation violations
     *
     * @param  ConstraintViolationListInterface $violations
     * @return View
     */
    protected function getValidationErrorResponse(ConstraintViolationListInterface $violations)
    {
        return $this->getResponse($this->getConstraintViolations($violations), self::STATUS_ERROR, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Request $request
     * @param array $fields
     * @return array
     */
    protected function getParamsFromRequest(Request $request, $fields = [])
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
