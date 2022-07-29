<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_dingdan_zhuwa extends Model
{
    protected $table = 'p_dingdan_zhuwa';
    public $id;
    public $dd_id;
    public $changdu;
    public $numbers;
}