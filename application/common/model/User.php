<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/20 0020
 * Time: 10:51
 */

namespace app\common\model;
use think\Model;
class User extends Model
{
    protected $connection = 'db_config1';
    protected $table = 'ty_user';
    protected $pk = 'u_id';

    /*
     * 检验用户是否存在，存在返回用户id和分公司编码
     * @param $mobile
     * @return bool|mixed
     */
    public function check_user($mobile){
        $res = $this->where('mobile',$mobile)->field('u_id,saccno')->find();
        if ($res){
            return $res;
        }else{
            return false;
        }
    }
}