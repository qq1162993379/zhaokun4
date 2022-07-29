<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_guige extends Model
{
    protected $table = 'p_guige';
    public $id;
    public $kuandu;
    public $names;
}