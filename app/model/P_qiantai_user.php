<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_qiantai_user extends Model
{
    protected $table = 'p_qiantai_user';
    public $id;
    // public $opid;
    public $username;
    public $passwords;
}