<?php

namespace Poncho\AdminBundle\Tests\App\Controller;

use Poncho\AdminBundle\Lib\Controller\AdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class DefaultController extends AdminController
{
    #[Route('', name: 'test.home')]
    public function index(): Response
    {
        return $this->render('@PonchoAdmin/layout.html.twig');
    }
}
