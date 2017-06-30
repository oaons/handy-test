<?php

namespace CoreBundle\Form\Type;

use CoreBundle\DataTransformer\ObjectToIdentifierTransformer;
use Doctrine\Common\Persistence\ObjectManager;
use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @DI\FormType
 */
class ObjectIdentifierType extends AbstractType
{

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @DI\InjectParams({
     *      "objectManager" = @DI\Inject("doctrine.orm.entity_manager"),
     * })
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new ObjectToIdentifierTransformer($this->objectManager, $options['class'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefault('compound', false);
        $resolver->setRequired('class');
    }
}
