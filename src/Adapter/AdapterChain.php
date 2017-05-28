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

use Traversable;
use Xloit\Bridge\Zend\Authentication\AuthenticationResult;
use Xloit\Bridge\Zend\Authentication\Exception;
use Xloit\Bridge\Zend\Authentication\Options\AuthenticationOptions;
use Zend\Stdlib\PriorityQueue;

/**
 * An {@link AdapterChain} class.
 *
 * @package Xloit\Bridge\Zend\Authentication\Adapter
 */
class AdapterChain extends AbstractAdapter implements AdapterChainInterface
{
    /**
     * Default priority at which adapters are added.
     *
     * @var int
     */
    const DEFAULT_PRIORITY = 1;

    /**
     * Adapter chain
     *
     * @var PriorityQueue
     */
    protected $adapters;

    /**
     * Array of authentication results.
     *
     * @var AuthenticationResult[]
     */
    protected $results = [];

    /**
     * Indicates whether to break the chain weh failure.
     *
     * @var bool
     */
    protected $breakChainOnFailure = true;

    /**
     * Constructor to prevent {@link AdapterChain} from being loaded more than once.
     *
     * @param array                       $adapters
     * @param array|AuthenticationOptions $options
     */
    public function __construct(array $adapters = [], $options = null)
    {
        parent::__construct($options);

        $this->setAdapters($adapters);
    }

    /**
     * Returns the value of BreakChainOnFailure.
     * If breakChainOnFailure is true, then if the adapter fails, the next adapter in the chain, if one exists,
     * will not be executed.
     *
     * @return bool
     */
    public function getBreakChainOnFailure()
    {
        return $this->breakChainOnFailure;
    }

    /**
     * Sets the value of BreakChainOnFailure.
     *
     * @param bool $breakChainOnFailure
     *
     * @return $this
     */
    public function setBreakChainOnFailure($breakChainOnFailure)
    {
        $this->breakChainOnFailure = (bool) $breakChainOnFailure;

        return $this;
    }

    /**
     *
     *
     * @param array $adapters
     *
     * @return $this
     */
    public function setAdapters(array $adapters)
    {
        $this->adapters = new PriorityQueue();

        foreach ($adapters as $priority => $adapter) {
            if (!is_numeric($priority)) {
                $priority = self::DEFAULT_PRIORITY;
            }

            $this->attach($adapter, (int) $priority);
        }

        return $this;
    }

    /**
     * Attach a adapter to the end of the chain.
     *
     * @param AdapterInterface $adapter
     * @param int              $priority Priority at which to enqueue adapter; defaults to 1 (higher executes earlier)
     *
     * @return $this
     */
    public function attach(AdapterInterface $adapter, $priority = self::DEFAULT_PRIORITY)
    {
        $this->adapters->insert($adapter, $priority);

        return $this;
    }

    /**
     * Adds a adapter to the beginning of the chain.
     *
     * @param AdapterInterface $adapter
     *
     * @return $this
     */
    public function prependAdapter(AdapterInterface $adapter)
    {
        $priority = self::DEFAULT_PRIORITY;

        if (!$this->adapters->isEmpty()) {
            $extractedNodes = $this->adapters->toArray(PriorityQueue::EXTR_PRIORITY);

            rsort($extractedNodes, SORT_NUMERIC);

            $priority = $extractedNodes[0] + 1;
        }

        $this->adapters->insert($adapter, $priority);

        return $this;
    }

    /**
     * Merge the adapter chain with the one given in parameter.
     *
     * @param AdapterChain $adaptersChain
     *
     * @return $this
     */
    public function merge(AdapterChain $adaptersChain)
    {
        $adapters = $adaptersChain->adapters->toArray(PriorityQueue::EXTR_BOTH);

        foreach ($adapters as $item) {
            $this->attach($item['data'], $item['priority']);
        }

        return $this;
    }

    /**
     * Performs an authentication attempt.
     * Adapters are run in the order in which they were added to the chain (FIFO).
     *
     * @return AuthenticationResult[]
     * @throws \Xloit\Bridge\Zend\Authentication\Exception\RuntimeException
     * @throws \Zend\Authentication\Adapter\Exception\ExceptionInterface
     */
    public function authenticate()
    {
        /** @noinspection IsEmptyFunctionUsageInspection */
        if (empty($this->adapters)) {
            throw new Exception\RuntimeException('A value for the adapter was not provided');
        }

        $this->results = [];

        $this->setup();

        foreach ($this->adapters as $adapter) {
            /** @var AdapterInterface $adapter */
            $result = $adapter->authenticate();

            $this->results[] = $result;

            if ($result->isValid()) {
                continue;
            }

            if ($this->breakChainOnFailure) {
                break;
            }
        }

        return $this->results;
    }

    /**
     *
     *
     * @return AuthenticationResult[]
     */
    public function logout()
    {
        $this->results = [];

        $this->storeAuthenticationResult(AuthenticationResult::LOGOUT);

        foreach ($this->adapters as $adapter) {
            /** @var AdapterInterface $adapter */
            $this->results[] = $adapter->logout();
        }

        /** @noinspection IsEmptyFunctionUsageInspection */
        if (empty($this->results)) {
            $this->results[] = $this->authenticateCreateAuthResult();
        }

        return $this->results;
    }

    /**
     * Return the count of attached adapters.
     *
     * @return int
     */
    public function count()
    {
        return count($this->adapters);
    }

    /**
     * Returns array of authentication results.
     *
     * @return AuthenticationResult[]
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Returns array of validation failure messages.
     *
     * @return array
     */
    public function getMessages()
    {
        $results  = $this->getResults();
        $messages = [];

        foreach ($results as $result) {
            /** @noinspection SlowArrayOperationsInLoopInspection */
            $messages = array_replace_recursive($messages, $result->getMessages());
        }

        return $messages;
    }

    /**
     * Get all the adapters.
     *
     * @return AdapterInterface[]
     */
    public function getAdapters()
    {
        return $this->adapters->toArray(PriorityQueue::EXTR_DATA);
    }

    /**
     * Removes all previously set adapters.
     *
     * @return void
     */
    public function clearAdapters()
    {
        $this->adapters = [];
    }

    /**
     * Deep clone handling.
     *
     * @return void
     */
    public function __clone()
    {
        $this->adapters = clone $this->adapters;
    }

    /**
     * Retrieve an external iterator.
     *
     * @link   http://php.net/manual/en/iteratoraggregate.getiterator.php
     *
     * @return Traversable An instance of an object implementing Iterator or Traversable.
     */
    public function getIterator()
    {
        return $this->adapters->getIterator();
    }

    /**
     * String representation of object.
     *
     * @link   http://php.net/manual/en/serializable.serialize.php
     *
     * @return string the string representation of the object or null.
     */
    public function serialize()
    {
        return serialize(
            [
                'adapters' => $this->adapters->toArray(PriorityQueue::EXTR_BOTH),
                'results'  => $this->results
            ]
        );
    }

    /**
     * Constructs the object.
     *
     * @link   http://php.net/manual/en/serializable.unserialize.php
     *
     * @param string $serialized The string representation of the object.
     *
     * @return void
     */
    public function unserialize($serialized)
    {
        $data          = unserialize($serialized);
        $this->results = [];

        /** @noinspection ForeachSourceInspection */
        foreach ($data['results'] as $item) {
            $this->results[] = $item;
        }

        /** @noinspection ForeachSourceInspection */
        foreach ($data['adapters'] as $item) {
            $this->adapters->insert($item['data'], $item['priority']);
        }
    }
}
