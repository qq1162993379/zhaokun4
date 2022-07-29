<?php
namespace app\controller;

use Error;
use Exception;
use app\model\Counters;
use think\response\Html;
use think\response\Json;
use think\facade\Log;
use think\facade\Request;

use app\model\P_user;
use app\model\P_product;
use app\model\P_dingdan_mingxi;
use app\model\P_dingdan_zhuwa;
use app\model\P_dingdan;
use app\model\P_guige;
use app\model\P_user_price;
use app\model\P_qiantai_user;
use app\model\P_admin_user;
use app\model\P_zhuwa_chicun;
use app\model\P_dingdan_houdu;
use app\model\P_product_guige_price;
use app\model\P_houdu;
use app\model\P_zhonglei;
use app\model\P_dingdan_up;
use app\model\P_dingdan_mingxi_up;
use app\model\P_dingdan_zhuwa_up;


use think\facade\View;
class Admin{

    #模板渲染加载
    private function temp(){
        $config = [
            'view_path'	    =>	'../app/view/admin/',
            'taglib_begin' => '{!',
            'tpl_begin' =>'{!',
            // 'tpl_end' =>'!}',
            // 'taglib_end' =>'!}',
            // 'view_suffix'   =>	'html',
        ];
        $template = new \think\Template($config);
        return $template;
    }
    #构造数据返回格式
    private function returns($code,$data,$msg=''){
        $res = [
            'code' => $code,
            'data' => $data,
            'msg' => $msg
        ];
        return json($res);
    }
    #登录验证
    public function yz(){
		session_start();
		if(isset($_COOKIE["ids"]) && isset($_SESSION["ids"]) && isset($_SESSION["level"]) && $_COOKIE["ids"]!=null && $_SESSION["ids"] == $_COOKIE["ids"] && $_SESSION["level"] == 2){
			return 1;
		}else{
			return 0;
		}
	}

    /**
     * 登陆界面
     */
    public function index(){
        $template = $this->temp();
        $template->fetch('index');
    }

    /**
     * 主页静态页面
     * 
     */
    public function admin()
    {
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return;
        }

        // $template = $this->temp();
        $template->fetch('admin');
    }

    /**
     * 登录接口
     * @return json
     */
    public function login(){
        $uname = Request::param('uname');
        $password = Request::param('password');
        $qiantai_user = new P_admin_user;
        $res = $qiantai_user->where(['username'=>$uname,'passwords'=>$password])->find();
        if($res == null){
            return $this->returns(0,0,"用户名密码错误");
        }
        session_start();
        setcookie("username",$res['username'],time()+3600*12);
        setcookie("ids",$res['id'],time()+3600*12);
        // setcookie("level",1,time()+3600*12);
        $_SESSION["ids"] = $res['id'];
        $_SESSION["username"] = $res['username'];
        $_SESSION["level"] = 2;

        return $this->returns(1,1,"登陆成功");

    }

    /**
     * 订单列表界面
     */
    public function dingdan_new_list(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return;
        }

        // $template = $this->temp();
        $template->fetch('dingdan_new_list');
    }

    /**
     * @return Json
     * 订单列表获取
     */
    public function dingdanget():Json{
        #登陆状态验证
        // if($this->yz()==0){
        //     // $template->fetch('index');
        //     return json(['rows'=>[['username'=>"登陆失效"]],'total'=>0]);
        // }
        #接收页码和每页数量
        $page = Request::param('page') == null ? 1:intval(Request::param('page'));
        $rows = Request::param('rows') == null ? 10:intval(Request::param('rows'));

        $wheres = [];#查询条件
        #是否有状态参数
        $zt0 = Request::param('zt');
        if($zt0!=null){
            // $wheres['zt'] = $zt0;
            array_push($wheres,['zt','=',$zt0]);
        }

        #是否有结账参数
        $jz0 = Request::param('jz');
        if($jz0 != null){
            array_push($wheres,['jz','=',$jz0]);
        }


        #是否有取货吗
        $qhm0 = Request::param('qhm');
        if($qhm0!=null){
            // $wheres['qhm'] = $qhm0;
            array_push($wheres,['qhm','=',$qhm0]);
        }
        #是否有用户姓名（重名怎么办？先不考虑）
        $username0 = Request::param('username');
        if($username0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('names',$username0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有tel
        $tel0 = Request::param('tel');
        if($tel0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('tel',$tel0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有opid
        $opid0 = Request::param('opid');
        if($opid0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('opid',$opid0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有时间
        $time1 = Request::param('time1');
        $time2 = Request::param('time2');
        if($time1 != null && $time2!=null){
            $rt1 = strtotime($time1." 00:00:00");
            $rt2 = strtotime($time2." 23:59:59");

            array_push($wheres,['times','<',$rt2]);
            array_push($wheres,['times','>',$rt1]);
        }

        #构造数据查询
        $dingdan = new P_dingdan;
        if(count($wheres)>0){
            $res = $dingdan->where($wheres)->order("id",'desc')->limit(($page-1)*$rows,$rows)->select();
        }else{
            $res = $dingdan->order("id",'desc')->limit(($page-1)*$rows,$rows)->select();
        }
        
        
        #查询用户信息列表并构建基于用户id的索引，这么做是因为用户量少减少数据库访问次数，若用户量大量提升需要进行修改
        $user = new P_user;
        $userinf = $user->select();
        $userinf_new = [];
        for($i=0;$i<count($userinf);$i++){
            $userinf_new[$userinf[$i]['id']] = $userinf[$i];
        }

        #变化一部分数据显示
        for($i=0;$i<count($res);$i++){
            $res[$i]['times'] = date("Y-m-d H:i:s",$res[$i]['times']);
            $res[$i]['username'] = $userinf_new[$res[$i]['user_id']]['names'];
            $res[$i]['tel'] = $userinf_new[$res[$i]['user_id']]['tel'];
            $res[$i]['opid'] = $userinf_new[$res[$i]['user_id']]['opid'];

            $res[$i]['zw_danwei']==1?$res[$i]['zw_danwei']="米":$res[$i]['zw_danwei']="平方米";

            $zt1 = "";
            $caozuo = "";
            switch($res[$i]['zt']){
                case 0: $zt1 = "<font color='orange'>已修改，待确认</font>";$caozuo="<button onclick='dd_xiugai(".$res[$i]['id'].")'>查看修改</button>";break;
                case 1: $zt1 = "<font color='red'>已下单，待接受</font>";$caozuo="<button onclick='dd_jieshou(".$res[$i]['id'].")'>接收订单</button>";break;
                case 2: $zt1 = "<font color='yellow'>已接受，备货中</font>";$caozuo="<button onclick='dd_kequhuo(".$res[$i]['id'].")'>通知取货</button>";break;
                case 3: $zt1 = "<font color='green'>可取货</font>";$caozuo="<button onclick='dd_quhuo(".$res[$i]['id'].")'>取货</button>";break;
                case 4: $zt1 = "<font color='blue'>取货完毕</font>";break;
            }
            $res[$i]['zt'] = $zt1;
            $res[$i]['infor'] = "<button onclick='chakanxiangqing(".$res[$i]['id'].")'>查看详情</button>";
            $res[$i]['clicks'] = $caozuo;

            if($res[$i]['jz'] == 1 ){
                $res[$i]['jz'] = "<font color='red'>未结账<font><button onclick='dd_jiezhang(".$res[$i]['id'].")'>结账</button>";
            }else{
                $res[$i]['jz'] = "<font color='green'>已结账<font>";
            }
        }
        #订单总数
        $zs = $dingdan->where($wheres)->field(['COUNT(id) AS "aa"'])->select();
        return json(['rows'=>$res,'total'=>$zs[0]['aa']]);
    }

    /**
     * @return Json
     * 订单详细获取
     */
    public function dingdan_xq():Json{
        #登陆状态验证
        if($this->yz()==0){
            $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }

        $did = Request::param('id');
       
        #查询主订单内容
        $mingxi = new P_dingdan_mingxi;
        $zhuwa = new P_dingdan_zhuwa;

        $mingxi_data = $mingxi->where("dd_id",$did)->select();
        $zhuwa_data = $zhuwa->where("dd_id",$did)->select();
        
        $dd = new P_dingdan;
        $sel = $dd->where('id',$did)->find();

        if($sel == null){
            return $this->returns(0,$did,"查无此订单");
        }

        $usermod = new P_user;
        $user = $usermod->where('id',$sel['user_id'])->find();

        //构造返回数据
        $resdata = [];
        $resdata['id'] = $sel['id']; 
        $resdata['times'] = date("Y-m-d H:i:s",$sel['times']);
        $resdata['names'] = $user['names'];
        $resdata['tel'] = $user['tel'];
        $zt1 = "";
        switch($sel['zt']){
            case 0: $zt1 = "已修改，待确认";break;
            case 1: $zt1 = "已下单，待接受";break;
            case 2: $zt1 = "已接受，备货中";break;
            case 3: $zt1 = "可取货";break;
            case 4: $zt1 = "取货完毕";break;
        }
        $resdata['zt'] = $zt1;
        // $resdata['ztwz'] = $zt1;
        $resdata['money1'] = $sel['money1'];
        $resdata['money2'] = $sel['money2'];
        $resdata['jz'] = $sel['jz'];
        $resdata['qhm'] = $sel['qhm'];
        $resdata['zw_guige'] = $sel['zw_guige'];
        $resdata['zw_houdu'] = $sel['zw_houdu'];
        $resdata['zw_yanse'] = $sel['zw_yanse'];
        $resdata['zw_danwei'] = $sel['zw_danwei'];
        $resdata['beizhu'] = $sel['beizhu'];



        $resdata0=[
            'dingdan'=>$resdata,
            'mingxi' =>$mingxi_data,
            'zhuwa' =>$zhuwa_data
        ];
        return $this->returns(1,$resdata0);
    }

    
/**  订单修改相关部分 */

    /**
     * @return Json
     * 订单修改记录
     */
    public function dd_xiugai_jl(){
        $ids = Request::param('id');
        $resdata0 = [
            'yuanshi'=>[],
            'xiugai'=>[]
        ];

        $dd1 = new P_dingdan;
        $mx1 = new P_dingdan_mingxi;
        $zw1 = new P_dingdan_zhuwa;
        $dd2 = new P_dingdan_up;
        $mx2 = new P_dingdan_mingxi_up;
        $zw2 = new P_dingdan_zhuwa_up;

        


    }




    /**
     * @return Json
     * 订单状态修改 接受订单
     */
    public function dingdan_jieshou():Json{

        #登陆状态验证
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }


        $did = Request::param('id');
        $dd = new P_dingdan;
        $res = $dd->where('id',$did)->update(['zt'=>2]);
        if($res){
            return $this->returns(1,$res,"订单确认成功");
        }else{
            return $this->returns(2,$res,"订单确认失败");
        }
        
    }
    /**
     * @return Json
     * 订单状态修改 变为可取货，并生成取货吗
     */
    public function dingdan_kequhuo():Json{

        #登陆状态验证
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }

        $did = Request::param('id');
        $dd = new P_dingdan;
        

        $times = date("md-H");#六位数字
        $sjs = rand(10,99);
        $arr = ['A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
        $zm1 = $arr[rand(0,25)];
        $zm2 = $arr[rand(0,25)];

        $yzm = $times.$sjs.$zm1.$zm2;

        $res = $dd->where('id',$did)->update(['zt'=>3,'qhm'=>$yzm]);

        if($res){
            return $this->returns(1,$res,"订单状态修改成功");
        }else{
            return $this->returns(2,$res,"订单状态修改失败");
        }
    }
    /**
     * @return Json
     * 订单状态修改 变为可取货，并生成取货吗
     */
    public function dingdan_quhuo():Json{
        #登陆状态验证
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }


        $did = Request::param('id');
        $qhm = Request::param('qhm');
        $dd = new P_dingdan;

        $res = $dd->where('id',$did)->find();
        if($res['qhm'] == $qhm){
            $res1 = $dd -> where('id',$did)->update(['zt'=>4]);
            if($res1){
                return $this->returns(1,$res,"取货成功");
            }else{
                return $this->returns(0,$res,"数据库错误，修改失败");
            }
        }else{
            return $this->returns(2,$res,"取货码验证错误");
        }
    }
    /**
     * @return Json
     * 订单结账状态修改 结账
     */
    public function dingdan_jiezhang(){
        #登陆状态验证
        // session_start();
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }
        
        $did = Request::param('id');
        $pass1 = Request::param('passwords');
        $dd = new P_dingdan;
        $users = new P_admin_user;
        
		$uid = $_SESSION["ids"];
        $uinf = $users->where('id',$uid)->find();
        if($uinf['passwords'] != $pass1){
            return $this->returns(4,0,"管理员密码错误");
        }
        //允许结账

        $res = $dd->where('id',$did)->update(['jz'=>2]);
        if($res){
            return $this->returns(1,$res,"结账成功");
        }else{
            return $this->returns(2,$res,"数据库错误，结账失败");
        }
    }

    /**
     * @return Json
     * 订单统计信息获取
     */
    public function dingdan_tongji():Json{
        #登陆状态验证
        // if($this->yz()==0){
        //     // $template->fetch('index');
        //     return $this->returns(3,0,"登陆状态错误");
        // }
        #接收页码和每页数量
        // $page = Request::param('page') == null ? 1:intval(Request::param('page'));
        // $rows = Request::param('rows') == null ? 10:intval(Request::param('rows'));

        $wheres = [];#查询条件
        #是否有状态参数
        $zt0 = Request::param('zt');
        if($zt0!=null){
            // $wheres['zt'] = $zt0;
            array_push($wheres,['zt','=',$zt0]);
        }

        #是否有结账参数
        $jz0 = Request::param('jz');
        if($jz0 != null){
            array_push($wheres,['jz','=',$jz0]);
        }


        #是否有取货吗
        $qhm0 = Request::param('qhm');
        if($qhm0!=null){
            // $wheres['qhm'] = $qhm0;
            array_push($wheres,['qhm','=',$qhm0]);
        }
        #是否有用户姓名（重名怎么办？先不考虑）
        $username0 = Request::param('username');
        if($username0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('names',$username0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有tel
        $tel0 = Request::param('tel');
        if($tel0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('tel',$tel0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有opid
        $opid0 = Request::param('opid');
        if($opid0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('opid',$opid0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有时间
        $time1 = Request::param('time1');
        $time2 = Request::param('time2');
        if($time1 != null && $time2!=null){
            $rt1 = strtotime($time1." 00:00:00");
            $rt2 = strtotime($time2." 23:59:59");

            array_push($wheres,['times','<',$rt2]);
            array_push($wheres,['times','>',$rt1]);
        }

        #构造数据查询
        $dingdan = new P_dingdan;
        if(count($wheres)>0){
            $res = $dingdan->where($wheres)->select();
        }else{
            $res = $dingdan->select();
        }
        
        $dds = count($res);#订单总数

        $money1 = 0;
        $money2 = 0;

        $djz_money = 0;
        $djz_numb = 0;
        $yjz_money = 0;
        $yjz_numb = 0;


        for($i=0;$i<$dds;$i++){
            $money1 += $res[$i]['money1'];
            $money2 += $res[$i]['money2'];
            if($res[$i]['jz']==1){
                $djz_money += $res[$i]['money2'];
                $djz_numb++;
            }else if($res[$i]['jz']==2){
                $yjz_money += $res[$i]['money2'];
                $yjz_numb++;
            }
        }
        $data = [
            'numbers'=>$dds,
            'money1' =>$money1,
            'money2' => $money2,
            'djz' =>$djz_money,
            'djz_numb' =>$djz_numb,
            'yjz' =>$yjz_money,
            'yjz_numb' =>$yjz_numb
        ];
        return $this->returns(1,$data);
    }
    
    /**
     * @return Json
     * 订单批量结账
     */
    public function dingdan_jiezhang_pl(){
        #登陆状态验证
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }

        //验证用户密码
        $pass1 = Request::param('passwords');
        $users = new P_admin_user;
        
		$uid = $_SESSION["ids"];
        $uinf = $users->where('id',$uid)->find();
        if($uinf['passwords'] != $pass1){
            return $this->returns(4,0,"管理员密码错误");
        }
        //允许结账

        $wheres = [];#查询条件
        #是否有状态参数
        $zt0 = Request::param('zt');
        if($zt0!=null){
            // $wheres['zt'] = $zt0;
            array_push($wheres,['zt','=',$zt0]);
        }

        #是否有结账参数
        $jz0 = Request::param('jz');
        if($jz0 != null){
            array_push($wheres,['jz','=',$jz0]);
        }


        #是否有取货吗
        $qhm0 = Request::param('qhm');
        if($qhm0!=null){
            // $wheres['qhm'] = $qhm0;
            array_push($wheres,['qhm','=',$qhm0]);
        }
        #是否有用户姓名（重名怎么办？先不考虑）
        $username0 = Request::param('username');
        if($username0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('names',$username0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有tel
        $tel0 = Request::param('tel');
        if($tel0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('tel',$tel0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有opid
        $opid0 = Request::param('opid');
        if($opid0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('opid',$opid0)->find();
            if($udat1 == null){
                // $wheres['user_id'] = 0;
                array_push($wheres,['user_id','=',0]);
            }else{
                // $wheres['user_id'] = $udat1['id'];
                array_push($wheres,['user_id','=',$udat1['id']]);
            }
        }
        #是否有时间
        $time1 = Request::param('time1');
        $time2 = Request::param('time2');
        if($time1 != null && $time2!=null){
            $rt1 = strtotime($time1." 00:00:00");
            $rt2 = strtotime($time2." 23:59:59");

            array_push($wheres,['times','<',$rt2]);
            array_push($wheres,['times','>',$rt1]);
        }

        #构造数据查询
        $dingdan = new P_dingdan;
        if(count($wheres)>0){
            $res = $dingdan->where($wheres)->update(['jz'=>2]);
        }else{
            return $this->returns(5,0,"不允许未包含任何筛选条件的结账操作");
        }
        
        return $this->returns(1,$res,"本次结账订单数：".$res);
    }


//下面先把产品相关的接口写好
//上面关于订单的部分还缺少订单详情的修改和添加
//把产品写完之后，写上面那些更方便
//
//同时上面还缺少excel导出部分，等最后再弄


/**
 * 产品管理界面
 */
public function product_page(){
    $template = $this->temp();
    if($this->yz()==0){
        $template->fetch('index');
        return;
    }

    // $template = $this->temp();
    $template->fetch('product_page');
}



    /**
     * @return Json
     * 产品新增
     */
    public function productadd():Json{ 
        
         #登陆状态验证
         if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }

        $pro = new P_product;
        $paixu = $pro->count();
        $data = [
            'names' => Request::param('names'),
            'guige' => Request::param('guige'),
            'danwei' => Request::param('danwei'),
            'danjia' => Request::param('danjia'),
            'paixu' => $paixu+1
        ];
        $res0 = $pro->save($data);
        if($res0){
            return $this->returns(1,$res0,"添加成功");
        }else{
            return $this->returns(2,$res0,"添加失败");
        }
        
    }
    /**
     * @return Json
     * 产品列表
     */
    public function productlist():Json{
        #登陆状态验证
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }

        $pro = new P_product;
        $res0 = $pro->order('paixu')->select();

        $nums = $pro->count();

        return json(['rows'=>$res0,'total'=>$nums]);
    }

    /**
     * @return Json
     * 产品修改
     */
    public function productupdate():Json{
        #登陆状态验证
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }

        $pro = new P_product;
        $ids = Request::param('id');

        $data = [
            // 'names' => Request::param('names'),
            'guige' => Request::param('guige'),
            'danwei' => Request::param('danwei'),
            'danjia' => Request::param('danjia'),
        ];
        $res0 = $pro->where('id',$ids)->update($data);
        return $this->returns(1,$res0,"修改完成");
    }

    /**
     * @return Json
     * 排序变化
     */
    public function productpaixu(){
        $ids =  Request::param('id');
        $paixu = Request::param('paixu');
        $caozuo = Request::param('caozuo');// 1 up or 2 down

        $pro = new P_product;
        $numb = $pro->count();
        if($caozuo == 1 && $paixu == 0 || $caozuo == 2 && $paixu == $numb){//返回错误
            return $this->returns(2,0,"排序调整错误");
        }
        if($caozuo==1){
            $dp = -1;
        }else{
            $dp = 1;
        }

        $mb_paixu = $paixu + $dp;//目标排序

        $pro->where('paixu',$mb_paixu)->update(['paixu'=>$paixu]);
        $pro->where('id',$ids)->update(['paixu'=>$mb_paixu]);

        return $this->returns(1,1);
    }

    /**
     * @return Json
     * 删除产品
     */
    public function productdel(){
        $ids =  Request::param('id');
        $pro = new P_product;
        $yssj = $pro->where('id',$ids)->find();
        $paixu = $yssj['paixu'];
        //删除
        $res0 = $pro->where('id',$ids)->delete();

        //排序修改
        $res1 = $pro->where('paixu','>',$paixu)->order("paixu")->select();
        for($i=0;$i<count($res1);$i++){
            $pxls = $res1[$i]['paixu'];
            $pro->where("id",$res1[$i]['id'])->update(['paixu'=>$pxls-1]);
        }
        if($res0){
            return $this->returns(1,$res0,"删除成功");
        }else{
            return $this->returns(2,$res0,"删除失败");
        }
    }



    /**
     * 主瓦管理界面
     */
    public function zhuwa_page(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return;
        }

        // $template = $this->temp();
        $template->fetch('zhuwa_page');
    }
    /**
     * @return Json
     * 主瓦尺寸列表
     */
    public function zhuwa_list(){
        $zw = new P_zhuwa_chicun;
        $res = $zw->order('chicun')->select();
        return json($res);
    }


    /**
     * @return Json
     * 新增主瓦尺寸
     */
    public function zhuwa_chicun_add(){
        $chicun = Request::param('chicun');
        $zw = new P_zhuwa_chicun;
        $res = $zw->insert(['chicun'=>$chicun]);
        if($res){
            return $this->returns(1,$res,"添加成功");
        }else{
            return $this->returns(2,$res,"添加失败");
        }
    }
    /**
     * @return Json
     * 删除主瓦尺寸
     */
    public function zhuwa_chicun_del(){
        $ids = Request::param('id');
        $zw = new P_zhuwa_chicun;
        $res = $zw->where('id',$ids)->delete();
        if($res){
            return $this->returns(1,$res,"删除成功");
        }else{
            return $this->returns(2,$res,"删除失败");
        }
    }

    /**
     * 规格管理界面
     */
    public function guige_page(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return;
        }

        // $template = $this->temp();
        $template->fetch('guige_page');
    }

    /**
     * @return Json
     * 规格列表
     */
    public function guige_list(){
        $zw = new P_guige;
        $res = $zw->select();
        return json($res);
    }

    /**
     * @return Json
     * 新增主瓦规格
     */
    public function zhuwa_guige_add(){
        $names = Request::param('names');
        $kuandu = Request::param('kuandu');

        $zw = new P_guige;
        $res = $zw->insert(['names'=>$names,'kuandu'=>$kuandu]);
        if($res){
            return $this->returns(1,$res,"添加成功");
        }else{
            return $this->returns(2,$res,"添加失败");
        }
    }
    /**
     * @return Json
     * 删除主瓦规格
     */
    public function zhuwa_guige_del(){
        $ids = Request::param('id');
        $zw = new P_guige;
        $res = $zw->where('id',$ids)->delete();
        $price = new P_product_guige_price;
        $res2 = $price->where(["guige_id"=>$ids])->delete();
        if($res && $res2){
            return $this->returns(1,$res,"删除成功");
        }else{
            return $this->returns(2,$res,"删除失败");
        }
    }
    /**
     * @return Json
     * 修改主瓦规格
     */
    public function zhuwa_guige_update(){
        $names = Request::param('names');
        $kuandu = Request::param('kuandu');
        $ids = Request::param('id');

        $zw = new P_guige;
        $res = $zw->where('id',$ids)->update(['names'=>$names,'kuandu'=>$kuandu]);
        if($res){
            return $this->returns(1,$res,"修改成功");
        }else{
            return $this->returns(2,$res,"修改失败，或者与原数据无差别");
        }
    }
    /**
     * 厚度管理界面
     */
    public function houdu_page(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return;
        }

        // $template = $this->temp();
        $template->fetch('houdu_page');
    }

    /**
     * @return Json
     * 厚度列表
     */
    public function houdu_list(){
        $zw = new P_houdu;
        $res = $zw->select();
        return json($res);
    }
/**
     * @return Json
     * 新增主瓦厚度
     */
    public function zhuwa_houdu_add(){
        $houdu = Request::param('houdu');
        // $kuandu = Request::param('kuandu');

        $zw = new P_houdu;
        $res = $zw->insert(['houdu'=>$houdu]);
        if($res){
            return $this->returns(1,$res,"添加成功");
        }else{
            return $this->returns(2,$res,"添加失败");
        }
    }
    /**
     * @return Json
     * 删除主瓦厚度
     */
    public function zhuwa_houdu_del(){
        $ids = Request::param('id');
        $zw = new P_houdu;
        $res = $zw->where('id',$ids)->delete();
        $price = new P_product_guige_price;
        $res2 = $price->where(["houdu_id"=>$ids])->delete();
        if($res && $res2){
            return $this->returns(1,$res,"删除成功");
        }else{
            return $this->returns(2,$res,"删除可能失败，或不存在对应价格");
        }
    }
    /**
     * @return Json
     * 修改主瓦厚度
     */
    public function zhuwa_houdu_update(){
        $houdu = Request::param('houdu');
        // $kuandu = Request::param('kuandu');
        $ids = Request::param('id');

        $zw = new P_houdu;
        $res = $zw->where('id',$ids)->update(['houdu'=>$houdu]);
        if($res){
            return $this->returns(1,$res,"修改成功");
        }else{
            return $this->returns(2,$res,"修改失败，或者与原数据无差别");
        }
    }

    /**
     * 种类管理界面
     */
    public function zhonglei_page(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return;
        }

        // $template = $this->temp();
        $template->fetch('zhonglei_page');
    }

    /**
     * @return Json
     * 种类列表
     */
    public function zhonglei_list(){
        $zw = new P_zhonglei;
        $res = $zw->select();
        return json($res);
    }
/**
     * @return Json
     * 新增主瓦种类
     */
    public function zhuwa_zhonglei_add(){
        $houdu = Request::param('houdu');
        // $kuandu = Request::param('kuandu');

        $zw = new P_zhonglei;
        $res = $zw->insert(['leibie'=>$houdu]);
        if($res){
            return $this->returns(1,$res,"添加成功");
        }else{
            return $this->returns(2,$res,"添加失败");
        }
    }
    /**
     * @return Json
     * 删除主瓦种类
     */
    public function zhuwa_zhonglei_del(){
        $ids = Request::param('id');
        $zw = new P_zhonglei;
        $res = $zw->where('id',$ids)->delete();
        $price = new P_product_guige_price;
        $res2 = $price->where(["zhonglei_id"=>$ids])->delete();
        if($res && $res2){
            return $this->returns(1,$res,"删除成功");
        }else{
            return $this->returns(2,$res,"删除失败，或者不存在相关价格");
        }
    }
    /**
     * @return Json
     * 修改主瓦种类
     */
    public function zhuwa_zhonglei_update(){
        $houdu = Request::param('houdu');
        // $kuandu = Request::param('kuandu');
        $ids = Request::param('id');

        $zw = new P_zhonglei;
        $res = $zw->where('id',$ids)->update(['leibie'=>$houdu]);
        if($res){
            return $this->returns(1,$res,"修改成功");
        }else{
            return $this->returns(2,$res,"修改失败，或者与原数据无差别");
        }
    }

    /**
     * @return Json
     * 主瓦价格相关 获得各项索引列表
     */
    public function product_price_option(){
        $guige = new P_guige;
        $houdu = new P_houdu;
        $zhonglei = new P_zhonglei;

        $resdata = [
            "guige"=>$guige->order("kuandu")->select(),
            "houdu"=>$houdu->order("houdu")->select(),
            "zhonglei"=>$zhonglei->select()
        ];
        return $this->returns(1,$resdata);
    }
    /**
     * @return Json
     * 主瓦价格相关 管理界面
     */
    public function product_price_page(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return;
        }

        // $template = $this->temp();
        $template->fetch('product_price_page');
    }

    /**
     * @return Json
     * 主瓦价格相关 列表
     */
    public function product_price_list(){
        $pp = new P_product_guige_price;
        $pplist = $pp->order("guige_id,zhonglei_id,danwei,houdu_id")->select();

        $guige0 = new P_guige;
        $houdu0 = new P_houdu;
        $zhonglei0 = new P_zhonglei;

        
        $guige = $guige0->order("kuandu")->select();
        $houdu = $houdu0->order("houdu")->select();
        $zhonglei = $zhonglei0->select();

        #先对规格等项目进行数据改造，改成字典样式方便使用
        $guigedata = [];
        for($i=0;$i<count($guige);$i++){
            $guigedata[$guige[$i]['id']] = $guige[$i];
        }
        $hoududata = [];
        for($i=0;$i<count($houdu);$i++){
            $hoududata[$houdu[$i]['id']] = $houdu[$i];
        }
        $zhongleidata = [];
        for($i=0;$i<count($zhonglei);$i++){
            $zhongleidata[$zhonglei[$i]['id']] = $zhonglei[$i];
        }


        #下面对返回数据进行改造
        for($i=0;$i<count($pplist);$i++){
            $pplist[$i]['guige'] = $guigedata[$pplist[$i]['guige_id']]["names"];
            $pplist[$i]['houdu'] = $hoududata[$pplist[$i]['houdu_id']]["houdu"];
            $pplist[$i]['zhonglei'] = $zhongleidata[$pplist[$i]['zhonglei_id']]['leibie'];

            if($pplist[$i]['danwei']==1){
                $pplist[$i]['danwei_name']="米";
            }else{
                $pplist[$i]['danwei_name']="平方米";
            }
        }
        
        return json($pplist);

    }

    /**
     * @return Json
     * 主瓦价格相关 新增
     */
    public function product_prict_add(){
        $data = [
            'guige_id' => Request::param('guige_id'),
            'houdu_id' => Request::param('houdu_id'),
            'zhonglei_id' => Request::param('zhonglei_id'),
            // 'danjia' => Request::param('danjia'),
            'danwei' => Request::param('danwei'),
            'pro_id' => 1
        ];
        $pp  = new P_product_guige_price;

        $find1 = $pp->where($data)->find();
        if($find1!=null){
            return $this->returns(3,0,"已存在相同的 规格、种类、厚度 对应的价格，添加失败");
        }
        $data['danjia'] = Request::param('danjia');

        $res = $pp->insert($data);
        if($res){
            return $this->returns(1,$res,"添加成功");
        }else{
            return $this->returns(2,$res,"添加失败");
        }
    }

    /**
     * @return Json
     * 主瓦价格相关 删除
     */
    public function product_prict_del(){
        $pp  = new P_product_guige_price;
        $ids = Request::param('id');
        $res = $pp->where("id",$ids)->delete();
        if($res){
            return $this->returns(1,$res,"删除成功");
        }else{
            return $this->returns(2,$res,"删除失败");
        }
    }

    /**
     * @return Json
     * 主瓦价格相关 修改
     */

    public function product_prict_update(){
        $ids = Request::param('id');
        $price = Request::param('danjia');

        $pp  = new P_product_guige_price;
        $res = $pp->where('id',$ids)->update(['danjia'=>$price]);

        if($res){
            return $this->returns(1,$res,"修改成功");
        }else{
            return $this->returns(2,$res,"修改失败，或者未作出修改");
        }
    }




}