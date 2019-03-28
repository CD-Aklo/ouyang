<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/27 0027
 * Time: 14:05
 */

namespace app\admin\model;


use think\Model;

class GoodsModel extends Model
{
    // 设置当前模型对的数据库链接
    protected $connection = 'db_config1';
    // 设置当前模型对应的完整数据表名称
    protected $table = 'ty_goods';
}