<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGeneTree\SetupBundle\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class FirstUserForm extends AbstractType {
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
            ->add('dbSystem', 'choice', array(
                'choices' => array(
                    'mysql' => 'MySql',
                    'pgsql' => 'PostgreSQL'
                )
            ))
            ->add('host', 'text', array(
                            'required' => true,
                            'trim'     => true
                        )
            )
            ->add('port', 'text', array(
                            'required' => true,
                            'trim'     => true
                        )
            )
            ->add('user', 'text', array(
                            'required' => true,
                            'trim'     => true
                        )
            )
            ->add('password', 'text', array(
                                'required' => false
                            )
            )
            ->add('dbname', 'text', array(
                              'required' => true,
                              'trim'     => true
                          )
            )
            ->add('prefix', 'text', array(
                              'required' => false,
                              'trim'     => true
                          )
            )->add('confirmedMigration', 'checkbox'
            );
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
    }

}