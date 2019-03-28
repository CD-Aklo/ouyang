<?php
namespace app\admin\controller;
use think\Db;
use think\facade\Request;
class System extends Common
{
    //站点设置
    public function system($sys_id=1){
        $table = db('system');
        if(Request::isAjax()) {
            $data = Request::except('file');
            if($table->where('sys_id',1)->update($data)!==false) {
                savecache('System');
                return json(['code' => 1, 'msg' => '设置保存成功!', 'url' => url('system/system')]);
            } else {
                return json(array('code' => 0, 'msg' =>'设置保存失败！'));
            }
        }else{
            $system = $table->where('sys_id', $sys_id)->find();
            $this->assign('system', json_encode($system,true));
            return $this->fetch();
        }
    }

}
