<?php

namespace app\model;

use think\Model;

// Counters 定义数据库model
class P_admin_user extends Model
{
    protected $table = 'P_admin_user';
    public $id;
    // public $opid;
    public $username;
    public $passwords;
}