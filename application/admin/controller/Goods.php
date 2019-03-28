<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/23 0023
 * Time: 14:48
 */

namespace app\admin\controller;

use think\Db;
use app\admin\model\GoodsModel;
use think\facade\Request;
use think\facade\Cache;
class Goods extends Common
{
    public function goodsList()
    {
        $goods = new  GoodsModel();
        if (Request::isAjax()){
            $page = Request::post('page','1','trim');
            $pageSize = Request::post('limit','10','trim');
            $list = $goods->field('g_id,cate_id,goods_u8_sn,goods_name,is_recommend,is_new,is_hot,sort,is_coupon,is_stop,status,brand_id,sales_num,sales_base,goods_spec')
                ->paginate(['list_rows'=>$pageSize,'page'=>$page])->toArray();
            return $result = ['code'=>0,'data'=>$list['data'],'count'=>$list['total']];
        }
        return $this->fetch();
    }

    public function goodsSn()
    {
        $goods = new GoodsModel();
        $data = Request::post();
        $res = $goods->where('g_id',$data['id'])->update([$data['field']=>$data['vaule']]);
        if ($res){
            return $result = ['code'=>1,'msg'=>'修改成功',];
        }else{
            return $result = ['code'=>0,'msg'=>'修改失败'];
        }
    }
    //将原来商品数据导到现在商品表中
    public function data()
    {
        exit;
        $data = Db::connect('tp')
                    ->name('goods')
                    ->field('store_count,weight,market_price,shop_price
                    ,chailing_price,cost_price,price_ladder,is_free_shipping,goods_type
                    spec_type,spu,shipping_area_ids,',true)
                    ->limit(5000,500)   //笨办法 一次就只能跑500数据
                    ->select();
        foreach ($data as $k=>$v){
        $info[$k]['g_id'] = $v['goods_id'];$info[$k]['cate_id'] = $v['cat_id'];
        $info[$k]['channel_cate_id'] = $v['extend_cat_id'];$info[$k]['goods_u8_sn'] = $v['ubabianhao'];
        $info[$k]['goods_name'] = $v['goods_name'];$info[$k]['goods_mini_name'] = $v['goods_name_xiao'];
        $info[$k]['goods_style'] = $v['goods_kuanshi'];$info[$k]['goods_unique_sn'] = $v['goods_only_ma'];
        $info[$k]['goods_unit'] = $v['sku'];$info[$k]['goods_factory'] = 1;
        $info[$k]['upper_time'] = $v['on_time'];$info[$k]['click_num'] =0;
        $info[$k]['brand_id'] = $v['brand_id'];$info[$k]['max_num'] =0;
        $info[$k]['comment_num'] =0;$info[$k]['keywords'] = $v['keywords'];
        $info[$k]['goods_remark'] = $v['goods_remark'];$info[$k]['goods_content'] = $v['goods_content'];
        $info[$k]['original_img'] = $v['original_img'];$info[$k]['is_real'] = $v['is_real'];
        $info[$k]['original_img'] = $v['original_img'];$info[$k]['is_real'] = $v['is_real'];
        $info[$k]['is_reserve'] = $v['is_yuding'];$info[$k]['reserve_min_num'] = 0;
        $info[$k]['reserve_cycle'] = 0;$info[$k]['status'] = $v['is_on_sale'];
        $info[$k]['is_coupon'] = $v['iscoupon'];$info[$k]['is_recommend'] = $v['is_recommend'];
        $info[$k]['is_new'] = $v['is_new'];$info[$k]['is_hot'] = $v['is_hot'];
        $info[$k]['sort'] = $v['sort'];$info[$k]['last_update'] = $v['last_update'];
        $info[$k]['give_integral'] = $v['give_integral'];$info[$k]['exchange_integral'] = $v['exchange_integral'];
        $info[$k]['suppliers_id'] = 0 ;$info[$k]['sales_num'] = $v['sales_sum'];
        $info[$k]['sale_type'] = 1 ;$info[$k]['activity_id'] = 0;
        $info[$k]['goods_spec'] = $v['goods_guige'] ;$info[$k]['retail_unit'] = $v['skuchai'];
        $info[$k]['goods_spec'] = $v['goods_guige'] ;$info[$k]['retail_unit'] = $v['skuchai'];
        $info[$k]['storage_type'] = $v['storage_type'] ;$info[$k]['quality_period'] = $v['baozhizhouqi'];
        $info[$k]['storage_type'] = $v['storage_type'] ;$info[$k]['quality_period'] = $v['baozhizhouqi'];
        $info[$k]['ratio'] = $v['ratio'] ;$info[$k]['sales_base'] = $v['sales_base'];
        $info[$k]['is_given'] = $v['given_type'] ;$info[$k]['given_u8_sn'] = $v['given_username'];
        $info[$k]['given_saccno'] = $v['given_saccno'] ;$info[$k]['store_id'] = rand(1,3);
        $info[$k]['is_stop'] = $v['is_stop'];
        }
        $goods = new  GoodsModel();
        $res =  $goods->insertAll($info);
        echo '<pre>';
        var_dump($res);
        exit;
    }
}