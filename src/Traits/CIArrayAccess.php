<?php

namespace RTNatePHP\BasicApp\Traits;

trait CIArrayAccess
{
    public function offsetExists($offset)
    {
        return $this->ci->has($offset);
    }

    public function offsetGet($offset)
    {
        return $this->ci->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->ci->set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('Cannot unset values on the DI Container');
    }
}