<?php

namespace Kariae\AuthorizerBundle\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Authorize
{
    /**
     * @Required
     *
     * @var string
     */
    public $name;
}
