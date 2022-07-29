<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_user_price extends Model
{
    protected $table = 'p_user_price';
    public $id;
    public $user_id;
    public $pro_id;
    public $price;
}