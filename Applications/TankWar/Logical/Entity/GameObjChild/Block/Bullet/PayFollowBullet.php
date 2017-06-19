<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/2
 * Time: 9:26
 */
class PayFollowBullet extends Bullet
{
    protected static $concept;
    protected static $id;
    protected static $die;
    protected static $length;
    protected static $width;

    protected static $hp;
    protected static $life;
    protected static $side;
    protected static $is_no_enemy;

    protected static $face_to;
    protected static $speed;
    protected static $attack;
    protected static $from;
    protected static $number;
    protected static $distance_life;
    protected static $super_shoot_interval;

    protected $target_tank = null;
    protected $move_queue = array();

    /**
     * 目前跟踪弹只支持玩家坦克使用，如需PC坦克也使用则需要修改findNearestEnemyTank方法在坦克搜索里添加getPlayerTanks
     * PayFollowBullet constructor.
     *
     * @param $attr array
     */
    public function __construct($attr)
    {
        parent::__construct($attr);
    }

    /**
     *
     *  根据最近的某个方位的敌方坦克
     * 1. 计算出路线
     * 2. 将自己注册进敌方坦克的（跟踪者列表）属性里，每当地方移动就将移动信息（方向）加进该跟踪弹的移动队列
     *
     * @param $tank Tank
     *
     * @return null
     */
    public function followTheNearestTank($tank)
    {
        if (!$tank) return null;

        $face_to = 0;
        if ($tmp_face_to = $this->theGameObjectPositionOfMe($tank)) {
            $face_to = $tmp_face_to;
        }
        switch ($face_to) {
            case 1:
                $times = $tank->getY() - $this->y + 2;
                break;
            case 2:
                $times = $this->y - $tank->getY() - 2;
                break;
            case 3:
                $times = $this->x - $tank->getX() - 2;
                break;
            case 4:
                $times = $tank->getX() - $this->getX() + 2;
                break;
            default:
                return null;
        }
        $tmp_move_array = array();
        for ($i = 0; $i < $times; $i++) {
            $tmp_move_array[] = $face_to;
        }
        $this->setTargetTank($tank);
        $this->setMoveQueue($tmp_move_array);
        $this->target_tank->registerFollowBullets($this);
        return null;
    }

    /**
     * @return Tank
     */
    public function getTargetTank()
    {
        return $this->target_tank;
    }

    /**
     * @param null $target_tank
     *
     * @return $this
     */
    public function setTargetTank($target_tank)
    {
        $this->target_tank = $target_tank;
        return $this;
    }

    /**
     * @return array
     */
    public function &getMoveQueue(): array
    {
        return $this->move_queue;
    }

    /**
     * @param array $move_queue
     * @return $this
     */
    public function setMoveQueue(array $move_queue)
    {
        $this->move_queue = $move_queue;
        return $this;
    }




    public function move($po)
    {
//        如果方向移动队列没有空，则返回下一步需要移动的方向（此时并没有置换方向（调用set），将在move方法内部调用setFaceTo）
        $face_to = reset($this->move_queue);
        if (!$face_to)
            $face_to = $this->getFaceTo(); // TODO: Change the autogenerated stub
        if ($face_to <= 2) {
            $this->setWidth(2);
            $this->setLength(1);
        } else {
            $this->setWidth(1);
            $this->setLength(2);
        }
        $this->setFaceTo($face_to);
        array_shift($this->getMoveQueue());
        parent::move($face_to); // TODO: Change the autogenerated stub
    }

    /**
     * @param $x
     * @param $y
     * @param $width
     * @param $length
     * @param $obj
     * @return array
     * @param Bullet|map $bullet Bullet
     *                           是否打到砖块(假设自己是x,y,width,length的情况下)
     *
     * if else  是因为: 当刚射出来的时候(正打算射出来,这个时候是没有子弹对象的 所以不能用上面那段有bullets_hit的代码)
     *
     */
    static public function isHittingObj($x, $y, $width, $length, $obj)
    {
        echo "进入PayFollowBullet的HittingObj\n";
        if ((is_a($obj, Bullet::class)||is_subclass_of(obj,Bullet::class)) && ($bullet = $obj)) {

            /** @var Bullet $bullet */
            $map = $bullet->getMap();
//        碰撞到子弹
            $collision_objects = $bullet->getMap()->computeCollision($x, $y, $width, $length, $bullet->getConcept());
            $id = $bullet->get_Id();
            $from_tank = $bullet->getFrom();
//        把自己过滤掉
            $bullets_hit = (array_filter($collision_objects,
                function ($var) use ($id) {
                    return $var->get_Id() != $id && $var->getId != PayFollowBullet::getId();
                }
            ));

//            碰到碰撞体
            $block_collision_objects = $map->computeCollision($x, $y, $width, $length, 3);
//            把自己的坦克过滤掉
            $blocks_hit = (array_filter($block_collision_objects,
                function ($var) use ($from_tank) {
                    return ($var->get_Id() != $from_tank->get_Id()) && ($var->getSide() != $from_tank->getSide());

                }
            ));
            return array_merge($bullets_hit, $blocks_hit);

        } else if ((is_subclass_of($obj, Tank::class) || is_a($obj, Tank::class)) && ($tank = $obj)) {
            /** @var Tank $tank */
            $map = $tank->getMap();
//            碰撞到子弹(此时自己不可能存在)
            $bullets_hit = $map->computeCollision($x, $y, $width, $length, 5);
//            碰到砖块坦克
            $block_collision_objects = $map->computeCollision($x, $y, $width, $length, 3);
//            把开炮的坦克过滤掉
            $blocks_hit = (array_filter($block_collision_objects,
                function ($var) use ($tank) {
                    return ($var->get_Id() != $tank->get_Id());
                }
            ));
            return array_merge($bullets_hit, $blocks_hit);

        }
        return null;
    }

    /**
     * @param $po int 被跟踪的坦克移动了
     */
    public function tankMoved($po){
        $this->getMoveQueue()[] = $po;
    }

    /**
     * @param bool $update
     *
     * @return Bullet
     */
    public function destroy($update = true)
    {
        if ($this->getTargetTank())
            unset($this->getTargetTank()->getFollowBulletsArray()[$this->get_Id()]);
        return parent::destroy($update); // TODO: Change the autogenerated stub
    }

}