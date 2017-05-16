<?php

namespace Kariae\AuthorizerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Kariae\AuthorizerBundle\Controller\AuthorizerControllerInterface;
use Kariae\AuthorizerBundle\Annotation\Authorize;

class DefaultController extends Controller implements AuthorizerControllerInterface
{
    /**
     * @Authorize(name="index")
     */
    public function indexAction()
    {
        return $this->render('KariaeAuthorizerBundle:Default:index.html.twig');
    }

    public function getName()
    {
        return "Default";
    }
}
