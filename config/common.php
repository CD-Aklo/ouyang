<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/3/18 0018
 * Time: 15:37
 */
return [
     //分公司配置
     'filiale' => [
         88888888 => ['name'=>'四川', 'saccno'=>88888888],
         11020008 => ['name'=>'重庆', 'saccno'=>11020008],
         11020016 => ['name'=>'陕西', 'saccno'=>11020016],
         11020009 => ['name'=>'云南', 'saccno'=>11020009],
         11020014 => ['name'=>'西藏', 'saccno'=>11020014],
         11020006 => ['name'=>'贵州', 'saccno'=>11020006],
         11020033 => ['name'=>'湖北', 'saccno'=>11020033],
     ],
    //广告位配置
    'ads_position' => [
        1 => ['position_id'=>1, 'name'=>'【首页】轮播'],
        2 => ['position_id'=>2, 'name'=>'【首页】焦点图']
    ],
    //会员身份配置
    'identity_type' => [
        1 => ['id'=>1,'name'=>'账期会员'],
        2 => ['id'=>2,'name'=>'线下付款'],
        3 => ['id'=>3,'name'=>'货到收现'],
    ],
    //结算周期
    'identity_time' => [
        //period  结算周期   period_charge_ratio 结算比率
        1 => ['id'=>1,'period_attr'=>'货到付款','period_charge_ratio'=>'0.01'],
        2 => ['id'=>2,'period_attr'=>'周结','period_charge_ratio'=>'0.02'],
        3 => ['id'=>3,'period_attr'=>'半月结','period_charge_ratio'=>'0.03'],
        4 => ['id'=>4,'period_attr'=>'月结(30/45)','period_charge_ratio'=>'0.04'],
        5 => ['id'=>5,'period_attr'=>'月结(45/60)','period_charge_ratio'=>'0.05'],
        6 => ['id'=>6,'period_attr'=>'月结(大于60天)','period_charge_ratio'=>'0.08'],
        7 => ['id'=>7,'period_attr'=>'特殊申请0','period_charge_ratio'=>'0'],
        8 => ['id'=>8,'period_attr'=>'特殊申请1','period_charge_ratio'=>'0.01'],
        9 => ['id'=>9,'period_attr'=>'特殊申请2','period_charge_ratio'=>'0.02'],
        10 => ['id'=>10,'period_attr'=>'特殊申请3','period_charge_ratio'=>'0.03'],
        11 => ['id'=>11,'period_attr'=>'特殊申请4','period_charge_ratio'=>'0.04'],
        12 => ['id'=>12,'period_attr'=>'特殊申请5','period_charge_ratio'=>'0.05'],
    ],
    //文章分类
    'article' => [
        1 => ['id'=>1,'type'=>'新闻'],
        2 => ['id'=>2,'type'=>'公告'],
        3 => ['id'=>3,'type'=>'说明'],
    ]
];