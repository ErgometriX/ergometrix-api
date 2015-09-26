<?php

namespace Xaj\ErgoBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('XajErgoBundle:Default:index.html.twig', array('name' => $name));
    }
}
