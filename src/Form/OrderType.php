<?php

namespace App\Form;

use App\Entity\City;
use App\Entity\Order;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', null, [
                'label' => 'First Name',
                'attr' => ['placeholder' => 'Enter first name','class'=>'form-control']
            ])
            ->add('lastName', null, [
                'label' => 'Last Name',
                'attr' => ['placeholder' => 'Enter last name','class'=>'form-control']
            ])
            ->add('phone', null, [
                'label' => 'Phone',
                'attr' => ['placeholder' => 'Enter phone','class'=>'form-control']
            ])
            ->add('adresse', null, [
                'label' => 'Address',
                'attr' => ['placeholder' => 'Enter address','class'=>'form-control']
            ])
            // ->add('createdAt', null, [
            //     'widget' => 'single_text'
            // ])
            ->add('city', EntityType::class, [
                'class' => City::class,
                'choice_label' => 'name',
                'attr' => ['class'=>'form-control']
            ])
            ->add('payOnDelivery', null, [
                'label' => 'Pay on Delivery',
                'attr' => ['class'=>'form-check-input']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
