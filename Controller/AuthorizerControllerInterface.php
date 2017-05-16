<?php

/*
 * This file is part of the KariaeAuthorizerBundle package.
 *
 * (c) Zakariae Filali <filali.zakariae@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kariae\AuthorizerBundle\Controller;

/**
 * Interface to be implemented by any controller you want to set its
 * authorization using this bundle.
 *
 * @author Zakariae Filali <filali.zakariae@gmail.com>
 */
interface AuthorizerControllerInterface
{
    /**
     * Return the Controller name used in the UI.
     * @return string Controller name
     */
    public function getName();
}
