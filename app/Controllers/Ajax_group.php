<?php namespace App\Controllers;


use App\Models\Group_model;
use App\Models\Members_model;
use App\Models\Invitation_model;


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
	
	
	
	/**************************** MEMBERS NOT IN EVENT ***************************/
	public function get_members_not_in_event() {
		
		$eventId = trim($_GET['eventId']);
		$keyword = trim($_GET['keyword']);
		
		//log_message('debug',"****** ajax_group : get_members_not_in_event : ".json_encode($_GET));
		
		$group_model = new Group_model();
		
		// On récupère les membres du groupe ne participant pas à l'évènement + recherche sur le mainInstru
		$list_members = $group_model->get_members_not_in_event($eventId, $keyword, true);

		$output = json_encode($list_members);
		echo $output;
		
	}
	
	
	/**************************** MEMBERS NOT INVITED (AND not in Event) ***************************/
	public function get_members_not_invited() {
		
		$eventId = trim($_GET['eventId']);
		$keyword = trim($_GET['keyword']);
		
		//log_message('debug',"****** ajax_group : get_members_not_invited : ".json_encode($_GET));
		
		$invitation_model = new Invitation_model();
		$group_model = new Group_model();
		
		// On récupère les membres du groupe ne participant pas à l'évènement + recherche sur le mainInstru
		$list_members_not_in_event = $group_model->get_members_not_in_event($eventId, $keyword, true);
		
		// On récupère les membres invités 	// 1 : group	2 : jam		3 : repetition
		$list_members_invited = $invitation_model->where( ['targetTag' => 2, 'targetId' => $eventId] )->findAll();
		
		
		log_message("debug","list_members_not_in_event");
		log_message("debug",json_encode($list_members_not_in_event));
		log_message("debug","list_members_invited");
		log_message("debug",json_encode($list_members_invited));
		
		
		// On cherche les membres pas dans l'event qui ont déjà été invité
		for ($i = 0; $i < count($list_members_invited) ; $i++) {
			
			$key = array_search($list_members_invited[$i]["receiverId"], array_column($list_members_not_in_event, 'id'));
			
			// On retire les gens déjà invité des membres proposés
			if ($key !== false) {
				array_splice($list_members_not_in_event, $key, 1);
			}			
			
		}		

		$output = json_encode($list_members_not_in_event);
		echo $output;
		
	}
	

}
?>