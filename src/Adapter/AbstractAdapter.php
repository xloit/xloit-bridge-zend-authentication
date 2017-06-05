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
use Zend\Authentication\Adapter\AbstractAdapter as BaseAbstractAdapter;

/**
 * An {@link AbstractAdapter} abstract class.
 *
 * @abstract
 * @package Xloit\Bridge\Zend\Authentication\Adapter
 */
abstract class AbstractAdapter extends BaseAbstractAdapter implements AdapterInterface
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
     * Constructor to prevent {@link AbstractAdapter} from being loaded more than once.
     *
     * @param array|AuthenticationOptions $options
     */
    public function __construct($options = null)
    {
        if (!$options) {
            $options = [];
        }

        $this->setOptions($options);
    }
}
