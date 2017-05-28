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
use Zend\Authentication\Adapter\DbTable\CredentialTreatmentAdapter;

/**
 * A {@link DbTable} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Adapter
 */
class DbTable extends CredentialTreatmentAdapter implements AdapterInterface
{
    use AdapterTrait;

    /**
     *
     *
     * @var array
     */
    protected $authenticateResultInfo = [
        'code'     => AuthenticationResult::FAILURE,
        'identity' => null,
        'messages' => []
    ];

    /**
     * This method abstracts the steps involved with making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @return bool
     * @throws Exception\RuntimeException in the event that setup was not done properly.
     */
    protected function authenticateSetup()
    {
        $exception = null;

        /** @noinspection IsEmptyFunctionUsageInspection */
        if (empty($this->tableName)) {
            $exception = 'A table must be supplied for the DbTable authentication adapter.';
        } /** @noinspection IsEmptyFunctionUsageInspection */ elseif (empty($this->identityColumn)) {
            $exception = 'An identity column must be supplied for the DbTable authentication adapter.';
        } /** @noinspection IsEmptyFunctionUsageInspection */ elseif (empty($this->credentialColumn)) {
            $exception = 'A credential column must be supplied for the DbTable authentication adapter.';
        }

        if (null !== $exception) {
            throw new Exception\RuntimeException($exception);
        }

        $this->setup();

        return true;
    }

    /**
     * This method attempts to validate that the record in the resultset is indeed a record that matched the identity
     * provided to this adapter.
     *
     * @param  array $resultIdentity
     *
     * @return AuthenticationResult
     */
    protected function authenticateValidateResult($resultIdentity)
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($resultIdentity['zend_auth_credential_match'] != '1') {
            $this->storeAuthenticationResult(
                AuthenticationResult::FAILURE_CREDENTIAL_INVALID,
                null,
                ['Supplied credential is invalid.']
            );
        } else {
            unset($resultIdentity['zend_auth_credential_match']);

            $this->resultRow = $resultIdentity;

            $this->storeAuthenticationResult(
                AuthenticationResult::SUCCESS,
                $resultIdentity
            );
        }

        return $this->authenticateCreateAuthResult();
    }
}
