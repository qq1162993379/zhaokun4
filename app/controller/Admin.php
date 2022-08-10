<?php
namespace app\controller;

use Error;
use Exception;
use app\model\Counters;
use think\response\Html;
use think\response\Json;
use think\facade\Log;
use think\facade\Request;
use think\facade\Session;
use think\facade\Cookie;

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

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader;
use think\exception\ValidateException;


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
		// if(Session::has('ids')){
		// 	return 1;
		// }else{
		// 	return 0;
		// }
        return 1;
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
            return $this->returns(1,Session::get('ids'));
        }

        // $template = $this->temp();
        $template->fetch('admin');
    }
    /**销售用户界面 */
    public function user_xs_page(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return $this->returns(1,Session::get('ids'));
        }

        // $template = $this->temp();
        $template->fetch('user_xs_page');
    }
    /**销售用户列表 */
    public function user_xs_list(){
        $page = Request::param('page') == null ? 1:intval(Request::param('page'));
        $rows = Request::param('rows') == null ? 10:intval(Request::param('rows'));
        $users = new P_user;
        $res = $users->limit(($page-1)*$rows,$rows)->select();
        $zs = $users->field(['COUNT(id) AS "aa"'])->select();
        return json(['rows'=>$res,'total'=>$zs[0]['aa']]);
    }
    /**前台用户界面 */
    public function user_qt_page(){
        $template = $this->temp();
        if($this->yz()==0){
            $template->fetch('index');
            return $this->returns(1,Session::get('ids'));
        }

        // $template = $this->temp();
        $template->fetch('user_qt_page');
    }
    /**前台用户列表 */
    public function user_qt_list(){
        $page = Request::param('page') == null ? 1:intval(Request::param('page'));
        $rows = Request::param('rows') == null ? 10:intval(Request::param('rows'));
        $users = new P_qiantai_user;
        $res = $users->limit(($page-1)*$rows,$rows)->select();
        $zs = $users->field(['COUNT(id) AS "aa"'])->select();
        return json(['rows'=>$res,'total'=>$zs[0]['aa']]);
    }
    /**修改前台用户密码 */
    public function user_qt_editpass(){
        $id = Request::param('id');
        $passwords = Request::param('passwords');
        $qtuser = new P_qiantai_user;
        $res = $qtuser->where('id',$id)->update(['passwords'=>$passwords]); 
        if($res){
            return $this->returns(1,0,"修改成功");
        }else{
            return $this->returns(2,0,"修改失败");
        }
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
        // session_start();
        // setcookie("username",$res['username'],time()+3600*12);
        // setcookie("ids",$res['id'],time()+3600*12);
        // // setcookie("level",1,time()+3600*12);
        // $_SESSION["ids"] = $res['id'];
        // $_SESSION["username"] = $res['username'];
        // $_SESSION["level"] = 2;


        // session("ids",strval($res['id']));
        // session("username",strval($res['username']));
        // session("level",strval(2));

        // Cookie::set('username', strval($res['username']), 3600*12);
        // Cookie::set('ids', strval($res['id']), 3600*12);


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
        #是否有订单号
        $ddh0 = Request::param('dingdanhao');
        if($ddh0!=null){
            // $wheres['qhm'] = $qhm0;
            array_push($wheres,['dingdanhao','=',$ddh0]);
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

        //下面是对高级搜索的支持部分
        $money1 = Request::param('money1');
        if($money1!=null){
            if(strstr($money1,".") == false){
                $money1 = $money1.".00";
            }
            array_push($wheres,['money1','LIKE',$money1]);
        }
        $money2 = Request::param('money2');
        if($money2!=null){
            if(strstr($money2,".") == false){
                $money2 = $money2.".00";
            }
            array_push($wheres,['money2','LIKE',$money2]);
        }
        $guige = Request::param('guige');
        if($guige!=null){
            array_push($wheres,['zw_guige','=',$guige]);
        }
        $houdu = Request::param('houdu');
        if($houdu!=null){
            array_push($wheres,['zw_houdu','=',$houdu]);
        }
        $zhonglei = Request::param('zhonglei');
        if($zhonglei!=null){
            array_push($wheres,['zw_zhonglei','=',$zhonglei]);
        }
        $yanse = Request::param('yanse');
        if($yanse!=null){
            array_push($wheres,['zw_yanse','=',$yanse]);
        }


        #构造数据查询
        $dingdan = new P_dingdan;
        if(count($wheres)>0){
            $res = $dingdan->where($wheres)->order("times",'desc')->limit(($page-1)*$rows,$rows)->select();
        }else{
            $res = $dingdan->order("times",'desc')->limit(($page-1)*$rows,$rows)->select();
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
        $resdata['zw_zhonglei'] = $sel['zw_zhonglei'];
        $resdata['zw_danwei'] = $sel['zw_danwei'];
        $resdata['beizhu'] = $sel['beizhu'];
        $resdata['zw_guige2'] = $sel['zw_guige2'];
        $resdata['dingdanhao'] = $sel['dingdanhao'];
        $resdata['ysr'] = $sel['ysr'];

        $resdata0=[
            'dingdan'=>$resdata,
            'mingxi' =>$mingxi_data,
            'zhuwa' =>$zhuwa_data
        ];
        return $this->returns(1,$resdata0);
    }

    /**
     * *订单导出 导出文件生成 已弃用
     * @return Json
     */
    public function dingdan_daochu2(){
        $did = Request::param('id');
        //先拿到订单数据
        $dd = new P_dingdan;
        $mx = new P_dingdan_mingxi;
        $zw = new P_dingdan_zhuwa;
        $users = new P_user;

        $dddata = $dd->where('id',$did)->find();
        $mxdata = $mx->where('dd_id',$did)->select();
        $zwdata = $zw->where('dd_id',$did)->select();

        //制作需要的导出数据
        /**  核销清单 */
        //分三个格子填单号
        $danhao = $dddata['dingdanhao'];
        $danhao1 = "单号：".substr($danhao,0,9);
        $danhao2 = substr($danhao,9,5);
        $danhao3 = substr($danhao,14);
        //日期
        $dd_date = date("Y年n月j日",$dddata['times']);
        //销售
        $userdate = $users->where("id",$dddata['user_id'])->find();
        $username = $userdate['names'];
        //规格
        $dd_guige = $dddata['zw_guige'];
        //厚度
        $dd_houdu = $dddata['zw_houdu'];
        //颜色
        $dd_yanse = $dddata['zw_yanse'];
        //明细条目 包括【序号 名称 规格 单位 数量 单价】
        $mx_tm = [];
        for($i=0;$i<count($mxdata);$i++){
            $mx_tm[$i][0] = $i+1;
            $mx_tm[$i][1] = $mxdata[$i]['pro_names'];
            $mx_tm[$i][2] = $mxdata[$i]['guige'];
            $mx_tm[$i][3] = $mxdata[$i]['danwei'];
            $mx_tm[$i][4] = $mxdata[$i]['pro_id']==1?$mxdata[$i]['numbers']:number_format($mxdata[$i]['numbers'],0);
            $mx_tm[$i][5] = $mxdata[$i]['money2'];
        }
        
        //主瓦条目 包括【波数 长度 块数】
        //先拿到长度：波数 字典
        $zwcc0 = new P_zhuwa_chicun;
        $zwcc = $zwcc0->select();
        $zwcc2 = [];
        for($i=0;$i<count($zwcc);$i++){
            $zwcc2[$zwcc[$i]['chicun']] = $zwcc[$i]['boshu'];
        }
        
        $zw_tm = [];//主瓦二维数组
        for($i=0;$i<count($zwdata);$i++){
            $ls = [];
            // $zw_tm[$i][0] = $zwcc2[$zwdata[$i]['changdu']];
            // $zw_tm[$i][1] = $zwdata[$i]['changdu'];
            // $zw_tm[$i][2] = $zwdata[$i]['numbers'];
            if($zwdata[$i]['numbers']>0){
                $ls[0] = $zwcc2[$zwdata[$i]['changdu']];
                $ls[1] = $zwdata[$i]['changdu'];
                $ls[2] = $zwdata[$i]['numbers'];
                array_push($zw_tm,$ls);
            }
        }
        // return json($zw_tm);
        //加载xlsx文件
        $mobanpath = "../public/static/excel/moban.xlsx";
        $downpath = "../public/static/excel/down/";
        $objReader = IOFactory::createReader('Xlsx');
        $objReader->setReadDataOnly(TRUE);
        $objPHPExcel = $objReader->load($mobanpath);
        if(count($zw_tm)>15 || count($mx_tm)>15){//大表
            $sheet = $objPHPExcel->getSheet(1);
        }else{                                   //小表
            $sheet = $objPHPExcel->getSheet(0);
            $sheet->fromArray($mx_tm, null, "A6");
            $sheet->fromArray($zw_tm, null, "H6");
            $sheet->setCellValue("A2",$danhao1);
            $sheet->setCellValue("C2",$danhao2);
            $sheet->setCellValue("D2",$danhao3);
            $sheet->setCellValue("J2",$dd_date);
            $sheet->setCellValue("C3",$username);
            $sheet->setCellValue("F3",$dd_guige);
            $sheet->setCellValue("H3",$dd_houdu);
            $sheet->setCellValue("J3",$dd_yanse);
        }
        $newExcel = new Spreadsheet();
        $newExcel->addSheet($sheet);
        $newExcel->removeSheetByIndex(0);
        $writer = IOFactory::createWriter($newExcel, "Xlsx");
        $writer->save($downpath.$danhao."_".time().".xlsx");
    }

    public function banbentest(){
        // $data = 1;
        return json(["data"=>phpinfo()]);
    }

    /**
     * *订单导出 导出文件生成
     * @return Json
     */
    public function dingdan_daochu(){
        //先将数据处理好，然后复制原模版，在读取新文件然后赋值和保存，不设只读，就能保留格式

        $did = Request::param('id');
        //先拿到订单数据
        $dd = new P_dingdan;
        $mx = new P_dingdan_mingxi;
        $zw = new P_dingdan_zhuwa;
        $users = new P_user;

        $dddata = $dd->where('id',$did)->find();
        $mxdata = $mx->where('dd_id',$did)->select();
        $zwdata = $zw->where('dd_id',$did)->select();

        //制作需要的导出数据
        /**  核销清单 */
        //分三个格子填单号
        $danhao = $dddata['dingdanhao'];
        $danhao1 = "单号：".substr($danhao,0,9);
        $danhao2 = substr($danhao,9);
        // $danhao3 = substr($danhao,14);
        //日期
        $dd_date = date("Y年n月j日",$dddata['times']);
        //销售
        $userdate = $users->where("id",$dddata['user_id'])->find();
        $username = $userdate['names'];
        //规格
        $dd_guige = $dddata['zw_guige'];
        //厚度
        $dd_houdu = $dddata['zw_houdu'];
        //颜色
        $dd_yanse = $dddata['zw_yanse'];
        //验收人
        $dd_ysr = $dddata['ysr'];
        //明细条目 包括【序号 名称 规格 单位 数量 单价】
        $mx_tm = [];
        for($i=0;$i<count($mxdata);$i++){
            $mx_tm[$i][0] = $i+1;
            $mx_tm[$i][1] = $mxdata[$i]['pro_names'];
            $mx_tm[$i][2] = $mxdata[$i]['guige'];
            $mx_tm[$i][3] = $mxdata[$i]['danwei'];
            $mx_tm[$i][4] = $mxdata[$i]['pro_id']==1?$mxdata[$i]['numbers']:number_format($mxdata[$i]['numbers'],0);
            $mx_tm[$i][5] = $mxdata[$i]['money2'];
        }
        
        //主瓦条目 包括【波数 长度 块数】
        //先拿到长度：波数 字典
        $zwcc0 = new P_zhuwa_chicun;
        $zwcc = $zwcc0->select();
        $zwcc2 = [];
        for($i=0;$i<count($zwcc);$i++){
            $zwcc2[$zwcc[$i]['chicun']] = $zwcc[$i]['boshu'];
        }
        
        $zw_tm = [];//主瓦二维数组
        for($i=0;$i<count($zwdata);$i++){
            $ls = [];
            // $zw_tm[$i][0] = $zwcc2[$zwdata[$i]['changdu']];
            // $zw_tm[$i][1] = $zwdata[$i]['changdu'];
            // $zw_tm[$i][2] = $zwdata[$i]['numbers'];
            if($zwdata[$i]['numbers']>0){
                $ls[0] = $zwcc2[$zwdata[$i]['changdu']];
                $ls[1] = $zwdata[$i]['changdu'];
                $ls[2] = $zwdata[$i]['numbers'];
                array_push($zw_tm,$ls);
            }
        }
        // return json($zw_tm);
        //加载xlsx文件
        $mobanpath = "../public/static/excel/moban.xlsx";
        // $downpath = "../public/static/excel/down/";
        $downpath = "./text/";
        $new_file = $danhao."_".time().".xlsx";

        copy($mobanpath,$downpath.$new_file);

        $objReader = IOFactory::createReader('Xlsx');
        // $objReader->setReadDataOnly(TRUE);
        $objPHPExcel = $objReader->load($downpath.$new_file);
        //

        //已经成功保留格式 现在就确认下模板是哪个，然后删除另一个即可


        if(count($zw_tm)>15 || count($mx_tm)>15){//大表
            $sheet = $objPHPExcel->getSheet(1);
            $sheet->fromArray($mx_tm, null, "A6");
            $sheet->fromArray($zw_tm, null, "H6");
            $sheet->setCellValue("A2",$danhao1);
            $sheet->setCellValue("C2",$danhao2);
            // $sheet->setCellValue("D2",$danhao3);
            $sheet->setCellValue("J2",$dd_date);
            $sheet->setCellValue("C3",$username);
            $sheet->setCellValue("F3",$dd_guige);
            $sheet->setCellValue("H3",$dd_houdu);
            $sheet->setCellValue("J3",$dd_yanse);
            $sheet->setCellValue("U47",$dd_ysr);
            $objPHPExcel->removeSheetByIndex(0);

        }else{                                   //小表
            $sheet = $objPHPExcel->getSheet(0);
            $sheet->fromArray($mx_tm, null, "A6");
            $sheet->fromArray($zw_tm, null, "H6");
            $sheet->setCellValue("A2",$danhao1);
            $sheet->setCellValue("C2",$danhao2);
            // $sheet->setCellValue("D2",$danhao3);
            $sheet->setCellValue("J2",$dd_date);
            $sheet->setCellValue("C3",$username);
            $sheet->setCellValue("F3",$dd_guige);
            $sheet->setCellValue("H3",$dd_houdu);
            $sheet->setCellValue("J3",$dd_yanse);
            $sheet->setCellValue("U22",$dd_ysr);
            $objPHPExcel->removeSheetByIndex(1);
            
        }
        // $newExcel = new Spreadsheet();
        // $newExcel->addSheet($sheet);
        // $newExcel->removeSheetByIndex(0);
        $writer = IOFactory::createWriter($objPHPExcel, "Xlsx");
        $writer->save($downpath.$new_file);
        return $this->returns(1,$new_file,"导出成功");
    }

    /**
     * *订单导出 导出文件下载
     * @return Json
     */
    public function dingdan_daochu_down(){
        $downpath = "../public/static/excel/down/";
        $names = Request::param('names');
        return download($downpath.$names, $names);
    }
    /**
     * *订单导出 清空缓存文件
     * @return Json
     */
    public function dingdan_huancun_del(){
        $dir = "../public/static/excel/down/";
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    // echo "filename: $file : filetype: " . filetype($dir . $file) . "\n";
                    if(strpos($file,".xlsx")){
                        unlink($dir.$file);
                    }
                    
                }
                closedir($dh);
            }
        }
        return $this->returns(1,0,"删除完毕");
    }


    
/**  订单修改相关部分 */

    /**
     * @return Json
     * 订单修改记录
     */
    public function dd_xiugai_jl(){
        $did = Request::param('id');
        // $resdata0 = [
        //     'yuanshi'=>[],
        //     'xiugai'=>[]
        // ];
        /**
         * 订单修改的逻辑是 用户提交修改后，将原始订单状态变成 已修改待确认，然后将修改内容存到 xxxx_up表中
         * 若用户在管理员确认前进行撤回，则直接删除up表中内容
         * 若管理员查看后进行确认，则将up表中内容替换到原始表中，删除up表内容，并将状态改成已确认
         * 换句话说，已确认的修改不可撤回
         */

        $dd1 = new P_dingdan;
        $mx1 = new P_dingdan_mingxi;
        $zw1 = new P_dingdan_zhuwa;
        $dd2 = new P_dingdan_up;
        $mx2 = new P_dingdan_mingxi_up;
        $zw2 = new P_dingdan_zhuwa_up;


        $mingxi_data = $mx1->where("dd_id",$did)->select();//原始明细
        $zhuwa_data = $zw1->where("dd_id",$did)->select();//原始主瓦
        $mingxi_data_xg = $mx2->where("dd_id",$did)->select();
        $zhuwa_data_xg = $zw2->where("dd_id",$did)->select();
        
        $sel = $dd1->where('id',$did)->find();
        $sel2 = $dd2->where('dd_id',$did)->find();

        if($sel == null){
            return $this->returns(0,$did,"查无此订单");
        }
        if($sel2 == null){
            return $this->returns(0,$did,"未找到修改数据");
        }

        $usermod = new P_user;
        $user = $usermod->where('id',$sel['user_id'])->find();

        //构造返回数据
        $resdata = [];
        $resdata['id'] = $sel['id']; 
        $resdata['times'] = date("Y-m-d H:i:s",$sel['times']);
        $resdata['times2'] = date("Y-m-d H:i:s",$sel2['times']);
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
        $resdata['money1'] = $sel['money1'].'->'.$sel2['money1'];
        $resdata['money2'] = $sel['money2'].'->'.$sel2['money2'];
        $resdata['jz'] = $sel['jz'];
        $resdata['qhm'] = $sel['qhm'];
        $resdata['zw_guige'] = $sel['zw_guige'].'->'.$sel2['zw_guige'];
        $resdata['zw_guige2'] = $sel['zw_guige2'].'->'.$sel2['zw_guige2'];
        $resdata['zw_houdu'] = $sel['zw_houdu'].'->'.$sel2['zw_houdu'];
        $resdata['zw_yanse'] = $sel['zw_yanse'].'->'.$sel2['zw_yanse'];
        $resdata['zw_zhonglei'] = $sel['zw_zhonglei'].'->'.$sel2['zw_zhonglei'];
        $resdata['zw_danwei'] = $sel['zw_danwei'];
        $resdata['zw_danwei2'] = $sel2['zw_danwei'];
        $resdata['beizhu'] = $sel['beizhu'].'-><font color="green">'.$sel2['beizhu'].'</font>';
        $resdata['dingdanhao'] = $sel['dingdanhao'];
        $resdata['ysr'] = $sel['ysr'];


        //遍历明细和主瓦，构建返回格式
        //首先对修改订单进行处理，变成字典
        $mingxi_data_xg0 = [];
        for($i=0;$i<count($mingxi_data_xg);$i++){
            $mingxi_data_xg0[$mingxi_data_xg[$i]['pro_id']] = $mingxi_data_xg[$i];
        }
        $mingxi_data0 = [];
        for($i=0;$i<count($mingxi_data);$i++){
            $mingxi_data0[$mingxi_data[$i]['pro_id']] = $mingxi_data[$i];
        }

        for($i=0;$i<count($mingxi_data);$i++){
            $zj = $mingxi_data0[$mingxi_data[$i]['pro_id']];
            if($zj["guige"] != $mingxi_data[$i]['guige']){
                $mingxi_data[$i]['guige'] .= '-><font color="green">'.$zj["guige"].'</font>';
            }
            // if($zj["numbers"] != $mingxi_data[$i]['numbers']){
                $mingxi_data[$i]['numbers'] .= '-><font color="green">'.$zj["numbers"].'</font>';
            // }
            if($zj["zmoney"] != $mingxi_data[$i]['zmoney']){
                $mingxi_data[$i]['zmoney'] .= '-><font color="green">'.$zj["zmoney"].'</font>';
            }
        }
        //判断是否有新增产品
        for($i=0;$i<count($mingxi_data_xg);$i++){
            $proid = $mingxi_data_xg[$i]['pro_id'];
            if(!array_key_exists($proid,$mingxi_data0)){//修改订单中的某一项在原来的数据中不存在
                $mingxi_data_xg[$i]['numbers'] = '0-><font color="green">'.$mingxi_data_xg[$i]['numbers'].'</font>';
                $mingxi_data_xg[$i]['zmoney'] = '0-><font color="green">'.$mingxi_data_xg[$i]['zmoney'].'</font>';
                $mingxi_data_xg[$i]['pro_names'] = '<font color="green">'.$mingxi_data_xg[$i]['pro_names'].'</font>';
                array_push($mingxi_data,$mingxi_data_xg[$i]);
            }

        }




        $resdata0=[
            'dingdan'=>$resdata,
            'mingxi' =>$mingxi_data,
            'zhuwa' =>$zhuwa_data,
            'zhuwa2' =>$zhuwa_data_xg
        ];
        return $this->returns(1,$resdata0);

        


    }

    /**
     * @return Json
     * 订单状态修改 确认修改
     */
    public function dingdan_xg_qr(){
        //确认修改 将up库的内容复制到基础库，并将原本内容删除
        $did = Request::param('id');

        
        $dd1 = new P_dingdan;
        $mx1 = new P_dingdan_mingxi;
        $zw1 = new P_dingdan_zhuwa;
        $dd2 = new P_dingdan_up;
        $mx2 = new P_dingdan_mingxi_up;
        $zw2 = new P_dingdan_zhuwa_up;
        //删除现在库
        $dd1->where('id',$did)->delete();
        $mx1->where('dd_id',$did)->delete();
        $zw1->where('dd_id',$did)->delete();

        //读取修改库
        $dddata = $dd2->where('dd_id',$did)->find();
        $mxdata = $mx2->where('dd_id',$did)->select();
        $zwdata = $zw2->where('dd_id',$did)->select();
        // $dddata = $dddata[0];
        // return $dddata;
        $dddata = json_decode(json_encode($dddata),TRUE);
        // return json($dddata);
        //先存新订单并生成新的订单号
        unset($dddata['id']);
        unset($dddata['dd_id']);
        $dddata['zt'] = 2;
        // return json($dddata);
        $ddid = $dd1->insertGetId($dddata);//新的订单号
        
        //构建明细、主瓦存储
        $mxdata0 = [];
        $zwdata0 = [];
        for($i=0;$i<count($mxdata);$i++){
            $ls= json_decode(json_encode($mxdata[$i]),TRUE);
            unset($ls['id']);
            $ls['dd_id'] = $ddid;
            $mxdata0[$i]=$ls;
        }
        for($i=0;$i<count($zwdata);$i++){
            
            $ls= json_decode(json_encode($zwdata[$i]),TRUE);
            unset($ls['id']);
            $ls['dd_id'] = $ddid;
            $zwdata0[$i]=$ls;
        }
        //存储明细、主瓦数据
        $res1 = $mx1->insertAll($mxdata0);
        $res2 = $zw1->insertAll($zwdata0);

        //删除临时修改数据
        $mx2->where('dd_id',$did)->delete();
        $zw2->where('dd_id',$did)->delete();
        $dd2->where('dd_id',$did)->delete();

        if($res1 && $res2){
            return $this->returns(1,0,"修改确认成功");
        }else{
            return $this->returns(2,0,"修改确认失败");
        }

    }


    //后台订单修改部分
    /**
     * @return Json
     * 订单修改 增加订单明细产品
     * 
     */
    public function dingdan_xg_add_mx(){
        $did = Request::param('id');//订单id
        $pid = Request::param('pro_id');
        $numb = Request::param('numbs');
        $money1 = Request::param('money1');

        $dmx = new P_dingdan_mingxi;
        $pro = new P_product;
        $dd = new P_dingdan;

        //先从product表中查出产品数据，然后计算总金额，添加到订单明细表中，再读取订单表的总金额，然后加上该明细的金额数修改回去
        $prodata = $pro->where('id',$pid)->find();
        $danjia = $prodata['danjia'];
        $danwei = $prodata['danwei'];
        $proname = $prodata['names'];

        $pro_zongjine = number_format($danjia*$numb,4);//这是新增物品的总金额
        $pro_zongjine2 = number_format($money1*$numb,4);//用户指定总金额
        //ps：这里默认新加的订单明细是原本明细中没有的新产品，不再做叠加判断
        
        $res1 = $dmx->insert(['dd_id'=>$did,'pro_id'=>$pid,'money1'=>$money1,'pro_names'=>$proname,'money2'=>$danjia,'numbers'=>$numb,'danwei'=>$danwei,'zmoney'=>$pro_zongjine]);

        //明细数据插入完，读取订单数据
        $dingdan_zje = $dd->where('id',$did)->find();
        $zje1 = $dingdan_zje['money1']+$pro_zongjine2;//用户
        $zje2 = $dingdan_zje['money2']+$pro_zongjine;//实际
        //修改
        $res2 = $dd->where('id',$did)->update(['money1'=>$zje1,'money2'=>$zje2]);

        if($res1 && $res2){
            return $this->returns(1,0,"添加成功");
        }else{
            return $this->returns(1,0,"添加失败");
        }
    }
    /**
     * @return Json
     * 订单修改 增加订单中的主瓦
     * 
     */
    public function dingdan_xg_add_zw(){
        $dd = new P_dingdan;
        $zw = new P_dingdan_zhuwa;
        $zw_cc = new P_zhuwa_chicun;
        $mx = new P_dingdan_mingxi;
        // $dd = new P_dingdan;
        $did =  Request::param('id');//订单id
        $cid = Request::param('chicun');
        $numbs = Request::param('numb');

        //首先读取出chicun的id对应的尺寸数目
        //然后计算总量，并添加到zw表中（这里还需要读取一下宽度，即订单表中的zw_guige2）
        //然后将新增总量增加到明细表中主瓦项上，并计算该项新的金额
        //然后将新的金额添加到订单表中

        //1首先读取出chicun的id对应的尺寸数目
        $chicun_data = $zw_cc->where('id',$cid)->find();
        
        $chicun_sj = $chicun_data['chicun'];
        //2然后计算总量，并添加到zw表中（这里还需要读取一下宽度，即订单表中的zw_guige2）
        $dingdan_data = $dd->where('id',$did)->find();
        $kuandu = $dingdan_data['zw_guige2'];
        $zongliang1 = number_format($numbs*$chicun_sj,4);//米
        $zongliang2 = number_format($numbs*$chicun_sj*$kuandu,4);//平方米
        $res1 = $zw->insert(['dd_id'=>$did,'changdu'=>$chicun_sj,'numbers'=>$numbs,'chicun1'=>$zongliang1,'chicun2'=>$zongliang2]);
        //3然后将新增总量增加到明细表中主瓦项上，并计算该项新的金额
        $zw_mx = $mx->where(['dd_id'=>$did,'pro_id'=>1])->find();//即默认主瓦的proid是1

        $ys_numb = $zw_mx['numbers'];
        $ys_danjia = $zw_mx['money2'];
        // $new_numb = $ys_numb + $zong
        //这里还和主瓦的计算单位有关，米/平方米
        $zw_danwei = $dingdan_data['zw_danwei'];
        $ys_zmoney = $zw_mx['zmoney'];

        $yh_danjia = $zw_mx['money1'];

        if($zw_danwei==1){
            $new_numb = $ys_numb + $zongliang1;//新的主瓦数量
            $add_money = number_format($zongliang1*$ys_danjia,4);//增加的金额
            $add_money2 = number_format($zongliang1*$yh_danjia,4);//增加的用户自定金额总金额
        }else{
            $new_numb = $ys_numb + $zongliang2;
            $add_money = number_format($zongliang2*$ys_danjia,4);
            $add_money2 = number_format($zongliang2*$yh_danjia,4);
        }
        
        $res2 = $mx->where(['dd_id'=>$did,'pro_id'=>1])->update(['numbers'=>$new_numb,'zmoney'=>$add_money+$ys_zmoney]);

        //4然后将新的金额添加到订单表中
        $ys_dd_zmoney1 = $dingdan_data['money1']+$add_money2;
        $ys_dd_zmoney2 = $dingdan_data['money2']+$add_money;
        $res3 = $dd->where('id',$did)->update(['money1'=>$ys_dd_zmoney1,'money2'=>$ys_dd_zmoney2]);

        if($res1 && $res2 && $res3){
            return $this->returns(1,0,"添加成功");
        }else{
            return $this->returns(1,0,"添加失败");
        }

    }

    /**
     * @return Json
     * 订单修改 提交修改
     * 
     */
    public function dingdan_xg_tjxg(){
        // $postdata = Request::param('data');
        $postdata = base64_decode(Request::param('data'));
        $data = json_decode($postdata,true);//三项 data mxdata zwdata
        $dddata = $data['data'];
        $mxdata = $data['mxdata'];
        $zwdata = $data['zwdata'];
        
        //拿到数据之后，开始进行修改
        $dd = new P_dingdan;
        $zw = new P_dingdan_zhuwa;
        $mx = new P_dingdan_mingxi;
        //首先是订单主体部分
        $did = $dddata['did'];
        unset($dddata['did']);
        $dd->where('id',$did)->update($dddata);

        //然后是明细部分
        for($i=0;$i<count($mxdata);$i++){
            $mxtm = $mxdata[$i];
            $ids = $mxtm['id'];
            unset($mxtm['id']);
            $mx->where('id',$ids)->update($mxtm);
        }

        //然后是主瓦部分
        for($i=0;$i<count($zwdata);$i++){
            $zwtm = $zwdata[$i];
            $ids = $zwtm['id'];
            unset($zwtm['id']);
            $zw->where('id',$ids)->update($zwtm);
        }

        return $this->returns(1,1,"修改完成");
    }

    /**
     * @return Json
     * 订单修改 删除明细条目
     * 
     */
    public function dingdan_xg_mx_del(){
        //基本思路，先找到对应的proid，是主瓦则不允许删除
        //然后删除对应体条目，并找到ddid，然后读取ddid对应的所有条目，求和获得总订单的money1和money2，进行更新
        $mxid = Request::param('id');
        $dd = new P_dingdan;
        $mx = new P_dingdan_mingxi;

        $mxdat1 = $mx->where('id',$mxid)->find();
        if(!$mxdat1){
            return $this->returns(2,$mxdat1,"找不到对应明细");
        }
        if($mxdat1['pro_id'] == 1){
            return $this->returns(3,0,"主瓦条目不允许删除");
        }
        $ddid = $mxdat1['dd_id'];//拿到订单id
        $res1 = $mx->where('id',$mxid)->delete();
        if(!$res1){
            return $this->returns(4,0,"条目删除时失败1");
        }
        $new_mx = $mx->where('dd_id',$ddid)->select();
        $zmoney1 = 0;
        $zmoney2 = 0;
        for($i=0;$i<count($new_mx);$i++){
            $zmoney1 += $new_mx[$i]['numbers']*$new_mx[$i]['money1'];
            $zmoney2 += $new_mx[$i]['zmoney'];
        }
        $zmoney1 = number_format($zmoney1,2);//用户自定总价
        $zmoney2 = number_format($zmoney2,2);//实际订单总金额
        $res2 = $dd->where('id',$ddid)->update(['money1'=>$zmoney1,'money2'=>$zmoney2]);
        if($res2){
            return $this->returns(1,0,"删除成功，总金额修改成功");
        }else{
            return $this->returns(0,0,"删除成功，总金额修改失败");
        }
    }

    /**
     * 订单删除
     * 
     */
    public function dingdan_del(){
        $did = Request::param('id');
        $dd = new P_dingdan;
        $mx = new P_dingdan_mingxi;
        $zw = new P_dingdan_zhuwa;
        $res1 = $dd->where('id',$did)->delete();
        $res2 = $mx->where('dd_id',$did)->delete();
        $res3 = $zw->where('dd_id',$did)->delete();
        if($res1 && $res2 && $res3){
            return $this->returns(1,0,"删除成功");
        }else{
            return $this->returns(2,0,"删除失败");
        }
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
     * 订单状态修改 取货
     */
    public function dingdan_quhuo():Json{
        #登陆状态验证
        if($this->yz()==0){
            // $template->fetch('index');
            return $this->returns(3,0,"登陆状态错误");
        }


        $did = Request::param('id');
        $qhm = Request::param('qhm');//这里进行了变更，传过来的值是验收人姓名
        $dd = new P_dingdan;

        // $res = $dd->where('id',$did)->find();
        // if($res['qhm'] == $qhm){
        
        


        $res1 = $dd->where('id',$did)->update(['zt'=>4,'ysr'=>$qhm]);
        if($res1){
            return $this->returns(1,$res1,"取货成功");
        }else{
            return $this->returns(0,$res1,"数据库错误，修改失败");
        }
        // }else{
        //     return $this->returns(2,$res,"取货码验证错误");
        // }
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