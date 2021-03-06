<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/8
 * Time: 15:55
 */
class IceFace extends Terrain
{
    protected static $id;
    protected static $length;
    protected static $width;
    protected static $concept;

    function effect($tank)
    {
        parent::effect($tank); // TODO: Change the autogenerated stub
//        如果已经在冰面效果之中
        if (!$tank->getIceEffectTimer()) {
            return;
        }
        if (is_a($tank->getLifeBuff(), "IceNoEffectBuff")) {
            return;
        }
        $timer_id = \Workerman\Lib\Timer::add(
            0.3,
            function ($tank) use (&$timer_id) {
                /** @var Tank $tank */
                $tank->ice_move_step = $tank->ice_move_step??random_int(2, 3);
                if (!$tank->ice_move_step) {
                    unset($tank->ice_move_step);
                    \Workerman\Lib\Timer::del($timer_id);
                }
                foreach (range(1, $tank->getSpeed()) as $once) {
                    $tank->move($tank->getFaceTo());
                }
                $tank->ice_move_step--;
                $tank->updateMe();
        }, array($tank));
        $tank->ice_effect_timer_id = $timer_id;
    }
}

