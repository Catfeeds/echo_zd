<?php
/**
 * 参数配置文件
 *
 * @author tivon
 * @date 2015-09-07
 */
return array(
    'cookieprefix' => 'house_',

    //css\js等静态资源路径
    'adminStaticPath' => '/static/admin/',
    'vipStaticPath' => '/static/vip/',
    'wapStaticPath' => '/static/wap/',
    'globalStaticPath' => '/static/global/',

    //总后台API
    'logApi' => 'api/log/',                     //日志api
    'bbsLoginApi' => 'api/bbs/login',           //前台登录
    'bbsGetuserinfoApi' => 'api/bbs/getuserinfo',           //前台登录
    'bbsLogoutApi' => 'api/bbs/logout',         //前台登录
    'smsApi' => 'api/mobile/sendMessage',       //短信发送接口
    'siteConfigApi' => array(                   //全站配置接口
        'list' => 'api/siteConfig/list/',
        'view' => 'api/siteConfig/view/',
        'create' => '',
        'update' => '',
        'delete' => '',
    ),
    'userApi' => array(                     //用户信息接口
        'list' => 'api/user/list/',
        'view' => 'api/user/view/',
        'create' => '',
        'update' => '',
        'delete' => '',
    ),
    //信息来源(包括resoldzf,resoldesf)
    'source' => array(
        '1' => '个人',
        '2' => '中介',
        '3' => '后台'
    ),
    //审核状态(二手房和租房共用)
    'checkStatus' => [
        '0' => '未审核',
        '1' => '正常',
        '2' => '审核中',
        '3' => '未通过'
    ],
    //销售状态(二手房和租房共用)
    'saleStatus' => [
        '1' => '上架',
        '2' => '下架',
        '3' => '回收'
    ],
    //电话确认
    'contacted' => [
        '0' => '否',
        '1' => '是'
    ],
    //房源分类
    'category' => [
        1 => '住房',
        2 => '商铺',
        3 => '写字楼'
    ],
    //房源分类
    'categoryPinyin' => [
        1 => 'zhuzhai',
        2 => 'shangpu',
        3 => 'xiezilou'
    ],
    //二手房求购状态
    'qgStatus' => [
        '0' => '未审核',
        '1' => '正常',
        '2' => '审核中',
        '3' => '未通过'
    ],
    //二手房求购状态
    'qzStatus' => [
        '0' => '未审核',
        '1' => '正常',
        '2' => '审核中',
        '3' => '未通过'
    ],
    //店铺状态
    'shopStatus' => [
        0 => '未审核',
        1 => '正常',
        2 => '禁用',
    ],
    //求租期望户型
    'qiuzufangtype' => [
        1 => '一居',
        2 => '二居',
        3 => '三居',
        4 => '四居',
        5 => '五居',
        6 => '五居以上'
    ],
    //房源举报处理状态
    'deal' => [
        0 => '未处理',
        1 => '已处理'
    ],
    //举报的房源类型
    'report_type' => [
        1 => '二手房',
        2 => '租房',
    ],
    //举报的链接
    'report_url' => [
        1 => '/resoldhome/esf/info',
        2 => '/resoldhome/zf/info',
    ],
    //判断二手房租房
    'esf_or_zf' => [
        1=> '二手房',
        2=>'租房'
    ],
    // 新房知识库标签分类
    'baikeTagCate'=> [
        0 => '新房',
        1 => '二手房',
        2 => '租房'
    ],
    // 二手房导航配置
    'resoldNav'=> [
        '首页',
        '二手房'=>[
            '在售房源',
            '个人房源',
            '邻校房',
            '找小区',
            '找经纪人',
            '求购',
            '我要卖房',
        ],
        '租房'=>[
            '个人租房',
            '整租房源',
            '合租房源',
            '求租',
            '我要出租',
        ],
        '写字楼'=>[
            '写字楼出售',
            '写字楼出租',
            '写字楼求购',
            '写字楼求租',
        ],
        '商铺'=>[
            '商铺出售',
            '商铺出租',
            '商铺求购',
            '商铺求租',
        ]
    ],
    'orderArr'=>[
    0=>'未审核',
    1=>'审核中',
    2=>'调度中',
    3=>'已完成',
    ],
    'recomCate'=>[
    1=>'首页推荐房源长图',
    2=>'首页推荐房源短图',
    3=>'首页优选房源',

    // 2=>'首页热门推荐',
    // 3=>'首页banner'
    ],
    'recomType'=>[
    1=>'资讯',
    2=>'评论',
    ],
    'imageTag'=>[
    '效果图',
    '样板间',
    '实景图',
    '沙盘图',
    '工程进度',
    '活动图',
    '交通图',
    '配套图',
    '项目现场',
    ],
       
    'dllx'=>[1=>'全案总代',2=>'联合代理',3=>'渠道代理',4=>'开发商渠道'],
    'msgArr'=>['用户注册驳回'=>'SMS_143700675','申请门店码'=>'SMS_143705685',
'下发门店码'=>'SMS_143705701',
'绑定门店码成功通知店长'=>'SMS_143700711',
'绑定门店码成功通知用户'=>'SMS_143705720',
'独立经纪人注册'=>'SMS_143700727',
'独立经纪人通过'=>'SMS_143713655',
'拨打电话通知'=>'SMS_143705818',
'在线签约通知'=>'SMS_143705823',
'添加报备通知'=>'SMS_143705832',
'投诉举报'=>'SMS_143700824',
'客户状态变更通知'=>'SMS_143700826',
'跟进通知用户'=>'SMS_143705840',
'客户成交认筹通知市场'=>'SMS_143705846',
'公司资料修改通知'=>'SMS_143700839',
'客户分配案场销售'=>'SMS_143700841',
'后台新增员工'=>'SMS_143867124',
'渠道绑定公司项目成功'=>'SMS_143700833','验证码'=>'SMS_94305028','昨日统计'=>'SMS_143862149','门店码通知后台'=>'SMS_146809409'],
    'wxArr'=>[
/**
{{first.DATA}}
消费者：{{keyword1.DATA}}
消费类型：{{keyword2.DATA}}
已支付金额：{{keyword3.DATA}}
支付时间：{{keyword4.DATA}}
{{remark.DATA}}*/
    '会员付款成功提醒'=> 'elw3lLf05KcyS1AmxFx3y7E3YfXRcc6tK6okKslUV3w',
/**{{first.DATA}}

您的{{name.DATA}}有效期至{{expDate.DATA}}。
{{remark.DATA}}*/
'到期提醒'=> 'HMH_Dtvky1JCG7-ry4WVDGoO2DaDYoih2tDClOgdrHQ',
/**
{{first.DATA}}
审核状态：{{keyword1.DATA}}
审核时间：{{keyword2.DATA}}
{{remark.DATA}}
*/
'审核提醒'=> 'TL9-g39GbuFEsbP8Qz--morFJpDIpfoaRO6j6nHqsWU',]
);
