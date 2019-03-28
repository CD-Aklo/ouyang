<?php
namespace app\admin\controller;
use think\Db;
use think\facade\Env;
use app\admin\model\Admin;
class Index extends Common
{
    protected $menus; 
    public function initialize(){
        parent::initialize();
        // 获取缓存数据
        $adminRule = cache('adminRule');
        if(!$adminRule){
            $adminRule = db('admin_rule')->where('menustatus=1')->order('sort')->select();
            cache('AdminRule', $adminRule, 3600);
       }
        //声明数组
        $menus = array();
        foreach ($adminRule as $key=>$val){
            $adminRule[$key]['href'] = url($val['href']);
            if($val['pid']==0){
                if(session('aid')!=1){
                    if(in_array($val['rule_id'],$this->adminRules)){
                        $menus[] = $val;
                    }
                }else{
                    $menus[] = $val;
                }
            }
        }
        foreach ($menus as $k=>$v){
            foreach ($adminRule as $kk=>$vv){
                if($v['rule_id']==$vv['pid']){
                    if(session('aid')!=1) {
                        if (in_array($vv['rule_id'], $this->adminRules)) {
                            $menus[$k]['children'][] = $vv;
                        }
                    }else{
                        $menus[$k]['children'][] = $vv;
                    }
                }
            }
        }
        $this->menus = $menus;
    }
    public function index(){
        $this->assign('menus',json_encode($this->menus,true));
        return $this->fetch();
    }
    public function main(){
        $version = Db::query('SELECT VERSION() AS ver');
        $config  = [
            'url'             => $_SERVER['HTTP_HOST'],
            'server_os'       => PHP_OS,
            'server_port'     => $_SERVER['SERVER_PORT'],
            'server_ip'       => $_SERVER['SERVER_ADDR'],
            'server_soft'     => $_SERVER['SERVER_SOFTWARE'],
            'php_version'     => PHP_VERSION,
            'mysql_version'   => $version[0]['ver'],
            'max_upload_size' => ini_get('upload_max_filesize')
        ];
        $this->assign('config', $config);
        return $this->fetch();
    }
    public function clear(){
        $R = Env::get('runtime_path');
        if ($this->_deleteDir($R)) {
            $result['info'] = '清除缓存成功!';
            $result['status'] = 1;
        } else {
            $result['info'] = '清除缓存失败!';
            $result['status'] = 0;
        }
        $result['url'] = url('admin/index/index');
        return $result;
    }
    private function _deleteDir($R)
    {
        $handle = opendir($R);
        while (($item = readdir($handle)) !== false) {
            if ($item != '.' and $item != '..') {
                if (is_dir($R . '/' . $item)) {
                    $this->_deleteDir($R . '/' . $item);
                } else {
                    if (!unlink($R . '/' . $item))
                        die('error!');
                }
            }
        }
        closedir($handle);
        return rmdir($R);
    }

    //退出登陆
    public function logout(){
        session(null);
        $this->redirect('admin/login/index');
    }

    //修改密码
    public function changepwd(){
        $this->assign('menus',json_encode($this->menus,true));
        return $this->fetch();
    }

    //修改密码
    public function passowdform(){
        $admin = new Admin();
        $aid = session('aid');
        $info = $admin->getInfo($aid);
        if(request()->isPost()){
            $oldpwd = input('post.oldpwd');
            $newpwd = input('post.newpwd');
            $renewpwd = input('post.renewpwd');
            if($newpwd != $renewpwd){
                return $result = ['code'=>0,'msg'=>'两次输入密码不一致!'];
            }
            $info = $admin->getPwd($aid);
            if(md5($oldpwd) != $info['pwd']){
                return $result = ['code'=>0,'msg'=>'旧密码错误!'];
            }
            $where['admin_id'] = $aid;
            $data['pwd'] = md5($newpwd);
            Admin::update($data,$where);
            return $result = ['code'=>1,'msg'=>'修改成功!'];
        } else {
            $this->assign('info', json_encode($info,true));
            return $this->fetch('passowdForm');
        }
    }
    
}
