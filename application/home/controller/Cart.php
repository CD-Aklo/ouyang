<?php
namespace  app\home\controller;

use think\Db;
use think\Request;
use app\home\Models\CartModel;
class Cart extends Common
{
    /**
     *  商品购物车展示
    **/
    public function CartList(Request $request)
    {
        $userId = $_COOKIE['userId'];
        $status = 1;
//        通过用户ID判断用户登陆状态
        if(empty($userId)){
            $this->redirect('user/login','登陆失效，请重新登陆！');
        }
//        获取购物车列表
        $cartmodel = new CartModel();
        $cartlist = $cartmodel->cartListInfo($userId,$status);
//        $cartlist = CartModel();
        $this->assign('cartList',$cartlist);
        $this->assign('title',"购物车");
        return $this->fetch();
    }

}