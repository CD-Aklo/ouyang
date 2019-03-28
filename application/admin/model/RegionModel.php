<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/16 0016
 * Time: 15:14
 */

namespace app\admin\model;
use think\Model;
class RegionModel extends Model
{
    // 设置当前模型对的数据库链接
    protected $connection = 'db_config1';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'ty_region1';

    /*
     * @param $pages   分页参数
     * @param $map     搜索关键字
     * @param $down    查看下级条件
     * @param $up      查看上级条件
     * @return array
     */
    public function region_list($pages,$map,$down,$up){
        $list = $this->where($map)
                    ->where($down)
                    ->where($up)
                    ->paginate($pages)
                    ->toArray();
        if (empty($list['data'])) return $result = ['code'=>1,'msg'=>'暂无相关数据','data'=>[],'count'=>0];
        //查询上级信息
        foreach ($list['data'] as $k=>$v){
            $up_id[] = $v['p_id'];
        }
        $up_list = $this->where('id','in',$up_id)->field('id,name')->select()->toArray();
        foreach ($list['data'] as $k=>$v){
            if (!empty($up_list)){
                foreach ($up_list as $key=>$value){
                    if ($v['p_id']==$value['id']){
                        $list['data'][$k]['p_name'] = $value['name'];
                    }
                }
            }
        }
        return $result = ['code'=>0,'msg'=>'获取成功','data'=>$list['data'],'count'=>$list['total']];
    }
    public function region_add($data,$id){
        //查询前台传过来的城市信息
        $res = $this->where('id',$id)->field('id,level,p_id')->find();
        //判断是增加同级还是下级
        $info['name'] = $data['name'];
        $info['distance'] = $data['distance'];
        $info['delivery'] = $data['delivery'];
        $info['freight'] = $data['freight'];
        $info['freight_free'] = $data['freight_free'];
        if ($data['level'] == 1){
            $info['level'] = $res['level'];
            $info['p_id'] = $res['p_id'];
        }else{
            $info['level'] = $res['level']+1;
            $info['p_id'] = $res['id'];
        }
        $row = $this->save($info);
        if ($row){
            return $result = ['code'=>1,'msg'=>'新增成功'];
        }else{
            return $result = ['code'=>0,'msg'=>'新增失败'];
        }
    }

}