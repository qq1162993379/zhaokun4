<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_dingdan_mingxi extends Model
{
    protected $table = 'p_dingdan_mingxi';
    public $id;
    public $dd_id;
    public $pro_names;
    public $pro_id;
    public $money1;
    public $money2;
    public $numbers;
    public $guige;
    public $danwei;
}