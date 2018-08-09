<?php
/**
 * 部门添加编辑
 * @author shichang
 * @date 2015-09-11
 */
$this->pageTitle = '编辑部门';
$this->breadcrumbs = array('部门管理' => '/admin/department/arealist', $this->pageTitle);
?>
<?php $form = $this->beginWidget('HouseForm', array('htmlOptions' => array('class' => 'form-horizontal'))) ?>
    <div class="form-group">
        <label class="col-md-2 control-label">部门名称</label>
        <div class="col-md-4">
            <?php echo $form->textField($area, 'name', array('class' => 'form-control')) ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($area, 'name') ?></div>
    </div>
    <div class="form-group">
        <label class="col-md-2 control-label">父分类<span class="required" aria-required="true">*</span></label>
        <div class="col-md-4">
            <?php echo $form->dropDownList($area, 'parent', $catelist, array('class' => 'form-control', 'multiple' => false, 'encode' => false)) ?>
        </div>
        <div class="col-md-2"><?php echo $form->error($area, 'parent') ?></div>
    </div>
    
    <div class="form-group">
        <label class="col-md-2 control-label">部门排序</label>
        <div class="col-md-4">
            <?php echo $form->textField($area, 'sort', array('class' => 'form-control')) ?>
        </div>
    </div>
    <div class="form-group">
        <label class="col-md-2 control-label">部门状态</label>
        <div class="col-md-4 radio-list">
        <?php echo $form->radioButtonList($area, 'status', AreaExt::$status, array('separator' => '', 'template'=>'<label>{input} {label}</label>')) ?>
        </div>
    </div>
    
    <div class="form-actions">
        <div class="row">
            <div class="col-md-offset-3 col-md-9">
                <button type="submit" class="btn green">保存</button>
                <?php echo CHtml::link('返回', $this->createUrl('arealist'), array('class' => 'btn default')) ?>
            </div>
        </div>
    </div>

<?php $this->endWidget(); ?>

<?php
Yii::app()->clientScript->registerScriptFile('/static/global/plugins/bmap.js', CClientScript::POS_END);
Yii::app()->clientScript->registerScriptFile('/static/admin/pages/scripts/map.js', CClientScript::POS_END);

 ?>
