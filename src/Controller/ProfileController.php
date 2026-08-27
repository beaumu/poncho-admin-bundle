<?php

namespace Poncho\AdminBundle\Controller;

use Poncho\AdminBundle\Entity\BaseAdminUser;
use Poncho\AdminBundle\Lib\Controller\AdminController;
use Poncho\AdminBundle\PonchoAdminConfiguration;
use Poncho\AdminBundle\Service\UserManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use function Symfony\Component\Translation\t;

class ProfileController extends AdminController
{
    public const PROFILE_ROUTE = 'poncho_admin_profile_index';

    public function __construct(protected readonly UserManagerInterface $userManager, protected readonly PonchoAdminConfiguration $config)
    {
    }

    public function index(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user instanceof BaseAdminUser) {
            throw new NotFoundHttpException(\sprintf('Profile view are only available for fully authenticate %s user.', BaseAdminUser::class));
        }

        $settingsForm = $this->createForm($this->config->userProfileForm(), $user);
        $settingsForm->handleRequest($request);

        if ($settingsForm->isSubmitted() && $settingsForm->isValid()) {
            $this->userManager->updatePassword($user);
            $this->userManager->save($user);

            $this->toastSuccess(t('message.account_updated', [], 'PonchoAdmin'));

            return $this->redirectToRoute(self::PROFILE_ROUTE);
        }

        return $this->render('@PonchoAdmin/profile/index.html.twig', [
            'user' => $user,
            'settings_form' => $settingsForm->createView(),
        ]);
    }
}
