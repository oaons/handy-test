<?php

namespace ApiBundle\Form;

use ApiBundle\Exception\FormInvalidException;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @DI\Service("api.form.handler")
 */
class FormHandler
{

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @DI\InjectParams({
     *     "formFactory" = @DI\Inject("form.factory"),
     * })
     * @param FormFactoryInterface $formFactory
     */
    public function __construct(FormFactoryInterface $formFactory)
    {
        $this->formFactory = $formFactory;
    }

    /**
     * @param Request $request
     * @param string|FormTypeInterface $formType
     * @param null|mixed $data
     * @param array $options
     *
     * @throws FormInvalidException
     * @return FormInterface
     */
    public function handleRequest(Request $request, $formType, $data = null, array $options = [])
    {
        $form = $this->formFactory->createNamed('', $formType, $data, $options);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            $form->submit([]);
        }

        if ($form->isValid()) {
            return $form;
        }

        throw new FormInvalidException($form);
    }

    /**
     * @param null|array|string $submitData
     * @param string|FormTypeInterface $formType
     * @param null|mixed $data
     * @param array $options
     *
     * @throws FormInvalidException
     * @return FormInterface
     */
    public function submit($submitData, $formType, $data = null, array $options = [])
    {
        $form = $this->formFactory->createNamed('', $formType, $data, $options);
        $form->submit($submitData);

        if ($form->isValid()) {
            return $form;
        }

        throw new FormInvalidException($form);
    }

}
