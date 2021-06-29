<!-- flexdatalist pour les input !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />

<!-- flexdatalist pour l'input !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />

<script type="text/javascript">
	
	// Variable globale du membre sélectionné
	var selectedMember = "";
	
	
	$(function() {
		
		// On rempli les FLEXDATALIST
		// MEMBRES
		$('.memberList').flexdatalist({
			 minLength: 2,
			 selectionRequired: true,
			 url: "<?php echo site_url('ajax_group/get_members_not_in_event'); ?>",
			 params: { 'eventId': <?php echo $jam_item["id"] ?> },
			 searchIn: ["pseudo","prenom","nom","mainInstru"],
			 visibleProperties: ["pseudo","prenom","nom","mainInstru"],
			 searchByWord: true,	// mots séparé par un espace pris en compte
			 searchContain: false,	// keyword forcément au début d'un mot
			 valueProperty: ['id','pseudo']	// on envoie l'attribut 'id' quand on appelle la méthode val()
			})
			
			// Un member est select ou value.length < minLength => set.text = undefined
			.on('change:flexdatalist', function(event, set, options) {
				
				// value.length < minLength => set.text = undefined
				if (set.text == "") {
					$("#add_member_form #member_details div:not(.col)").each(function(index) {
						$(this).children("span").empty();
						$(this).addClass("hidden");
					});
					$("#add_member_form #member_details").addClass("hidden");
				}

				else {
					
					// On change le curseur
					document.body.style.cursor = 'wait';
					
					// Requète ajax au serveur
					$.post("<?php echo site_url(); ?>/ajax_members/get_member_and_listInstru",
					
						{
							'memberId': $(".memberList").flexdatalist('value')['id'],	// renvoie l'id du membre
						},
					
						function (return_data) {
							
							//console.log(return_data);
							$obj = JSON.parse(return_data);
							
							// On change le curseur
							document.body.style.cursor = 'default';
							
							// On affiche les données du membre sélectionné
							if ($obj['state'] == 1) {
								
								// On conserve le membre dans une variable en cas d'ajout
								selectedMember = $obj["member"];
								
								// On actualise l'id du membre (évite une recherche de l'id dans la datalist sur un add)
								$("#add_member_form #member_details").attr("memberId",$obj["member"].id);
								
								// On rempli le member_details
								if ($obj["member"].nom.length) {
									$("#add_member_form #prenom_nom_span").empty().append("<b>"+$obj["member"].prenom+" "+$obj["member"].nom+"</b>");
									$("#add_member_form #prenom_nom").removeClass("hidden");
								}
								if (typeof $obj["listInstru"][0].instruName !== 'undefined' && $obj["listInstru"][0].instruName.length) {
									$("#add_member_form #mainInstru_span").empty().append($obj["listInstru"][0]['instruName']);
									$("#add_member_form #mainInstru").removeClass("hidden");
								}
								if ($obj["member"].email.length) {
									$("#add_member_form #email_span").empty().append($obj["member"].email);
									$("#add_member_form #email").removeClass("hidden");
								}
								
								// On s'occupe de l'avatar
								if ($obj["member"].hasAvatar == 1) {
									$("#add_member_form #member_details img#avatar").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$obj["member"].id+'.png');
								}
								else $("#add_member_form #member_details img#avatar").prop("src",'<?php echo base_url("images/icons/avatar2.png"); ?>');
								
								
								$("#add_member_form #member_details").removeClass("hidden");
								
							}
							else {
								console.log($obj['data']);
							}
						}
					);
				}

				// On active ou non le bouton d'ajout
				if (set.text == "") $("#addBtn").addClass("disabled");
				else $("#addBtn").removeClass("disabled");
				
			});
			
				
	});
	
	
	
	
	// Requête ajax d'ajout d'un membre appartenant au groupe mais pas à l'évènement
	//	!! le boutton d'ajout garanti l'existence de la saisie dans la liste des membres
	function add_member_request() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax_jam/join_jam",
		
			{	
				'slugJam':'<?php echo $jam_item['slug']; ?>',
				'id': $(".memberList").flexdatalist('value')['id'],	// renvoie l'id du membre
				'event_admin': 0
			},
		
			function (return_data) {
				
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					// On ajoute le membre dans la liste
					//add_member_ui($obj['data']);
					// On ferme la modal
					//$("#addBtn").addClass("disabled");
					//$("#addModal").modal('hide');
					document.location.reload();
				}
				else {
					console.log($obj['data']);
				}
			}
		);
	}
	
	
	// Ajout d'admin dans la liste
	/*function add_member_ui() {
	
		console.log(selectedMember);
	
		// On créé l'item qui sera affiché
		$tr = $('<tr tmemberId="'+selectedMember.id+'">');
			// Checkbox
			$content = '<td class="selector"><span style="display:none">0</span><input type="checkbox" /></td>';
			// Admin et référent
			$content += '<td></td>';
			
			// Profil
			$content += '<td>'+selectedMember.pseudo+'</td>';
			$content += '<td>'+selectedMember.prenom+'</td>';
			$content += '<td>'+selectedMember.nom+'</td>';
			$content += '<td>'+selectedMember.email+'</td>';
			
			// Age
			$content += '<td>'+selectedMember.age+'</td>';
			
			//Genre
			if (selectedMember.genre == 1) genre = "man";
			else if (selectedMember.genre == 2) genre = "woman";
			else genre = "";
			$content += '<td>'+genre+'</td>';
			
			// Mobile
			$content += '<td>'+selectedMember.mobile+'</td>';
			
			// Pupitre principal
			$content += '<td><img style="height:16px; vertical-align: text-top; margin: 0px 5px 2px 5px" src="<?php echo base_url(); ?>/images/icons/'+$selectedMember.mainPupitre['iconURL']+'" title="'+$selectedMember.mainPupitre['pupitreLabel']+'"><span class="hidden">'+$selectedMember.mainPupitre['id']+'</span></td>';

			
			
		$tr.append($content);
		
		// On ajoute l'admin à la liste
		$("#member_list table tbody").append($tr);

	}*/
	
	/*function reset() {
		if ($("#add_member_form #member_details").css("display") == "block") {
			$("#add_member_form #member_details").css("display","none");
			$("#add_member_form #prenom_nom").empty();
			$("#add_member_form #instru1_span").empty();
			$("#add_member_form #email_span").empty();
		}
	}*/
	
	
	
 </script>
 
 
<!-- Formulaire !-->
<div id="add_member_form" class="container-fluid">
	<form role="form" class="form-horizontal" data-toggle="validator">

		<!-------- SEARCH MEMBER --------->
		<div class="form-group">
			<div class="col-sm-12">
				<label for="pseudo" class="hidden">Pseudo</label>
				<input id="searchInput" class="form-control memberList flexdatalist" type="input" name="adminInput" placeholder="Membre du gro">
				
				<!-- On affiche les détails s'il y en a !-->
				<div id="member_details" class="soften small panel panel-default hidden flexDetails" memberId="">
					<div class="col" style="display:inline-block; vertical-align:top"><img id="avatar" class="img-circle avatarNotSet" src="<?php echo base_url('images/icons/avatar2.png'); ?>" width="50" height="50"></div>
					<div class="col" style="display:inline-block; padding-left: 8px">
						<div id="prenom_nom" class="hidden"><span id="prenom_nom_span"></span></div>
						<div id="mainInstru" class="hidden"><i class="bi bi-music-note-beamed"></i><span id="mainInstru_span"></span></div>
						<div id="email" class="hidden"><i class="bi bi-envelope-fill"></i><span id="email_span"></span></div>
					</div>
				</div>
			</div>
		</div>
		
		
		
		<div class="modal-footer">
			<button type="button" class="btn btn-default" data-dismiss="modal">Fermer</button>
			<button id="addBtn" class="btn btn-primary disabled" type="button" onclick="add_member_request()">Ajouter</button>
		</div>

 	</form>
 </div>
 
