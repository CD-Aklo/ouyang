<?php
namespace app\admin\controller;
use function MongoDB\BSON\toJSON;
use think\Db;
use clt\Leftnav;
use app\admin\model\Admin;
use app\admin\model\AdminGroup;
use app\admin\model\AdminRule;
use think\facade\Request;
use think\Validate;
use think\facade\Config;
class Auth extends Common
{
    //管理员列表
    public function adminList(){
        if(Request::isAjax()){
            $val=input('val');
            $url['val'] = $val;
            $this->assign('testval',$val);
            $map='';
            if($val){
                $map['username|email|tel']= array('like',"%".$val."%");
            }
            if (session('aid')!=1){
                $map='admin_id='.session('aid');
            }
            $list=Db::table(config('database.prefix').'admin')->alias('a')
                ->join(config('database.prefix').'admin_group ag','a.group_id = ag.group_id','left')
                ->field('a.*,ag.title')
                ->where($map)
                ->select();
            foreach ($list as $k=>$v){
                $list[$k]['filiale'] = Config::get('common.filiale')[$v['saccno']]['name'];
            }
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list,'rel'=>1];
        }
        return view();
    }

    public function adminAdd(){
        if(Request::isAjax()){
            $data = input('post.');
            $check_user = Admin::get(['username'=>$data['username']]);
            if ($check_user) {
                return $result = ['code'=>0,'msg'=>'用户已存在，请重新输入用户名!'];
            }
            $data['pwd'] = input('post.pwd', '', 'md5');
            $data['addtime'] = time();
            $data['ip'] = request()->ip();
            //验证
            $msg = $this->validate($data,'app\admin\validate\Admin');
            if($msg!='true'){
                return $result = ['code'=>0,'msg'=>$msg];
            }
            //单独验证密码
            $checkPwd = Validate::make([input('post.pwd')=>'require']);
            if (false === $checkPwd) {
                return $result = ['code'=>0,'msg'=>'密码不能为空！'];
            }
            //添加
            if (Admin::create($data)) {
                return ['code'=>1,'msg'=>'管理员添加成功!','url'=>url('adminList')];
            } else {
                return ['code'=>0,'msg'=>'管理员添加失败!'];
            }
        }else{
            $admin_group = AdminGroup::all();
            $filiale = Config::get('common.filiale');
            $this->assign('filiale',$filiale);
            $this->assign('AdminGroup',$admin_group);
            $this->assign('info','null');
            $this->assign('selected', 'null');
            $this->assign('title','添加管理员');
            return view('adminForm');
        }
    }
    //删除管理员
    public function adminDel(){
        $admin_id=input('post.admin_id');
        if (session('aid')==1){
            Admin::where('admin_id','=',$admin_id)->delete();
            return $result = ['code'=>1,'msg'=>'删除成功!'];
        }else{
            return $result = ['code'=>0,'msg'=>'您没有删除管理员的权限!'];
        }
    }
    //修改管理员状态
    public function adminState(){
        $id=input('post.id');
        $is_open=input('post.is_open');
        if (empty($id)){
            $result['status'] = 0;
            $result['info'] = '用户ID不存在!';
            $result['url'] = url('adminList');
            return $result;
        }
        db('admin')->where('admin_id='.$id)->update(['is_open'=>$is_open]);
        $result['status'] = 1;
        $result['info'] = '用户状态修改成功!';
        $result['url'] = url('adminList');
        return $result;
    }
    //更新管理员信息
    public function adminEdit(){
        if(request()->isPost()){
            $data = input('post.');
            $pwd=input('post.pwd');
            $map[] = ['admin_id','<>',$data['admin_id']];
            $where['admin_id'] = $data['admin_id'];

            if($data['username']){
                $map[] = ['username','=',$data['username']];
                $check_user = Admin::where($map)->find();
                if ($check_user) {
                    return $result = ['code'=>0,'msg'=>'用户已存在，请重新输入用户名!'];
                }
            }
            if ($pwd){
                $data['pwd']=input('post.pwd','','md5');
            }else{
                unset($data['pwd']);
            }
            $data['updatetime'] = time();
            $msg = $this->validate($data,'app\admin\validate\Admin');
            if($msg!='true'){
                return $result = ['code'=>0,'msg'=>$msg];
            }
            Admin::update($data,$where);
            if( $data['admin_id'] == session('aid')){
                session('username',$data['username']);
                $avatar = $data['avatar']==''?'/static/admin/images/0.jpg':$data['avatar'];
                session('avatar',$avatar);
            }
            return $result = ['code'=>1,'msg'=>'管理员修改成功!','url'=>url('adminList')];
        }else{
            $admin_group = AdminGroup::all();
            $admin = new Admin();
            $filiale = Config::get('common.filiale');
            $info = $admin->getInfo(input('admin_id'));
            $this->assign('filiale',$filiale);
            $this->assign('info', json_encode($info,true));
            $this->assign('AdminGroup',$admin_group);
            $this->assign('title','编辑管理员');
            return view('adminForm');
        }
    }
    /*-----------------------用户组管理----------------------*/
    //用户组管理
    public function adminGroup(){
        if(request()->isPost()){
            $list = AdminGroup::all();
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list,'rel'=>1];
        }
        return view();
    }
    //删除管理员分组
    public function groupDel(){
        AdminGroup::where('group_id','=',input('group_id'))->delete();
        return $result = ['code'=>1,'msg'=>'删除成功!'];
    }
    //添加分组
    public function groupAdd(){
        if(request()->isPost()){
            $data=input('post.');
            $data['addtime']=time();
            AdminGroup::create($data);
            $result['msg'] = '管理组添加成功!';
            $result['url'] = url('adminGroup');
            $result['code'] = 1;
            return $result;
        }else{
            $this->assign('title','添加管理组');
            $this->assign('info','null');
            return $this->fetch('groupForm');
        }
    }
    //修改分组
    public function groupEdit(){
        if(request()->isPost()) {
            $data=input('post.');
            $data['updatetime'] = time();
            $where['group_id'] = $data['group_id'];
            AdminGroup::update($data,$where);
            $result = ['code'=>1,'msg'=>'管理组修改成功!','url'=>url('adminGroup')];
            return $result;
        }else{
            $group_id = input('group_id');
            $info = AdminGroup::get(['group_id'=>$group_id]);
            $this->assign('info', json_encode($info,true));
            $this->assign('title','编辑管理组');
            return $this->fetch('groupForm');
        }
    }
    //分组配置规则
    public function groupAccess(){
        $nav = new Leftnav();
        $admin_rule=db('admin_rule')->field('rule_id,pid,title')->order('sort asc')->select();
        $rules = db('admin_group')->where('group_id',input('group_id'))->value('rules');
        $arr = $nav->auth($admin_rule,$pid=0,$rules);
        $arr[] = array(
            "rule_id"=>0,
            "pid"=>0,
            "title"=>"全部",
            "open"=>true
        );
        $this->assign('data',json_encode($arr,true));
        return $this->fetch();
    }
    public function groupSetaccess(){
        $rules = input('post.rules');
        if(empty($rules)){
            return array('msg'=>'请选择权限!','code'=>0);
        }
        $data = input('post.');
        $where['group_id'] = $data['group_id'];
        if(AdminGroup::update($data,$where)){
            return array('msg'=>'权限配置成功!','url'=>url('adminGroup'),'code'=>1);
        }else{
            return array('msg'=>'保存错误','code'=>0);
        }
    }

    /********************************权限管理*******************************/
    public function adminRule(){
        if(request()->isPost()){
            $arr = cache('AdminRuleList');
            if(!$arr){
				$arr = Db::name('AdminRule')->order('pid asc,sort asc')->select();
				foreach($arr as $k=>$v){
                    $arr[$k]['lay_is_open']=false;
                }
                cache('AdminRuleList', $arr, 3600);
            }
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$arr,'is'=>true,'tip'=>'操作成功'];
        }
        return view();
    }
    public function ruleAdd(){
        if(request()->isPost()){
            $data = input('post.');
            $data['addtime'] = time();
            AdminRule::create($data);
            cache('AdminRule', NULL);
            cache('AdminRuleList', NULL);
            cache('addAdminRuleList', NULL);
            return $result = ['code'=>1,'msg'=>'权限添加成功!','url'=>url('adminRule')];
        }else{
            $nav = new Leftnav();
            $arr = cache('addAdminRuleList');
            if(!$arr){
                $AdminRule = AdminRule::all(function($query){
                    $query->order('sort', 'asc');
                });
                $arr = $nav->menu($AdminRule);
                cache('addAdminRuleList', $arr, 3600);
            }
            $this->assign('admin_rule',$arr);//权限列表
            return $this->fetch();
        }
    }
    public function ruleOrder(){
        $admin_rule=db('admin_rule');
        $data = input('post.');
        if($admin_rule->update($data)!==false){
            cache('AdminRuleList', NULL);
            cache('AdminRule', NULL);
            cache('addAdminRuleList', NULL);
            return $result = ['code'=>1,'msg'=>'排序更新成功!','url'=>url('adminRule')];
        }else{
            return $result = ['code'=>0,'msg'=>'排序更新失败!'];
        }
    }
    //设置权限菜单显示或者隐藏
    public function ruleState(){
        $rule_id=input('post.rule_id');
        $menustatus=input('post.menustatus');
        if(db('admin_rule')->where('rule_id='.$rule_id)->update(['menustatus'=>$menustatus])!==false){
            cache('AdminRule', NULL);
            cache('AdminRuleList', NULL);
            cache('addAdminRuleList', NULL);
            return ['status'=>1,'msg'=>'设置成功!'];
        }else{
            return ['status'=>0,'msg'=>'设置失败!'];
        }
    }
    public function ruleDel(){
        $rule_id = input('param.rule_id');
        AdminRule::where('rule_id','=', $rule_id)->delete();
        cache('AdminRule', NULL);
        cache('AdminRuleList', NULL);
        cache('addAdminRuleList', NULL);
        return $result = ['code'=>1,'msg'=>'删除成功!'];
    }

    public function ruleEdit(){
        if(request()->isPost()) {
            $data = input('post.');
            $data['updatetime'] = time();
            $where['rule_id'] = $data['rule_id'];
            if(AdminRule::update($data, $where)) {
                cache('AdminRule', NULL);
                cache('AdminRuleList', NULL);
                cache('addAdminRuleList', NULL);
                return json(['code' => 1, 'msg' => '保存成功!', 'url' => url('adminRule')]);
            } else {
                return json(['code' => 0, 'msg' =>'保存失败！']);
            }
        }else{
            $admin_rule = AdminRule::get(function($query){
                $query->where(['rule_id'=>input('rule_id')])->field('rule_id,href,title,icon,sort,menustatus');
            });
            $this->assign('rule',$admin_rule);
            return $this->fetch();
        }
    }
}