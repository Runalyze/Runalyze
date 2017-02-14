<?php

namespace Runalyze\Model\Common;

use Runalyze\Model\Entity;

trait WithNullableArraysTrait
{
    public function ensureArraysToBeNotNull()
    {
        /** @var Entity $this */
        foreach (static::allDatabaseProperties() as $key) {
            if ($this->isArray($key) && null === $this->get($key)) {
                $this->set($key, []);
            }
        }
    }

    public function ensureArraysToBeNullIfEmpty()
    {
        /** @var Entity $this */
        foreach (static::allDatabaseProperties() as $key) {
            if ($this->isArray($key) && empty($this->get($key))) {
                $this->set($key, null);
            }
        }
    }
}
