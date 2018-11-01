<?php
$this->pageTitle = $pt;
$this->breadcrumbs = array($this->pageTitle);
$statusArr = SubExt::$status;
?>
<div class="table-toolbar">
    <div class="btn-group pull-left">
        <form class="form-inline">
            <div class="form-group">
                <?php echo CHtml::dropDownList('time_type',$time_type,array('created'=>'添加时间','updated'=>'修改时间'),array('class'=>'form-control','encode'=>false)); ?>
            </div>
            <?php Yii::app()->controller->widget("DaterangepickerWidget",['time'=>$time,'params'=>['class'=>'form-control chose_text']]);?>
            
            <div class="form-group">
                <?php echo CHtml::dropDownList('hid',$hid,CHtml::listData(PlotExt::model()->findAll(),'id','title'),array('class'=>'form-control chose_select select2','encode'=>false,'prompt'=>'--请选择楼盘--','style'=>'min-width:400px')); ?>
            </div>
            <div class="form-group">
                <?php echo CHtml::dropDownList('aid',$aid,CHtml::listData(Tools::menuMake(DepartmentExt::model()->findAll(),-1,'id'),'id','name'),array('class'=>'form-control chose_select select2','encode'=>false,'prompt'=>'--请选择部门--','style'=>'min-width:400px')); ?>
            </div>
            <div class="form-group">
                <?php echo CHtml::dropDownList('is_all',$is_all,['当前部门数据','所有子部门数据'],array('class'=>'form-control chose_select','encode'=>false,'prompt'=>'--请选择部门数据类型--')); ?>
            </div>
            <button type="submit" class="btn blue">搜索</button>
            <a class="btn yellow" onclick="removeOptions()"><i class="fa fa-trash"></i>&nbsp;清空</a>
        </form>
    </div>
</div>
<div class="row">
    <div class="portlet box blue">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-cogs"></i>数据总览
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
        <div class="table-scrollable">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            报备总数
                        </th>
                            <th>
                            到访数
                        </th>
                        <th>
                            大定数
                        </th>
                        <th>
                            签约数
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <?=$allws+$alldd+$allqy+$alldf?>
                        </td>
                            <td>
                            <?=$alldf?>
                        </td>
                        <td>
                            <?=$alldd?>
                        </td>
                        <td>
                            <?=$allqy?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
</div>
<div class="row">
    <div class="portlet box green">
    <div class="portlet-title">
        <div class="caption">
            <i class="fa fa-cogs"></i>项目数据
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
        <div class="table-scrollable">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>
                            项目名
                        </th>
                        <th>
                            报备总数
                        </th>
                            <th>
                            到访数
                        </th>
                        <th>
                            大定数
                        </th>
                        <th>
                            签约数
                        </th>
                        <th>
                            自访数
                        </th>
                        <th>
                            渠道客
                        </th>
                        <th>
                            自访比
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php if($plotarr) foreach ($plotarr as $k=> $v) { !isset($v['ws']) && $v['ws'] = 0;!isset($v['zf']) && $v['zf'] = 0;!isset($v['tf']) && $v['tf'] = 0;!isset($v['df']) && $v['df'] = 0;!isset($v['dd']) && $v['dd'] = 0;!isset($v['qy']) && $v['qy'] = 0; ?>
                    <tr>
                        <td>
                            <?=$k?>
                        </td>
                        <td>
                            <?=$v['ws']+$v['df']+$v['dd']+$v['qy']?>
                        </td>
                         <td>
                            <?=$v['df']?>
                        </td>
                        <td>
                            <?=$v['dd']?>
                        </td>
                        <td>
                            <?=$v['qy']?>
                        </td>
                        <td>
                            <?=$v['zf']?>
                        </td>
                        <td>
                            <?=$v['tf']?>
                        </td>
                        <td>
                            <?=($v['tf']+$v['tf'])?(round($v['zf']/($v['tf']+$v['tf']),2)):'-'?>
                        </td>
                    </tr>
                <?php } ?>
                    
                </tbody>
            </table>
        </div>
    </div>
    
</div>
</div>
<script>
<?php Tools::startJs(); 
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/select2/select2.min.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile('/static/global/plugins/select2/select2.css');
Yii::app()->clientScript->registerCssFile('/static/admin/pages/css/select2_custom.css');

$js = "
            $(function(){
               $('.select2').select2({
                  placeholder: '请选择',
                  allowClear: true
               });

                 $('.form_datetime').datetimepicker({
                     autoclose: true,
                     isRTL: Metronic.isRTL(),
                     format: 'yyyy-mm-dd',
                     minView: 'month',
                     language: 'zh-CN',
                     pickerPosition: (Metronic.isRTL() ? 'bottom-right' : 'bottom-left'),
                 });

            });


            ";

Yii::app()->clientScript->registerScript('add',$js,CClientScript::POS_END);
?>
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
