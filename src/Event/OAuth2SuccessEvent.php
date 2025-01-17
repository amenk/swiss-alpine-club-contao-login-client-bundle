<?php

declare(strict_types=1);

/*
 * This file is part of Swiss Alpine Club Contao Login Client Bundle.
 *
 * (c) Marko Cupic 2023 <m.cupic@gmx.ch>
 * @license MIT
 * For the full copyright and license information,
 * please view the LICENSE file that was distributed with this source code.
 * @link https://github.com/markocupic/swiss-alpine-club-contao-login-client-bundle
 */

namespace Markocupic\SwissAlpineClubContaoLoginClientBundle\Event;

use Markocupic\SwissAlpineClubContaoLoginClientBundle\Client\OAuth2Client;
use Symfony\Contracts\EventDispatcher\Event;

class OAuth2SuccessEvent extends Event
{
    public const NAME = 'sac_oauth2_client.oauth2_success';

    public function __construct(
        private readonly OAuth2Client $oAuth2Client,
    ) {
    }

    public function getOAuth2Client(): OAuth2Client
    {
        return $this->oAuth2Client;
    }
}
