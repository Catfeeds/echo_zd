<?php
$cname = Yii::app()->file->sitename;
$this->pageTitle = $cname.'后台欢迎您';
?>
<?php 
	$thishits = $allhits = $thissubs = $allsubs = $thism = $allm = $thiscomes = $allcomes = 0;
	$hids = [];
	$hidsa = Yii::app()->db->createCommand("select id from plot where deleted=0")->queryAll();
	if($hidsa) {
		foreach ($hidsa as $key => $value) {
			$thishits += Yii::app()->redis->getClient()->hGet('plot_views',$value['id']);
			$hids[] = $value['id'];
		}
	}
	$allhits = Yii::app()->db->createCommand("select sum(views) from plot where deleted=0")->queryScalar();

	$criteria = new CDbCriteria;
	$criteria->addInCondition('hid',$hids);
	$allsubs = SubExt::model()->undeleted()->count($criteria);
	$criteria->addCondition('created>=:begin and created<=:end');
	$criteria->params[':begin'] = TimeTools::getDayBeginTime();
	$criteria->params[':end'] = TimeTools::getDayEndTime();
	$thissubs = SubExt::model()->undeleted()->count($criteria);

	$criteria = new CDbCriteria;
	$criteria->addInCondition('hid',$hids);
	$criteria->addCondition('status>=3 and status<6');
	$allm = SubExt::model()->undeleted()->count($criteria);
	$criteria->addCondition('updated>=:begin and updated<=:end');
	$criteria->params[':begin'] = TimeTools::getDayBeginTime();
	$criteria->params[':end'] = TimeTools::getDayEndTime();
	$thism = SubExt::model()->undeleted()->count($criteria);

    $criteria = new CDbCriteria;
    $criteria->addInCondition('hid',$hids);
    $criteria->addCondition('status>=1');
    $allcomes = SubExt::model()->undeleted()->count($criteria);
    $criteria->addCondition('updated>=:begin and updated<=:end');
    $criteria->params[':begin'] = TimeTools::getDayBeginTime();
    $criteria->params[':end'] = TimeTools::getDayEndTime();
    $thiscomes = SubExt::model()->undeleted()->count($criteria);

?>
<div class="alert alert-info">
    <center><strong><?=Yii::app()->user->username?></strong>您好！欢迎登录<?=$cname?>系统后台。</center>
</div>
<div class="row">
    <div class="col-lg-3 col-md-3">
        <div class="dashboard-stat blue-madison">
            <div class="visual">
                <i class="fa fa-comments"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $thishits.'/'.($allhits+$thishits) ?>
                </div>
                <div class="desc">
                    今日楼盘点击数/总数
                </div>
            </div>
            <a class="more" href="<?php echo $this->createUrl('plot/list')?>">
                查看更多 <i class="m-icon-swapright m-icon-white"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-3">
        <div class="dashboard-stat red-intense">
            <div class="visual">
                <i class="fa fa-bar-chart-o"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $thissubs.'/'.$allsubs ?>
                </div>
                <div class="desc">
                    今日新增报备数量/总数
                </div>
            </div>
            <a class="more" href="<?php echo $this->createUrl('sub/list')?>">
                查看更多 <i class="m-icon-swapright m-icon-white"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-3">
        <div class="dashboard-stat green-haze">
            <div class="visual">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $thiscomes.'/'.$allcomes ?>
                </div>
                <div class="desc">
                    今日新增来访数/总数
                </div>
            </div>
            <a class="more" href="<?php echo $this->createUrl('sub/list',['cate'=>1])?>">
                查看更多 <i class="m-icon-swapright m-icon-white"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-3 col-md-3">
        <div class="dashboard-stat purple-plum">
            <div class="visual">
                <i class="fa fa-globe"></i>
            </div>
            <div class="details">
                <div class="number">
                    <?php echo $thism.'/'.$allm?>
                </div>
                <div class="desc">
                    今天成交数量/总数
                </div>
            </div>
            <a class="more" href="<?php echo $this->createUrl('sub/list',['cj'=>1])?>">
                查看更多 <i class="m-icon-swapright m-icon-white"></i>
            </a>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-8">
        <!-- Begin: life time stats -->
        <div class="portlet box red-sunglo">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-bar-chart-o"></i> 业绩总览
                </div>
                <div class="tools">
                    <a href="#portlet-config" data-toggle="modal" class="config" data-original-title="" title="">
                                    </a>
                    <a href="javascript:;" class="reload" data-original-title="" title="">
                                    </a>
                </div>
                <ul class="nav nav-tabs" style="margin-right: 10px">
                   <!--  <li class="">
                        <a href="#portlet_tab2" data-toggle="tab" id="statistics_amounts_tab" aria-expanded="false">
                                        市场部 </a>
                    </li> -->
                    <li class="active">
                        <a href="#portlet_tab1" data-toggle="tab" aria-expanded="true">
                                        销售业绩 </a>
                    </li>
                </ul>
            </div>
            <div class="portlet-body">
                <div class="tab-content">
                    <div class="tab-pane active" id="portlet_tab1" style="display: flex;">
                        <div id="statistics_1" class="chart" style="padding: 0px; position: relative;width: 30%">
    
                        </div>
                        <div id="statistics_12" class="chart" style="padding: 0px; position: relative;width: 70%">
    
                        </div>
                    </div>
                   <!--  <div class="tab-pane" id="portlet_tab2">
                        <div id="statistics_2" class="chart" style="padding: 0px; position: relative;">

                        </div>
                        <div id="statistics_22" class="chart" style="padding: 0px; position: relative;">

                        </div>
                    </div> -->
                </div>
                <div class="well no-margin no-border">
                    <div class="row">
                        <div class="col-md-3 col-sm-3 col-xs-6 text-stat">
                            <span class="label label-success">
                                            销售额: </span>
                            <h3>￥<?=$saleNum?></h3>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-6 text-stat">
                            <span class="label label-info">
                                            销售面积: </span>
                            <h3><?=$sizeNum?> ㎡</h3>
                        </div>
                        <div class="col-md-3 col-sm-3 col-xs-6 text-stat">
                            <span class="label label-danger">
                                            已销楼盘数: </span>
                            <h3><?=$qyNum?></h3>
                        </div>
                        <!-- <div class="col-md-3 col-sm-3 col-xs-6 text-stat">
                            <span class="label label-warning">
                                            : </span>
                            <h3>235090</h3>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End: life time stats -->
    </div>
    <div class="col-md-4">
        <!-- Begin: life time stats -->
        <div class="portlet box blue-steel">
            <div class="portlet-title">
                <div class="caption">
                    <i class="fa fa-thumb-tack"></i>Overview
                </div>
                <div class="tools">
                    <a href="javascript:;" class="collapse" data-original-title="" title="">
                                    </a>
                    <a href="#portlet-config" data-toggle="modal" class="config" data-original-title="" title="">
                                    </a>
                    <a href="javascript:;" class="reload" data-original-title="" title="">
                                    </a>
                    <a href="javascript:;" class="remove" data-original-title="" title="">
                                    </a>
                </div>
            </div>
            <div class="portlet-body">
                <div class="tabbable-line">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#overview_1" data-toggle="tab" aria-expanded="true">
                                            Top Selling </a>
                        </li>
                        <li class="">
                            <a href="#overview_2" data-toggle="tab" aria-expanded="false">
                                            Most Viewed </a>
                        </li>
                        <li class="">
                            <a href="#overview_3" data-toggle="tab" aria-expanded="false">
                                            New Customers </a>
                        </li>
                        <li class="dropdown">
                            <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                                            Orders <i class="fa fa-angle-down"></i>
                                            </a>
                            <ul class="dropdown-menu" role="menu">
                                <li>
                                    <a href="#overview_4" tabindex="-1" data-toggle="tab">
                                                    Latest 10 Orders </a>
                                </li>
                                <li>
                                    <a href="#overview_4" tabindex="-1" data-toggle="tab">
                                                    Pending Orders </a>
                                </li>
                                <li>
                                    <a href="#overview_4" tabindex="-1" data-toggle="tab">
                                                    Completed Orders </a>
                                </li>
                                <li>
                                    <a href="#overview_4" tabindex="-1" data-toggle="tab">
                                                    Rejected Orders </a>
                                </li>
                            </ul>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="overview_1">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                Product Name
                                            </th>
                                            <th>
                                                Price
                                            </th>
                                            <th>
                                                Sold
                                            </th>
                                            <th>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Apple iPhone 4s - 16GB - Black </a>
                                            </td>
                                            <td>
                                                $625.50
                                            </td>
                                            <td>
                                                809
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Samsung Galaxy S III SGH-I747 - 16GB </a>
                                            </td>
                                            <td>
                                                $915.50
                                            </td>
                                            <td>
                                                6709
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Motorola Droid 4 XT894 - 16GB - Black </a>
                                            </td>
                                            <td>
                                                $878.50
                                            </td>
                                            <td>
                                                784
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Regatta Luca 3 in 1 Jacket </a>
                                            </td>
                                            <td>
                                                $25.50
                                            </td>
                                            <td>
                                                1245
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Samsung Galaxy Note 3 </a>
                                            </td>
                                            <td>
                                                $925.50
                                            </td>
                                            <td>
                                                21245
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Inoval Digital Pen </a>
                                            </td>
                                            <td>
                                                $125.50
                                            </td>
                                            <td>
                                                1245
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Metronic - Responsive Admin + Frontend Theme </a>
                                            </td>
                                            <td>
                                                $20.00
                                            </td>
                                            <td>
                                                11190
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="overview_2">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                Product Name
                                            </th>
                                            <th>
                                                Price
                                            </th>
                                            <th>
                                                Views
                                            </th>
                                            <th>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Metronic - Responsive Admin + Frontend Theme </a>
                                            </td>
                                            <td>
                                                $20.00
                                            </td>
                                            <td>
                                                11190
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Regatta Luca 3 in 1 Jacket </a>
                                            </td>
                                            <td>
                                                $25.50
                                            </td>
                                            <td>
                                                1245
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Apple iPhone 4s - 16GB - Black </a>
                                            </td>
                                            <td>
                                                $625.50
                                            </td>
                                            <td>
                                                809
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Samsung Galaxy S III SGH-I747 - 16GB </a>
                                            </td>
                                            <td>
                                                $915.50
                                            </td>
                                            <td>
                                                6709
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Motorola Droid 4 XT894 - 16GB - Black </a>
                                            </td>
                                            <td>
                                                $878.50
                                            </td>
                                            <td>
                                                784
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Samsung Galaxy Note 3 </a>
                                            </td>
                                            <td>
                                                $925.50
                                            </td>
                                            <td>
                                                21245
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Inoval Digital Pen </a>
                                            </td>
                                            <td>
                                                $125.50
                                            </td>
                                            <td>
                                                1245
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="overview_3">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                Customer Name
                                            </th>
                                            <th>
                                                Total Orders
                                            </th>
                                            <th>
                                                Total Amount
                                            </th>
                                            <th>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        David Wilson </a>
                                            </td>
                                            <td>
                                                3
                                            </td>
                                            <td>
                                                $625.50
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Amanda Nilson </a>
                                            </td>
                                            <td>
                                                4
                                            </td>
                                            <td>
                                                $12625.50
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Jhon Doe </a>
                                            </td>
                                            <td>
                                                2
                                            </td>
                                            <td>
                                                $125.00
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Bill Chang </a>
                                            </td>
                                            <td>
                                                45
                                            </td>
                                            <td>
                                                $12,125.70
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Paul Strong </a>
                                            </td>
                                            <td>
                                                1
                                            </td>
                                            <td>
                                                $890.85
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Jane Hilson </a>
                                            </td>
                                            <td>
                                                5
                                            </td>
                                            <td>
                                                $239.85
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Patrick Walker </a>
                                            </td>
                                            <td>
                                                2
                                            </td>
                                            <td>
                                                $1239.85
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="tab-pane" id="overview_4">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover table-bordered">
                                    <thead>
                                        <tr>
                                            <th>
                                                Customer Name
                                            </th>
                                            <th>
                                                Date
                                            </th>
                                            <th>
                                                Amount
                                            </th>
                                            <th>
                                                Status
                                            </th>
                                            <th>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        David Wilson </a>
                                            </td>
                                            <td>
                                                3 Jan, 2013
                                            </td>
                                            <td>
                                                $625.50
                                            </td>
                                            <td>
                                                <span class="label label-sm label-warning">
                                                        Pending </span>
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Amanda Nilson </a>
                                            </td>
                                            <td>
                                                13 Feb, 2013
                                            </td>
                                            <td>
                                                $12625.50
                                            </td>
                                            <td>
                                                <span class="label label-sm label-warning">
                                                        Pending </span>
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Jhon Doe </a>
                                            </td>
                                            <td>
                                                20 Mar, 2013
                                            </td>
                                            <td>
                                                $125.00
                                            </td>
                                            <td>
                                                <span class="label label-sm label-success">
                                                        Success </span>
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Bill Chang </a>
                                            </td>
                                            <td>
                                                29 May, 2013
                                            </td>
                                            <td>
                                                $12,125.70
                                            </td>
                                            <td>
                                                <span class="label label-sm label-info">
                                                        In Process </span>
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Paul Strong </a>
                                            </td>
                                            <td>
                                                1 Jun, 2013
                                            </td>
                                            <td>
                                                $890.85
                                            </td>
                                            <td>
                                                <span class="label label-sm label-success">
                                                        Success </span>
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Jane Hilson </a>
                                            </td>
                                            <td>
                                                5 Aug, 2013
                                            </td>
                                            <td>
                                                $239.85
                                            </td>
                                            <td>
                                                <span class="label label-sm label-danger">
                                                        Canceled </span>
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <a href="javascript:;">
                                                        Patrick Walker </a>
                                            </td>
                                            <td>
                                                6 Aug, 2013
                                            </td>
                                            <td>
                                                $1239.85
                                            </td>
                                            <td>
                                                <span class="label label-sm label-success">
                                                        Success </span>
                                            </td>
                                            <td>
                                                <a href="javascript:;" class="btn default btn-xs green-stripe">
                                                        View </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- End: life time stats -->
    </div>
</div>
<script>
<?php Tools::startJs(); ?>
var myChart = echarts.init(document.getElementById('statistics_1'));
option = {
    tooltip: {
        trigger: 'item',
        formatter: "{a} <br/>{b}: {c} ({d}%)"
    },
    legend: {
        // orient: 'vertical',
        // x: 'left',
        data:['未售','大定','签约']
    },
    series: [
        {
            name:'业绩总览',
            type:'pie',
            radius: ['50%', '70%'],
            avoidLabelOverlap: false,
            // label: {
            //     normal: {
            //         show: true
            //     },
            //     position: 'inside'
            // },
            label: {
                normal: {
                    show: true,
                    position: 'outside'
                },
                emphasis: {
                    show: true,
                    textStyle: {
                        fontSize: '30',
                        fontWeight: 'bold'
                    }
                }
            },
            labelLine: {
                normal: {
                    show: true
                }
            },
            data:[
                {value:<?=$notSaleNum?>, name:'未售'},
                {value:<?=$ddNum?>, name:'大定'},
                {value:<?=$qyNum?>, name:'签约'},
            ]
        }
    ]
};
myChart.setOption(option);
var myChart1 = echarts.init(document.getElementById('statistics_12'));
option = {
    tooltip : {
        trigger: 'axis',
        axisPointer : {            // 坐标轴指示器，坐标轴触发有效
            type : 'shadow'        // 默认为直线，可选为：'line' | 'shadow'
        }
    },
    legend: {
        data:['未售','大定','签约']
    },
    grid: {
        left: '3%',
        right: '4%',
        bottom: '3%',
        containLabel: true
    },
    xAxis : [
        {
            type : 'category',
            data : <?=json_encode($plt)?>
        }
    ],
    yAxis : [
        {
            type : 'value'
        }
    ],
    series : [
        {
            name:'未售',
            barWidth: 80,
            type:'bar',
            stack: 'a',
            label: {
                normal: {
                    show: true
                },
                position: 'inside'
            },
            data:<?=json_encode($wsarr)?>
        },
        {
            name:'大定',
            barWidth: 80,
            type:'bar',
            stack: 'a',
            label: {
                normal: {
                    show: true
                },
                position: 'inside'
            },
            data:<?=json_encode($ddarr)?>
        },
        {
            name:'签约',
            barWidth: 80,
            type:'bar',
            stack: 'a',
            label: {
                normal: {
                    show: true
                },
                position: 'inside'
            },
            data:<?=json_encode($qyarr)?>
        },
        // {
        //     name:'视频广告',
        //     type:'bar',
        //     data:[150, 232, 201, 154, 190, 330, 410]
        // },
        // {
        //     name:'搜索引擎',
        //     type:'bar',
        //     data:[862, 1018, 964, 1026, 1679, 1600, 1570],
        //     markLine : {
        //         lineStyle: {
        //             normal: {
        //                 type: 'dashed'
        //             }
        //         },
        //         data : [
        //             [{type : 'min'}, {type : 'max'}]
        //         ]
        //     }
        // },
        // {
        //     name:'百度',
        //     type:'bar',
        //     barWidth : 5,
        //     stack: '搜索引擎',
        //     data:[620, 732, 701, 734, 1090, 1130, 1120]
        // },
        // {
        //     name:'谷歌',
        //     type:'bar',
        //     stack: '搜索引擎',
        //     data:[120, 132, 101, 134, 290, 230, 220]
        // },
        // {
        //     name:'必应',
        //     type:'bar',
        //     stack: '搜索引擎',
        //     data:[60, 72, 71, 74, 190, 130, 110]
        // },
        // {
        //     name:'其他',
        //     type:'bar',
        //     stack: '搜索引擎',
        //     data:[62, 82, 91, 84, 109, 110, 120]
        // }
    ]
};
myChart1.setOption(option);
<?php Yii::app()->clientScript->registerScriptFile("/static/global/scripts/echarts/echarts.js",CClientScript::POS_END); ?>
<?php Tools::endJs('js') ?>
</script>