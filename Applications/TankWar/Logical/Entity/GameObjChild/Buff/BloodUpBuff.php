<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/9
 * Time: 17:46
 */
class BloodUpBuff extends Buff
{
    protected static $concept;
    protected static $id;
    protected static $die;
    protected static $length;
    protected static $width;

    protected static $buff_type;

    protected static $blood_up;

    /**
     * @return mixed
     */
    public function getBloodUp()
    {
        return $this->blood_up??static::$blood_up;
    }

    /**
     * @param mixed $blood_up
     */
    public function setBloodUp($blood_up)
    {
        $this->blood_up = $blood_up;
    }

    function active()
    {
        parent::active(); // TODO: Change the autogenerated stub
        $this->getTankGot()->hpUp($this->getBloodUp());
    }

}