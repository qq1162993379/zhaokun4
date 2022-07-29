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


use think\facade\View;

/**
 *公司前台控制器，主要业务为 1确认订单内容（确认接受订单） 2确认已配货 3确认取货   
 **/
class Qiantai{
    #模板渲染加载
    private function temp(){
        $config = [
            'view_path'	    =>	'../app/view/qiantai/',
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
		if(isset($_COOKIE["ids"]) && isset($_SESSION["ids"]) && isset($_SESSION["level"]) && $_COOKIE["ids"]!=null && $_SESSION["ids"] == $_COOKIE["ids"] && $_SESSION["level"] >= 1){
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
        $qiantai_user = new P_qiantai_user;
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
        $_SESSION["level"] = 1;

        return $this->returns(1,1,"登陆成功");

    }

    /**
     * 待接收订单列表界面
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
        if($this->yz()==0){
            // $template->fetch('index');
            return json(['rows'=>[['username'=>"登陆失效"]],'total'=>0]);
        }
        #接收页码和每页数量
        $page = Request::param('page') == null ? 1:intval(Request::param('page'));
        $rows = Request::param('rows') == null ? 10:intval(Request::param('rows'));

        $wheres = [];#查询条件
        #是否有状态参数
        $zt0 = Request::param('zt');
        if($zt0!=null){
            $wheres['zt'] = $zt0;
        }
        #是否有取货吗
        $qhm0 = Request::param('qhm');
        if($qhm0!=null){
            $wheres['qhm'] = $qhm0;
        }
        #是否有用户姓名（重名怎么办？先不考虑）
        $username0 = Request::param('username');
        if($username0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('names',$username0)->find();
            if($udat1 == null){
                $wheres['user_id'] = 0;
            }else{
                $wheres['user_id'] = $udat1['id'];
            }
        }
        #是否有tel
        $tel0 = Request::param('tel');
        if($tel0!=null){
            $user1 = new P_user;
            $udat1 = $user1->where('tel',$tel0)->find();
            if($udat1 == null){
                $wheres['user_id'] = 0;
            }else{
                $wheres['user_id'] = $udat1['id'];
            }
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

            $zt1 = "";
            $caozuo = "";
            switch($res[$i]['zt']){
                case 1: $zt1 = "<font color='red'>已下单，待接受</font>";$caozuo="<button onclick='dd_jieshou(".$res[$i]['id'].")'>接收订单</button>";break;
                case 2: $zt1 = "<font color='yellow'>已接受，备货中</font>";$caozuo="<button onclick='dd_kequhuo(".$res[$i]['id'].")'>通知取货</button>";break;
                case 3: $zt1 = "<font color='green'>可取货</font>";$caozuo="<button onclick='dd_quhuo(".$res[$i]['id'].")'>取货</button>";break;
                case 4: $zt1 = "<font color='blue'>取货完毕</font>";break;
            }
            $res[$i]['zt'] = $zt1;
            $res[$i]['infor'] = "<button onclick='chakanxiangqing(".$res[$i]['id'].")'>查看详情</button>";
            $res[$i]['clicks'] = $caozuo;
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

        $resdata['beizhu'] = $sel['beizhu'];

        $resdata0=[
            'dingdan'=>$resdata,
            'mingxi' =>$mingxi_data,
            'zhuwa' =>$zhuwa_data
        ];
        return $this->returns(1,$resdata0);
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
     * 添加用户测试接口
     */
    public function addqiantai(){
        $uname = Request::param('uname');
        $password = Request::param('password');
        $qiantai_user = new P_qiantai_user;
        $res = $qiantai_user->insert(['username'=>$uname,'passwords'=>$password]);
        return $this->returns(1,$res);
    }
    /**
     * homepage
     * @return Html
     */
    public function homepage(): Html
    {
        # html路径: ../view/Qiantai/admin.html
        return response(file_get_contents(dirname(dirname(__FILE__)).'/view/Qiantai/homepage.html'));
    }
}