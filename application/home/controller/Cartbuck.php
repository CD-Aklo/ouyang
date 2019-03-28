<?php
namespace app\home\controller;
use think\Db;
use think\Request;
class Cart extends Common {
	
	//购物车功能点
	//商品可以增加减少单品数量，更改数量的同时要检查单品库存量、单价、活动的变化，单价要取最低价格
	//商品如果存在阶梯价要根据单品的数量取对应的商品单价
	//抄码商品不能更商品数量，不参加任何活动，同一个抄码产品，如果添加2件，则显示的是两条数据
	//商品只能参加1个活动，不区分活动类型，活动选择所属分公司
	//套餐内的单品，可以参加其他套餐，但不能参加任何活动
	//特价货，业务员针对某商品申请的特价。当购买整件商品时享受特价，拆零购买不享受特价，按照普通拆零单价计算
	//特供货，后台添加的特供货按照规则判断商品
	//商城价格原则，用户所有能享受的价格，取最低
	//如果购买数量超出活动单次限购数量，则单价恢复成原价
	//样品用户单次可以用积分兑换1份，用户可多次兑换样品
	//拆零购买的商品不参加任何活动
	//支持优惠券的商品不能参加任何活动
	//预定商品(预售有差异)有预定起定量，不能拆零，可使用优惠券，不能参加其他活动
	//预售商品生成预售订单时只计算商品总价，不计算运费和仓储费，不减少此商品的库存，不能使用优惠券
	//预售商品提货时转换成现货订单，订单总价为0，只计算运费和仓储费，同时减少商品库存
	
	//活动类型 1无 2抢购 3团购 4满赠 5预售 6折扣
	protected $saleType = [1=>'' ,2=>'抢', 3=>'团', 4=>'满', 5=>'预', 6=>'折'];

    public function cartList() {
    	$totalAmount = 0.00;
    	$userId = $_COOKIE['userId'];
    	$cartList = Db::connect('db_config1')->name('cart')->alias('c')
    		->leftJoin('goods g','c.goods_id = g.g_id')
    		->leftJoin('store s','g.store_id = s.s_id')
    		->leftJoin('goods_image gi','c.goods_id = gi.goods_id')
    		->where('c.user_id', $userId)
    		->where('gi.goods_id', '>', 0)
    		->field('c.*,gi.image,g.store_id,g.status as g_status,s.store_name,s.is_sample')->select();
//    	echo Db::name('cart')->getLastSql();
    	foreach ($cartList as &$val) {
    		//获取活动信息
    		$activity = $this->getGoodsActivity($val['activity_id'], $val['sale_type']);
    		$val['activity_type'] = $activity['activity_type'];
    		$val['activity_title'] = $activity['activity_title'];
    		//判断是否为样品仓的商品，样品要特殊处理
    		if($val['is_sample'] == 1) { 

    		}
    		$val['sale_type_tag'] = $this->saleType[$val['sale_type']];
    		//获取好货推荐即精选信息
    		$val['goods_recommend'] = [];
    		$recommendList = Db::connect('db_config1')->name('goods_recommend')->where('goods_id', $val['goods_id'])->field('gr_id, title')->select();
    		$val['goods_recommend'] = $recommendList;
    		if($val['selected'] == 1) { 
    			$totalAmount += $val['goods_num'] * $val['goods_price'];
    		}
    	}
    	$totalAmount = number_format($totalAmount, 2);
    	$this->assign('title', '购物车');
    	$this->assign('cartList',$cartList);
    	$this->assign('totalAmount', $totalAmount);
		return $this->fetch();
    }

    //根据活动ID、类型获取活动信息
    public function getGoodsActivity($activity_id, $activity_type) {
    	$act['activity_type'] = '';
    	$act['activity_title'] = '';
    	$act['activity_url'] = '';
    	//echo Db::connect('db_tysp_server')->name('ty_activity_flashsale')->getLastSql();
    	switch ($activity_type) {
			case 2:
				$act['activity_type'] = '抢购';
				$activity = Db::connect('db_tysp_server')->table('ty_activity_flashsale')->where('afs_id', $activity_id)->find();
				if($activity) {
					$act['activity_title'] = $activity['title'];
				}
				break;
			case 3:
				$act['activity_type'] = '团购';
				$activity = Db::connect('db_tysp_server')->table('ty_activity_group')->where('ag_id', $activity_id)->find();
				if($activity) {
					$act['activity_title'] = $activity['title'];
				}
				break;
			case 4:
				$act['activity_type'] = '满赠';
				$activity = Db::connect('db_tysp_server')->table('ty_activity_full_gift')->where('afg_id', $activity_id)->find();
				if($activity) {
					$act['activity_title'] = $activity['title'];
				}
				break;
			case 5:
				$act['activity_type'] = '预售';
				$activity = Db::connect('db_tysp_server')->table('ty_activity_presell')->where('ap_id', $activity_id)->find();
				if($activity) {
					$act['activity_title'] = $activity['title'];
				}
				break;
			case 6:
				$act['activity_type'] = '折扣';
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

    //根据用户ID，商品ID获取库存及符合条件的最低价格【非抄码、非赠品】
    public function getGoodsDetail($user_id, $goods_id, $num) {
    	if($num < 1){
    		return false;
    	}
    	//初始化商品单价信息
    	//type:normal普通价，ladder阶梯价，activity活动价
    	$goodsPrice['type'] = 'normal';
    	$goodsPrice['num']  = $num;
    	$goodsPrice['price'] = 0.00;

    	//用户优先选所属分公司仓库的商品，当分公司无货时则可以选择其他仓库
    	//查询当前用户信息
    	$userInfo = db('user')->where('u_id', $user_id)->find();

    	//查询商品所在仓库信息
    	$goodsInfo = db('goods')->alias('g')
    		->leftJoin('store s','g.store_id = s.s_id')
    		->where('g.g_id', $goods_id)
    		->field('g.*,s.store_sn,s.store_name,s.saccno,s.is_sample')->find();
    	//如果此商品没有对应仓库信息返回false
    	if(empty($goodsInfo['store_sn'])){
    		return false;
    	}

    	//根据仓库编号拼接数据表名
    	$goodsPriceTable = 'ty_goods_price_' . $goodsInfo['store_sn'];
 	   	$goodsPriceInfo = Db::connect('db_tysp_server')->table($goodsPriceTable)->where('goods_id', $goods_id)->find();
 	   	echo '<pre>';
    	print_r($goodsPriceInfo);
    	//取出商品单价
    	$unitPrice = $goodsPriceInfo['shop_price'];
    	$goodsPrice['price'] = $unitPrice;
    	//取出商品阶梯价格
    	if(!empty($goodsPriceInfo['ladder_price'])){
    		$ladderPrice = json_decode($goodsPriceInfo['ladder_price'], true);
    		if(!empty($ladderPrice)){
    			$goodsPrice['type'] = 'ladder';
    			$unitPrice = $this->getLadderPrice($num, $ladderPrice);
    			if($unitPrice < $goodsPrice['price']){
    				$goodsPrice['price'] = $unitPrice;
    			}
    		}
    	}
    	print_r($goodsPrice);

    	//查询商品参加活动信息，单品只能参加一个活动
    	$activity_id = $goodsInfo['activity_id'];
    	$activity_type = $goodsInfo['sale_type'];
    	switch ($activity_type) {
			case 2:
				//判断抢购活动是否结束、是否在活动期间、每次购买数是否在限定范围内、用户类型、付款类型等
				//如果购买数超出限定范围，则按非活动价计算金额
				$activity = Db::connect('db_tysp_server')->table('ty_activity_flashsale')->where('afs_id', $activity_id)->find();
				//如果活动结束或者售罄则break
				if($activity['status'] != 1){
					break;
				}
				//如果当前用户所属分公司和活动分公司不相同则break
				if($userInfo['saccno'] != $activity['saccno']){
					break;
				}
				//如果活动用户类型不支持当前用户类型则break【0全部，1普通，2认证】
				if(!$activity['group_type'] && ($userInfo['user_type'] != $activity['group_type'])){
					break;
				}
				//如果购买数超过活动单次购买数或则超过活动剩余数break
				if($num > $activity['buy_limit'] || $num > $activity['remain_num']){
					break;
				}
				if($activity['price'] < $goodsPrice['price']){
					$goodsPrice['type'] = 'activity';
    				$goodsPrice['price'] = $activity['price'];
    			}
				break;
			case 3:
				//判断团购活动是否结束、是否在活动期间、销售对象、起团量、最大团购量等
				//根据团购阶梯配置计算金额|{5:20, 10:18}单价:数量
				$activity = Db::connect('db_tysp_server')->table('ty_activity_group')->where('ag_id', $activity_id)->find();
				//如果活动结束或者售罄则break
				if($activity['status'] != 1){
					break;
				}
				//如果当前用户所属分公司和活动分公司不相同则break
				if($userInfo['saccno'] != $activity['saccno']){
					break;
				}
				//如果活动用户类型不支持当前用户类型则break【0全部，1普通，2认证】
				if(!$activity['group_type'] && ($userInfo['user_type'] != $activity['group_type'])){
					break;
				}
				//如果购买数超过活动单次购买数或则超过活动剩余数break
				if($num < $activity['mini_num'] || $num > $activity['max_num'] || $num > $activity['remain_num']){
					break;
				}
				//根据阶梯价格匹配商品最低单价
				if(!empty($activity['group_price'])){
		    		$ladderPrice = json_decode($activity['group_price'], true);
		    		if(!empty($ladderPrice)){
		    			$unitPrice = $this->getLadderPrice($num, $ladderPrice);
		    			if($unitPrice < $goodsPrice['price']){
		    				$goodsPrice['type'] = 'activity';
		    				$goodsPrice['price'] = $unitPrice;
		    			}
		    		}
		    	}
				break;
			case 4:
				//判断满赠活动是否结束、是否在活动期间、销售对象
				//根据满赠阶梯配置计算赠品|{1000:10, 2000:20}金额:赠送量，单位一般为拆零单位，也可为件
				$activity = Db::connect('db_tysp_server')->table('ty_activity_full_gift')->where('afg_id', $activity_id)->find();
				break;
			case 5:
				//判断预售活动是否结束、是否在活动期间、单笔预购最低量、最大量
				//预售商品生成预售订单时只计算商品总价，不计算运费和仓储费，不减少此商品的库存，不能使用优惠券
				$activity = Db::connect('db_tysp_server')->table('ty_activity_presell')->where('ap_id', $activity_id)->find();
				break;
			case 6:
				$activity = Db::connect('db_tysp_server')->table('ty_activity_discount')->where('ad_id', $activity_id)->find();
				break;
			default:
				$activity = [];
				break;
    	}
    	print_r($activity);
    }

    public function add() {
	 	$this->getGoodsDetail(1, 1, 40);
    }

    //根据购买数获取商品阶梯单价
    public function getLadderPrice($num, $ladderPrice) {
    	$unitPrice = 0.00;
    	ksort($ladderPrice);
		$ladderNum = array_keys($ladderPrice);
		$ladderValue = array_values($ladderPrice);
		$maxLadderNum = max($ladderNum);
		$minLadderValue = min($ladderValue);
		if($num >= $maxLadderNum){
			$unitPrice = $minLadderValue;
		}else{
			foreach ($ladderPrice as $k => $v) {
				if($num < $k){
					$unitPrice = $v;
					break;
				}
			}
		}
		return $unitPrice;
    }

    /**
     *
     *  购物车商品明细
     *
    **/
    public function CartListInfo(Request $request)
    {
        $userId = $_COOKIE['userId'];
        $id = $request->id;
        $data = Db::connect("db_config1")->name('cart')
            ->leftJoin('goods g','c.goods_id = g.g_id')
            ->leftJoin('store s','g.store_id = s.s_id')
            ->leftJoin('goods_image gi','c.goods_id = gi.goods_id')
            ->where('c.user_id', $userId)
            ->where('gi.goods_id', '>', 0)
            ->where('c.sale_type',$id)
            ->field('c.*,gi.image,g.store_id,g.status as g_status,s.store_name,s.is_sample')
            ->select();
        echo $data->getLastSql();
        return ['data' => $data,'code' => 1 ,'msg' => '查询成功！'];
    }


    /**
     *
     * 数据循环赋值
    **/
    public function CirculationForLi()
    {

    }
}