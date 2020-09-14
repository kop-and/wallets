<?php

namespace App\Form;

use App\Entity\Account;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class TransactionAccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('fromAccount', EntityType::class, [
                'class'  => Account::class,
                'mapped' => false
            ])
            ->add('toAccount', EntityType::class, [
                'class'  => Account::class,
                'mapped' => false
            ])
            ->add('amount', NumberType::class, [
                'mapped' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Account::class,
            'csrf_protection' => false,
        ));
    }

    public function getName()
    {
        return 'account';
    }
}
