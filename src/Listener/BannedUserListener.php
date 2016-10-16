<?php
/**
 * This source file is part of Xloit project.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License that is bundled with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * <http://www.opensource.org/licenses/mit-license.php>
 * If you did not receive a copy of the license and are unable to obtain it through the world-wide-web,
 * please send an email to <license@xloit.com> so we can send you a copy immediately.
 *
 * @license   MIT
 * @link      http://xloit.com
 * @copyright Copyright (c) 2016, Xloit. All rights reserved.
 */

namespace Xloit\Bridge\Zend\Authentication\Listener;

use Xloit\Bridge\Zend\Authentication\AuthenticationEvent;
use Xloit\Bridge\Zend\Authentication\AuthenticationResult;
use Xloit\Bridge\Zend\Authentication\Identity\UserBannableInterface;
use Xloit\Bridge\Zend\EventManager\Listener\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * A {@link BannedUserListener} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Listener
 */
class BannedUserListener extends AbstractListenerAggregate
{
    /**
     * Attach one or more listeners.
     * Implementors may add an optional $priority argument; the EventManager implementation will pass this to the
     * aggregate.
     *
     * @param EventManagerInterface $events
     * @param int                   $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = -500)
    {
        $this->listeners[] = $events->attach(
            AuthenticationEvent::AUTH,
            [
                $this,
                'onAuthentication'
            ],
            $priority
        );
    }

    /**
     *
     *
     * @param AuthenticationEvent $event
     *
     * @return void
     */
    public function onAuthentication($event)
    {
        $result = $event->getResult();

        if (!$result->isValid()) {
            return;
        }

        $identity = $result->getIdentity();

        if (($identity instanceof UserBannableInterface || method_exists($identity, 'isBanned'))
            && $identity->isBanned()
        ) {
            $event->setResult(
                new AuthenticationResult(
                    AuthenticationResult::FAILURE_BANNED,
                    $identity,
                    ['Your account has been banned.']
                )
            );
        }
    }
}
