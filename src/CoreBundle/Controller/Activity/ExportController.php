<?php

namespace Runalyze\Bundle\CoreBundle\Controller\Activity;

use Runalyze\Bundle\CoreBundle\Entity\Account;
use Runalyze\Export\File;
use Runalyze\Export\Share;
use Runalyze\View\Activity\Context;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    /**
     * @Route("/activity/{id}/export/social/{typeid}", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function exporterExportAction($id, $typeid, Account $account) {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        if (Share\Types::isValidValue((int)$typeid)) {
            $context = new Context((int)$id, $account->getId());
            $exporter = Share\Types::get((int)$typeid, $context);

            if ($exporter instanceof Share\AbstractSnippetSharer) {
                $exporter->display();
            }
        }

        return new Response();
    }

    /**
     * @Route("/activity/{id}/export/file/{typeid}", requirements={"id" = "\d+"})
     * @Security("has_role('ROLE_USER')")
     */
    public function fileExportAction($id, $typeid, Account $account) {
        $Frontend = new \Frontend(true, $this->get('security.token_storage'));

        if (File\Types::isValidValue((int)$typeid)) {
            $context = new Context((int)$id, $account->getId());
            $exporter = File\Types::get((int)$typeid, $context);

            if ($exporter instanceof File\AbstractFileExporter) {
                $exporter->downloadFile();
                exit;
            }
        }

        return new Response();
    }
}
