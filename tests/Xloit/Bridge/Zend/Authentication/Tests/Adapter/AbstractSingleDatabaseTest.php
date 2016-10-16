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

namespace Xloit\Bridge\Zend\Authentication\Tests\Adapter;

use Xloit\Bridge\Zend\Authentication;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Test class for {@link AbstractSingleDatabaseTest}.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\Authentication\Tests\Adapter
 */
abstract class AbstractSingleDatabaseTest extends AbstractDatabaseTest
{
    /**
     * Ensures expected behavior for authentication success
     */
    public function testAuthenticateSuccess()
    {
        $this->_adapter->setIdentity('username');
        $this->_adapter->setCredential('password');

        $result = $this->_adapter->authenticate();

        $this->assertTrue($result->isValid());
    }

    /**
     * Ensures expected behavior for for authentication failure
     * reason: Identity not found.
     */
    public function testAuthenticateFailureIdentityNotFound()
    {
        $this->_adapter->setIdentity('non_existent_username');
        $this->_adapter->setCredential('password');

        $result = $this->_adapter->authenticate();

        $this->assertEquals(Authentication\AuthenticationResult::FAILURE_IDENTITY_NOT_FOUND, $result->getCode());
    }

    /**
     * Ensures expected behavior for for authentication failure
     * reason: Identity not found.
     */
    public function testAuthenticateFailureIdentityAmbiguous()
    {
        $sqlInsert = 'INSERT INTO users (username, password, real_name) VALUES '
                     . '("username", "password", "My Real Name")';

        $this->_db->query($sqlInsert, DbAdapter::QUERY_MODE_EXECUTE);

        $this->_adapter->setIdentity('username');
        $this->_adapter->setCredential('password');

        $result = $this->_adapter->authenticate();

        $this->assertEquals(Authentication\AuthenticationResult::FAILURE_IDENTITY_AMBIGUOUS, $result->getCode());
    }

    /**
     * Ensures expected behavior for authentication failure because of a bad password
     */
    public function testAuthenticateFailureInvalidCredential()
    {
        $this->_adapter->setIdentity('username');
        $this->_adapter->setCredential('password_bad');

        $result = $this->_adapter->authenticate();

        $this->assertFalse($result->isValid());
    }

    /**
     * Ensure that exceptions are caught
     */
    public function testCatchExceptionNoIdentity()
    {
        $this->setExpectedException(
            Authentication\Exception\RuntimeException::class,
            'A value for the identity was not provided'
        );

        $this->_adapter->authenticate();
    }

    /**
     * Ensure that exceptions are caught
     */
    public function testCatchExceptionNoCredential()
    {
        $this->setExpectedException(
            Authentication\Exception\RuntimeException::class,
            'A credential value was not provided'
        );

        $this->_adapter->setIdentity('username');
        $this->_adapter->authenticate();
    }

    /**
     * Test to see same usernames with different passwords can not authenticate
     * when flag is not set. This is the current state of
     * Zend_Auth_Adapter_DbTable (up to ZF 1.10.6)
     *
     * @group   ZF-7289
     */
    public function testEqualUsernamesDifferentPasswordShouldNotAuthenticateWhenFlagIsNotSet()
    {
        $sqlInsert = 'INSERT INTO users (username, password, real_name) '
                     . 'VALUES ("username", "otherpass", "Test user 2")';
        $this->_db->query($sqlInsert, DbAdapter::QUERY_MODE_EXECUTE);

        // test if user 1 can authenticate
        $this->_adapter->setIdentity('username')
                       ->setCredential('password');

        $result = $this->_adapter->authenticate();

        $this->assertContains(
            'More than one record matches the supplied identity.',
            $result->getMessages()
        );
        $this->assertFalse($result->isValid());
    }

    /**
     * Test to see same usernames with different passwords can authenticate when
     * a flag is set
     *
     * @group   ZF-7289
     */
    public function testEqualUsernamesDifferentPasswordShouldAuthenticateWhenFlagIsSet()
    {
        $sqlInsert = 'INSERT INTO users (username, password, real_name) '
                     . 'VALUES ("username", "otherpass", "Test user 2")';
        $this->_db->query($sqlInsert, DbAdapter::QUERY_MODE_EXECUTE);

        // test if user 1 can authenticate
        $this->_adapter->setIdentity('username')
                       ->setCredential('password')
                       ->setAmbiguityIdentity(true);
        $result = $this->_adapter->authenticate();
        $this->assertNotContains(
            'More than one record matches the supplied identity.',
            $result->getMessages()
        );
        $this->assertTrue($result->isValid());
        $this->assertNotEmpty($result->getIdentity());
        $this->assertEquals('username', $result->getIdentity()['username']);

        $this->_adapter = null;
        $this->_setupAuthAdapter();

        // test if user 2 can authenticate
        $this->_adapter->setIdentity('username')
                       ->setCredential('otherpass')
                       ->setAmbiguityIdentity(true);
        $result2 = $this->_adapter->authenticate();
        $this->assertNotContains(
            'More than one record matches the supplied identity.',
            $result->getMessages()
        );
        $this->assertTrue($result2->isValid());
        $this->assertNotEmpty($result2->getIdentity());
        $this->assertEquals('username', $result2->getIdentity()['username']);
    }
}
