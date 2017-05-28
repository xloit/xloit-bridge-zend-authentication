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

use Zend\Authentication\Result;
use Zend\Stdlib\ResponseInterface as Response;

/**
 * An {@link AuthenticationResult} class.
 *
 * @package Xloit\Bridge\Zend\Authentication
 */
class AuthenticationResult extends Result
{
    /**
     * Failure due to identity is banned.
     *
     * @var int
     */
    const FAILURE_BANNED = -6;

    /**
     * Failure due to attempted login.
     *
     * @var int
     */
    const FAILURE_LOGIN_ATTEMPT = -8;

    /**
     * Failure due to not verified.
     *
     * @var int
     */
    const FAILURE_NOT_VERIFIED = -7;

    /**
     * Failure due to not verified.
     *
     * @var int
     */
    const LOGOUT = -5;

    /**
     *
     *
     * @var Response
     */
    protected $response;

    /**
     * Constructor to prevent {@link AuthenticationResult} from being loaded more than once.
     *
     * @param int   $code
     * @param mixed $identity
     * @param array $messages
     */
    public function __construct($code, $identity = null, array $messages = [])
    {
        parent::__construct($code, $identity, $messages);
    }

    /**
     *
     *
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     *
     *
     * @param Response $response
     *
     * @return $this
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }
}
