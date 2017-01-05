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

namespace Xloit\Bridge\Zend\Authentication\Options;

use Xloit\Bridge\Zend\Authentication\AuthenticationResult as Result;
use Zend\Crypt\Password\Bcrypt;
use Zend\Crypt\Password\PasswordInterface;
use Zend\Stdlib\AbstractOptions as ZendAbstractOptions;

/**
 * An {@link AbstractOptions} abstract class.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\Authentication\Options
 */
abstract class AbstractOptions extends ZendAbstractOptions
{
    /**
     * Total time of seconds to make session sticky.
     *
     * @var int
     */
    const REMEMBER_ME_SECONDS = 1209600; // 2 weeks

    /**
     * Default number of seconds to make session sticky.
     *
     * @var int
     */
    protected $expiredTime = self::REMEMBER_ME_SECONDS;

    /**
     *
     *
     * @var array
     */
    protected $resultMessages = [
        Result::SUCCESS                    => 'Authentication success.',
        Result::FAILURE                    => 'General failure.',
        Result::FAILURE_IDENTITY_NOT_FOUND => 'An User account with the supplied identity could not be found.',
        Result::FAILURE_IDENTITY_AMBIGUOUS => 'More than one record matches the supplied identity.',
        Result::FAILURE_CREDENTIAL_INVALID => 'An User account with the supplied identity could not be found.',
        Result::FAILURE_UNCATEGORIZED      => 'Failure due to unknown reasons.',
        Result::FAILURE_NOT_VERIFIED       => 'Your account is not verified yet.',
        Result::FAILURE_BANNED             => 'Your account has been banned.',
        Result::FAILURE_LOGIN_ATTEMPT      => 'You exceeded the maximum allowed number of login attempt.',
        Result::LOGOUT                     => 'Logout success.'
    ];

    /**
     * Changed from 14 to 10 to prevent possible DOS attacks due to the high computational time.
     *
     * @link   http://timoh6.github.io/2013/11/26/Aggressive-password-stretching.html
     *
     * @var array
     */
    protected $cryptOptions = [
        'cost' => 10
    ];

    /**
     *
     *
     * @var PasswordInterface
     */
    protected $cryptService;

    /**
     *
     *
     * @param int $expiredTime
     *
     * @return static
     */
    public function setExpiredTime($expiredTime)
    {
        $this->expiredTime = $expiredTime;

        return $this;
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
     * @param array $cryptOptions
     *
     * @return static
     */
    public function setCryptOptions(array $cryptOptions)
    {
        $this->cryptOptions = $cryptOptions;

        return $this;
    }

    /**
     *
     *
     * @return array
     */
    public function getCryptOptions()
    {
        return $this->cryptOptions;
    }

    /**
     *
     *
     * @param PasswordInterface $cryptService
     *
     * @return static
     */
    public function setCryptService(PasswordInterface $cryptService)
    {
        $this->cryptService = $cryptService;

        return $this;
    }

    /**
     *
     *
     * @return PasswordInterface
     * @throws \Zend\Crypt\Password\Exception\InvalidArgumentException
     */
    public function getCryptService()
    {
        if (null === $this->cryptService) {
            $this->setCryptService(new Bcrypt($this->cryptOptions));
        }

        return $this->cryptService;
    }

    /**
     *
     *
     * @param int    $code
     * @param string $message
     *
     * @return static
     */
    public function setResultMessage($code, $message)
    {
        $this->resultMessages[$code] = $message;

        return $this;
    }

    /**
     *
     *
     * @param array $resultMessages
     *
     * @return static
     */
    public function setResultMessages(array $resultMessages)
    {
        foreach($resultMessages as $code => $message) {
            $this->resultMessages[$code] = $message;
        }

        return $this;
    }

    /**
     *
     *
     * @return array
     */
    public function getResultMessages()
    {
        return $this->resultMessages;
    }

    /**
     *
     *
     * @param int $code
     *
     * @return string
     */
    public function getResultMessage($code)
    {
        if (!array_key_exists($code, $this->resultMessages)) {
            $code = Result::FAILURE_UNCATEGORIZED;
        }

        return $this->resultMessages[$code];
    }
}
