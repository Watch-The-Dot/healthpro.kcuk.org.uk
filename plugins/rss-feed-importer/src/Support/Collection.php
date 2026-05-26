<?php

namespace WatchTheDot\Plugins\RSSImporter\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * Mutable Collection
 * 
 * @template T
 */
class Collection implements ArrayAccess, Countable, IteratorAggregate {
    /**
     * @param T[] $items
     */
    public function __construct(
        private array $items = []
    ) {}

    public function map( callable $callback ): self {
        $this->items = array_map( $callback, $this->items );

        return $this;
    }

    public function filter( callable $callback ): self {
        $this->items = array_filter( $this->items, $callback );
        
        return $this;
    }

    public function join( array|string $separator ) : string {
        return implode( $separator, $this->items );
    }

    public function to_array() {
        return $this->items;
    }

    public function offsetExists(mixed $offset): bool {
        return isset($this->items[$offset]);
    }

    /**
     * @return T
     */
    public function offsetGet(mixed $offset): mixed {
        return $this->items[$offset];
    }

    public function offsetSet(mixed $key, mixed $value): void {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    public function offsetUnset(mixed $offset): void {
        unset($this->items[$offset]);
    }

    public function getIterator(): Traversable {
        return new ArrayIterator( $this->items );
    }

    public function count(): int {
        return count( $this->items );
    }
}