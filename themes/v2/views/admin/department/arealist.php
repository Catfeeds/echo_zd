<?php
$this->pageTitle = '部门列表';
$this->breadcrumbs = array('部门管理' => '/admin/area/arealist', $this->pageTitle);
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/jquery-nestable/jquery.nestable.js', CClientScript::POS_END);
Yii::app()->clientScript->registerCssFile("/static/global/plugins/jquery-nestable/jquery.nestable.css");
?>
<div class="tip-container">
</div>
<div class="portlet-body flip-scroll">
    <div style="padding-bottom:20px;float:right">

        <a href="<?php echo $this->createUrl('areaedit') ?>" class="btn blue">添加部门&nbsp;&nbsp;<i class="fa fa-plus"></i></a>
        <?php echo CHtml::button('保存修改', array('id' => 'do_sort', 'class' => 'btn green')) ?>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="portlet-body lightblue">
                <?php echo $tree ? $tree : ''; ?>
                <?php
                $js = "
                    $(function(){
                        $('#treeDepartmentExt').nestable({maxDepth:2});
                        $('#do_sort').on('click',function(ev){
                            dt = JSON.stringify($('#treeDepartmentExt').nestable('serialize'));
                            $.post('{$this->createUrl('sortArea')}',{data:dt},function(msg) {
                                if(msg.code) {
                                    location.reload();
                                }
                            });
                        });
                        $('button[data-action=\"collapse\"]').css('display','none');
                        $('button[data-action=\"expand\"]').css('display','block');
                        $('#treeDepartmentExt .dd-item').prepend('<span style=\"float:right;cursor: pointer; line-height:30px;\"><span style=\"margin:0 10px;\" onclick=\"editArea($(this))\">编辑</span>|<span style=\"margin:0 10px;\" onclick=\"staffList($(this))\">成员管理</span>|<span style=\"margin:0 10px;\"  onclick=\"delArea($(this))\">删除</span></span>');
                        $('#treeDepartmentExt .dd-item[data-status=1]').find('span:first').prepend('<span style=\"margin:0 10px;\" onclick=\"setStatus(this)\" class=\"dd-status\">禁用</span>|');
                        $('#treeDepartmentExt .dd-item[data-status=0] .dd-handle').addClass('bg-red-pink');
                        $('#treeDepartmentExt .dd-item[data-status=0]').find('span:first').prepend('<span class=\"dd-status\" onclick=\"setStatus(this)\" style=\"margin:0 10px;\">启用</span>|');


                    });
                    ";
                Yii::app()->clientScript->registerScript('tree', $js, CClientScript::POS_END);
                ?>
            </div>
        </div>
    </div>
    <!-- 弹窗 -->
    <div class="modal fade" id="Admin" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h3 class="panel-title">
                        <span id="fade-title"></span>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    </h3>
                </div>
                <div class="modal-body">
                    <iframe id="AdminIframe" width="100%" height="100%" scrolling="no" frameborder="0"></iframe>
                </div>
            </div>
        </div>
    </div>

    <script language="JavaScript">
        setInterval(function() {
            $("#AdminIframe").height($("#AdminIframe").contents().find("body").height());
            var $panel_title = $('#fade-title');
            $panel_title.html($("#AdminIframe").contents().find("title").html());
        }, 200);
        function delArea(obj) {
            var subItem = $(obj).closest('li').find('.dd-item');
            if (subItem.length) {
                toastr.error('该分类下存在子分类，无法删除');
                return false;
            } else {
                del($(obj).closest('li').data('id'), obj);
            }
        }
        function editArea(ts) {
            var edit_url;
            var id;
            id = ts.closest('li').data('id') || 0;
            edit_url = '<?php echo Yii::app()->createUrl('admin/department/areaedit'); ?>' + '?id=' + id;
            window.location.href = edit_url;
        }
        function staffList(ts) {
            var edit_url;
            var id;
            id = ts.closest('li').data('id') || 0;
            edit_url = '<?php echo Yii::app()->createUrl('admin/department/slist'); ?>' + '?did=' + id;
            window.location.href = edit_url;
        }

        function setStatus(obj) {
            var ddItem = $(obj).closest('.dd-item');
            var currentStatus = ddItem.attr('data-status');

            if (currentStatus == 1) {
                ddItem.attr('data-status', 0);
                $(obj).text('启用');

                var subItem = ddItem.find('.dd-item');
                if (subItem.length) {
                    subItem.find('.dd-status').text('启用');
                    subItem.attr('data-status', 0);
                }

                ddItem.find('.dd-handle').addClass('bg-red-pink');
            } else {
                ddItem.attr('data-status', 1);
                $(obj).text('禁用');

                var subItem = ddItem.find('.dd-item');
                if (subItem.length) {
                    subItem.find('.dd-status').text('禁用');
                    subItem.attr('data-status', 1);
                }
                ddItem.find('.dd-handle').removeClass('bg-red-pink');
            }

        }
    </script>

    <script>
        function del(id, obj) {
            if (confirm("【提示】该操作将删除该部门下的所有楼盘、所有分站信息等其他与部门相关的数据，请谨慎操作，是否继续?")) {
                //location.href="<?php echo $this->createUrl('areadel') ?>?id="+id;
                var url = "<?php echo $this->createUrl('areadel') ?>?id=" + id;


                $.getJSON(url, {}, function(data) {
                    if (data.code == 100) {
                        toastr.success('删除成功！');
                        $(obj).closest('li').remove();
                    } else {
                        toastr.error('删除失败！');
                    }
                });
            }
        }
    </script>



</div>
