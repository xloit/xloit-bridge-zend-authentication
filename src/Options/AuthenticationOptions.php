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

use Xloit\Bridge\Zend\Authentication\Exception;

/**
 * An {@link AuthenticationOptions} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Options
 */
class AuthenticationOptions extends AbstractOptions
{
    /**
     * Property to use for the identity.
     *
     * @var string
     */
    protected $identityProperty = 'username';

    /**
     * Property to use for the credential.
     *
     * @var string
     */
    protected $credentialProperty = 'password';

    /**
     *
     *
     * @return string
     */
    public function getIdentityProperty()
    {
        return $this->identityProperty;
    }

    /**
     *
     *
     * @param  string $identityProperty
     *
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public function setIdentityProperty($identityProperty)
    {
        if (!is_string($identityProperty) || !$identityProperty) {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided identity property is invalid, %s given', gettype($identityProperty))
            );
        }

        $this->identityProperty = $identityProperty;

        return $this;
    }

    /**
     *
     *
     * @return string
     */
    public function getCredentialProperty()
    {
        return $this->credentialProperty;
    }

    /**
     *
     *
     * @param  string $credentialProperty
     *
     * @return static
     * @throws Exception\InvalidArgumentException
     */
    public function setCredentialProperty($credentialProperty)
    {
        if (!is_string($credentialProperty) || !$credentialProperty) {
            throw new Exception\InvalidArgumentException(
                sprintf('Provided credential property is invalid, %s given', gettype($credentialProperty))
            );
        }

        $this->credentialProperty = $credentialProperty;

        return $this;
    }
}
