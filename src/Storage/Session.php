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

namespace Xloit\Bridge\Zend\Authentication\Storage;

use Zend\Authentication\Storage;
use Zend\Session\Container;
use Zend\Session\ManagerInterface as SessionManager;

/**
 * A {@link Session} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Storage
 */
class Session extends Storage\Session
{
    /**
     * Default session namespace.
     *
     * @var string
     */
    const NAMESPACE_DEFAULT = 'Xloit_Authentication';

    /**
     * Default number of seconds to make session sticky.
     *
     * @var int
     */
    protected $expiredTime = 1209600; // 2 weeks

    /**
     *
     *
     * @var mixed
     */
    protected $identity;

    /**
     * Constructor to prevent {@link Session} from being loaded more than once.
     *
     * @param mixed          $namespace
     * @param mixed          $member
     * @param SessionManager $manager
     */
    public function __construct(
        $namespace = self::NAMESPACE_DEFAULT, $member = self::MEMBER_DEFAULT, SessionManager $manager = null
    ) {
        parent::__construct($namespace, $member, $manager);
    }

    /**
     * Returns true if and only if storage is empty.
     *
     * @return bool
     */
    public function isEmpty()
    {
        if (parent::isEmpty()) {
            return true;
        }

        if (null === $this->read()) {
            $this->clear();

            return true;
        }

        return false;
    }

    /**
     * Returns the contents of storage. Behavior is undefined when storage is empty.
     *
     * @return mixed
     */
    public function read()
    {
        $identity = $this->identity;

        if (null === $identity) {
            $identity = parent::read();

            $this->identity = $identity;
        }

        return $identity;
    }

    /**
     * Will return the key of the identity.
     *
     * @return mixed
     */
    public function getIdentity()
    {
        return $this->read();
    }

    /**
     *
     *
     * @return SessionManager
     */
    public function getManager()
    {
        return $this->getStorageContainer()->getManager();
    }

    /**
     *
     *
     * @return Container
     */
    public function getStorageContainer()
    {
        return $this->session;
    }

    /**
     *
     *
     * @return int
     */
    public function getExpiredTime()
    {
        return $this->expiredTime;
    }

    /**
     *
     *
     * @param int $expiredTime
     *
     * @return $this
     */
    public function setExpiredTime($expiredTime)
    {
        $this->expiredTime = $expiredTime;

        return $this;
    }

    /**
     * Set the TTL (in seconds) for the session cookie expiry.
     *
     *
     * @param bool     $rememberMe
     * @param int|null $time
     *
     * @return $this
     */
    public function rememberMe($rememberMe = true, $time = null)
    {
        if ($rememberMe) {
            if ($time !== null) {
                $this->setExpiredTime($time);
            }

            $this->getManager()->rememberMe($this->getExpiredTime());
        }

        return $this;
    }

    /**
     * Set a 0s TTL for the session cookie.
     *
     * @return $this
     */
    public function forgetMe()
    {
        $this->getManager()->forgetMe();

        return $this;
    }
}
