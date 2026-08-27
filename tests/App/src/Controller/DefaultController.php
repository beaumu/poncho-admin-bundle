<?php

namespace Poncho\AdminBundle\Tests\App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Poncho\AdminBundle\Lib\Controller\AdminController;

#[Route('/')]
class DefaultController extends AdminController
{
    #[Route('', name: 'test.home')]
    public function index(): Response
    {
        return $this->render('@PonchoAdmin/layout.html.twig');
    }
}
