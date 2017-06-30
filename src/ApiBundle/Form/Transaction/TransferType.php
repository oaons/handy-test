<?php

namespace ApiBundle\Form\Transaction;

use CoreBundle\Entity\Account;
use CoreBundle\Form\Type\ObjectIdentifierType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TransferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'to_account',
                ObjectIdentifierType::class,
                [
                    'class' => Account::class,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'description' => 'Transfer to account',
                ]
            )
            ->add(
                'amount',
                NumberType::class,
                [
                    'constraints' => [
                        new Assert\NotBlank(),
                        new Assert\Range(['min' => 0.0001]),
                    ],
                    'description' => 'Transfer amount',
                ]
            );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'csrf_protection' => false,
            ]
        );
    }
}
