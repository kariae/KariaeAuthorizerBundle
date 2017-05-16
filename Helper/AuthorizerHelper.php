<?php

/*
 * This file is part of the KariaeAuthorizerBundle package.
 *
 * (c) Zakariae Filali <filali.zakariae@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Kariae\AuthorizerBundle\Helper;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Router;
use Doctrine\Common\Annotations\AnnotationReader;
use Kariae\AuthorizerBundle\Annotation\Auth as AuthAnnotation;
use Kariae\AuthorizerBundle\Controller\AuthorizerControllerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

/**
* Authorizer helper class
*/
class AuthorizerHelper
{
    /** @var $router Router */
    private $router;
    /** @var $annotationReader AnnotationReader */
    private $annotationReader;

    private $redisHost;
    private $redisPrefix = 'auth-';
    private $cacheDuration = 604800; // 7 days

    public function __construct(Router $router, string $redisHost)
    {
        $this->router           = $router;
        $this->redisHost        = $redisHost;
        $this->annotationReader = new AnnotationReader();
    }

    /**
     * Get Controllers list to authorize
     * @return array
     */
    public function getControllersList()
    {
        $list       = [];
        $collection = $this->router->getRouteCollection();
        $allRoutes  = $collection->all();

        foreach ($allRoutes as $route) {
            $defaults = $route->getDefaults();
            if (isset($defaults['_controller'])) {
                $controllerAction = explode('::', $defaults['_controller']);
                $controller = $controllerAction[0];
                if (class_exists($controller)) {
                    $controllerObject = new $controller();

                    if ($controllerObject instanceof AuthorizerControllerInterface) {
                        $action = $controllerAction[1];
                        $data = $this->checkClassMethods($controllerObject, $action);

                        if ($data) {
                            $this->addController($data, $list);
                        }
                    }
                }
            }
        }

        return $list;
    }

    /**
     * Get action name of a method in a Controller
     * @param string $methodName
     * @return string|null
     */
    public function getAction(string $methodName)
    {
        $prefix = "Action";
        $prefixLength = -6;

        if (substr($methodName, $prefixLength) == $prefix) {
            return substr($methodName, 0, $prefixLength);
        }

        return null;
    }

    /**
     * Set action name of a method in a Controller
     * @param string $methodName
     * @return string|null
     */
    public function setAction(string $methodName)
    {
        $prefix = "Action";

        return $methodName . $prefix;
    }

    /**
     * Get list of methods to authorize for a given Controller
     * @param Controller $controllerObject
     * @param string $action
     * @return array
     */
    private function checkClassMethods(Controller $controllerObject, string $action)
    {
        $reflectedMethod = new \ReflectionMethod($controllerObject, $action);

        $methodAuthAnnotation = $this->annotationReader->getMethodAnnotation($reflectedMethod, AuthAnnotation::class);

        if ($methodAuthAnnotation) {
            $data = [
                "controllerName" =>  $controllerObject->getName(),
                "controller"     =>  get_class($controllerObject),
                "action"         =>  $methodAuthAnnotation->name,
            ];

            return $data;
        }

        return false;
    }

    /**
     * Add {Controller, Action} to given list
     * @param array $data
     * @param array &$list
     */
    private function addController(array $data, array &$list)
    {
        if (array_search($data, $list) > -1) {
            throw new \Exception("The couple {Controller, Action} {".$data['controller'].", ".$data['action']."} is not unique", 1);
        }

        $list[] = $data;
    }
}
