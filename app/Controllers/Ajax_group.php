<?php namespace App\Controllers;


use App\Models\Group_model;
use App\Models\Members_model;


class Ajax_group extends BaseController {


	public function index() {
	}

	
	// Ajouter un instrument à un membre
	public function delete_news() {
		
		$newsId = trim($_POST['newsId']);
		
		// On supprime l'instrument du membre
		$state = $this->news_model->delete_news($newsId);
		
		$return_data = array(
			'state' => $state,
			'data' => ""
		);
		$output = json_encode($return_data);
		echo $output;
	}
	
	
	
	/**************************** MEMBERS NOT IN JAM ***************************/
	public function get_members_not_in_event() {
		
		$eventId = trim($_GET['eventId']);
		$keyword = trim($_GET['keyword']);
		
		//log_message('debug',"****** ajax_group : get_members_not_in_event : ".json_encode($_GET));
		
		$group_model = new Group_model();
		$members_model = new Members_model();
		
		// On récupère les membres du groupe ne participant pas à l'évènement + recherche sur le mainInstru
		$list_members = $group_model->get_members_not_in_event($eventId, $keyword, true);

		$output = json_encode($list_members);
		echo $output;
		
	}
	

}
?>