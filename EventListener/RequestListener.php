<?php

/*
 * This file is part of the KariaeAuthorizerBundle package.
 *
 * (c) Zakariae Filali <filali.zakariae@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kariae\AuthorizerBundle\EventListener;

use Kariae\AuthorizerBundle\Controller\AuthorizerControllerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Kariae\AuthorizerBundle\Helper\AuthorizerHelper;

/**
* Eventlistener triggered before every request
*/
class RequestListener
{
    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var AuthorizerHelper
     */
    protected $authorizerHelper;

    /**
     * @param AuthorizationChecker $authorizationChecker
     */
    public function __construct(AuthorizationChecker $authorizationChecker, AuthorizerHelper $authorizerHelper)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->authorizerHelper     = $authorizerHelper;
    }

    public function onKernelController(FilterControllerEvent $event)
    {
        $controllerObject = $event->getController();
        $controller       = $controllerObject[0];
        $action           = $this->authorizerHelper->getAction($controllerObject[1]);

        if (!is_array($controllerObject)) {
            return;
        }

        if ($controller instanceof AuthorizerControllerInterface) {
            if (!$this->authorizationChecker->isGranted($action, $controller)) {
                throw new AccessDeniedHttpException('This action needs a valid token!');
            }
        }
    }
}
