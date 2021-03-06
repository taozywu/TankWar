<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/24
 * Time: 11:11
 */
class NormalArmorBuff extends Buff
{
    protected static $concept;
    protected static $id;
    protected static $die;
    protected static $length;
    protected static $width;

    protected static $buff_type;

    protected static $shield_up; // 护甲增加量

    /**
     * @return mixed
     */
    public function getShieldUp()
    {
        return $this->shield_up??static::$shield_up;
    }

    /**
     * @param mixed $shield_up
     */
    public function setShieldUp($shield_up)
    {
        $this->shield_up = $shield_up;
    }

    function active()
    {
        parent::active(); // TODO: Change the autogenerated stub
        echo "在NormalArmorBuff里。需要增加的护甲数为：{$this->getShieldUp()}\n";
        $this->getTankGot()->shieldUp($this->getShieldUp());
    }
}