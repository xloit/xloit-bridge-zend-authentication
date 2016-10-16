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

use Zend\EventManager\Event;
use Zend\Stdlib\RequestInterface as Request;

/**
 * An {@link AuthenticationEvent} class.
 *
 * @package Xloit\Bridge\Zend\Authentication
 */
class AuthenticationEvent extends Event
{
    /**
     *
     *
     * @var string
     */
    const AUTH = 'authenticate';

    /**
     *
     *
     * @var string
     */
    const AUTH_FAILED = 'authenticate.failed';

    /**
     *
     *
     * @var string
     */
    const AUTH_LOGOUT = 'authenticate.logout';

    /**
     *
     *
     * @var string
     */
    const AUTH_SUCCESS = 'authenticate.success';

    /**
     * Returns the identity.
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->getParam('identity');
    }

    /**
     * Sets the identity.
     *
     * @param mixed $identity
     *
     * @return static
     */
    public function setIdentity($identity = null)
    {
        if (null === $identity) {
            $this->setResult();
        }

        $this->setParam('identity', $identity);

        return $this;
    }

    /**
     *
     *
     * @param AuthenticationResult $result
     *
     * @return static
     */
    public function setResult(AuthenticationResult $result = null)
    {
        if ($result && $result->getIdentity()) {
            $this->setIdentity($result->getIdentity());
        }

        $this->setParam('result', $result);

        return $this;
    }

    /**
     *
     *
     * @return AuthenticationResult
     */
    public function getResult()
    {
        return $this->getParam('result');
    }

    /**
     *
     *
     * @return Adapter\AdapterInterface
     */
    public function getAdapter()
    {
        return $this->getParam('adapter');
    }

    /**
     *
     *
     * @param Adapter\AdapterInterface $adapter
     *
     * @return static
     */
    public function setAdapter(Adapter\AdapterInterface $adapter)
    {
        $this->setParam('adapter', $adapter);

        return $this;
    }

    /**
     *
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->getParam('request');
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
        $this->setParam('request', $request);

        return $this;
    }
}
