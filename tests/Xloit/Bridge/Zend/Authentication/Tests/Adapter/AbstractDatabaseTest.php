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

use PDO;
use Zend\Db\Adapter\Adapter as DbAdapter;

/**
 * Test class for {@link AbstractDatabaseTest}.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\Authentication\Tests\Adapter
 */
abstract class AbstractDatabaseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * SQLite database connection
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $_db = null;

    /**
     * Database table authentication adapter
     *
     * @var \Xloit\Bridge\Zend\Authentication\Adapter\AdapterInterface
     */
    protected $_adapter = null;

    /**
     * Set up test configuration
     */
    public function setUp()
    {
        if (!getenv('TESTS_AUTH_ADAPTER_DB_PDO_SQLITE_ENABLED')) {
            $this->markTestSkipped('Tests are not enabled in phpunit.xml');

            return;
        } elseif (!extension_loaded('pdo')) {
            $this->markTestSkipped('PDO extension is not loaded');

            return;
        } elseif (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('SQLite PDO driver is not available');

            return;
        }

        $this->_setupDbAdapter();
        $this->_setupAuthAdapter();
    }

    /**
     * Tears down the fixture, for example, close a network connection.
     */
    public function tearDown()
    {
        $this->_adapter = null;

        if ($this->_db instanceof DbAdapter) {
            $this->_db->query('DROP TABLE [users]');
        }

        $this->_db = null;
    }

    protected function _setupDbAdapter($optionalParams = [])
    {
        $params = [
            'driver' => 'pdo_sqlite',
            'dbname' => getenv('TESTS_AUTH_ADAPTER_DB_PDO_SQLITE_DATABASE')
        ];

        /** @noinspection IsEmptyFunctionUsageInspection */
        if (!empty($optionalParams)) {
            $params['options'] = $optionalParams;
        }

        $this->_db = new DbAdapter($params);

        $sqlCreate = 'CREATE TABLE IF NOT EXISTS [users] ( '
                     . '[id] INTEGER  NOT NULL PRIMARY KEY, '
                     . '[username] VARCHAR(50) NOT NULL, '
                     . '[password] VARCHAR(32) NULL, '
                     . '[real_name] VARCHAR(150) NULL)';
        $this->_db->query($sqlCreate, DbAdapter::QUERY_MODE_EXECUTE);

        $sqlDelete = 'DELETE FROM users';
        $this->_db->query($sqlDelete, DbAdapter::QUERY_MODE_EXECUTE);

        $sqlInsert = 'INSERT INTO users (username, password, real_name) '
                     . 'VALUES ("username", "password", "My Real Name")';
        $this->_db->query($sqlInsert, DbAdapter::QUERY_MODE_EXECUTE);
    }

    abstract protected function _setupAuthAdapter();
}
