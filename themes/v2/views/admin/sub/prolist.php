<?php
$this->pageTitle = '报备流水列表';
$this->breadcrumbs = array($this->pageTitle);
// $statusArr = SubExt::$status;
?>
<div class="table-toolbar">

    <div class="pull-right">
        <a href="<?php echo $this->createAbsoluteUrl('proedit',['sid'=>$sid]) ?>" class="btn blue">
            添加报备流水 <i class="fa fa-plus"></i>
        </a>
    </div>
</div>
   <table class="table table-bordered table-striped table-condensed flip-content table-hover">
    <thead class="flip-content">
    <tr>
        <th class="text-center">ID</th>
        <th class="text-center">操作者</th>
        <th class="text-center">状态</th>
        <th class="text-center">备注</th>
        <th class="text-center">添加时间</th>
        <th class="text-center">修改时间</th>
        <th class="text-center">操作</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($infos as $k=>$v): ?>
        <tr>
            <td style="text-align:center;vertical-align: middle"><?php echo $v->id; ?></td>
            <td class="text-center"><?=$v->uid&&$v->user?$v->user->name:($v->staff==1?'管理员':($v->staffObj?$v->staffObj->name:''))?></td>
            <td class="text-center"><?=SubProExt::$status[$v->status]?></td>
            <td class="text-center"><?=$v->note?></td>
            <td class="text-center"><?=date('Y-m-d H:i:s',$v->created)?></td>
            <td class="text-center"><?=date('Y-m-d',$v->updated)?></td>
            
            <td style="text-align:center;vertical-align: middle">
                <a href="<?php echo $this->createUrl('proedit',array('id'=>$v->id,'sid'=>$sid)); ?>" class="btn default btn-xs green"><i class="fa fa-edit"></i> 修改 </a>


            </td>
        </tr>
    <?php endforeach;?>
    </tbody>
</table>

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
