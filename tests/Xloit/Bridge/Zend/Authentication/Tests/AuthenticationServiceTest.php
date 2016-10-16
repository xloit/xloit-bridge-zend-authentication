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

namespace Xloit\Bridge\Zend\Authentication\Tests;

use Xloit\Bridge\Zend\Authentication\AuthenticationResult;
use Xloit\Bridge\Zend\Authentication\AuthenticationService;
use Xloit\Bridge\Zend\Authentication\Listener;
use Zend\Authentication\Storage\Session;
use Zend\EventManager\ListenerAggregateInterface;

/**
 * Test class for {@link AuthenticationServiceTest}.
 *
 * @package Xloit\Bridge\Zend\Authentication\Tests
 */
class AuthenticationServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Holds the authentication service value.
     *
     * @var AuthenticationService
     */
    protected $auth;

    /**
     * Sets up the fixture, for example, open a network connection.
     *
     * @return void
     */
    public function setUp()
    {
        $this->auth = new AuthenticationService(new Fixture\SessionStorage());
    }

    /**
     * Ensures that getStorage() returns Zend_Auth_Storage_Session
     *
     * @return void
     */
    public function testGetStorage()
    {
        $storage = $this->auth->getStorage();

        $this->assertInstanceOf(Session::class, $storage);
    }

    public function testAdapter()
    {
        $this->assertNull($this->auth->getAdapter());

        $successAdapter = new Fixture\SuccessAdapter();
        $authService    = $this->auth->setAdapter($successAdapter);

        $this->assertSame($authService, $this->auth);
        $this->assertSame($successAdapter, $this->auth->getAdapter());
    }

    /**
     * Ensures expected behavior for successful authentication
     *
     * @return void
     */
    public function testAuthenticate()
    {
        $result = $this->authenticate();

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertTrue($this->auth->hasIdentity());
        $this->assertEquals('someIdentity', $this->auth->getIdentity());
    }

    public function testAuthenticateSetAdapter()
    {
        $result = $this->authenticate(new Fixture\SuccessAdapter());

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertTrue($this->auth->hasIdentity());
        $this->assertEquals('someIdentity', $this->auth->getIdentity());
    }

    public function testFailureAuthenticate()
    {
        $adapter = new Fixture\Adapter();

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertFalse($this->auth->hasIdentity());
        $this->assertEquals(null, $this->auth->getIdentity());
    }

    public function testAuthenticateBannedUserListenerUserInvalid()
    {
        $this->attach(new Listener\BannedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserInvalid;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertFalse($this->auth->hasIdentity());
        $this->assertEquals(null, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::FAILURE_BANNED, $result->getCode());
    }

    public function testAuthenticateBannedUserListenerUserUnverifiedNotBanned()
    {
        $this->attach(new Listener\BannedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserUnverifiedNotBanned;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertTrue($this->auth->hasIdentity());
        $this->assertEquals($user, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::SUCCESS, $result->getCode());
    }

    public function testAuthenticateBannedUserListenerUserValid()
    {
        $this->attach(new Listener\BannedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserValid;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertTrue($this->auth->hasIdentity());
        $this->assertEquals($user, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::SUCCESS, $result->getCode());
    }

    public function testAuthenticateBannedUserListenerUserVerifiedBanned()
    {
        $this->attach(new Listener\BannedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserVerifiedBanned;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertFalse($this->auth->hasIdentity());
        $this->assertEquals(null, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::FAILURE_BANNED, $result->getCode());
    }

    public function testAuthenticateVerifiedUserListenerUserInvalid()
    {
        $this->attach(new Listener\VerifiedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserInvalid;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertFalse($this->auth->hasIdentity());
        $this->assertEquals(null, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::FAILURE_NOT_VERIFIED, $result->getCode());
    }

    public function testAuthenticateVerifiedUserListenerUserUnverifiedNotBanned()
    {
        $this->attach(new Listener\VerifiedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserUnverifiedNotBanned;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertFalse($result->isValid());
        $this->assertFalse($this->auth->hasIdentity());
        $this->assertEquals(null, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::FAILURE_NOT_VERIFIED, $result->getCode());
    }

    public function testAuthenticateVerifiedUserListenerUserValid()
    {
        $this->attach(new Listener\VerifiedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserValid;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertTrue($this->auth->hasIdentity());
        $this->assertEquals($user, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::SUCCESS, $result->getCode());
    }

    public function testAuthenticateVerifiedUserListenerUserVerifiedBanned()
    {
        $this->attach(new Listener\VerifiedUserListener());

        $adapter = new Fixture\Adapter();
        $user = new Fixture\Identity\UserVerifiedBanned;

        $adapter->setResults(AuthenticationResult::SUCCESS, $user);

        $result = $this->authenticate($adapter);

        $this->assertInstanceOf(AuthenticationResult::class, $result);
        $this->assertTrue($result->isValid());
        $this->assertTrue($this->auth->hasIdentity());
        $this->assertEquals($user, $this->auth->getIdentity());
        $this->assertEquals(AuthenticationResult::SUCCESS, $result->getCode());
    }

    /**
     * Ensures expected behavior for clearIdentity()
     *
     * @return void
     */
    public function testClearIdentity()
    {
        $this->authenticate();
        $this->auth->clearIdentity();

        $this->assertFalse($this->auth->hasIdentity());
        $this->assertEquals(null, $this->auth->getIdentity());
    }

    protected function authenticate($adapter = null)
    {
        if ($adapter === null) {
            $adapter = new Fixture\SuccessAdapter();
        }

        return $this->auth->authenticate($adapter);
    }

    protected function attach(ListenerAggregateInterface $listener)
    {
        $listener->attach($this->auth->getEventManager());
    }

}
