<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/16 0016
 * Time: 14:34
 */

namespace app\admin\controller;
use app\admin\model\RegionModel;
use think\facade\Request;
class Region   extends Common
{
    public function index(){
        $reg = new  RegionModel();
        if (Request::isAjax()){
            $key = input('post.key');
            $id = input('post.id');
            $p_id = input('post.p_id');
            $map = $down = $up='';
            if (isset($key))  $map = "name like '%".$key."%'";
            if (isset($id))   $down = 'p_id = '.$id;
            if (isset($p_id)){
                $up_id = $reg->where('id',$p_id)->value('p_id');
                $up = 'p_id = '.$up_id;
            }
            $page = input('page')?input('page'):1;
            $pageSize = input('limit')?input('limit'):10;
            $pages = ['list_rows'=>$pageSize,'page'=>$page];
            $res = $reg->region_list($pages,$map,$down,$up);
            return $res;
        }
        $province = $reg->where('level',1)->field('id,name')->select();
        $this->assign('province',$province);
        return $this->fetch();
    }
    //地区添加
    public function add(){
        $reg = new RegionModel();
        $province = $reg->where('level',1)->field('id,name,level')->select()->toArray();
        if (Request::isPost()){
            $data = input('post.');
            //根据前台传参来判断增加哪一级
            if (!empty($data['town'])){
                $res =  $reg->region_add($data,$data['town']);
            }else if (!empty($data['area'])){
                $res = $reg->region_add($data,$data['area']);
            }else if (!empty($data['city'])){
                $res = $reg->region_add($data,$data['city']);
            }else{
                $res = $reg->region_add($data,$data['province']);
            }
            if ($res['code']){
                $this->success($res['msg'],'index');
            }else{
                $this->success($res['msg'],'index');
            }
        }
        $this->assign('province',$province);
        return $this->fetch();
    }
    //ajax 获取下级地区
    public function ajaxGetRegion(){
        $p_id = input('post.id');
        $reg = new RegionModel();
        if(Request::isAjax()){
            $res = $reg->where('p_id',$p_id)->select()->toArray();
            if ($res){
                return $result = ['code'=>0,'msg'=>'获取成功','data'=>$res];
            }else{
                return $result = ['code'=>1,'msg'=>'没有下级数据','data'=>[]];
            }
        }
    }
    //删除地区
    public function del(){
        $id = input('id');
        $reg = new RegionModel();
        $res = $reg->where('id',$id)->delete();
        if ($res){
            return $result = ['code'=>1,'msg'=>'删除成功!'];
        }else{
            return $result = ['code'=>0,'msg'=>'操作失败!'];
        }
    }
    //编辑地区
    public function edit(){
        $id = input('get.id');
        $reg =  new  RegionModel();
        $res = $reg->where('id',$id)->find();
        $this->assign('res',$res);
        if (Request::isAjax()){
            $data = input('post.data');
            $id = $data['id'];
            $row = $reg->save($data,['id'=>$id]);
            if ($row){
                return $result = ['code'=>1,'msg'=>'更新成功'];
            }else{
                return $result = ['code'=>0,'msg'=>'更新失败'];
            }
        }
        return $this->fetch();
    }
    public function do_js(){
        $id = input('post.province');
        $reg = new RegionModel();
        $pro = $reg::where('id', $id)->field('id,name,p_id')->find();
        $pros = [$pro['id']=>$pro];   //需要的第一个数组
        $city = $reg::where('p_id',$id)->field('id,name,p_id')->select()->toArray();
        foreach ($city as $k=>$v){
            $city_id[] = $v['id'];   //下级需要的id
            $citys[$v['p_id']][] = $v;
        }
        $area = $reg::where('p_id','in',$city_id)->field('id,name,p_id')->select()->toArray();
        foreach ($area as $k=>$v){
            $area_id[] = $v['id'];
            $areas[$v['p_id']][] = $v;
        }
        $town = $reg::where('p_id','in',$area_id)->select()->toArray();
        $towns = [];
        foreach ($town as $k=>$v){
            $towns[$v['p_id']][] = $v;
        }
        $art = json_encode(['province'=>$pros,'city'=>$citys,'district'=>$areas,'town'=>$towns]);
        $arts = "areajson[$id] = '".$art."'";     //文件内容
        $filename = './area/area_'.$id.'.js';    //文件路径以及文件名
        $file = fopen($filename,'w');
        $file_r = fwrite($file,$arts);
        fclose($file);
        return (['code'=>1,'msg'=>'成功写入字节数  '.$file_r]);
    }
}