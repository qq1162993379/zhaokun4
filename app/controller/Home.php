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
use app\model\P_zhuwa_chicun;
use app\model\P_dingdan_houdu; 
use app\model\P_dingdan_up;
use app\model\P_dingdan_mingxi_up;
use app\model\P_dingdan_zhuwa_up;


class Home{

    #获取当前连接用户的openid对应的数据库条目
    private function getuserid(){
        $opid = Request::header('x-openapi-seqid');
        $users = new P_user;
        $fin = $users->where('opid',$opid)->find();
        return $fin;
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



    /** 
    * @return Json
    * 用户注册状态查询
    */
    public function userzt():Json{
        $fin = $this->getuserid();
        if($fin == null){#未注册的新用户
            $users = new P_user;
            $opid = Request::header('x-openapi-seqid');
            $datas = ['opid'=>$opid,'names'=>'','tel'=>''];
            $res0 = $users->save($datas);

            return $this->returns(1,$res0);
        }else{#已注册用户
            // $datas = ['opid'=>$opid,'names'=>'','tel'=>''];
            // $res0 = $users->save($datas);
            // $res0 = $users->where('id',$fin['id'])->update($datas);
            $data = [
                'names' => $fin['names'],
                'tel' => $fin['tel']
            ];
            return $this->returns(2,$data);
        }
    }

    /**
     * @return Json
     * 产品目录获取接口
     */
    public function productlist():Json{
        $pro = new P_product;
        $res0 = $pro->select();
        return $this->returns(1,$res0);
    }
    /**
     * @return Json
     * 用户个人价格获取接口
     */
    public function user_price():Json{
        $fin =$this->getuserid();
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        $ids = $fin['id'];
        $up = new P_user_price;
        $data = $up->where('user_id',$ids)->select();
        if(count($data) == 0){
            return $this->returns(2,0,"用户不存在自定价格");
        }else{
            return $this->returns(1,$data);
        }
    }
    /**
     * @return Json
     * 用户个人价格初始化,为使用简便，默认为初始化为实际价格
     */
    public function user_priceadd():Json{
        $fin =$this->getuserid();
        
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        $ids = $fin['id'];
        $up = new P_user_price;

        $old = $up->where('user_id',$ids)->select();#已有的自定价格

        $pro = new P_product;
        $data1 = $pro->select();
        $data = [];
        for($i = 0; $i < count($data1); $i++){
            $dat0 = [
                'user_id' => $ids,
                'pro_id' => $data1[$i]['id'],
                'price' => $data1[$i]['danjia'],
            ];
            $data[$i] = $dat0;
        }
        #为了适应“新增商品”事件，增加一个刷新机制，即增加未记录的商品的自定价格初始化
        #￥data已经存了已有商品的价格条目，现在对其进行删减，将不存在old中的条目作为实际插入条目
        $new_dat = [];
        $j = 0;
        for($i = 0; $i < count($data); $i++){
            $jl = 0;
            for($k=0;$k<count($old);$k++){
                if($data[$i]['pro_id']==$old[$k]['pro_id']){
                    $jl = 1;
                }
            }
            if($jl == 0){
                $new_dat[$j] = $data[$i];
                $j++;
            }
        }


        $res0 = $up->insertAll($new_dat);
        return $this->returns(1,$res0);
    }

    /**
     * @return Json
     * 用户个人价格修改(单条)
     */
    public function user_priceupdate():Json{
        $fin =$this->getuserid();
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        $ids = $fin['id'];
        $up = new P_user_price;

        $pid = Request::param('pid');
        $price = Request::param('price');

        $res = $up->where(['pro_id'=>$pid,'user_id'=>$ids])->update(['price'=>$price]);

        return $this->returns(1,$res);
    }

    /**
     * @return Json
     * 用户个人信息修改
     */
    public function userinfoupdate():Json{
        $fin =$this->getuserid();
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        $date = [
            'names'=>Request::param('names'),
            'tel'=>Request::param('tel')
        ];
        $users = new P_user;
        $res = $users->where('id',$fin['id'])->update($date);
        return $this->returns(1,$res);
    }
    /**
     * @return Json
     * 用户个人信息获取
     */
    public function userinfo():Json{
        $fin =$this->getuserid();
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        $data = [
            'names' => $fin['names'],
            'tel' => $fin['tel']
        ];
        return $this->returns(1,$data);
    }

    /**
     * @return Json
     * 订单提交
     */
    public function dingdan():Json{
        $fin =$this->getuserid();
        ;
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        //获取产品的详细信息列表
        $pro = new P_product;
        $pro_data0 = $pro->select();
        $pro_data = [];
        for($i = 0; $i < count($pro_data0); $i++){//做一个根据pro_id的索引，方便后续使用
            $pro_data[$pro_data0[$i]['id']] = $pro_data0[$i];
        }
        //接受提交的订单数据，收到的数据格式为json，经过base64编码，形式为：{mingxi:[{pro_id:xxx,pro_names:xxx,numbers:xxx,money1:xxx},{},...,{}],zhuwa:[{changdu:xx,numbers:xx},{},...,{}]}
        $bm = base64_decode(Request::param('data'));
        $data = json_decode($bm,true);

        $mingxi = $data['mingxi'];
        $zhuwa = $data['zhuwa'];
        //计算订单金额和实际金额
        $money1 = 0;
        $money2 = 0;
        for($i = 0; $i < count($mingxi); $i++){
            $prid = $mingxi[$i]['pro_id'];
            $dmoney1 = $mingxi[$i]['money1'];
            $money1 += $dmoney1*$mingxi[$i]['numbers'];
            $money2 += $pro_data[$prid]['danjia']*$mingxi[$i]['numbers'];
        }

        //先生成新的订单号
        $times = time();
        $uid = $fin['id'];
        $dingdan = new P_dingdan;

        //这里进行改变，传过来的是规格的id
        $postguige = $data['guige'];
        $dd_guige = new P_guige;
        $data_guige = $dd_guige->where('id',$postguige)->find();
        $guigename = $data_guige['names'];
        $guigekuandu = $data_guige['kuandu'];

        //这里添加一个订单号生成
        $timebs1 = strtotime(date("Y-m-d",time())." 00:00:00");
        $xuhao = $dingdan->where([['times','<',$times],['times','>',$timebs1]])->count();
        $xuhao += 1;
        //把序号做成三位数
        if($xuhao<10){
            $xuhao = '00'.$xuhao;
        }else if($xuhao<100){
            $xuhao = '0'.$xuhao;
        }
        $dingdanhao = "ZSJC-".date("Ymd",$times)."-".$xuhao;


        $dddata0 = [
            'user_id'=>$uid,
            'times'=>$times,
            'zt' => 1,
            'money1' =>$money1,
            'money2' =>$money2,
            'jz' =>1,
            'qhm' =>'',
            'beizhu'=>$data['beizhu'],
            'zw_guige'=>$guigename,
            'zw_guige2'=>$guigekuandu,
            'zw_houdu'=>$data['houdu'],
            'zw_yanse'=>$data['yanse'],
            'zw_paixu'=>$data['paixu'],
            'zw_hebing'=>$data['hebing'],
            'zw_danwei' =>$data['danwei'],
            'dingdanhao'=>$dingdanhao


        ];
        $ddid = $dingdan->insertGetId($dddata0);#订单号$did


        //构建明细存储对象
        $mingxi_data = [];#订单明细存储数据
        $zhuwa_data = [];#主瓦存储数据
        for($i = 0; $i < count($mingxi); $i++){
            $mingxi_data[$i]['pro_id'] = $mingxi[$i]['pro_id'];
            $mingxi_data[$i]['pro_names'] = $pro_data[$mingxi[$i]['pro_id']]['names'];
            $mingxi_data[$i]['numbers'] = $mingxi[$i]['numbers'];
            $mingxi_data[$i]['money1'] = $mingxi[$i]['money1'];
            $mingxi_data[$i]['dd_id'] = $ddid;
            $mingxi_data[$i]['money2'] = $pro_data[$mingxi[$i]['pro_id']]['danjia'];
            $mingxi_data[$i]['guige'] = $pro_data[$mingxi[$i]['pro_id']]['guige'];
            $mingxi_data[$i]['danwei'] = $pro_data[$mingxi[$i]['pro_id']]['danwei'];
            $mingxi_data[$i]['zmoney'] = number_format($mingxi[$i]['numbers']*$pro_data[$mingxi[$i]['pro_id']]['danjia'],4);
        }
        //构建主瓦存储对象
        //有关的字段为 paixu hebing
        for($i = 0; $i < count($zhuwa); $i++){
            $zhuwa_data[$i]['dd_id'] = $ddid;
            $zhuwa_data[$i]['changdu'] = $zhuwa[$i]['changdu'];
            $zhuwa_data[$i]['numbers'] = $zhuwa[$i]['numbers'];
        }
        if($data['hebing'] == 1){//长度通项合并
            for($i = 0; $i < count($zhuwa_data)-1; $i++){
                if($zhuwa_data[$i]['numbers']!=0){//当前i不等于0
                    for($j=$i+1;$j<count($zhuwa_data);$j++){
                        if($zhuwa_data[$i]['changdu']==$zhuwa_data[$j]['changdu']){
                            $zhuwa_data[$i]['numbers'] += $zhuwa_data[$j]['numbers'];
                            $zhuwa_data[$j]['numbers'] = 0;
                        }
                    }
                }
                
            }
            $zhuwa_data_ls = [];
            $j = 0;
            for($i = 0; $i < count($zhuwa_data); $i++){
                if($zhuwa_data[$i]['numbers']!=0){//当前i不等于0
                    $zhuwa_data_ls[$j] = $zhuwa_data[$i];
                    $j += 1;
                }
            }
            $zhuwa_data = $zhuwa_data_ls;
        }
        if($data['paixu'] == 1){//长度排序
            for($i = 0; $i < count($zhuwa_data); $i++){
                for($j=$i+1;$j<count($zhuwa_data);$j++){
                    if($zhuwa_data[$j]['changdu']<$zhuwa_data[$i]['changdu']){
                        $ls = $zhuwa_data[$i];
                        $zhuwa_data[$i] = $zhuwa_data[$j];
                        $zhuwa_data[$j] = $ls;
                    }
                }
            }
        }
        //计算主瓦尺寸 多少米或者多少平方米
        $guige = new P_guige;
        $zwcc = $guige->where("names",$data['guige'])->find();//前台传过来的是名称
        $kuandu = $zwcc['kuandu'];#主瓦宽度
        
        for($i = 0; $i < count($zhuwa_data); $i++){
            $zhuwa_data[$i]['chicun1'] = $zhuwa_data[$i]['changdu']*$zhuwa_data[$i]['numbers'];
        }
        
        for($i = 0; $i < count($zhuwa_data); $i++){
            $zhuwa_data[$i]['chicun2'] = $zhuwa_data[$i]['changdu']*$zhuwa_data[$i]['numbers']*$kuandu;
        }
        
        


        //存储
        $ddmx = new P_dingdan_mingxi;
        $res1 = $ddmx->insertAll($mingxi_data);
        
        $zw = new P_dingdan_zhuwa;
        $res2 = $zw->insertAll($zhuwa_data);

        if($res1 == count($mingxi) && $res2 == count($zhuwa_data)){
            return $this->returns(1,$ddid);
        }else{
            return $this->returns(2,0,"订单提交错误");
        }
    }

    /**
     * 订单修改
     * @return Json
     */
    public function dingdanupdate():Json{
        $fin =$this->getuserid();
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }

        $did = Request::param('did');
        //获取产品的详细信息列表
        $pro = new P_product;
        $pro_data0 = $pro->select();
        $pro_data = [];
        for($i = 0; $i < count($pro_data0); $i++){//做一个根据pro_id的索引，方便后续使用
            $pro_data[$pro_data0[$i]['id']] = $pro_data0[$i];
        }
        //接受提交的订单数据，收到的数据格式为json，经过base64编码，形式为：{mingxi:[{pro_id:xxx,pro_names:xxx,numbers:xxx,money1:xxx},{},...,{}],zhuwa:[{changdu:xx,numbers:xx},{},...,{}]}
        $bm = base64_decode(Request::param('data'));
        $data = json_decode($bm,true);

        $mingxi = $data['mingxi'];
        $zhuwa = $data['zhuwa'];
        //计算订单金额和实际金额
        $money1 = 0;
        $money2 = 0;
        for($i = 0; $i < count($mingxi); $i++){
            $prid = $mingxi[$i]['pro_id'];
            $dmoney1 = $mingxi[$i]['money1'];
            $money1 += $dmoney1*$mingxi[$i]['numbers'];
            $money2 += $pro_data[$prid]['danjia']*$mingxi[$i]['numbers'];
        }

        //先生成新的订单号
        $times = time();
        $uid = $fin['id'];
        $dingdan = new P_dingdan_up;

        //这里进行改变，传过来的是规格的id
        $postguige = $data['guige'];
        $dd_guige = new P_guige;
        $data_guige = $dd_guige->where('id',$postguige)->find();
        $guigename = $data_guige['names'];
        $guigekuandu = $data_guige['kuandu'];

        $dddata0 = [
            'user_id'=>$uid,
            'dd_id'=>$did,
            'times'=>$times,
            'zt' => 1,
            'money1' =>$money1,
            'money2' =>$money2,
            'jz' =>1,
            'qhm' =>'',
            'beizhu'=>$data['beizhu'],
            'zw_guige'=>$guigename,
            'zw_guige2'=>$guigekuandu,
            'zw_houdu'=>$data['houdu'],
            'zw_yanse'=>$data['yanse'],
            'zw_paixu'=>$data['paixu'],
            'zw_hebing'=>$data['hebing'],
            'zw_danwei' =>$data['danwei'],
            'zw_zhonglei'=>$data['zhonglei'],
            // 'zw_guige' =>$data['guige'],
            // 'zw_guige2' =>$data['guige2'],
        ];
        $ddid = $dingdan->insertGetId($dddata0);#修改订单号$ddid


        //构建明细存储对象
        $mingxi_data = [];#订单明细存储数据
        $zhuwa_data = [];#主瓦存储数据
        for($i = 0; $i < count($mingxi); $i++){
            $mingxi_data[$i]['pro_id'] = $mingxi[$i]['pro_id'];
            $mingxi_data[$i]['pro_names'] = $pro_data[$mingxi[$i]['pro_id']]['names'];
            $mingxi_data[$i]['numbers'] = $mingxi[$i]['numbers'];
            $mingxi_data[$i]['money1'] = $mingxi[$i]['money1'];
            $mingxi_data[$i]['dd_id'] = $did;
            $mingxi_data[$i]['money2'] = $pro_data[$mingxi[$i]['pro_id']]['danjia'];
            $mingxi_data[$i]['guige'] = $pro_data[$mingxi[$i]['pro_id']]['guige'];
            $mingxi_data[$i]['danwei'] = $pro_data[$mingxi[$i]['pro_id']]['danwei'];
            $mingxi_data[$i]['zmoney'] = number_format($mingxi[$i]['numbers']*$pro_data[$mingxi[$i]['pro_id']]['danjia'],4);
        }
        //构建主瓦存储对象
        //有关的字段为 paixu hebing
        for($i = 0; $i < count($zhuwa); $i++){
            $zhuwa_data[$i]['dd_id'] = $did;
            $zhuwa_data[$i]['changdu'] = $zhuwa[$i]['changdu'];
            $zhuwa_data[$i]['numbers'] = $zhuwa[$i]['numbers'];
        }
        if($data['hebing'] == 1){//长度通项合并
            for($i = 0; $i < count($zhuwa_data)-1; $i++){
                if($zhuwa_data[$i]['numbers']!=0){//当前i不等于0
                    for($j=$i+1;$j<count($zhuwa_data);$j++){
                        if($zhuwa_data[$i]['changdu']==$zhuwa_data[$j]['changdu']){
                            $zhuwa_data[$i]['numbers'] += $zhuwa_data[$j]['numbers'];
                            $zhuwa_data[$j]['numbers'] = 0;
                        }
                    }
                }
                
            }
            $zhuwa_data_ls = [];
            $j = 0;
            for($i = 0; $i < count($zhuwa_data); $i++){
                if($zhuwa_data[$i]['numbers']!=0){//当前i不等于0
                    $zhuwa_data_ls[$j] = $zhuwa_data[$i];
                    $j += 1;
                }
            }
            $zhuwa_data = $zhuwa_data_ls;
        }
        if($data['paixu'] == 1){//长度排序
            for($i = 0; $i < count($zhuwa_data); $i++){
                for($j=$i+1;$j<count($zhuwa_data);$j++){
                    if($zhuwa_data[$j]['changdu']<$zhuwa_data[$i]['changdu']){
                        $ls = $zhuwa_data[$i];
                        $zhuwa_data[$i] = $zhuwa_data[$j];
                        $zhuwa_data[$j] = $ls;
                    }
                }
            }
        }
        //计算主瓦尺寸 多少米或者多少平方米
        $guige = new P_guige;
        $zwcc = $guige->where("names",$data['guige'])->find();//前台传过来的是名称
        $kuandu = $zwcc['kuandu'];#主瓦宽度
        
        for($i = 0; $i < count($zhuwa_data); $i++){
            $zhuwa_data[$i]['chicun1'] = $zhuwa_data[$i]['changdu']*$zhuwa_data[$i]['numbers'];
        }
        
        for($i = 0; $i < count($zhuwa_data); $i++){
            $zhuwa_data[$i]['chicun2'] = $zhuwa_data[$i]['changdu']*$zhuwa_data[$i]['numbers']*$kuandu;
        }
        
        


        //存储
        $ddmx = new P_dingdan_mingxi_up;
        $res1 = $ddmx->insertAll($mingxi_data);
        
        $zw = new P_dingdan_zhuwa_up;
        $res2 = $zw->insertAll($zhuwa_data);

        if($res1 == count($mingxi) && $res2 == count($zhuwa_data)){
            return $this->returns(1,$ddid);
        }else{
            return $this->returns(2,0,"订单提交错误");
        }
    }





    /**
     * @return Json
     * 用户订单列表获取
     */
    public function dingdanget():Json{
        $fin =$this->getuserid();
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        $uid = $fin['id'];#用户id

        //根据用户id搜索订单列表
        $dingdans = new P_dingdan;
        $ddlist = $dingdans->where('user_id',$uid)->select();
        date_default_timezone_set("PRC");
        // $resdata = [];
        for($i=0;$i<count($ddlist);$i++){
            // $resdata[$i] = [];
            $ddlist[$i]['id'] = $ddlist[$i]['id']; 
            $ddlist[$i]['times'] = date("Y-m-d H:i:s",$ddlist[$i]['times']);
            $zt1 = "";
            switch($ddlist[$i]['zt']){
                case 1: $zt1 = "已下单，待接受";break;
                case 2: $zt1 = "已接受，备货中";break;
                case 3: $zt1 = "可取货";break;
                case 4: $zt1 = "取货完毕";break;
            }
            $ddlist[$i]['zt'] = $ddlist[$i]['zt'];
            $ddlist[$i]['ztwz'] = $zt1;
            // $resdata[$i]['money1'] = $ddlist[$i]['money1'];
            // $resdata[$i]['money2'] = $ddlist[$i]['money2'];
            // $resdata[$i]['jz'] = $ddlist[$i]['jz'];
            // $resdata[$i]['qhm'] = $ddlist[$i]['qhm'];

            // $resdata[$i]['beizhu'] = $ddlist[$i]['beizhu'];
            // $resdata[$i]['zw_guige'] = $ddlist[$i]['zw_guige'];
        }


        return $this->returns(1,$ddlist);
    }
    /**
     * @return Json
     * 用户订单详情
     */
    public function dingdanxq():Json{
        $fin =$this->getuserid();
        if($fin==null){
            return $this->returns(3,0,"不存在用户");
        }
        $uid = $fin['id'];
        $did = Request::param('id');
        #先做订单所属判断,不是本人或者无此订单输出失败
        $dd = new P_dingdan;
        $sel = $dd->where('id',$did)->find();
        if($sel==null || $sel['user_id']!=$uid){
            return $this->returns("2",0,"订单获取失败");
        }
        #查询主订单内容
        $mingxi = new P_dingdan_mingxi;
        $zhuwa = new P_dingdan_zhuwa;

        $mingxi_data = $mingxi->where("dd_id",$did)->select();
        $zhuwa_data = $zhuwa->where("dd_id",$did)->select();

        //构造返回数据
        // $resdata = [];
        // $resdata['id'] = $sel['id']; 
        $sel['times'] = date("Y-m-d H:i:s",$sel['times']);
        $zt1 = "";
        switch($sel['zt']){
            case 1: $zt1 = "已下单，待接受";break;
            case 2: $zt1 = "已接受，备货中";break;
            case 3: $zt1 = "可取货";break;
            case 4: $zt1 = "取货完毕";break;
        }
        $sel['zt'] = $sel['zt'];
        $sel['ztwz'] = $zt1;
        // $resdata['money1'] = $sel['money1'];
        // $resdata['money2'] = $sel['money2'];
        // $resdata['jz'] = $sel['jz'];
        // $resdata['qhm'] = $sel['qhm'];

        $resdata0=[
            'dingdan'=>$sel,
            'mingxi' =>$mingxi_data,
            'zhuwa' =>$zhuwa_data
        ];
        return $this->returns(1,$resdata0);

    }


    /**
     * @return Json
     * json测试
     */
    public function jsontest():Json{
        $bm = base64_decode(Request::param('data'));
        $data = json_decode($bm,true);


        // $mingxi = [];
        // $mingxi[0] = ['pro_id'=>1,'pro_name'=>"xxx",'numbers'=>2,'money1'=>2.25];
        // $zhuwa = [];
        // $zhuwa[0] = ['changdu'=>1.23,'numbers'=>2];
        // $zhuwa[1] = ['changdu'=>2.23,'numbers'=>5];
        // $dt=['mingxi' => $mingxi,'zhuwa'=>$zhuwa];
        // $ret = json_encode($dt);

        return $this->returns(1,$data['mingxi'][0]['pro_id']);
    }




}