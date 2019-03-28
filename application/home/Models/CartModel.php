<?php
namespace app\home\Models;

use think\facade\Cache;
use think\Model;
use think\Db;
class CartModel extends Model
{
    public $redis;
    /**
     *
     *  购物车商品详情
     *
     **/
    public function cartListInfo($userId,$status)
    {
        $totalAmount = 0.00;
        $data = Db::connect('db_config1')->name('cart as c')
            ->leftJoin('goods g','c.goods_id = g.g_id')
            ->leftJoin('store s','g.store_id = s.s_id')
            ->leftJoin('goods_image gi','c.goods_id = gi.goods_id')
            ->leftJoin('goods_recommend gr','c.goods_id = gr.goods_id')
            ->where('c.user_id', $userId)
            ->where('gi.goods_id', '>', 0)
            ->where('c.sale_status','=',$status)
            ->field('c.*,case c.sale_type when 1 then "普" when 2 then "抢" when 3 then "团"
             when 4 then "满" when 5 then "预" else "折" end as sale_type,case c.sale_status
              when 1 then "现货" when 2 then "预售" when 3 then "预定" else "生鲜" end 
              as sale_status,if(c.status=1,"正常","失效") as status,if(c.is_code_copy=1,
              "抄","") as is_code_copy,gi.image,g.store_id,g.status as g_status,s.store_name,
              s.store_sn,if(g.is_sample=1,"是","否") as is_sample,
              (c.goods_amout-c.coupon_money-c.shipping_money-pack_money) as totalAmout,
              gr.title,gr.desc,gr.recommend_goods_id')
            ->select();
        foreach ($data as $key => $val){
            //获取活动信息
            $activity = $this->getGoodsActivity($val['activity_id'], $val['sale_type']);
            $data[$key]['activity_type'] = $activity['activity_type'];
            $data[$key]['activity_title'] = $activity['activity_title'];
//            获取符合条件的商品最低价（抄码，赠品除外）
            $goodsinfo = $this->cartGoodsInfo($userId,$val['goods_id'],$val['goods_num']);
//            计算购物车选中商品总金额
            if($val['selected'] == 1){
                $totalAmount += $val['totalAmout'];
            }
            //根据仓库编号拼接数据表名
            if($val['store_sn']){
                $goodsPriceTable = 'ty_goods_price_' . $val['store_sn'];
            }

        }
//        $data = Db::query("select * from ".Db::connect('db_config1')."ty_cart where 1");
//         $con =  Db::connect('db_config1')->query("select * from ty_cart where 1");
        var_dump($data);exit();
    }
    /**
     *
     * 根据活动ID、类型获取活动信息
     *
    **/
    public function getGoodsActivity($activity_id, $activity_type) {
        $act['activity_type'] = '';
        $act['activity_title'] = '';
        $act['activity_url'] = '';
        //echo Db::connect('db_tysp_server')->name('ty_activity_flashsale')->getLastSql();
        switch ($activity_type) {
            case 2:
                $act['activity_type'] = '抢';
                $activity = Db::connect('db_tysp_server')->table('ty_activity_flashsale')->where('afs_id', $activity_id)->find();
                if($activity) {
                    $act['activity_title'] = $activity['title'];
                }
                break;
            case 3:
                $act['activity_type'] = '团';
                $activity = Db::connect('db_tysp_server')->table('ty_activity_group')->where('ag_id', $activity_id)->find();
                if($activity) {
                    $act['activity_title'] = $activity['title'];
                }
                break;
            case 4:
                $act['activity_type'] = '满';
                $activity = Db::connect('db_tysp_server')->table('ty_activity_full_gift')->where('afg_id', $activity_id)->find();
                if($activity) {
                    $act['activity_title'] = $activity['title'];
                }
                break;
            case 5:
                $act['activity_type'] = '预';
                $activity = Db::connect('db_tysp_server')->table('ty_activity_presell')->where('ap_id', $activity_id)->find();
                if($activity) {
                    $act['activity_title'] = $activity['title'];
                }
                break;
            case 6:
                $act['activity_type'] = '折';
                $activity = Db::connect('db_tysp_server')->table('ty_activity_discount')->where('ad_id', $activity_id)->find();
                if($activity) {
                    $act['activity_title'] = $activity['title'];
                }
                break;
            default:
                $act['activity_type'] = '';
                $act['activity_title'] = '';
                $act['activity_url'] = '';
                break;
        }
        return $act;
    }

    /**
     *
     *  通过查询获取商品门店价格和阶梯价格
     *
    **/
    public function cartGoodsInfo($userId,$goods_id,$goods_num)
    {
//        得到用户
        $userinfo = Db::connect('db_config1')->name('user')
            ->where('u_id',$userId)
            ->find();
        //通过商品Id获取商品信息
        $goods = Cache::store('redis')->get('9goods');
//        商品仓库库存量
        $store = Cache::store('redis')->get('4storeId');
        $ladderPrice = Cache::store('redis')->get('5ladder');
        if($store['goods_price']){
            echo "牛逼，有个性！";
        }
        var_dump($ladderPrice);exit();
    }

}