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
class SplStack extends SplDoublyLinkedList
{
    public function __construct()
    {
        parent::setIteratorMode(SplDoublyLinkedList::IT_MODE_LIFO);
    }

    public function setIteratorMode($mode)
    {
        $itMode = SplDoublyLinkedList::IT_MODE_LIFO;

        if (($mode & SplDoublyLinkedList::IT_MODE_DELETE) === SplDoublyLinkedList::IT_MODE_DELETE) {
            $itMode = $itMode | SplDoublyLinkedList::IT_MODE_DELETE;
        } else {
            $itMode = $itMode | SplDoublyLinkedList::IT_MODE_KEEP;
        }

        parent::setIteratorMode($itMode);
    }
}
