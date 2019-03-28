<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/23 0023
 * Time: 15:09
 */

namespace app\admin\controller;
use function Couchbase\defaultDecoder;
use think\Db;
use think\facade\Request;

class Other extends Common
{
    //友情链接列表
    public function friend()
    {
        if (Request::isAjax()){
            $pageSize = Request::post('limit','','trim');
            $page = Request::post('page','1','trim');
            $name = Request::post('name','','trim');
            $list = Db::name('friends_link')
                    ->where('name','like',"%".$name."%")
                    ->order('sort')
                    ->paginate(['list_rows'=>$pageSize,'page'=>$page])
                    ->toArray();
            foreach ($list['data'] as $k=>&$v){
                $v['add_time'] = date('Y-m-d',$v['add_time']);
            }
            return $result = ['code'=>0,'data'=>$list['data'],'count'=>$list['total']];
        }
        return $this->fetch();
    }
    //添加友情链接
    public function friendAdd()
    {
        if (Request::isAjax()){
            $data = Request::except('file');
            $data['add_time'] = time();
            $res = Db::name('friends_link')->insert($data);
            if ($res){
                return $result = ['code'=>1,'msg'=>'新增成功','url'=>url('friend')];
            }else{
                return $result = ['code'=>0,'msg'=>'新增失败','url'=>url('friend')];
            }
        }else{
            $this->assign('title','添加友情链接');
            $this->assign('info','null');
            return $this->fetch();
        }

    }
    //友情链接编辑
    public function friendEdit()
    {
        if (Request::isAjax())
        {
             $data = Request::except('file');
             $res = Db::name('friends_link')->update($data);
             if($res){
                 return $result = ['code'=>1,'msg'=>'更新成功','url'=>url('friend')];
             }else{
                 return $result = ['code'=>0,'msg'=>'更新失败','url'=>url('friend')];
             }
        }else {
            $fl_id = Request::get('fl_id','','trim');
            $info = Db::name('friends_link')->field('fl_id,name,link_url,link_logo')->where('fl_id',$fl_id)->find();
            $this->assign('info',json_encode($info,true));
            $this->assign('title','编辑友情链接');
            $this->assign('edit','edit');
            return $this->fetch('friend_add');
        }
    }
    //友情链接排序
    public function friendSort()
    {
        $data = Request::post();
        Db::name('friends_link')->update($data);
        return $result = ['code'=>1,'msg'=>'排序成功'];
    }
    //友情链接状态修改
    public function friendStatus()
    {
        $data = Request::post();
        $fl_id = $data['fl_id'];
        $field = $data['field'];
        $value = $data['value'];
        $res = Db::name('friends_link')->where('fl_id',$fl_id)->update([$field=>$value]);
        if ($res){
            return $result = ['code'=>1,'msg'=>'修改成功'];
        }else{
            return $result = ['code'=>0,'msg'=>'修改失败'];
        }
    }
    //友情链接删除
    public function friendDel()
    {
        $fl_id = Request::post('fl_id','','trim');
        $res = Db::name('friends_link')->where('fl_id',$fl_id)->delete();
        if ($res){
            return $result = ['code'=>1,'msg'=>'删除成功'];
        }else{
            return $result = ['code'=>1,'msg'=>'删除失败'];
        }
    }
    public function test(){
        $url = url('');
        var_dump($url);
    }
}