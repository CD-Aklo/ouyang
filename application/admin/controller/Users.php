<?php
namespace app\admin\controller;
use app\common\model\User as UsersModel;
use think\facade\Config;
use think\Db;
use think\facade\Request;

class Users extends Common {
    protected $sex = ['未知', '男', '女'];
    //会员列表
    public function get_table_name($u_id){
        $name =  'user_profile_'.$u_id%5;
        return $name;
    }
    public function index(){
        if(request()->isPost()){
            $key=input('post.key');
            $page =input('page')?input('page'):1;
            $pageSize =input('limit')?input('limit'):config('pageSize');
            $list=db('users')
                ->where('email|mobile','like',"%".$key."%")
                ->order('uid desc')
                ->paginate(array('list_rows'=>$pageSize,'page'=>$page))
                ->toArray();
            foreach ($list['data'] as $k => &$v){
                $v['reg_time'] = date('Y-m-d H:s',$v['reg_time']);
                $v['sex'] = $this->sex[$v['sex']];
            }
            return $result = ['code'=>0,'msg'=>'获取成功!','data'=>$list['data'],'count'=>$list['total'],'rel'=>1];
        }
        return $this->fetch();
    }
    //设置会员状态
    public function usersState(){
        $uid=input('post.uid');
        $is_lock=input('post.is_lock');
        if(db('users')->where('uid='.$uid)->update(['is_lock'=>$is_lock])!==false){
            return ['status'=>1,'msg'=>'设置成功!'];
        }else{
            return ['status'=>0,'msg'=>'设置失败!'];
        }
    }
    public function add(){
        if(request()->isPost()){
            $data = input('post.');
            $data['user_type'] = 2;
            $data['password'] = md5('yaooo');
            $data['reg_time'] = time();
            UsersModel::create($data);
            $result['code'] = 1;
            $result['msg'] = '添加成功!';
            $result['url'] = url('index');
            return $result;
        }else{
            $this->assign('title','添加用户');
            $province = db('Region')->where('level', 1)->select();
            $this->assign('province',json_encode($province,true));
            $city = [];
            $this->assign('city',json_encode($city,true));
            $district = [];
            $this->assign('district',json_encode($district,true));
            return $this->fetch();
        }
    }
    public function edit($uid=''){
        if(request()->isPost()){
            $user = new UsersModel;
            $data = input('post.');
            if(empty($data['password'])){
                unset($data['password']);
            }else{
                $data['password'] = md5($data['password']);
            }
            if ($user->save($data, ['uid'=>$data['uid']])!==false) {
                $result['msg'] = '修改成功!';
                $result['url'] = url('index');
                $result['code'] = 1;
            } else {
                $result['msg'] = '修改失败!';
                $result['code'] = 0;
            }
            return $result;
        }else{
            $info = UsersModel::where('uid', $uid)->find();
            $this->assign('info',json_encode($info,true));
            $this->assign('title','编辑用户');

            $province = db('Region')->where('level', 1)->select();
            $this->assign('province',json_encode($province,true));
            $city = [];
            $this->assign('city',json_encode($city,true));
            $district = [];
            $this->assign('district',json_encode($district,true));
            return $this->fetch();
        }
    }
    public function usersDel(){
        db('users')->delete(['uid'=>input('uid')]);
        //相关信息删除
        return $result = ['code'=>1,'msg'=>'删除成功!'];
    }
    public function getRegion(){
        $Region = db("region");
        $pid = input("pid");
        $arr = explode(':',$pid);
        $map['parent_id']=$arr[1];
        $list = $Region->where($map)->field('region_id,region_name')->select();
        return $list;
    }
    //会员身份列表
    public function userIdentity(){
        if (Request::isAjax()){
            $phone = input('post.phone');
            if ($phone){
                $where = 'mobile = '.$phone;
            }else{
                $where = '';
            }
            $page =input('page')?input('page'):1;
            $pageSize =input('limit')?input('limit'):10;
            $list = Db::connect('db_config1')
                ->name('user_identity')
                ->where($where)
                ->field('user_id,desc',true)
                ->order('add_time')
                ->paginate(array('list_rows'=>$pageSize,'page'=>$page))
                ->toArray();
            foreach ($list['data'] as $k=>&$v){   //对遍历到前台的数据进行处理
                $v['identity_type'] = Config::get('common.identity_type')[$v['identity_type']]['name'];
                $v['saccno'] = Config::get('common.filiale')[$v['saccno']]['name'];
                $v['start_time'] = date('Y-m-d H:i:s',$v['start_time']);
                $v['end_time'] = date('Y-m-d H:i:s',$v['end_time']);
                $v['add_time'] = date('Y-m-d ',$v['add_time']);
            }
            return $result = ['code'=>0,'msg'=>'获取成功','data'=>$list['data'],'count'=>$list['total']];
        }
        return $this->fetch('identity');
    }
    //会员身份添加
    public function identityAdd(){
        if (Request::isAjax()){
            $user = new UsersModel();
            $data = Request::post();
            $uDate =$user->check_user($data['mobile']);    //检查用户是否在商城注册
            if (!$uDate) return $result = ['code'=>-200,'msg'=>'用户不存在或者未注册!'];
            if (!$this->admin && $this->saccno!=$uDate['saccno']) return $result = ['code'=>-200,'msg'=>'会员所在分公司,与管理账号不匹配'];
            $set_id = Db::connect('db_config1')->table('ty_user_identity')->where('mobile',$data['mobile'])->field('ui_id')->find();
            if ($set_id) return $result = ['code'=>-200,'msg'=>'该用户已有会员身份!'];
            //构建插入数组
            $data['user_id'] = $uDate['u_id'];
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $data['add_time'] = time();
            if ($data['identity_type']=='1'){
                $data['period_attr'] = Config::get('common.identity_time')[$data['period']]['period_attr'];                 //结算周期
                $data['period_charge_ratio'] = Config::get('common.identity_time')[$data['period']]['period_charge_ratio']; //结算比率
            }else{
                $data['period_attr'] =  '账期';
                $data['period_charge_ratio'] = 0;
            }
            $res = Db::connect('db_config1')->name('user_identity')->insert($data);
            if ($res){
                return $result = ['code'=>200,'msg'=>'添加成功！','url'=>url('userIdentity')];
            }else{
                return $result = ['code'=>-200,'msg'=>'添加失败！','url'=>url('userIdentity')];
            }
        }else{
            $this->assign('title','添加身份');
            $this->assign('type',Config::get('common.identity_type'));
            $this->assign('period',Config::get('common.identity_time'));
            $this->assign('uData','null');
            return $this->fetch('identity_form');
        }
    }
    //会员身份编辑
    public function identityEdit(){
        if (Request::isAjax()){
            $data = Request::param();
            if ($data['identity_type']=='1'){
                $data['period_attr'] = Config::get('common.identity_time')[$data['period']]['period_attr'];                 //结算周期
                $data['period_charge_ratio'] = Config::get('common.identity_time')[$data['period']]['period_charge_ratio']; //结算比率
            }else{
                $data['period_attr'] =  '账期';
                $data['period_charge_ratio'] = 0;
            }
            $data['start_time'] = strtotime($data['start_time']);
            $data['end_time'] = strtotime($data['end_time']);
            $res = Db::connect('db_config1')->name('user_identity')->update($data);
            if ($res){
                return $result = ['code'=>1,'msg'=>'修改成功','url'=>url('userIdentity')];
            }else{
                return $result = ['code'=>0,'msg'=>'修改失败','url'=>url('userIdentity')];
            }
        }else{
            $ui_id = Request::get('ui_id');
            $uData = Db::connect('db_config1')
                        ->name('user_identity')
                        ->where('ui_id',$ui_id)
                        ->find();
            $uData['start_time'] = date('Y-m-d H:i:s',$uData['start_time']);
            $uData['end_time'] = date('Y-m-d H:i:s',$uData['end_time']);
            $this->assign('title','身份编辑');
            $this->assign('type',Config::get('common.identity_type'));
            $this->assign('period',Config::get('common.identity_time'));
            $this->assign('uData',json_encode($uData));
            return $this->fetch('identity_form');
        }
    }
    /***********************将原来的会员信息导入到新的表里面start**************************/
    public function identityDel(){
        $ui_id = Request::post('ui_id');
        $res = Db::connect('db_config1')->name('user_identity')->where('ui_id',$ui_id)->delete();
        if ($res){
            return $result = ['code'=>1,'msg'=>'删除成功！'];
        }else{
            return $result = ['code'=>0,'msg'=>'操作失败'];
        }
    }
    public function data(){
        exit;
        $n = input('get.id');
        $res = Db::table('tp_users')->limit($n,1000)->select();
        foreach ($res as $k=>$v){
            $data['u_id'] = $v['user_id'];
            $data['mobile_validated'] = $v['mobile_validated'];
            $data['email_validated'] = $v['email_validated'];
            $data['sex'] = $v['sex'];
            $data['avatar'] = $v['head_pic'];
            $data['birthday'] = $v['birthday'];
            $data['user_money'] = $v['user_money'];
            $data['prestore_money'] = $v['deposit_money'];
            $data['frozen_money'] = $v['frozen_money'];
            $data['pay_points'] = $v['pay_points'];
            $data['total_pay_amount'] = $v['total_amount'];
            $data['address_id'] = $v['address_id'];
            $data['last_login'] = $v['last_login'];
            $data['last_ip'] = $v['last_ip'];
            $data['qq'] = $v['qq'];
            $data['oauth'] = $v['oauth'];
            $data['openid'] = $v['openid'];
            $data['province'] = $v['province'];
            $data['city'] = $v['city'];
            $data['district'] = $v['district'];
            $data['nickname'] = $v['nickname'];
            $data['discount'] = $v['discount'];
            $data['is_lock'] = $v['is_lock'];
            $data['token'] = $v['token'];
            $data['unique_sn'] = $v['unique_user'];
            $data['is_agent'] = 0;
            $data['u8_sn'] = $v['ubaid'];
            $data['integral'] = $v['column'];
            $data['parent_channel_id'] = $v['canal_p'];
            $data['child_channel_id'] = $v['canal_c'];
            $data['shop_floor'] = $v['shop_floor'];
            $data['saccno'] = $v['saccno'];
            $data['add_time'] = $v['reg_time'];
            $data['archives'] = $v['usersnamewz'];
            $name = $this->get_table_name($v['user_id']);
            $row = Db::connect('db_config1')->table('ty_'.$name)->insert($data);
        }
        var_dump($row);
    }
    public function dataM(){
        exit;
        $n = input('get.id');
        $res = Db::table('tp_users')->field('user_id,user_type,mobile,password,saccno,reg_time,email')
            ->limit($n,1000)
            ->select();
        foreach ($res as $k=>$v){
            $data[$k]['u_id'] = $v['user_id'];
            $data[$k]['user_type'] = $v['user_type'];
            $data[$k]['identity_type'] = rand(1,4);
            $data[$k]['mobile'] = $v['mobile'];
            $data[$k]['email'] = $v['email'];
            $data[$k]['password'] = $v['password'];
            $data[$k]['saccno'] = $v['saccno'];
            $data[$k]['add_time'] = $v['reg_time'];
        }
        $row = Db::connect('db_config1')->table('ty_user')->insertAll($data);
        var_dump($row);
        exit;
    }
    /***********************将原来的会员信息导入到新的表里面end**************************/
}