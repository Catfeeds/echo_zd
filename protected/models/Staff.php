<?php

/**
 * This is the model class for table "staff".
 *
 * The followings are the available columns in table 'staff':
 * @property integer $id
 * @property string $name
 * @property string $openid
 * @property integer $parent
 * @property integer $is_manage
 * @property integer $is_jl
 * @property string $zw
 * @property string $name_phone
 * @property string $phone
 * @property string $dids
 * @property string $password
 * @property string $arr
 * @property integer $status
 * @property integer $created
 * @property integer $updated
 */
class Staff extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'staff';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('created', 'required'),
			array('parent, is_manage, is_jl, status, created, updated', 'numerical', 'integerOnly'=>true),
			array('name, zw, name_phone, password', 'length', 'max'=>100),
			array('openid, dids, arr', 'length', 'max'=>255),
			array('phone', 'length', 'max'=>20),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, openid, parent, is_manage, is_jl, zw, name_phone, phone, dids, password, arr, status, created, updated', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'openid' => 'Openid',
			'parent' => 'Parent',
			'is_manage' => 'Is Manage',
			'is_jl' => 'Is Jl',
			'zw' => 'Zw',
			'name_phone' => 'Name Phone',
			'phone' => 'Phone',
			'dids' => 'Dids',
			'password' => 'Password',
			'arr' => 'Arr',
			'status' => 'Status',
			'created' => 'Created',
			'updated' => 'Updated',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('openid',$this->openid,true);
		$criteria->compare('parent',$this->parent);
		$criteria->compare('is_manage',$this->is_manage);
		$criteria->compare('is_jl',$this->is_jl);
		$criteria->compare('zw',$this->zw,true);
		$criteria->compare('name_phone',$this->name_phone,true);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('dids',$this->dids,true);
		$criteria->compare('password',$this->password,true);
		$criteria->compare('arr',$this->arr,true);
		$criteria->compare('status',$this->status);
		$criteria->compare('created',$this->created);
		$criteria->compare('updated',$this->updated);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Staff the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
