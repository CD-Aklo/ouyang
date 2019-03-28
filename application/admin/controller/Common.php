<?php
namespace app\admin\controller;
use think\Db;
use think\Controller;
class Common extends Controller
{
    protected $system,$nav,$cache_model,$adminRules,$HrefId,$saccno;
    protected $admin;
    public function initialize()
    {
        //判断管理员是否登录
        if (!session('aid')) {
            $this->redirect('admin/login/index');
        }
        //存储管理员分公司编码
        $res = db('admin')->where('admin_id',session('aid'))->field('admin_id,saccno')->find();
        $this->saccno = $res['saccno'];
        //区分超级管理员
        if ($res['admin_id']==1){
            $this->admin = true;
        }else{
            $this->admin = false;
        }
        define('MODULE_NAME',strtolower(request()->controller()));
        define('ACTION_NAME',strtolower(request()->action()));
        //当前操作权限ID
        if(session('aid')!=1){
            $this->HrefId = db('admin_rule')->where('href',MODULE_NAME.'/'.ACTION_NAME)->value('rule_id');
            //当前管理员权限
            $map['a.admin_id'] = session('aid');
            $rules=Db::table(config('database.prefix').'admin')->alias('a')
                ->join(config('database.prefix').'admin_group ag','a.group_id = ag.group_id','left')
                ->where($map)
                ->value('ag.rules');
            $this->adminRules = explode(',',$rules);
            if($this->HrefId){
                if(!in_array($this->HrefId,$this->adminRules)){
                    $this->error('您无此操作权限');
                }
            }
        }
        $this->cache_model=array('AdminRule','System');
        foreach($this->cache_model as $r){
            if(!cache($r)){
                savecache($r);
            }
        }
        $this->system = cache('System');
        $this->rule = cache('AdminRule');
    }
    //空操作
    public function _empty(){
        return $this->error('空操作，返回上次访问页面中');
    }
}
