<?php
class Application_Model_Food extends Application_Model_AbstractMapper {
	public function __construct($db) {
		parent::__construct($db);
		$this -> db -> setFetchMode(Zend_Db::FETCH_ASSOC);
	}
	
	/*
	 * Gets a list of foods in season for the selected month and state
	 * @param string $month The month. Three-letter month code.
	 * @param string $state The two-letter state code.
	 * @return array
	 */
	public function getSeasonalFoods($month,$state)
	{
		$select = $this -> db -> select() 
					-> from('foods',array('id','name','type','image','background_color'))
					-> join('food_seasons','food_seasons.food_id=foods.id',array('source')) 
					-> where('state = ?', $state)
					-> where('month = ?', $month);
		$data=$this -> db -> fetchAll($select);
		if(!$data) return false;
		
		$foods = $this->getNutrition($data);
		
		return $foods;
	}
	
	/*
	 * Gets a list of all foods.
	 * @return array
	 */
	public function getAllFoods()
	{
		$select = $this -> db -> select() 
					-> from('foods',array('id','name','type','image','background_color'));
		$data=$this -> db -> fetchAll($select);
		if(!$data) return false;
		
		$foods = $this->getNutrition($data);
		
		return $foods;
	}

	/*
	 * Gets the nutrition data for an array of foods
	 * @param array $foods The list of foods
	 * @return array
	 */
	private function getNutrition($foods)
	{
		//convert to an array, with the food ID as the index
		$foodIds = array();
		$foodPositions = array();
		foreach($foods as $num => $row)
		{
			$foodIds[] = $row['id'];
			$foodPositions[$row['id']] = $num;
		}
		
		//get the nutrition info and join
		$select = $this -> db -> select()
				-> from('nutrition')
				-> where('food_id in ('.implode(',',$foodIds).')');
		$data=$this -> db -> fetchAll($select);
		
		if($data)
		{
			foreach($data as $row)
			{
				$foods[$foodPositions[$row['food_id']]]['nutrition'] = $row;
			}
		}
		return $foods;
	}
	
	/*
	 * Adds a food to the food_seasons table
	 * @param int $foodId The food ID
	 * @param string $state The two-letter state code
	 * @param stirng $month The three-letter month name
	 * @return void
	 */
	public function addFoodSeason($foodId,$state,$month)
	{
		//first, delete any record for this food on this season
		$this->db->delete('food_seasons',array('food_id='.$foodId,'month="'.$month.'"','state="'.$state.'"'));
		
		//now create the record
		$fields = array(
			'food_id'	=> $foodId,
			'month'		=> $month,
			'state'		=> $state,
			'source'	=> 'local'
		);
		
		$this->db->insert('food_seasons',$fields);
		
		$id = $this->db->lastInsertId();
		if($id<1){
			throw new Exception("Failed to create record");
		}
	}
}