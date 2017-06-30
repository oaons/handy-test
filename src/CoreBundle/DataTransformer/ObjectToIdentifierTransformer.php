<?php

namespace CoreBundle\DataTransformer;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class ObjectToIdentifierTransformer implements DataTransformerInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @param ObjectManager $objectManager
     * @param string $className
     */
    public function __construct(ObjectManager $objectManager, $className)
    {
        $this->objectManager = $objectManager;
        $this->className = $className;
    }

    /**
     * @param object $value
     * @throws TransformationFailedException
     * @return array|null
     */
    public function transform($value)
    {
        if (null === $value) {
            return null;
        }

        if (!is_a($value, $this->className)) {
            throw new TransformationFailedException(
                sprintf('Object should be of class %s', $this->className)
            );
        }

        return $this->objectManager->getClassMetadata($this->className)->getIdentifierValues($value);
    }

    /**
     * @param array|string $value
     * @throws TransformationFailedException
     * @return object|null
     */
    public function reverseTransform($value)
    {
        if (null === $value) {
            return null;
        }

        if (empty($value)) {
            throw new TransformationFailedException('Object identifier should not be empty');
        }

        try {
            $object = $this->objectManager->getRepository($this->className)->find($value);
        } catch (ORMException $exception) {
            throw new TransformationFailedException($exception->getMessage(), $exception->getCode(), $exception);
        } catch (DBALException $exception) {
            throw new TransformationFailedException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (null === $object) {
            throw new TransformationFailedException(sprintf('Object %s not found', $this->className));
        }

        return $object;
    }
}
