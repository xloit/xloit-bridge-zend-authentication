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

namespace Xloit\Bridge\Zend\Authentication;

use Zend\Authentication\AuthenticationService as ZendAuthenticationService;
use Zend\Authentication\Storage\StorageInterface;
use Zend\EventManager\EventInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Stdlib\RequestInterface as Request;
use Zend\Stdlib\ResponseInterface as Response;

/**
 * An {@link AuthenticationService} class.
 *
 * @package Xloit\Bridge\Zend\Authentication
 * @method Adapter\AdapterInterface getAdapter
 */
class AuthenticationService extends ZendAuthenticationService implements AuthenticationServiceInterface
{
    use EventManagerAwareTrait;

    /**
     *
     *
     * @var AuthenticationEvent
     */
    protected $event;

    /**
     * Persistent storage handler
     *
     * @var Request
     */
    protected $request;

    /**
     *
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     *
     *
     * @param Request $request
     *
     * @return static
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Get the auth event.
     *
     * @return AuthenticationEvent
     * @throws \Zend\EventManager\Exception\InvalidArgumentException
     */
    public function getEvent()
    {
        if (null === $this->event) {
            $event = new AuthenticationEvent();

            $this->setEvent($event);
        }

        return $this->event;
    }

    /**
     * Set an event to use during dispatch.
     * By default, will re-cast to AdapterChainEvent if another event type is provided.
     *
     * @param EventInterface $event
     *
     * @return static
     * @throws \Zend\EventManager\Exception\InvalidArgumentException
     */
    public function setEvent(EventInterface $event)
    {
        $target = $this;

        if (!($event instanceof AuthenticationEvent)) {
            $eventParams = $event->getParams();
            $eventTarget = $event->getTarget();

            if ($eventTarget) {
                $target = $eventTarget;
            }

            $event = new AuthenticationEvent();

            $event->setParams($eventParams);
        }

        $event->setIdentity(null)->setTarget($target);

        $this->event = $event;

        return $this;
    }

    /**
     * Returns the persistent storage handler.
     * Session storage is used by default unless a different storage adapter has been set.
     *
     * @return StorageInterface
     */
    public function getStorage()
    {
        if (null === $this->storage) {
            $this->setStorage(new Storage\Session(static::class));
        }

        return $this->storage;
    }

    /**
     * Authenticates against the supplied adapter.
     *
     * @param Adapter\AdapterInterface $adapter
     * @param Request                  $request
     *
     * @return AuthenticationResult
     * @throws Exception\AuthenticationStopException
     * @throws Exception\RuntimeException
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     * @throws \Zend\Authentication\Exception\ExceptionInterface
     * @throws \Zend\EventManager\Exception\InvalidArgumentException
     */
    public function authenticate(Adapter\AdapterInterface $adapter = null, Request $request = null)
    {
        // ZF-7546 - prevent multiple successive calls from storing inconsistent results Ensure storage has clean state.
        if ($this->hasIdentity()) {
            $this->clearIdentity();
        }

        $event = $this->prepareEvent($adapter, $request);

        $event->setResult($event->getAdapter()->authenticate());

        $result = $this->triggerEventResult(AuthenticationEvent::AUTH, $event);

        /** @var AuthenticationEvent $event */
        $event->setResult($result);

        if (!$result->isValid()) {
            return $this->triggerEventResult(AuthenticationEvent::AUTH_FAILED, $event);
        }

        $this->getStorage()->write($result->getIdentity());

        return $this->triggerEventResult(AuthenticationEvent::AUTH_SUCCESS, $event);
    }

    /**
     * Sign-in and provides an authentication result.
     *
     * @param string                        $username
     * @param string                        $password
     * @param Adapter\AdapterInterface|null $adapter
     * @param Request                       $request
     *
     * @return AuthenticationResult
     * @throws Exception\AuthenticationStopException
     * @throws Exception\RuntimeException
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     * @throws \Zend\Authentication\Exception\RuntimeException
     * @throws \Zend\Authentication\Exception\ExceptionInterface
     * @throws \Zend\EventManager\Exception\InvalidArgumentException
     */
    public function sign($username, $password, Adapter\AdapterInterface $adapter = null, Request $request = null)
    {
        /** @var Adapter\AdapterInterface $adapter */
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $adapter = $adapter ?: $this->getAdapter();

        $adapter->setIdentity($username);
        $adapter->setCredential($password);

        return $this->authenticate($adapter, $request);
    }

    /**
     * Logout and provides an authentication result.
     *
     * @param Adapter\AdapterInterface|null $adapter
     * @param Request                       $request
     *
     * @return AuthenticationResult
     * @throws Exception\AuthenticationStopException
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     * @throws \Zend\EventManager\Exception\InvalidArgumentException
     * @throws \Zend\Stdlib\Exception\InvalidArgumentException
     */
    public function logout(Adapter\AdapterInterface $adapter = null, Request $request = null)
    {
        $event = $this->prepareEvent($adapter, $request);

        $event->setResult($event->getAdapter()->logout());

        $this->clearIdentity();

        return $this->triggerEventResult(AuthenticationEvent::AUTH_LOGOUT, $event);
    }

    /**
     * Authenticates against the supplied adapter.
     *
     * @param string              $name
     * @param AuthenticationEvent $event
     *
     * @return AuthenticationResult
     * @throws Exception\AuthenticationStopException
     */
    protected function triggerEventResult($name, AuthenticationEvent $event)
    {
        $event->setName($name);

        $result = $this->getEventManager()->triggerEventUntil(
            function($value) {
                return $value instanceof Response || $value instanceof AuthenticationResult;
            },
            $event
        );

        $lastResult = $result->last();
        $response   = null;

        if ($lastResult instanceof Response && $result->stopped()) {
            $response = $lastResult;
        }

        if (!($lastResult instanceof AuthenticationResult)) {
            $lastResult = $event->getResult();
        }

        if (!($lastResult instanceof AuthenticationResult)) {
            throw new Exception\AuthenticationStopException(
                sprintf(
                    'Authentication was stopped without a valid result. Got "%s" instead',
                    is_object($lastResult) ? get_class($lastResult) : gettype($lastResult)
                )
            );
        }

        if ($response instanceof Response) {
            $lastResult->setResponse($response);
        }

        return $lastResult;
    }

    /**
     *
     *
     * @param Adapter\AdapterInterface $adapter
     * @param Request                  $request
     *
     * @return AuthenticationEvent
     * @throws Exception\RuntimeException
     * @throws \Zend\EventManager\Exception\InvalidArgumentException
     */
    protected function prepareEvent(Adapter\AdapterInterface $adapter = null, Request $request = null)
    {
        /** @var Adapter\AdapterInterface $adapter */
        /** @noinspection CallableParameterUseCaseInTypeContextInspection */
        $adapter = $adapter ?: $this->getAdapter();

        if (!$adapter) {
            throw new Exception\RuntimeException(
                sprintf(
                    'An adapter must be set or passed prior to calling %s()',
                    __METHOD__
                )
            );
        }

        $request = $request ?: $this->getRequest();
        $event   = $this->getEvent();

        $event->setIdentity(null)
              ->setAdapter($adapter)
              ->setTarget($this);

        if ($request) {
            $event->setRequest($request);
        }

        if ($this->hasIdentity()) {
            $event->setIdentity($this->getIdentity());
        }

        return $event;
    }
}
