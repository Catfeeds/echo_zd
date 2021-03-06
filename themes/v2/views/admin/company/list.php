<?php
$this->pageTitle = $this->controllerName.'列表';
$this->breadcrumbs = array($this->pageTitle);
$parentArea = AreaExt::model()->parent()->normal()->findAll();
$paarr = [0=>'不限'];
foreach ($parentArea as $pa) {
    $paarr[$pa->id] = $pa->name;
}
// var_dump($paarr);exit;
$parent = isset($city)&&$city?$city:(isset($parentArea[0])?$parentArea[0]->id:0);
$ppaarr = [0=>'不限'];
if($parent) {
    $paraa = AreaExt::model()->getByParent($parent)->normal()->findAll();
    $ppaarr = [0=>'不限'];
    foreach ($paraa as $pa) {
        $ppaarr[$pa->id] = $pa->name;
    }
}
?>
<?php $weekbg = TimeTools::getWeekBeginTime();$weeked =  TimeTools::getWeekEndTime(); ?>
<div class="alert alert-info">
    <center><strong><?=Yii::app()->user->username?></strong>您好！分销公司总数为：<strong><?=CompanyExt::model()->count()?></strong>，本周新增分销公司数为：<strong><?=CompanyExt::model()->count("created>=$weekbg and created<=$weeked")?></strong></center>
</div>
<div class="table-toolbar">
    <div class="btn-group pull-left">
        <form class="form-inline">
            <div class="form-group">
                <?php echo CHtml::dropDownList('type',$type,array('title'=>'标题','code'=>'门店码'),array('class'=>'form-control','encode'=>false)); ?>
            </div>
            <div class="form-group">
                <?php echo CHtml::textField('value',$value,array('class'=>'form-control chose_text')) ?>
            </div>
            <div class="form-group">
                <?php echo CHtml::dropDownList('time_type',$time_type,array('created'=>'添加时间','updated'=>'修改时间'),array('class'=>'form-control','encode'=>false)); ?>
            </div>
            <?php Yii::app()->controller->widget("DaterangepickerWidget",['time'=>$time,'params'=>['class'=>'form-control chose_text']]);?>
           <?php if(!$u): ?>
            <div class="form-group">
                <?php echo CHtml::dropDownList('status',$status,['未通过','已通过'],array('class'=>'form-control chose_select','encode'=>false,'prompt'=>'--选择状态--')); ?>
            </div>
            <div class="form-group">
                <label class="col-md-2 control-label text-nowrap">区域</label>
                <div class="col-md-10">
                    <?php
                    echo CHtml::dropDownList('city' ,$city ,$paarr , array(
                            'class'=>'form-control input-inline',
                            'ajax' =>array(
                                'url' => Yii::app()->createUrl('admin/area/ajaxGetArea'),
                                'update' => '#area',
                                'data'=>array('area'=>'js:this.value'),
                            )
                        )
                    );
                    ?>
                    <?php
                    echo CHtml::dropDownList('area' ,$area ,$ppaarr ? $ppaarr:array(0=>'--无子分类--') , array(
                            'class'=>'form-control input-inline',
                            'ajax' =>array(
                                'url' => Yii::app()->createUrl('admin/area/ajaxGetArea'),
                                'update' => '#street',
                                'data'=>array('area'=>'js:this.value'),
                            )
                        ));
                    ?>
                </div>
            </div>
        <?php endif;?>
            <button type="submit" class="btn blue">搜索</button>
            <a class="btn yellow" onclick="removeOptions()"><i class="fa fa-trash"></i>&nbsp;清空</a>
        </form>
    </div>
    <div class="pull-right">
        <a href="<?php echo $this->createAbsoluteUrl('edit') ?>" class="btn blue">
            添加<?=$this->controllerName?> <i class="fa fa-plus"></i>
        </a>
    </div>
</div>
   <table class="table table-bordered table-striped table-condensed flip-content table-hover">
    <thead class="flip-content">
    <tr>
        <th class="text-center">排序</th>
        <th class="text-center">ID</th>
        <th class="text-center">公司名</th>
        <th class="text-center">父级公司</th>
        <th class="text-center">地址</th>
        <th class="text-center">公司联系</th>
        <th class="text-center">门店码</th>
        <th class="text-center">添加时间</th>
        <!-- <th class="text-center">修改时间</th> -->
        <th class="text-center">状态</th>
        <th class="text-center">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($infos as $k=>$v): ?>
        <tr>
            <td style="text-align:center;vertical-align: middle" class="warning sort_edit"
                data-id="<?php echo $v['id'] ?>"><?php echo $v['sort'] ?></td>
            <td style="text-align:center;vertical-align: middle"><?php echo $v->id; ?></td>
            <td class="text-center"><?=$v->name?></td>
            <td class="text-center"><?=$v->parentCompany?$v->parentCompany->name:''?></td>
            <td class="text-center"><?=$v->address?></td> 
            <td class="text-center"><?=$v->manager.'/'.$v->phone?></td> 
            <td class="text-center"><?=$v->code?></td> 
            <td class="text-center"><?=date('Y-m-d H:i:s',$v->created)?></td>
            <!-- <td class="text-center"><?=date('Y-m-d',$v->updated)?></td> -->
            <td class="text-center"><?php echo CHtml::ajaxLink(UserExt::$status[$v->status],$this->createUrl('changeStatus'), array('type'=>'get', 'data'=>array('id'=>$v->id,'class'=>get_class($v)),'success'=>'function(data){location.reload()}'), array('class'=>'btn btn-sm '.UserExt::$statusStyle[$v->status])); ?></td>

            <td style="text-align:center;vertical-align: middle">
                <a href="<?php echo $this->createUrl('loglist',array('id'=>$v->id)); ?>" class="btn default btn-xs default"> 公司跟进 </a>
                <?php echo CHtml::ajaxLink('生成门店码',$this->createUrl('setCode'), array('type'=>'get', 'data'=>array('id'=>$v->id),'success'=>'function(data){location.reload()}'), array('class'=>'btn btn-sm red'.UserExt::$statusStyle[$v->status])); ?>
                <a href="<?php echo $this->createUrl('edit',array('id'=>$v->id)); ?>" class="btn default btn-xs green"><i class="fa fa-edit"></i> 修改 </a>
                <?php if(Yii::app()->user->is_m): ?>
               <?php echo CHtml::htmlButton('删除', array('data-toggle'=>'confirmation', 'class'=>'btn btn-xs red', 'data-title'=>'确认删除？', 'data-btn-ok-label'=>'确认', 'data-btn-cancel-label'=>'取消', 'data-popout'=>true,'ajax'=>array('url'=>$this->createUrl('del'),'type'=>'get','success'=>'function(data){location.reload()}','data'=>array('id'=>$v->id,'class'=>get_class($v)))));?>
           <?php endif;?>

            </td>
            
        </tr>
    <?php endforeach;?>
    </tbody>
</table>
<?php $this->widget('VipLinkPager', array('pages'=>$pager)); ?>

<script>
<?php Tools::startJs(); ?>
    setInterval(function(){
        $('#AdminIframe').height($('#AdminIframe').contents().find('body').height());
        var $panel_title = $('#fade-title');
        $panel_title.html($('#AdminIframe').contents().find('title').html());
    },200);
    function do_admin(ts){
        $('#AdminIframe').attr('src',ts.data('url')).load(function(){
            self = this;
            //延时100毫秒设定高度
            $('#Admin').modal({ show: true, keyboard:false });
            $('#Admin .modal-dialog').css({width:'1000px'});
        });
    }
    function set_sort(_this, id, sort){
            $.getJSON('<?php echo $this->createUrl('/admin/league/setSort')?>',{id:id,sort:sort,class:'<?=isset($infos[0])?get_class($infos[0]):''?>'},function(dt){
                location.reload();
            });
        }
    function do_sort(ts){
        if(ts.which == 13){
            _this = $(ts.target);
            sort = _this.val();
            id = _this.parent().data('id');
            set_sort(_this, id, sort);
        }
    }

    $(document).on('click',function(e){
          var target = $(e.target);
          if(!target.hasClass('sort_edit')){
             $('.sort_edit').trigger($.Event( 'keypress', 13 ));
          }
    });
    $('.sort_edit').click(function(){
        if($(this).find('input').length <1){
            $(this).html('<input type=\"text\" value=\"' + $(this).html() + '\" class=\"form-control input-sm sort_edit\" onkeypress=\"return do_sort(event)\" onblur=\"set_sort($(this),$(this).parent().data(\'id\'),$(this).val())\">');
            $(this).find('input').select();
        }
    });
    var getChecked  = function(){
        var ids = "";
        $(".checkboxes").each(function(){
            if($(this).parents('span').hasClass("checked")){
                if(ids == ''){
                    ids = $(this).val();
                } else {
                    ids = ids + ',' + $(this).val();
                }
            }
        });
        return ids;
    }

    $(".group-checkable").click(function () {
        var set = $(this).attr("data-set");
        $(set).each(function () {
            $(this).attr("checked", !$(this).attr("checked"));
        });
        $.uniform.update(set);
    });
    //清空选项
    function removeOptions()
    {
        // alert($('.chose_select').val());
        $('.chose_text').val('');
        $('.chose_select').val('');
    }

    $("#hname").on("dblclick",function(){
        var hnames = $(".hname");
        console.log(hnames);
        hnames.each(function(){
            var _this = $(this);
            $.getJSON("<?php echo $this->createUrl('/api/houses/getsearch') ?>",{key:_this.html()},function(dt){
                _this.append(" (" + dt.msg[1].length + ")");
            });
        });
    });
<?php Tools::endJs('js') ?>
</script>
