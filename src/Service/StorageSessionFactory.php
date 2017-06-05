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
use Xloit\Bridge\Zend\Authentication\Storage\Session;
use Xloit\Bridge\Zend\ServiceManager\AbstractFactory;
use Zend\Session\Container as SessionContainer;

/**
 * A {@link StorageSessionFactory} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Service
 */
class StorageSessionFactory extends AbstractFactory
{

    /**
     * Create the instance service (v3).
     *
     * @param ContainerInterface $container
     * @param string             $name
     * @param array              $options
     *
     * @return Session
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Xloit\Std\Exception\RuntimeException
     * @throws \Zend\Session\Exception\InvalidArgumentException
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $constructorConfig = $this->getConstructorConfig($container);
        $storage           = new Session(
            $constructorConfig['namespace'], $constructorConfig['member'], $constructorConfig['sessionManager']
        );

        return $storage;
    }

    /**
     *
     *
     * @param ContainerInterface $serviceLocator
     *
     * @return array
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     * @throws \Xloit\Bridge\Zend\ServiceManager\Exception\StateException
     * @throws \Xloit\Std\Exception\RuntimeException
     * @throws \Zend\Session\Exception\InvalidArgumentException
     */
    public function getConstructorConfig(ContainerInterface $serviceLocator)
    {
        $configuration = [
            'namespace'      => null,
            'member'         => null,
            'sessionManager' => null
        ];

        if ($this->hasOption('namespace')) {
            $configuration['namespace'] = $this->getOption('namespace', false);
        }

        if ($this->hasOption('member')) {
            $configuration['member'] = $this->getOption('member', false);
        }

        if ($this->hasOption('sessionManager')) {
            $configuration['sessionManager'] = $this->getOption('sessionManager');
        }

        if (is_string($configuration['sessionManager'])) {
            $configuration['sessionManager'] = $serviceLocator->get($configuration['sessionManager']);
        }

        if (null === $configuration['sessionManager']) {
            $configuration['sessionManager'] = SessionContainer::getDefaultManager();
        }

        return $configuration;
    }
}
