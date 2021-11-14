<?php namespace App\Controllers;

use App\Models\Members_model;
use App\Models\Jam_model;
use App\Models\Stage_model;
use App\Models\Lieux_model;

class Stage extends BaseController {

	public function suppress($slug) {
		/*$this->jam_model->delete_jam($slug);
		redirect('/jam');*/
	}

	
	/*********** INSCRIPTION STAGE ***************/
	public function inscription($slug) {
	
		$members_model = new Members_model();
		$jam_model = new Jam_model();
		$stage_model = new Stage_model();
		$lieux_model = new Lieux_model();
	
		// On récupère les infos du membres s'il est connecté (identification des admin pour affichage menu)
		$data['login'] = $this->session->login;
		$data['logged'] = $this->session->logged;
		$data['isSuperAdmin'] = (isset($data['logged']) && $this->session->superAdmin);

	
		if ($data['logged'] || $data['isSuperAdmin']) {

			// On récupère le membre
			$data['member'] = $members_model->get_member($data['login']);

			// On récupère la jam
			$data['jam_item'] = $jam_model->get_jam($slug);
			//log_message('debug', "members_item : ".json_encode($data['jam_item']));
			
			if (empty($data['jam_item'])) {
				throw CodeIgniterxceptionsPageNotFoundException::forPageNotFound;
			}
			
			// Si l'utilisateur n'est pas loggé et que la jam n'est pas publique
			if (!isset($_SESSION['login']) && $data['jam_item']['acces_jam'] == 0) {
				$data['page_title'] = 'Erreur';
				$data['title'] = 'Accés refusé !!';
				$data['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
				$this->load->view('templates/header', $data);
				$this->load->view('pages/message', $data);
				$this->load->view('templates/footer');
				return;
			}
							
			// On récupère le stage
			$data['stage_item'] = $stage_model->get_stage_jamId($data['jam_item']['id']);
			
			// On récupère le lieu du stage
			$data['lieu_stage_item'] = $lieux_model->get_lieux_by_id($data['stage_item']['lieuxId']);
			
			// On récupère le lieu de la jam
			$data['lieu_jam_item'] = $lieux_model->get_lieux_by_id($data['jam_item']['lieuxId']);
			
			// On récupère la liste d'instru classée par catégorie
			//$data['cat_instru_list'] = $this->instruments_model->get_categorized_instruments();
			
			// DATE conversion
			if (env('app.online')) setlocale(LC_ALL, 'fr_FR');
			else setlocale(LC_ALL, 'fra');

			$tmpDate = strtotime($data['jam_item']['date_debut']);
			$data['jam_item']['date_debut_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
			
			$tmpDate = strtotime($data['stage_item']['date_debut']);
			$data['stage_item']['date_debut_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
			
			$tmpDate = strtotime($data['stage_item']['date_debut']."+".($data['stage_item']['duree']-1)." days");
			$data['stage_item']['date_fin'] = date("Y-m-d",$tmpDate);
			$data['stage_item']['date_fin_norm'] = utf8_encode(strftime("%A %d %B %Y",$tmpDate));
		
		

			// On vérifie que le login de l'url existe
			if (isset($data['member'])) {
				
				//log_message("debug","member : ".json_encode($data['member']));
				
				// On normalise la date de naissance
				if (!empty($data['member']->naissance)) {
					$date = date_create_from_format("Y-m-d",$data['member']->naissance);
					$data['member']->naissance = date_format($date,"d/m/Y");
				}
				
				
				// On récupère l'instrument 1 de l'utilisateur
				$data['instru_list'] = $members_model->get_instruments($data['member']->id);
				//log_message("debug","instru_list : ".json_encode($data['instru_list']));
				if (isset($data['instru_list']) && $data['instru_list'] !== false) $data['member']->instrument1 = $data['instru_list'][0]['instruName'];
				else $data['member']->instrument1 = "";
			
			
				// On lance la vue
				echo view('stage/inscription', $data);
			}
			else {
				$data_page['page_title'] = 'Erreur';
				$data_page['title'] = 'Membre inexistant !';
				$data_page['message'] = 'Le membre <b>'.$data['login'].'</b> n\'existe pas !';
			
				echo view('pages/message', $data_page);
			}
		}
		
		
		else {
			$data_page['page_title'] = 'Erreur';
			$data_page['title'] = 'Accés refusé !!';
			$data_page['message'] = 'Votre statut sur le site ne vous permet pas d\'accéder à cette page';
		
			echo view('pages/message', $data_page);
		}
	}
}