<?php
// +----------------------------------------------------------------------
// | 文件: index.php
// +----------------------------------------------------------------------
// | 功能: 提供todo api接口
// +----------------------------------------------------------------------
// | 时间: 2021-11-15 16:20
// +----------------------------------------------------------------------
// | 作者: rangangwei<gangweiran@tencent.com>
// +----------------------------------------------------------------------

namespace app\controller;

use Error;
use Exception;
use app\model\Counters;
use think\response\Html;
use think\response\Json;
use think\facade\Log;
use think\facade\Request;

use app\model\P_user;


class Index
{

    /**
     * 主页静态页面
     * @return Html
     */
    public function index(): Html
    {
        # html路径: ../view/index.html
        return response(file_get_contents(dirname(dirname(__FILE__)).'/view/index.html'));
    }


    /**
     * 获取todo list
     * @return Json
     */
    public function getCount(): Json
    {
        try {
            $data = (new Counters)->find(1);
            if ($data == null) {
                $count = 0;
            }else {
                $count = $data["count"];
            }
            $res = [
                "code" => 0,
                "data" =>  $count
            ];
            Log::write('getCount rsp: '.json_encode($res));
            return json($res);
        } catch (Error $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("查询计数异常" . $e->getMessage())
            ];
            Log::write('getCount rsp: '.json_encode($res));
            return json($res);
        }
    }


    /**
     * 根据id查询todo数据
     * @param $action `string` 类型，枚举值，等于 `"inc"` 时，表示计数加一；等于 `"reset"` 时，表示计数重置（清零）
     * @return Json
     */
    public function updateCount($action): Json
    {
        try {
            if ($action == "inc") {
                $data = (new Counters)->find(1);
                if ($data == null) {
                    $count = 1;
                }else {
                    $count = $data["count"] + 1;
                }
    
                $counters = new Counters;
                $counters->create(
                    ["count" => $count, 'id' => 1],
                    ["count", 'id'],
                    true
                );
            }else if ($action == "clear") {
                Counters::destroy(1);
                $count = 0;
            }

            $res = [
                "code" => 0,
                "data" =>  $count
            ];
            Log::write('updateCount rsp: '.json_encode($res));
            return json($res);
        } catch (Exception $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("更新计数异常" . $e->getMessage())
            ];
            Log::write('updateCount rsp: '.json_encode($res));
            return json($res);
        }
    }

     /**
    * 
    * @return Json
    */
    public function test($ss): Json
    {
        $header = $info = Request::header();
        try{
            $res = ['code' => 0,
            'data'=> $ss,
            'header' => $header['x-openapi-seqid']];
            return json($res);
        }catch(Exception $e){
            $res = ['code' => 1,
            'data'=> 1 ,
            'errorMsg'=>$e->getMessage()];

            return json($res);
        }
    }
    /**
    * 
    * @return Json
    */
    public function test2(Request $request): Json
    {
        $header = Request::header('x-openapi-seqid');
        try{
            $res = ['code' => 0,
            // 'data'=> $ss,
            'header' => $header,
            'req' => Request::param('ddd')];
            return json($res);
        }catch(Exception $e){
            $res = ['code' => 1,
            'data'=> 1 ,
            'errorMsg'=>$e->getMessage()];

            return json($res);
        }
    }
    /**
    * 
    * @return Json
    */
    public function test_ins(){
        $opid = Request::header('x-openapi-seqid');
        
        #先做查询
        $users = new P_user;
        $fin = $users->where('opid',$opid)->find();
        if($fin == null){
            $datas = ['opid'=>$opid,'names'=>'','tel'=>''];
            $res0 = $users->save($datas);
            $res = [
                'code'=>1,
                'res' => $res0
            ];
            return json($res);
        }else{
            $datas = ['opid'=>$opid,'names'=>'','tel'=>''];
            // $res0 = $users->save($datas);
            $res0 = $users->where('id',$fin['id'])->update($datas);
            $res = [
                'code'=>2,
                'res' => $res0
            ];
            return json($res);
        }

        
    }
}
