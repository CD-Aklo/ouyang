<?php
namespace app\home\controller;

use think\Db;
use think\facade\Cache;
class Index extends Common {
	
    public function index() {
//        模拟商品信息
        $goods = array(
            0=>[
                "cart_id"=> 1,
                "user_id"=> 3975,
                "goods_u8_sn"=> "01010100001",
                "goods_id"=> 1,
                "goods_name"=> "140gUP盈泰单冻大胸（佳盈）",
                "goods_price"=> "180.00",
                "goods_original_price"=> "150.00",
                "goods_num"=> 2,
                "goods_amout"=> "360",
                "goods_unit"=> "件",
                "sku"=> "件",
                "spec_key"=> "",
                "spec_key_name"=> "",
                "selected"=> 1,
                "sale_type"=> "满",
                "sale_status"=> "现货",
                "shipping"=> 0,
                "shipping_name"=> NULL,
                "shipping_money"=> "0",
                "period"=> "",
                "coupon_id"=> NULL,
                "coupon_name"=> NULL,
                "coupon_money"=> NULL,
                "pack_money"=> "0",
                "storage_type"=> 1,
                "activity_id"=> 0,
                "is_retail"=> 0,
                "sku_retail"=> "",
                "specifications"=> "1*10kg，10kg",
                "ratio"=> 0,
                "is_gift"=> 0,
                "is_code_copy"=> "",
                "status"=>  "正常",
                "add_time"=> 1552534743,
                "image"=> "/upload/11.jpg",
                "store_id"=> 1,
                "g_status"=> 1,
                "store_name"=> "成都仓",
                "store_sn"=> "cdmaster",
                "is_sample"=> "否",
                "totalAmout"=> NULL,
                "title"=> NULL,
                "desc"=> NULL,
                "recommend_goods_id"=> NULL,
                "activity_type"=> "",
                "activity_title"=> "",
            ],
            1=>[
                "cart_id"=> 9,
                "user_id"=> 3975,
                "goods_u8_sn"=> "01010100001",
                "goods_id"=> 1,
                "goods_name"=> "140gUP盈泰单冻大胸（佳盈）",
                "goods_price"=> "180.00",
                "goods_original_price"=> "150.00",
                "goods_num"=> 2,
                "goods_amout"=> "360",
                "goods_unit"=> "件",
                "sku"=> "件",
                "spec_key"=> "",
                "spec_key_name"=> "",
                "selected"=> 1,
                "sale_type"=> "满",
                "sale_status"=> "现货",
                "shipping"=> 0,
                "shipping_name"=> NULL,
                "shipping_money"=> "0",
                "period"=> "",
                "coupon_id"=> NULL,
                "coupon_name"=> NULL,
                "coupon_money"=> NULL,
                "pack_money"=> "0",
                "storage_type"=> 1,
                "activity_id"=> 0,
                "is_retail"=> 0,
                "sku_retail"=> "",
                "specifications"=> "1*10kg，10kg",
                "ratio"=> 0,
                "is_gift"=> 0,
                "is_code_copy"=> "",
                "status"=> "正常",
                "add_time"=> 1552534743,
                "image"=> "/upload/11.jpg",
                "store_id"=> 1,
                "g_status"=> 1,
                "store_name"=> "成都仓",
                "store_sn"=> "cdmaster",
                "is_sample"=> "否",
                "totalAmout"=> NULL,
                "title"=> NULL,
                "desc"=> NULL,
                "recommend_goods_id"=> NULL,
                "activity_type"=> "",
                "activity_title"=> "",
            ],
            2=>[
                "cart_id"=> 2,
                "user_id"=> 3975,
                "goods_u8_sn"=> "01010100002",
                "goods_id"=> 2,
                "goods_name"=> "盈泰鸡骨架-M（佳盈）",
                "goods_price"=> "25.00",
                "goods_original_price"=> "30.00",
                "goods_num"=> 3,
                "goods_amout"=> "750",
                "goods_unit"=> "件",
                "sku"=> "件",
                "spec_key"=> "",
                "spec_key_name"=> "",
                "selected"=> 0,
                "sale_type"=> "抢",
                "sale_status"=> "现货",
                "shipping"=> 0,
                "shipping_name"=> NULL,
                "shipping_money"=> "0",
                "period"=> "",
                "coupon_id"=> NULL,
                "coupon_name"=> NULL,
                "coupon_money"=> NULL,
                "pack_money"=> "0",
                "storage_type"=> 1,
                "activity_id"=> 1,
                "is_retail"=> 1,
                "sku_retail"=> "",
                "specifications"=> "1*6.8kg,6.8kg",
                "ratio"=> 0,
                "is_gift"=> 0,
                "is_code_copy"=> "",
                "status"=> "正常",
                "add_time"=> 1552544743,
                "image"=> "/upload/15.jpg",
                "store_id"=> 3,
                "g_status"=> 1,
                "store_name"=> "成都仓",
                "store_sn"=> "cdsample",
                "is_sample"=> "否",
                "totalAmout"=> NULL,
                "title"=> "超级好吃的嘎嘎",
                "desc"=> "这个嘎嘎很好吃",
                "recommend_goods_id"=> 1,
                "activity_type"=> "",
                "activity_title"=> ""
            ]
        );
        foreach ($goods  as $key => $val){
            $goodsid = $val['cart_id'].'goods';
            Cache::store('redis')->set($goodsid,$val,3600);
        }
//        模拟门店库存信息
        $store_goods =array(
            0 => [
              'store_id'    => 2,
              'goods_id'    => 1,
              'goods_price' => '200.50',
              'stock_num'   => 2986,
              'goods_name'  => '盈泰鸡骨架-M（佳盈）'
            ],
            1 => [
                'store_id'    => 3,
                'goods_id'    => 1,
                'goods_price' => '285.95',
                'stock_num'   => 800,
                'goods_name'  => '140gUP盈泰单冻大胸（佳盈）-M'
            ],
            2 => [
                'store_id'    => 2,
                'goods_id'    => 1,
                'goods_price' => '200.50',
                'stock_num'   => 300,
                'goods_name'  => '盈泰鸡骨架-M（佳盈）'
            ],
            3 => [
                'store_id'    => 4,
                'goods_id'    => 1,
                'goods_price' => '285.95',
                'stock_num'   => 900,
                'goods_name'  => '140gUP盈泰单冻大胸（佳盈）'
            ],
        );
        foreach ($store_goods as $key => $val){
            $goodsid = $val['store_id'].'storeId';
            Cache::store('redis')->set($goodsid,$val,3600);
        }
//        模拟阶梯价格信息
        $goodsPrice = Db::connect('db_config2')->name('goods_ladderprice')
                ->where(1)
                ->select();
       foreach ($goodsPrice as $key =>$val){
           $goodsPriceID =$val['goods_id'].'ladder';
           Cache::store('redis')->set($goodsPriceID,$val,3600);
       }
    	echo 'hello index';
    }

    public function miss() {
    	echo '404 Not Found';
    }
}
