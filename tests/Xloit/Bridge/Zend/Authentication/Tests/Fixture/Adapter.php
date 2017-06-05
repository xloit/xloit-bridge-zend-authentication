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

namespace Xloit\Bridge\Zend\Authentication\Tests\Fixture;

use Xloit\Bridge\Zend\Authentication\Adapter\AbstractAdapter;

/**
 * A {@link Adapter} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Tests\Fixture
 */
class Adapter extends AbstractAdapter
{
    /**
     * Sets the value of Results.
     *
     * @param int   $code
     * @param mixed $identity
     * @param array $messages
     *
     * @return $this
     */
    public function setResults($code, $identity = null, array $messages = [])
    {
        $this->storeAuthenticationResult($code, $identity, $messages);

        return $this;
    }

    /**
     * Performs an authentication attempt
     *
     * @return \Zend\Authentication\Result
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface If authentication cannot be performed
     */
    public function authenticate()
    {
        return $this->authenticateCreateAuthResult();
    }
}
