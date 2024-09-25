<?php

namespace Evavel\Policies;

class Policy
{
    public static function make()
    {
        return new static();
    }

    public function __call($method, $params)
    {
        if (method_exists($this, $method)){
            return $this->$method($params);
        }
        return true;
    }

    /*public function viewAny(SRR_Model $user)
    {
        return true;
    }

    public function view(SRR_Model $user, SRR_Model $model)
    {
        return true;
    }

    public function create(SRR_Model $user)
    {
        return true;
    }

    public function update(SRR_Model $user, SRR_Model $model)
    {
        return true;
    }

    public function delete(SRR_Model $user, SRR_Model $model)
    {
        return true;
    }*/
}
