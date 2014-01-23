<?php

/*
 * (c) Alexandre Quercia <alquerci@email.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * @author Alexandre Quercia <alquerci@email.com>
 */
class SplDoublyLinkedList implements Iterator, ArrayAccess, Countable
{
    const IT_MODE_LIFO      = 1;
    const IT_MODE_DELETE    = 2;
    const IT_MODE_FIFO      = 4;
    const IT_MODE_KEEP      = 8;

    /**
     * @var array
     */
    private $list;

    /**
     * @var integer
     */
    private $numberOfElements;

    /**
     * @var integer
     */
    private $flags;

    /**
     * Constructs a new doubly linked list
     */
    public function __construct()
    {
        $this->list = array();
        $this->numberOfElements = 0;
        $this->setIteratorMode(SplDoublyLinkedList::IT_MODE_FIFO | SplDoublyLinkedList::IT_MODE_KEEP);
    }

    /**
     * Returns the mode of iteration
     *
     * @return integer Returns the different modes and flags that affect the iteration.
     */
    public function getIteratorMode()
    {
        return $this->flags;
    }

    /**
     * @param integer $mode
     */
    public function setIteratorMode($mode)
    {
        $itMode = 0;

        if (($mode & SplDoublyLinkedList::IT_MODE_LIFO) === SplDoublyLinkedList::IT_MODE_LIFO) {
            $itMode = $itMode | SplDoublyLinkedList::IT_MODE_LIFO;
        } else {
            $itMode = $itMode | SplDoublyLinkedList::IT_MODE_FIFO;
        }

        if (($mode & SplDoublyLinkedList::IT_MODE_DELETE) === SplDoublyLinkedList::IT_MODE_DELETE) {
            $itMode = $itMode | SplDoublyLinkedList::IT_MODE_DELETE;
        } else {
            $itMode = $itMode | SplDoublyLinkedList::IT_MODE_KEEP;
        }

        $this->flags = $itMode;
    }

    /**
     * Peeks at the node from the beginning of the doubly linked list
     *
     * @return mixed
     *
     * @throws RuntimeException When the data-structure is empty
     */
    public function bottom()
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('The data-structure is empty');
        }

        return $this->list[0];
    }

    /**
     * @return mixed The value of the last node.
     *
     * @throws RuntimeException When the data-structure is empty.
     */
    public function top()
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('The data-structure is empty.');
        }

        return $this->list[$this->numberOfElements - 1];
    }

    /**
     * Pops a node from the end of the doubly linked list
     *
     * @return mixed The value of the popped node.
     *
     * @throws RuntimeException When the data-structure is empty.
     */
    public function pop()
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('The data-structure is empty.');
        }

        $this->numberOfElements--;
        return array_pop($this->list);
    }

    /**
     * Pushes an element at the end of the doubly linked list
     *
     * @param mixed $value
     */
    public function push($value)
    {
        $this->list[$this->numberOfElements] = $value;
        $this->numberOfElements++;
    }

    /**
     * @return mixed The value of the shifted node.
     *
     * @throws RuntimeException When the data-structure is empty.
     */
    public function shift()
    {
        if ($this->isEmpty()) {
            throw new RuntimeException('The data-structure is empty.');
        }

        $this->numberOfElements--;

        return array_shift($this->list);
    }

    /**
     * Prepends the doubly linked list with an element
     *
     * @param mixed $value The value to unshift.
     */
    public function unshift($value)
    {
        array_unshift($this->list);
        $this->numberOfElements++;
    }

    /**
     * Checks whether the doubly linked list is empty.
     *
     * @return boolean Returns whether the doubly linked list is empty.
     */
    public function isEmpty()
    {
        return 0 === $this->numberOfElements;
    }

    /**
     * Returns whether the requested $index exists
     *
     * @param mixed $index
     */
    public function offsetExists($index)
    {
        if (!is_numeric($index)) {
            return false;
        }

        return isset($this->list[(integer) $index]);
    }

    /**
     * Returns the value at the specified $index
     *
     * @param integer $index
     *
     * @return mixed
     *
     * @throws OutOfRangeException when index is out of bounds or when index cannot be parsed as an integer.
     */
    public function offsetGet($index)
    {
        if (!$this->offsetExists($index)) {
            throw new OutOfRangeException(sprintf('"%s" is out of bounds or when index cannot be parsed as an integer.', $index));
        }

        return $this->list[(integer) $index];
    }

    /**
     * Sets the value at the specified $index to $newval
     *
     * @param integer $index The index being set.
     * @param mixed   $newval The new value for the index.
     *
     * @throws OutOfRangeException when index is out of bounds or when index cannot be parsed as an integer.
     */
    public function offsetSet($index, $newval)
    {
        if (null === $index) {
            $this->push($newval);
            return;
        }

        if (!$this->offsetExists($index)) {
            throw new OutOfRangeException(sprintf('"%s" is out of bounds or when index cannot be parsed as an integer.', $index));
        }

        $this->list[(integer) $index] = $newval;
    }

    /**
     * Sets the value at the specified $index to $newval
     *
     * @param integer $index The index being unset.
     *
     * @throws OutOfRangeException when index is out of bounds or when index cannot be parsed as an integer.
     */
    public function offsetUnset($index)
    {
        if (!$this->offsetExists($index)) {
            throw new OutOfRangeException(sprintf('"%s" is out of bounds or when index cannot be parsed as an integer.', $index));
        }

        unset($this->list[(integer) $index]);
        $this->numberOfElements--;
    }

    /**
     * Counts the number of elements in the doubly linked list.
     *
     * @return integer Returns the number of elements in the doubly linked list.
     */
    public function count()
    {
        return $this->numberOfElements;
    }

    /**
     * Get the current doubly linked list node.
     *
     * @return mixed The current node value.
     */
    public function current()
    {
        return current($this->list);
    }

    /**
     * Return current node index
     *
     * @return number The current node index.
     */
    public function key()
    {
        return key($this->list);
    }

    /**
     * Move the iterator to the previous node.
     */
    public function prev()
    {
        if (($this->flags & SplDoublyLinkedList::IT_MODE_LIFO) === SplDoublyLinkedList::IT_MODE_LIFO) {
            next($this->list);
        } else {
            prev($this->list);
        }
    }

    /**
     * Move the iterator to the next node.
     */
    public function next()
    {
        if (($this->flags & SplDoublyLinkedList::IT_MODE_DELETE) === SplDoublyLinkedList::IT_MODE_DELETE) {
            $key = $this->key();
        }

        if (($this->flags & SplDoublyLinkedList::IT_MODE_LIFO) === SplDoublyLinkedList::IT_MODE_LIFO) {
            prev($this->list);
        } else {
            next($this->list);
        }

        if (($this->flags & SplDoublyLinkedList::IT_MODE_DELETE) === SplDoublyLinkedList::IT_MODE_DELETE) {
            unset($this[$key]);
        }
    }

    /**
     * Rewind iterator back to the start
     */
    public function rewind()
    {
        if (($this->flags & SplDoublyLinkedList::IT_MODE_LIFO) === SplDoublyLinkedList::IT_MODE_LIFO) {
            end($this->list);
        } else {
            reset($this->list);
        }
    }

    /**
     * Check whether the doubly linked list contains more nodes
     */
    public function valid()
    {
        return null === key($this->list) ? false : true;
    }

    public function serialize()
    {

    }

    public function unserialize($serialized)
    {

    }
}
