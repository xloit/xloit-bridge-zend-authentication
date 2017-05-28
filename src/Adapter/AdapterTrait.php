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
use Xloit\Bridge\Zend\Authentication\Exception;
use Xloit\Bridge\Zend\Authentication\Options\AuthenticationOptions;

/**
 * A {@link AdapterTrait} trait.
 *
 * @package Xloit\Bridge\Zend\Authentication\Adapter
 */
trait AdapterTrait
{
    /**
     *
     *
     * @var AuthenticationOptions
     */
    protected $options;

    /**
     * Sets the identity for binding.
     *
     * @param mixed $identity
     *
     * @return $this
     */
    abstract public function setIdentity($identity);

    /**
     * Sets the credential for binding.
     *
     * @param mixed $credential
     *
     * @return $this
     */
    abstract public function setCredential($credential);

    /**
     *
     *
     * @param array|AuthenticationOptions $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        if (!($options instanceof AuthenticationOptions)) {
            $options = new AuthenticationOptions($options);
        }

        $this->options = $options;

        return $this;
    }

    /**
     *
     *
     * @return AuthenticationOptions
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * This method abstracts the steps involved with making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @return void
     * @throws \Xloit\Bridge\Zend\Authentication\Exception\RuntimeException
     */
    protected function setup()
    {
        if (null === $this->getIdentity()) {
            throw new Exception\RuntimeException('A value for the identity was not provided');
        }

        if (null === $this->getCredential()) {
            throw new Exception\RuntimeException('A credential value was not provided');
        }

        $this->storeAuthenticationResult(AuthenticationResult::FAILURE_UNCATEGORIZED);
    }

    /**
     * Authenticates against the supplied adapter.
     *
     * @return AuthenticationResult
     */
    public function logout()
    {
        $this->storeAuthenticationResult(AuthenticationResult::LOGOUT);

        $this->setIdentity(null)->setCredential(null);

        return $this->authenticateCreateAuthResult();
    }

    /**
     *
     *
     * @param int   $code
     * @param mixed $identity
     * @param array $messages
     *
     * @return void
     */
    protected function storeAuthenticationResult($code, $identity = null, array $messages = [])
    {
        $this->authenticateResultInfo['code']     = $code;
        $this->authenticateResultInfo['identity'] = $identity;
        $this->authenticateResultInfo['messages'] = $messages;
    }

    /**
     * Creates a {@link AuthenticationResult} object from the information that has been collected during the
     * authenticate() or logout() attempt.
     *
     * @return AuthenticationResult
     */
    protected function authenticateCreateAuthResult()
    {
        if ($this->options
            && (
                !array_key_exists('messages', $this->authenticateResultInfo)
                || empty($this->authenticateResultInfo['messages'])
            )
        ) {
            $this->authenticateResultInfo['messages'] = [
                $this->options->getResultMessage($this->authenticateResultInfo['code'])
            ];
        }

        return new AuthenticationResult(
            $this->authenticateResultInfo['code'],
            $this->authenticateResultInfo['identity'],
            $this->authenticateResultInfo['messages']
        );
    }
}
