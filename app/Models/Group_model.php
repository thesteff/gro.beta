<?php namespace App\Models;

use CodeIgniter\Model;

class Group_model extends Model {

	
	// Retourne un object infos sur le group
	public function get_infos() {

		// On récupère le nombre de ref
		$query = $this->db->query('
					SELECT COUNT(*)
					FROM morceau
					LEFT JOIN version
					ON version.morceauId = morceau.id
					WHERE morceau.id > 0
					');
		$nbRef = $query->getRowArray(0)['COUNT(*)'];		
					
		// On récupère le nombre de membres
		$builder = $this->db->table('membres');
		$nbMembers = $builder->countAll();
		
		$infos = (object) [
				'nbRef' => $nbRef,
				'nbMembers' => $nbMembers
			];
			
		return $infos;
	}
	
	
	
	// Retourne les membres d'un groupe absent d'un certain évènement
	public function get_members_not_in_event($eventId, $keyword, $searchMainInstru = false) {
		
		// On récupère les membres en fonction du keyword sur pseudo/nom/prénom (pas de prise en compte du mainInstru)
		if (!$searchMainInstru) {
			
			$list_members = $this->db->query('
				SELECT pseudo,
						membres.id as id,
						nom, prenom, email, mobile, admin, genre,
						instruments.id as instruId, instruments.name as mainInstru
						FROM membres
						INNER JOIN membre_instruments
						ON membres.id = membre_instruments.membresId
						INNER JOIN instruments
						ON instruments.id = membre_instruments.instrumentsId

						WHERE membres.id NOT IN
						(
							SELECT membres.id
							FROM membres
							LEFT JOIN jam_membres_relation
							ON membres.id = jam_membres_relation.membresId
							WHERE jam_membres_relation.jamId = '.$eventId.'
							AND jam_membres_relation.event_admin = 0
							GROUP BY membres.id
						)
						AND ( pseudo LIKE "'.$keyword.'%" OR nom LIKE "'.$keyword.'%" OR prenom LIKE "'.$keyword.'%" )
						ORDER BY pseudo
				');
		}
		
		// Si le keyword s'applique aussi sur le mainInstru
		else {
			
			$list_members = $this->db->query('
				SELECT pseudo,
						membres.id as id,
						nom, prenom, email, mobile, admin, genre,
						instruments.id as instruId, instruments.name as mainInstru
						FROM membres
						INNER JOIN (
							SELECT *
							FROM membre_instruments
							GROUP BY membresId
							ORDER BY id
						) AS sub
						ON membres.id = sub.membresId
						INNER JOIN instruments
						ON instruments.id = sub.instrumentsId

						WHERE membres.id NOT IN
						(
							SELECT membres.id
							FROM membres
							LEFT JOIN jam_membres_relation
							ON membres.id = jam_membres_relation.membresId
							WHERE jam_membres_relation.jamId = '.$eventId.'
							AND jam_membres_relation.event_admin = 0
							GROUP BY membres.id
						)
						AND ( pseudo LIKE "'.$keyword.'%" OR nom LIKE "'.$keyword.'%" OR prenom LIKE "'.$keyword.'%" OR instruments.name LIKE "'.$keyword.'%" )
						ORDER BY pseudo
				');
		}
			
		return $list_members->getResult();
	
	}
	
}