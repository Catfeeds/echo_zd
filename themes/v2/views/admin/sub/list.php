<?php
$this->pageTitle = $this->controllerName.'列表';
$this->breadcrumbs = array($this->pageTitle);
$statusArr = SubExt::$status;
?>
<div class="table-toolbar">
    <div class="btn-group pull-left">
        <form class="form-inline">
            <div class="form-group">
                <?php echo CHtml::dropDownList('type',$type,array('title'=>'标题'),array('class'=>'form-control','encode'=>false)); ?>
            </div>
            <div class="form-group">
                <?php echo CHtml::textField('value',$value,array('class'=>'form-control chose_text')) ?>
            </div>
            <div class="form-group">
                <?php echo CHtml::dropDownList('time_type',$time_type,array('created'=>'添加时间','updated'=>'修改时间'),array('class'=>'form-control','encode'=>false)); ?>
            </div>
            <?php Yii::app()->controller->widget("DaterangepickerWidget",['time'=>$time,'params'=>['class'=>'form-control chose_text']]);?>
            
            <div class="form-group">
                <?php echo CHtml::dropDownList('cate',$cate,$statusArr,array('class'=>'form-control chose_select','encode'=>false,'prompt'=>'--选择状态--')); ?>
            </div>
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
        <th class="text-center">ID</th>
        <th class="text-center">项目信息</th>
        <th class="text-center">分销信息</th>
        <th class="text-center">客户信息</th>
        <th class="text-center">市场人员</th>
        <th class="text-center">案场助理</th>
        <th class="text-center">案场销售</th>
        <th class="text-center">销售额</th>
        <th class="text-center">添加时间</th>
        <th class="text-center">修改时间</th>
        <th class="text-center">状态</th>
        <th class="text-center">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($infos as $k=>$v):
        $user = $v->user;
        $market_user = $v->market_user;
        $an_user = $v->an_user;
        $sale_user = $v->sale_user;
        $company = $v->company;
     ?>
        <tr>
            <td style="text-align:center;vertical-align: middle"><?php echo $v->id; ?></td>
            <td class="text-center"><a target="_blank" href="<?=$this->createUrl('/api/index/detail',['id'=>$v->hid])?>"><?=$v->plot->title?></a></td>
            <td class="text-center"><?=$user?($user->name.'/'.$user->phone.($company?('<br>'.$company->name):'')):''?></td> 
            <td class="text-center"><?=$v->name.'/'.$v->phone?></td> 
            <td class="text-center"><?=$market_user?$market_user->name:''?></td> 
            <td class="text-center"><?=$an_user?$an_user->name:''?></td> 
            <td class="text-center"><?=$sale_user?$sale_user->name:''?></td>
            <td class="text-center"><?=$v->sale_price?$v->sale_price:'-'?></td> 
            <td class="text-center"><?=date('Y-m-d H:i:s',$v->created)?></td>
            <td class="text-center"><?=date('Y-m-d',$v->updated)?></td>
            <td class="text-center" style="text-align:center;vertical-align: middle">
                <div class="btn-group">
                    <button id="btnGroupVerticalDrop1" type="button" class="btn btn-xs btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                    <?=$statusArr[$v->status]?> <i class="fa fa-angle-down"></i>
                    </button>
                    <ul class="dropdown-menu" role="menu">
                    <?php foreach($statusArr as $key=>$v1){?>
                        <li>
                            <?=CHtml::ajaxLink($v1,$this->createUrl('ajaxStatus',['kw'=>$key,'ids'=>$v->id]),['success'=>'function(){location.reload();}'])?>
                        </li>
                      <?php  }?>
                    </ul>
                </div>
            </td>
            <td style="text-align:center;vertical-align: middle">
                <a href="<?php echo $this->createUrl('prolist',array('sid'=>$v->id)); ?>" class="btn default btn-xs default"> 跟进列表 </a>
                <a href="<?php echo $this->createUrl('imagelist',array('sid'=>$v->id)); ?>" class="btn default btn-xs blue"> 材料列表 </a>
                <a href="<?php echo $this->createUrl('edit',array('id'=>$v->id)); ?>" class="btn default btn-xs green"><i class="fa fa-edit"></i> 修改 </a>
                <?php echo CHtml::htmlButton('删除', array('data-toggle'=>'confirmation', 'class'=>'btn btn-xs red', 'data-title'=>'确认删除？', 'data-btn-ok-label'=>'确认', 'data-btn-cancel-label'=>'取消', 'data-popout'=>true,'ajax'=>array('url'=>$this->createUrl('del'),'type'=>'get','success'=>'function(data){location.reload()}','data'=>array('id'=>$v->id,'class'=>get_class($v)))));?>


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
