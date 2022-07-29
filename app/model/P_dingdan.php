<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_dingdan extends Model
{
    protected $table = 'p_dingdan';
    public $id;
    public $user_id;
    public $times;
    public $beizhu;
    public $zt;
    public $jz;
    public $qhm;
    public $money1;
    public $money2;
    public $zw_guige;
    public $zw_houdu;
    public $zw_yanse;
    public $zw_paixu;
    public $zw_hebing;
}