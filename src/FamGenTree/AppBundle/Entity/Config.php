<?php
/**
 * Created by Christoph Graupner <ch.graupner@workingdeveloper.net>.
 *
 * Copyright (c) 2015 FamilyGenTree
 */

namespace FamGenTree\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="config", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="uniq_section_config_key", columns={"section", "config_key"})}
 * )
 *
 */
class Config
{

    /**
     * @ORM\Id
     * @ORM\Column(type="integer", name="id_config")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var
     * @ORM\Column(type="string", name="section", length=50)
     */
    protected $section;

    /**
     * @var
     * @ORM\Column(type="string", name="config_key", length=255)
     */
    protected $key;

    /**
     * @var
     * @ORM\Column(type="text", nullable=true)
     */
    protected $value;

    /**
     * @return mixed
     */
    public function getSection()
    {
        return $this->section;
    }

    /**
     * @param mixed $section
     */
    public function setSection($section)
    {
        $this->section = $section;
    }

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }


}