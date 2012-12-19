<?php
class AboutController extends Zend_Controller_Action
{
	public function init()
    {
		$this->view->jsBase = 'http://'.$_SERVER['HTTP_HOST'];
    }
	public function indexAction()
	{
		//Facebook app id. Set in the Apache config (See Readme)
		$this->view->fbAppId = FACEBOOKAPPID;
	}
}
	