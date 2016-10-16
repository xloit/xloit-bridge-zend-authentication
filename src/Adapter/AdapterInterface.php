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

namespace Xloit\Bridge\Zend\Authentication\Adapter;

use Xloit\Bridge\Zend\Authentication\AuthenticationResult;
use Xloit\Bridge\Zend\Authentication\Options\AuthenticationOptions;
use Zend\Authentication\Adapter\AdapterInterface as ZendAdapterInterface;

/**
 * An {@link AdapterInterface} interface.
 *
 * @package Xloit\Bridge\Zend\Authentication\Adapter
 */
interface AdapterInterface extends ZendAdapterInterface
{
    /**
     *
     *
     * @param  array|AuthenticationOptions $options
     *
     * @return static
     */
    public function setOptions($options);

    /**
     *
     *
     * @return AuthenticationOptions
     */
    public function getOptions();

    /**
     * Returns the credential of the account being authenticated, or NULL if none is set.
     *
     * @return mixed
     */
    public function getCredential();

    /**
     * Sets the credential for binding.
     *
     * @param  mixed $credential
     *
     * @return static
     */
    public function setCredential($credential);

    /**
     * Returns the identity of the account being authenticated, or NULL if none is set.
     *
     * @return mixed
     */
    public function getIdentity();

    /**
     * Sets the identity for binding.
     *
     * @param  mixed $identity
     *
     * @return static
     */
    public function setIdentity($identity);

    /**
     * Authenticates against the supplied adapter.
     *
     * @return AuthenticationResult
     */
    public function logout();
}
