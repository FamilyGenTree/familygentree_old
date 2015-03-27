<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\SetupBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class FirstSettingsForm extends AbstractType
{
    /**
     * Returns the name of this type.
     *
     * @return string The name of this type
     */
    public function getName()
    {
        return 'fgt_setup_database';
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', 'text', array(
                                   'required' => true,
                                   'trim'     => true
                               )
            )
            ->add('userName', 'text', array(
                                'required' => true,
                                'trim'     => true
                            )
            )
            ->add('email', 'text', array(
                             'required' => true,
                             'trim'     => true
                         )
            )
            ->add('password', 'text', array(
                                'required' => true
                            )
            )->add('passwordRepeat', 'text', array(
                'required' => true
            ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
    }

}