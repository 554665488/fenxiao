<?php defined('In33hao') or exit('Access Invalid!'); return array (
  'system' => 
  array (
    'name' => '平台',
    'child' => 
    array (
      0 => 
      array (
        'name' => '设置',
        'child' => 
        array (
          'setting' => '站点设置',
          'upload' => '上传设置',
          'message' => '邮件设置',
          'taobao_api' => '淘宝接口',
          'admin' => '权限设置',
          'admin_log' => '操作日志',
          'area' => '地区设置',
          'task' => '计划任务',
          'cache' => '清理缓存',
        ),
      ),
      1 => 
      array (
        'name' => '会员',
        'child' => 
        array (
          'member' => '会员管理',
          'saleorder' => '委托销售订单',
          'rate_income_outcome' => '统计',
        ),
      ),
      2 => 
      array (
        'name' => '网站',
        'child' => 
        array (
          'article_class' => '文章分类',
          'article' => '文章管理',
          'document' => '会员协议',
          'navigation' => '页面导航',
          'adv' => '广告管理',
          'rec_position' => '推荐位',
        ),
      ),
    ),
  ),
  'shop' => 
  array (
    'name' => '商城',
    'child' => 
    array (
      0 => 
      array (
        'name' => '设置',
        'child' => 
        array (
          'setting' => '商城设置',
          'upload' => '图片设置',
          'search' => '搜索设置',
          'seo' => 'SEO设置',
          'message' => '消息通知',
          'payment' => '支付方式',
          'express' => '快递公司',
          'express_api' => '快递接口',
          'waybill' => '运单模板',
          'web_config' => '首页管理',
          'web_channel' => '频道管理',
        ),
      ),
      1 => 
      array (
        'name' => '商品',
        'child' => 
        array (
          'goods' => '商品管理',
          'goods_class' => '分类管理',
          'brand' => '品牌管理',
          'type' => '类型管理',
          'spec' => '规格管理',
          'goods_album' => '图片空间',
          'goods_recommend' => '商品推荐',
        ),
      ),
      2 => 
      array (
        'name' => '店铺',
        'child' => 
        array (
          'store' => '店铺管理',
          'store_grade' => '店铺等级',
          'store_class' => '店铺分类',
          'domain' => '二级域名',
          'sns_strace' => '店铺动态',
          'help_store' => '店铺帮助',
          'store_joinin' => '商家入驻',
          'ownshop' => '自营店铺',
        ),
      ),
      3 => 
      array (
        'name' => '会员',
        'child' => 
        array (
          'member' => '会员管理',
          'points' => '积分管理',
          'points2' => '优惠券管理',
          'predeposit' => '预存款',
        ),
      ),
      4 => 
      array (
        'name' => '交易',
        'child' => 
        array (
          'order' => '商品订单',
          'vr_order' => '虚拟订单',
          'refund' => '退款管理',
          'return' => '退货管理',
          'vr_refund' => '虚拟订单退款',
          'consulting' => '咨询管理',
          'inform' => '举报管理',
          'evaluate' => '评价管理',
          'complain' => '投诉管理',
        ),
      ),
      5 => 
      array (
        'name' => '运营',
        'child' => 
        array (
          'operating' => '运营设置',
          'bill' => '结算管理',
          'vr_bill' => '虚拟订单结算',
          'mall_consult' => '平台客服',
          'rechargecard' => '平台充值卡',
          'delivery' => '物流自提服务站',
          'contract' => '消费者保障服务',
        ),
      ),
      6 => 
      array (
        'name' => '促销',
        'child' => 
        array (
          'operation' => '促销设定',
          'groupbuy' => '抢购管理',
          'vr_groupbuy' => '虚拟抢购设置',
          'promotion_cou' => '加价购',
          'promotion_xianshi' => '限时折扣',
          'promotion_mansong' => '店铺满即送',
          'promotion_bundling' => '优惠套装',
          'promotion_booth' => '推荐展位',
          'promotion_book' => '预售商品',
          'promotion_fcode' => 'Ｆ码商品',
          'promotion_combo' => '推荐组合',
          'promotion_sole' => '手机专享',
          'pointprod' => '积分兑换',
          'voucher' => '店铺代金券',
          'redpacket' => '平台红包',
          'activity' => '活动管理',
        ),
      ),
      7 => 
      array (
        'name' => '统计',
        'child' => 
        array (
          'stat_general' => '概述及设置',
          'stat_industry' => '行业分析',
          'stat_member' => '会员统计',
          'stat_store' => '店铺统计',
          'stat_trade' => '销量分析',
          'stat_goods' => '商品分析',
          'stat_marketing' => '营销分析',
          'stat_aftersale' => '售后分析',
        ),
      ),
    ),
  ),
  'cms' => 
  array (
    'name' => '资讯',
    'child' => 
    array (
      0 => 
      array (
        'name' => '设置',
        'child' => 
        array (
          'cms_manage' => '资讯设置',
          'cms_index' => '首页管理',
          'cms_navigation' => '导航管理',
          'cms_tag' => '标签管理',
          'cms_comment' => '评论管理',
        ),
      ),
      1 => 
      array (
        'name' => '专题',
        'child' => 
        array (
          'cms_special' => '专题管理',
        ),
      ),
      2 => 
      array (
        'name' => '文章',
        'child' => 
        array (
          'cms_article_class' => '文章分类',
          'cms_article' => '文章管理',
        ),
      ),
      3 => 
      array (
        'name' => '画报',
        'child' => 
        array (
          'cms_picture_class' => '画报分类',
          'cms_picture' => '画报管理',
        ),
      ),
    ),
  ),
  'mobile' => 
  array (
    'name' => '手机端',
    'child' => 
    array (
      0 => 
      array (
        'name' => '设置',
        'child' => 
        array (
          'mb_logo' => '手机端Logo',
          'mb_special' => '模板设置',
          'mb_category' => '分类图片',
          'mb_feedback' => '意见反馈',
          'mb_payment' => '手机支付',
        ),
      ),
    ),
  ),
);