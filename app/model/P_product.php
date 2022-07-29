<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_product extends Model
{
    protected $table = 'p_product';
    public $id;
    public $names;
    public $guige;
    public $danwei;
    public $danjia;
    public $paixu;
    public $xiugai;
}