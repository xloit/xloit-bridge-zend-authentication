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

namespace Xloit\Bridge\Zend\Authentication\Tests\Fixture\Identity;

use Xloit\Bridge\Zend\Authentication\Identity\UserBannableInterface;
use Xloit\Bridge\Zend\Authentication\Identity\VerifiedInterface;

/**
 * An {@link AbstractUser} class.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\Authentication\Tests\Fixture\Identity
 */
abstract class AbstractUser implements UserBannableInterface, VerifiedInterface
{
    /**
     * Returns the username
     *
     */
    public function getUsername()
    {
        return 'username';
    }

    /**
     * Indicates whether the current user has been banned.
     *
     * @return boolean
     */
    public function isBanned()
    {
        return true;
    }

    /**
     * Indicates whether the current user has been verified.
     *
     * @return boolean
     */
    public function isVerified()
    {
        return false;
    }
}
