<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_dingdan_up extends Model
{
    protected $table = 'p_dingdan_up';
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
    public $dd_id;
    public $times2;
}