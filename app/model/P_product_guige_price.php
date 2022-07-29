<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_product_guige_price extends Model
{
    protected $table = 'p_product_guige_price';
    public $id;
    public $pro_id;
    public $guige_id;
    public $danwei;
    public $zhonglei_id;
    public $houdu_id;
    public $danjia;
}