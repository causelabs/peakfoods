<?php
class Application_Model_Preferences extends Application_Model_AbstractMapper {
	public function __construct($db) {
		parent::__construct($db);
		$this -> db -> setFetchMode(Zend_Db::FETCH_ASSOC);
	}
	
	/*
	 * Gets a list of preferences for a selected Facebook ID
	 * @param string $id The Facebook ID
	 * @return array
	 */
	public function getPreferences($id)
	{
		$select = $this -> db -> select() 
					-> from('preferences')
					-> where('facebook_id = ?', $id);
					//echo $select->__toString();
		$data=$this -> db -> fetchRow($select);
		if(!$data) return false;
		
		return $data;
	}
	
	/*
	 * Sets the preferences for a selected Facebook ID
	 * @param string $id The Facebook ID
	 * @return void
	 */
	public function setPreferences($id,
		$dairyFree = 0, 
		$soyFree = 0, 
		$glutenFree = 0, 
		$sesameFree = 0, 
		$vegetarian = 0, 
		$noNuts = 0)
	{
		//delete the original record
		$id = preg_replace("/[^0-9]/","",$id);
		$this->db->delete('preferences',array('facebook_id='.$id));
		
		$fields['dairyFree'] = $dairyFree?1:0;
		$fields['soyFree'] = $soyFree?1:0;
		$fields['glutenFree'] = $glutenFree?1:0;
		$fields['sesameFree'] = $sesameFree?1:0;
		$fields['vegetarian'] = $vegetarian?1:0;
		$fields['noNuts'] = $noNuts?1:0;
		$fields['facebook_id'] = $id;
		
		$this->db->insert('preferences',$fields);
	}
}