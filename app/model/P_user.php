<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_user extends Model
{
    protected $table = 'p_user';
    public $id;
    public $opid;
    public $names;
    public $tel;
}