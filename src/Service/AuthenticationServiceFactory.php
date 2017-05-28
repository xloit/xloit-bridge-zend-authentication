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

namespace Xloit\Bridge\Zend\Authentication\Service;

use Interop\Container\ContainerInterface;
use Xloit\Bridge\Zend\Authentication\AuthenticationService;
use Xloit\Bridge\Zend\ServiceManager\AbstractFactory;

/**
 * An {@link AuthenticationServiceFactory} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Service
 */
class AuthenticationServiceFactory extends AbstractFactory
{
    /**
     * Create the instance service (v3).
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param array              $options
     *
     * @return AuthenticationService
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Std\Exception\RuntimeException
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $authenticationService = new AuthenticationService();

        if ($this->hasOption('storage')) {
            $authenticationService->setStorage($this->getOption('storage'));
        }

        if ($this->hasOption('adapter')) {
            $authenticationService->setAdapter($this->getOption('adapter'));
        }

        if ($this->hasOption('request')) {
            $authenticationService->setRequest($this->getOption('request'));
        } else {
            /** @var \Zend\Stdlib\RequestInterface $request */
            $request = $container->get('Request');

            $authenticationService->setRequest($request);
        }

        if ($this->hasOption('listeners')) {
            /** @var array $listeners */
            $listeners    = $this->getOption('listeners', false);
            $eventManager = $authenticationService->getEventManager();

            foreach ($listeners as $eventName => $listener) {
                $container->get($listener)->attach($eventManager);
            }
        }

        return $authenticationService;
    }
}
