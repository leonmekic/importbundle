<?php

namespace Factory\InstallBundle\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends FrontendController
{
    /**
     * @Route("/factory_install")
     */
    public function indexAction(Request $request)
    {
//        $products =  \Pimcore\Model\DataObject\Product::getList();
    }
}
