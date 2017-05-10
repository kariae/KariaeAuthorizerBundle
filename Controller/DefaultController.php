<?php

namespace Kariae\AuthorizerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('KariaeAuthorizerBundle:Default:index.html.twig');
    }
}
