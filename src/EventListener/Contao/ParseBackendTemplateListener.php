<?php

declare(strict_types=1);

/*
 * This file is part of Swiss Alpine Club Contao Login Client Bundle.
 *
 * (c) Marko Cupic 2021 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/swiss-alpine-club-contao-login-client-bundle
 */

namespace Markocupic\SwissAlpineClubContaoLoginClientBundle\EventListener\Contao;

use Contao\BackendTemplate;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\System;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * @Hook("parseBackendTemplate")
 */
class ParseBackendTemplateListener
{

    private RequestStack $requestStack;
    private ContaoFramework $framework;

    /**
     * ParseBackendTemplateListener constructor.
     */
    public function __construct(RequestStack $requestStack, ContaoFramework $framework)
    {
        $this->requestStack = $requestStack;
        $this->framework = $framework;
    }

    /**
     * Add SSO login button to the backend login form.
     *
     * @param $strContent
     * @param $strTemplate
     *
     * @return mixed
     */
    public function __invoke($strContent, $strTemplate)
    {
        if ('be_login' === $strTemplate) {
            /** @var System $systemAdapter */
            $systemAdapter = $this->framework->getAdapter(System::class);

            /** @var Controller $controllerAdapter */
            $controllerAdapter = $this->framework->getAdapter(Controller::class);

            if (!$systemAdapter->getContainer()->getParameter('sac_oauth2_client.oidc.enable_backend_sso')) {
                return $strContent;
            }

            $template = new BackendTemplate('mod_swiss_alpine_club_oidc_backend_login');

            // Get request token (disabled by default)
            $template->rt = '';
            $template->enableCsrfTokenCheck = false;

            if ($systemAdapter->getContainer()->getParameter('sac_oauth2_client.oidc.enable_csrf_token_check')) {
                if (preg_match('/name="REQUEST_TOKEN"\s+value=\"([^\']*?)\"/', $strContent, $matches)) {
                    $template->rt = $matches[1];
                    $template->enableCsrfTokenCheck = true;
                }
            }

            $template->targetPath = '';

            if (preg_match('/name="_target_path"\s+value=\"([^\']*?)\"/', $strContent, $matches)) {
                $template->targetPath = $matches[1];
            }

            $template->failurePath = '';

            if (preg_match('/name="_failure_path"\s+value=\"([^\']*?)\"/', $strContent, $matches)) {
                $template->failurePath = $matches[1];
            }

            $template->alwaysUseTargetPath = '';

            if (preg_match('/name="_always_use_target_path"\s+value=\"([^\']*?)\"/', $strContent, $matches)) {
                $template->alwaysUseTargetPath = (string) $matches[1];
            }

            // Check for error messages
            $flashBagKey = $systemAdapter->getContainer()->getParameter('sac_oauth2_client.session.flash_bag_key');
            $session = $this->requestStack->getCurrentRequest()->getSession();
            $flashBag = $session->getFlashBag()->get($flashBagKey);

            if (\count($flashBag) > 0) {
                $arrError = [];

                foreach ($flashBag[0] as $k => $v) {
                    $arrError[$k] = $v;
                }
                $template->error = $arrError;
            }

            $searchString = '</form>';
            $strContent = str_replace($searchString, $searchString.$controllerAdapter->replaceInsertTags($template->parse()), $strContent);
        }

        return $strContent;
    }
}
