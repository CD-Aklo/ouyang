<?php
namespace app\admin\controller;
use think\Db;
use think\facade\Request;
use think\facade\Config;
use think\facade\Cache;
class Ad extends Common
{
    //广告列表
    public function index(){
        if(Request::isAjax()) {
            $key = input('post.key');
            $page =input('page')?input('page'):1;
            $pageSize =input('limit')?input('limit'):10;
            $list = Db::table(config('database.prefix') . 'ads')
                ->where('name', 'like', "%" . $key . "%")
                ->order('sort','asc')
                ->paginate(array('list_rows'=>$pageSize,'page'=>$page))
                ->toArray();
            foreach ($list['data'] as $k=>&$v){
                $v['add_time'] = date('Y-m-d',$v['add_time']);
                $v['position_name'] = Config::get('common.ads_position')[$v['position_id']]['name'];
                $v['branch'] = Config::get('common.filiale')[$v['saccno']]['name'];
            }
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list['data'],'count'=>$list['total'],'rel'=>1];
        }
        return $this->fetch();
    }
    public function add(){
        if(Request::isAjax()) {
            //构建数组
            $data = Request::except('file');
            $data['add_time'] = time();
            $data['position_id'] = (int)$data['position_id'];
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['status'] = (int)($data['status']);
            $data['target'] = (int)($data['target']);
            $data['saccno'] = $this->saccno;
            $res =  db('ads')->insert($data);
            if ($res){
                return $result = ['code'=>1,'msg'=>'添加成功','url'=>url('index')];
            }else{
                return $result = ['code'=>0,'msg'=>'添加失败','url'=>url('index')];
            }
        }else{
            $adPosition = Config::get('common.ads_position');
            $this->assign('adPosition',$adPosition);
            $this->assign('title','添加广告');
            $this->assign('adInfo','null');
            $this->assign('selected', 'null');
            return $this->fetch('form');
        }
    }
    public function edit(){
        if(Request::isAjax()) {
            $data = Request::except('file');
            $data['update_time'] = time();
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $res =  db('ads')->update($data);
            if ($res){
                return $result = ['code'=>1,'msg'=>'修改成功','url'=>url('index')];
            }else{
                return $result = ['code'=>0,'msg'=>'修改失败','url'=>url('index')];
            }
        }else{
            $ad_id=input('ad_id');
            $adInfo=db('ads')->where(array('ad_id'=>$ad_id))->find();
            $adInfo['start_time'] = date('Y-m-d H:i:s',$adInfo['start_time']);
            $adInfo['end_time'] = date('Y-m-d H:i:s',$adInfo['end_time']);
            $adPosition = Config::get('common.ads_position');

            $this->assign('adPosition',$adPosition);
            $this->assign('adInfo',json_encode($adInfo));
            $this->assign('title','编辑广告');
            return $this->fetch('form');
        }
    }
    //设置广告状态
    public function editState(){
        $data = Request::post();
        if(db('ads')->where('ad_id='.$data['id'])->update([$data['field']=>$data['vaule']])!==false){
            return ['code'=>1,'msg'=>'设置成功!'];
        }else{
            return ['code'=>0,'msg'=>'设置失败!'];
        }
    }
    //ajax 排序
    public function adOrder(){
        $ad=db('ads');
        $data = Request::post();
        if($ad->update($data)!==false){
            return $result = ['msg' => '操作成功！','url'=>url('index'), 'code' =>1];
        }else{
            return $result = ['code'=>0,'msg'=>'操作失败！'];
        }
    }
    public function del(){
        db('ads')->where(array('ad_id'=>input('ad_id')))->delete();
        return ['code'=>1,'msg'=>'删除成功！'];
    }
    public function delAll(){
        $map[] =array('ad_id','in',input('param.ids/a'));
        db('ads')->where($map)->delete();
        $result['msg'] = '删除成功！';
        $result['code'] = 1;
        $result['url'] = url('index');
        return $result;
    }
}