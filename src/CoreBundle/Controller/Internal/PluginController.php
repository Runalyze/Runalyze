<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Internal;

use Runalyze\Bundle\CoreBundle\Controller\AbstractPluginsAwareController;
use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Bundle\CoreBundle\Entity\Plugin;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/_internal/plugin")
 */
class PluginController extends AbstractPluginsAwareController
{
    /**
     * @return \Runalyze\Bundle\CoreBundle\Entity\PluginRepository
     */
    protected function getPluginRepository()
    {
        return $this->getDoctrine()->getRepository('CoreBundle:Plugin');
    }

    /**
     * @Route("/toggle/{id}", name="internal-plugin-toggle")
     * @ParamConverter("plugin", class="CoreBundle:Plugin")
     * @Security("has_role('ROLE_USER')")
     */
    public function togglePanelAction(Plugin $plugin, Account $account)
    {
        if ($plugin->getAccount()->getId() != $account->getId()) {
            return $this->createAccessDeniedException();
        }

        // Only as long as PluginFactory's cache is in use
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        \PluginFactory::clearCache();

        $this->getPluginRepository()->toggleHidden($plugin);

        return new JsonResponse();
    }

    /**
     * @Route("/move/{id}/up", name="internal-plugin-move-up")
     * @ParamConverter("plugin", class="CoreBundle:Plugin")
     * @Security("has_role('ROLE_USER')")
     */
    public function movePanelUpAction(Plugin $plugin, Account $account)
    {
        if ($plugin->getAccount()->getId() != $account->getId()) {
            return $this->createAccessDeniedException();
        }

        // Only as long as PluginFactory's cache is in use
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        \PluginFactory::clearCache();

        $this->getPluginRepository()->movePanelUp($plugin);

        return new JsonResponse();
    }

    /**
     * @Route("/move/{id}/down", name="internal-plugin-move-down")
     * @ParamConverter("plugin", class="CoreBundle:Plugin")
     * @Security("has_role('ROLE_USER')")
     */
    public function movePanelDownAction(Plugin $plugin, Account $account)
    {
        if ($plugin->getAccount()->getId() != $account->getId()) {
            return $this->createAccessDeniedException();
        }

        // Only as long as PluginFactory's cache is in use
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));
        \PluginFactory::clearCache();

        $this->getPluginRepository()->movePanelDown($plugin);

        return new JsonResponse();
    }

    /**
     * @Route("/all-panels", name="internal-plugin-all-panels")
     * @Security("has_role('ROLE_USER')")
     */
    public function contentPanelsAction(Request $request, Account $account)
    {
        $Frontend = new \Frontend(false, $this->get('security.token_storage'));

        return $this->getResponseForAllEnabledPanels($request, $account);
    }
}
