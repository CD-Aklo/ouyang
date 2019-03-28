<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/23 0023
 * Time: 14:03
 */

namespace app\admin\controller;
use think\Db;
use think\facade\Config;
use think\facade\Request;
class Article extends Common
{
    //文章列表
    public function index()
    {
        if (Request::isAjax()){
            $page = Request::post('page','1','trim');
            $pageSize = Request::post('limit','10','trim');
            $key = Request::post('key','','trim');
            $list = Db::name('news')
                        ->where('title','like',"%".$key."%")
                        ->field('content',true)
                        ->order('sort')
                        ->paginate(['list_rows'=>$pageSize,'page'=>$page])->toArray();
            foreach ($list['data'] as $k=>&$v){
                $v['add_time'] = date('Y-m-s',$v['add_time']);
                $v['art_type'] = Config::get('common.article')[$v['art_type']]['type'];
            }
            return $result = ['code'=>0,'data'=>$list['data'],'count'=>$list['total']];
        }
        return $this->fetch();
    }
    //文章添加
    public function articleAdd()
    {
        if (Request::isAjax()){
            $data = Request::post();
            $data['add_time'] = time();
            $res = Db::name('news')->insert($data);
            if ($res){
                return $result = ['code'=>1,'msg'=>'新增成功！','url'=>url('index')];
            }else{
                return $result = ['code'=>0,'msg'=>'新增失败','url'=>url('index')];
            }
        }else{
            $this->assign('title','文章添加');
            $this->assign('type',Config::get('common.article'));
            $this->assign('info','null');
            return $this->fetch('form');
        }
    }
    //文章编辑
    public function artEdit()
    {
        if (Request::isAjax()){
            $data = Request::param();
            $res = Db::name('news')->update($data);
            if ($res){
                return $result = ['code'=>1,'msg'=>'编辑成功','url'=>url('index')];
            }else{
                return $result = ['code'=>0,'msg'=>'编辑失败','url'=>url('index')];
            }
        }
        $art_id = Request::param('art_id','','trim');
        $info = Db::name('news')->where('art_id',$art_id)->find();
        $this->assign('info',json_encode($info));
        $this->assign('type',Config::get('common.article'));
        $this->assign('title','文章编辑');
        return $this->fetch('form');
    }
    //文章排序
    public function artSort()
    {
        $data = Request::post();
        $res = Db::name('news')->update($data);
        if ($res){
            return $result = ['code'=>1,'msg'=>'排序成功'];
        }else{
            return $result = ['code'=>0,'msg'=>'排序失败'];
        }
    }
    //文章状态修改
    public function artStatus()
    {
        $data = Request::post();
        if(db('news')->where('art_id='.$data['id'])->update([$data['field']=>$data['vaule']])!==false){
            return ['code'=>1,'msg'=>'设置成功!'];
        }else{
            return ['code'=>0,'msg'=>'设置失败!'];
        }
    }
    //文章删除
    public function artDel(){
        Db::name('news')->where(array('art_id'=>Request::post('art_id')))->delete();
        return ['code'=>1,'msg'=>'删除成功！'];
    }
    //批量删除
    public function artDelAll(){
        $map[] =array('art_id','in',Request::post('ids/a'));
        Db::name('news')->where($map)->delete();
        $result['msg'] = '删除成功！';
        $result['code'] = 1;
        $result['url'] = url('index');
        return $result;
    }
    public function de(){
        phpinfo();
    }
}