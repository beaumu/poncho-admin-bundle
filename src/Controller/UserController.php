<?php

namespace Poncho\AdminBundle\Controller;

use Poncho\AdminBundle\Lib\Controller\AdminController;
use Poncho\AdminBundle\Lib\DataTable\ColumnActionBuilder;
use Poncho\AdminBundle\PonchoAdminConfiguration;
use Poncho\AdminBundle\Service\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

class UserController extends AdminController
{
    public function __construct(
        private readonly PonchoAdminConfiguration $config,
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function index(Request $request): Response
    {
        $table = $this->createTable($this->config->userTable());
        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getCallbackResponse();
        }

        return $this->render('@PonchoAdmin/datatable.html.twig', [
            'table' => $table,
        ]);
    }

    public function edit(Request $request, ?int $id = null): Response
    {
        if (null === $id) {
            $entity = $this->userManager->create();
        } else {
            $entity = $this->userManager->find($id);
            $this->throwNotFoundExceptionIfNull($entity);
        }

        $form = $this->createForm($this->config->userForm(), $entity, [
            'password_required' => null === $id,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updatePassword($entity);
            $this->userManager->save($entity);

            return $this->js()
                ->closeModal()
                ->reloadTable()
                ->toastSuccess(t('message.item_updated', [], 'PonchoAdmin'));
        }

        return $this->js()
            ->modal('@PonchoAdmin/user/edit.html.twig', [
                'form' => $form->createView(),
                'entity' => $entity,
            ]);
    }

    public function delete(Request $request, int $id): Response
    {
        $intention = ColumnActionBuilder::csrfIntention('poncho_admin_user_delete', ['id' => $id]);
        if (!$this->isCsrfTokenValid($intention, $request->query->getString('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $entity = $this->userManager->find($id);
        $this->throwNotFoundExceptionIfNull($entity);

        $this->userManager->delete($entity);

        return $this->js()
            ->closeModal()
            ->reloadTable()
            ->toastSuccess(t('message.item_deleted', [], 'PonchoAdmin'));
    }
}
