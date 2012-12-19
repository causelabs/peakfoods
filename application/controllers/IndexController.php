<?php
class IndexController extends Zend_Controller_Action
{
	protected $_db;
	
	protected $importUsername = IMPORTUSERNAME;
	protected $importPassword = IMPORTPASSWORD;
	
    public function init()
    {
		$this->db = $this->getInvokeArg('bootstrap')->getResource('database');
		
		$context_switch=$this->_helper->getHelper('contextSwitch');
		$context_switch->addActionContext('foods','json')
						->initContext();
		$this->view->jsBase = 'http://'.$_SERVER['HTTP_HOST'];
		$this->view->yummly_url = YUMMLYURL;
		$this->view->yummly_app_id = YUMMLYAPPID;
		$this->view->yummly_app_key = YUMMLYAPPKEY;
    }

    public function indexAction()
    {
    	//get a list of all foods
    	$foods=new Application_Model_Food($this->db);
		
		$foodList=$foods->getAllFoods();
		$this->view->foodList = $foodList;
		
		//list of months
		for($i = 1 ; $i <13 ; $i++)
		{
			$monthTime=mktime(0,0,0,$i,1,date("Y"));
			$monthCode=date("M",$monthTime);
			$months[$monthCode]['name']=date("F",$monthTime);
			$months[$monthCode]['selected']=($i==date("m")?' selected':'');
		}
		$this->view->months=$months;
		
		$this->view->states=array(
			"AL" => "Alabama",
			"AK" => "Alaska",
			"AZ" => "Arizona",
			"AR" => "Arkansas",
			"CA" => "California",
			"CO" => "Colorado",
			"CT" => "Connecticut",
			"DE" => "Delaware",
			"DC" => "District of Columbia",
			"FL" => "Florida",
			"GA" => "Georgia",
			"HI" => "Hawaii",
			"ID" => "Idaho",
			"IL" => "Illinois",
			"IN" => "Indiana",
			"IA" => "Iowa",
			"KS" => "Kansas",
			"KY" => "Kentucky",
			"LA" => "Louisiana",
			"ME" => "Maine",
			"MD" => "Maryland",
			"MA" => "Massachusetts",
			"MI" => "Michigan",
			"MN" => "Minnesota",
			"MS" => "Mississippi",
			"MO" => "Missouri",
			"MT" => "Montana",
			"NE" => "Nebraska",
			"NV" => "Nevada",
			"NH" => "New Hampshire",
			"NJ" => "New Jersey",
			"NM" => "New Mexico",
			"NY" => "New York",
			"NC" => "North Carolina",
			"ND" => "North Dakota",
			"OH" => "Ohio",
			"OK" => "Oklahoma",
			"OR" => "Oregon",
			"PA" => "Pennsylvania",
			"RI" => "Rhode Island",
			"SC" => "South Carolina",
			"SD" => "South Dakota",
			"TN" => "Tennessee",
			"TX" => "Texas",
			"UT" => "Utah",
			"VT" => "Vermont",
			"VA" => "Virginia",
			"WA" => "Washington",
			"WV" => "West Virginia",
			"WI" => "Wisconsin",
			"WY" => "Wyoming",
			"PO" => "Poland"
		);
		
		$this->view->currentState="AL";
		
		//Facebook app id. Set in the Apache config (See Readme)
		$this->view->fbAppId = FACEBOOKAPPID;
    	
    }
	
	public function foodsAction()
	{
		//no need to send the layout, returning JSON data
		$this->_helper->layout->disableLayout();
		Zend_Layout::getMvcInstance()->disableLayout();
		
		$state=$this->_getParam('state');
		$month=$this->_getParam('month');
		
		//default values
		if(!$state || (strlen($state)!=2))
			$state='CO';
		
		if(!$month || (strlen($month)!=3))
			$month='Jan';
		
		//get the data from the database
		$data['status']=1;
		$data['foods'] = array();
		
		$foods=new Application_Model_Food($this->db);
		$seasonalFoods=$foods->getSeasonalFoods($month, $state);
		if($seasonalFoods === false)
			$data['status'] = 0;
		else 
		{
			$data['foods']=$seasonalFoods;
		}
		
		//output just the data we want
		$this->view->clearVars();
		$this->view->data=$data;
		$this->_helper->json($data);
	}

	public function preferencesAction()
	{
		//no need to send the layout, returning JSON data
		$this->_helper->layout->disableLayout();
		Zend_Layout::getMvcInstance()->disableLayout();
		
		$id=$this->_getParam('id');
		$data['status'] = 0;
		$preference = new Application_Model_Preferences($this->db);
		
		if($id>0)
		{
			//saving
			if ($this->getRequest()->isPost())
			{
				$dairyFree = $this->_getParam('dairyFree',0);
				$soyFree = $this->_getParam('soyFree',0);
				$glutenFree = $this->_getParam('glutenFree',0);
				$sesameFree = $this->_getParam('sesameFree',0);
				$vegetarian = $this->_getParam('vegetarian',0);
				$noNuts = $this->_getParam('noNuts',0);
				$preference->setPreferences($id,$dairyFree, $soyFree, $glutenFree, $sesameFree, $vegetarian, $noNuts);
				$data['status'] = 1;
			}
			else 
			{
				$data['preferences'] = $preference->getPreferences($id);
				if($data['preferences']) $data['status'] = 1;
			}
		}
		
		//output just the data we want
		$this->view->clearVars();
		$this->view->data=$data;
		$this->_helper->json($data);
	}
	
	public function importAction()
	{
		$this->requireAuthentication();
		$this->view->log=array();
		$filePath=APPLICATION_PATH.'/../data/';
		$fileName=$filePath.'foods.csv';
		$file=fopen($fileName,'r');
		if(!$file) die("Failed to open file");
		
		$foods=new Application_Model_Food($this->db);
		
		while($row=fgetcsv($file,2000))
		{
			$this->view->log[]=implode(",",$row);
			//the first column is the food ID
			$foodId=array_shift($row);
			
			$currentState='';
			
			foreach($row as $value)
			{
				if(preg_match('/^[A-Za-z]{2}$/',$value)) //state
				{
					$currentState=strtoupper($value);
					$this->view->log[]='New State: '.$currentState;
				}
				elseif(preg_match('/^[A-Za-z]{3}$/',$value)) //month
				{
					$value=ucwords($value);
					$this->view->log[]='Value: '.$value;
					if($value=="All")
					{
						for($i=1 ; $i < 13 ; $i++)
						{
							$foods->addFoodSeason($foodId, $currentState, date("M",mktime(0,0,0,$i,1,2000)));
						}
					}
					else 
					{
						$foods->addFoodSeason($foodId, $currentState, $value);
					}
				}
			}
		}
		fclose($file);
	}

	public function importRemoteAction()
	{
		$this->requireAuthentication();
		$states = array();
		$seasons = array();
		$produce = array();
		$foodReference = array();
		$missingFoods = array();
		$foodStateSeasons = array();
		$this->view->log = array();
		$currentFoodList = array();
		
		$foods=new Application_Model_Food($this->db);
		
		$currentFoods = $foods->getAllFoods();
		foreach($currentFoods as $row)
		{
			$currentFoodList[$row['name']] = $row['id'];
		}
		
		$dataSource = file_get_contents('http://www.simplesteps.org/ss_el/json/produce');
		$data = json_decode($dataSource,true);
		
		// match state codes to the id number
		foreach($data['states'] as $stateData)
		{
			$states[$stateData['n']] = $stateData['a'];
		}
		unset($data['states']);
		
		//match seasons to their id
		//for now, we are not breaking months up into early or late
		foreach($data['seasons'] as $seasonData)
		{
			$seasonName = $seasonData['t'];
			$seasonName = str_replace(array('Early ','Late '),'',$seasonName);
			$seasons[$seasonData['n']] = substr($seasonName,0,3);
		}
		unset($data['seasons']);
		
		//match produce to their IDs
		foreach($data['produce'] as $produceData)
		{
			$produce[$produceData['n']] = $produceData['t'];
			if(!isset($currentFoodList[$produceData['t']])) $missingFoods[] = $produceData['t'];
			else $foodReference[$produceData['n']] = $currentFoodList[$produceData['t']];
			foreach($produceData['s'] as $produceDataValues)
			{
				if(isset($foodReference[$produceData['n']]))
				{
					$foodStateSeasons[$foodReference[$produceData['n']]][$states[$produceDataValues[1]]][$seasons[$produceDataValues[0]]] = 1;
				}
			}
		}
		unset($data['produce']);
		
		$this->view->log[] = "States:\n".print_r($states,true);
		$this->view->log[] = "Seasons:\n".print_r($seasons,true);
		$this->view->log[] = "Produce:\n".print_r($produce,true);
		$this->view->log[] = "Missing foods: ".implode(", ",$missingFoods);
		$this->view->log[] = "Produce List:\n".implode(", ",$produce);
		$this->view->log[] = "Food State Seasons:\n".print_r($foodStateSeasons,true);
		
		foreach($foodStateSeasons as $foodId => $foodData)
		{
			foreach($foodData as $currentState => $stateData)
			{
				foreach($stateData as $season => $ignore)
				{
					$this->view->log[] = "Creating record for $foodId, $currentState, $season";
					$foods->addFoodSeason($foodId, $currentState, $season);
				}
			}
		}
		
		//$this->view->log[] = print_r($data,true);
	} 

	private function requireAuthentication()
	{
		if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) 
		{
		    header('WWW-Authenticate: Basic realm="Peak Foods"');
		    header('HTTP/1.0 401 Unauthorized');
		    die('Authentication is required');
		} 
		else 
		{
			if(($_SERVER['PHP_AUTH_USER'] != $this->importUsername) || ($_SERVER['PHP_AUTH_PW'] != $this->importPassword))
			{
				header('WWW-Authenticate: Basic realm="Peak Foods"');
			    header('HTTP/1.0 401 Unauthorized');
			    die('Authentication is required');
			}
		}
	}
}