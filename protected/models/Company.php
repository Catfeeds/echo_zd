<?php

/**
 * This is the model class for table "company".
 *
 * The followings are the available columns in table 'company':
 * @property integer $id
 * @property string $name
 * @property string $address
 * @property string $manager
 * @property integer $city
 * @property integer $area
 * @property integer $street
 * @property string $phone
 * @property integer $parent
 * @property string $map_zoom
 * @property string $map_lng
 * @property string $map_lat
 * @property string $code
 * @property integer $msg_num
 * @property string $image
 * @property integer $adduid
 * @property integer $type
 * @property integer $status
 * @property integer $sort
 * @property integer $deleted
 * @property integer $created
 * @property integer $updated
 */
class Company extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'company';
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
			array('city, area, street, parent, msg_num, adduid, type, status, sort, deleted, created, updated', 'numerical', 'integerOnly'=>true),
			array('name, address, image', 'length', 'max'=>255),
			array('manager, map_zoom, map_lng, map_lat', 'length', 'max'=>100),
			array('phone', 'length', 'max'=>30),
			array('code', 'length', 'max'=>10),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, address, manager, city, area, street, phone, parent, map_zoom, map_lng, map_lat, code, msg_num, image, adduid, type, status, sort, deleted, created, updated', 'safe', 'on'=>'search'),
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
			'address' => 'Address',
			'manager' => 'Manager',
			'city' => 'City',
			'area' => 'Area',
			'street' => 'Street',
			'phone' => 'Phone',
			'parent' => 'Parent',
			'map_zoom' => 'Map Zoom',
			'map_lng' => 'Map Lng',
			'map_lat' => 'Map Lat',
			'code' => 'Code',
			'msg_num' => 'Msg Num',
			'image' => 'Image',
			'adduid' => 'Adduid',
			'type' => 'Type',
			'status' => 'Status',
			'sort' => 'Sort',
			'deleted' => 'Deleted',
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
		$criteria->compare('address',$this->address,true);
		$criteria->compare('manager',$this->manager,true);
		$criteria->compare('city',$this->city);
		$criteria->compare('area',$this->area);
		$criteria->compare('street',$this->street);
		$criteria->compare('phone',$this->phone,true);
		$criteria->compare('parent',$this->parent);
		$criteria->compare('map_zoom',$this->map_zoom,true);
		$criteria->compare('map_lng',$this->map_lng,true);
		$criteria->compare('map_lat',$this->map_lat,true);
		$criteria->compare('code',$this->code,true);
		$criteria->compare('msg_num',$this->msg_num);
		$criteria->compare('image',$this->image,true);
		$criteria->compare('adduid',$this->adduid);
		$criteria->compare('type',$this->type);
		$criteria->compare('status',$this->status);
		$criteria->compare('sort',$this->sort);
		$criteria->compare('deleted',$this->deleted);
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
	 * @return Company the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
