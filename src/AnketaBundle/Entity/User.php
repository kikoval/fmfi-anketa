<?php

namespace AnketaBundle\Entity;

/**
 * @orm:Entity
 */
class User
{
    /**
     * @orm:Id
     * @orm:Column(type="integer")
     * @orm:GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @orm:Column(type="string", length="255")
     */
    protected $name;
}