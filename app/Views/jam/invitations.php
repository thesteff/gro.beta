<!-- flexdatalist pour les input !-->
<script type="text/javascript" src="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.js"></script>
<link href="<?php echo base_url();?>/ressources/script/jquery-flexdatalist-2.2.4/jquery.flexdatalist.min.css" rel="stylesheet" type="text/css" />


<!-- Tablesorter -->
<link rel="stylesheet" href="<?php echo base_url();?>/ressources/tablesorter-master/css/theme.sand.css">
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/jquery.tablesorter.js"></script>
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-filter.js"></script>
<script src="<?php echo base_url();?>/ressources/tablesorter-master/js/widgets/widget-columnSelector.js"></script>


<script type="text/javascript">

	$(function() {	
		
		// On rempli le FLEXDATALIST avec les membres n'appartenant pas à l'event + 2 première lettres du nom/prenom/pseudo/mainInstru
		// MEMBRES
		$('#memberInput').flexdatalist({
			 cache: false,
			 minLength: 2,
			 selectionRequired: true,
			 url: "<?php echo site_url('ajax_group/get_members_not_invited'); ?>",
			 params: { 'eventId': <?php echo $jam_item["id"] ?> },
			 searchIn: ["pseudo","prenom","nom","mainInstru"],
			 visibleProperties: ["pseudo","prenom","nom","mainInstru"],
			 searchByWord: true,	// mots séparé par un espace pris en compte
			 searchContain: false,	// keyword forcément au début d'un mot
			 valueProperty: ['id','pseudo']	// on envoie l'attribut 'id' quand on appelle la méthode val()
			})
			
			// Un member est select ou value.length < minLength => set.text = undefined
			.on('change:flexdatalist', function(event, set, options) {
				
				// Si on vide l'input
				// value.length < minLength => set.text = undefined
				if (set.text == "") {
					$("#invitations_form #member_details div:not(.col)").each(function(index) {
						$(this).children("span").empty();
						$(this).addClass("hidden");
					});
					$("#invitations_form #member_details").addClass("hidden");
				}

				// Sinon on cherche les infos du membres sélectionné
				else {
					
					// On change le curseur
					document.body.style.cursor = 'wait';
					
					// Requète ajax au serveur
					$.post("<?php echo site_url(); ?>/ajax_members/get_member_and_listInstru",
					
						{
							'memberId': $("#memberInput").flexdatalist('value')['id'],	// renvoie l'id du membre
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
								$("#invitations_form #member_details").attr("memberId",$obj["member"].id);
								
								// On rempli le member_details
								if ($obj["member"].nom.length) {
									$("#invitations_form #prenom_nom_span").empty().append("<b>"+$obj["member"].prenom+" "+$obj["member"].nom+"</b>");
									$("#invitations_form #prenom_nom").removeClass("hidden");
								}
								if (typeof $obj["listInstru"][0].instruName !== 'undefined' && $obj["listInstru"][0].instruName.length) {
									$("#invitations_form #mainInstru_span").empty().append($obj["listInstru"][0]['instruName']);
									$("#invitations_form #mainInstru").removeClass("hidden");
								}
								if ($obj["member"].email.length) {
									$("#invitations_form #email_span").empty().append($obj["member"].email);
									$("#invitations_form #email").removeClass("hidden");
								}
								
								// On s'occupe de l'avatar
								if ($obj["member"].hasAvatar == 1) {
									$("#invitations_form #member_details img#avatar").prop("src",'<?php echo base_url("images/avatar"); ?>'+'/'+$obj["member"].id+'.png');
								}
								else $("#invitations_form #member_details img#avatar").prop("src",'<?php echo base_url("images/icons/avatar2.png"); ?>');
								
								
								// On affiche le tableau d'invitations
								$("#invitations_form #member_details").removeClass("hidden");
								
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
			
		
		
		// MEMBERLIST CHANGE
		// Gestion du bouton d'ajout d'admin en fonction de l'input
		$('#memberInput.flexdatalist').on('change:flexdatalist', function(event, set, options) {
			if ($('#memberInput').val() == '') $("#addBtn").addClass("disabled");
			else $("#addBtn").removeClass("disabled");
		});
		
		
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// On remplit la liste d'invitations
		$.post("<?php echo site_url(); ?>/ajax/get_invitations",
		
			{
				'targetTag': 2,		// 1 : group	2 : jam		3 : repetition
				'targetId': <?php echo $jam_item['id']; ?>
			},
			
			function (return_data) {
				
				//console.log("$:get_invitations : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {

					if ($obj['data'].length > 0) {
						
						// On rempli le tableau
						$.each($obj['data'], function(index) {
							// On rajoute l'invit à l'ui
							add_invitation($obj['data'][index], index%2 == 1);
						});
						
						// On update le tableau pour le trie
						var resort = true;
						$("#invitTab").trigger("updateAll", [ resort ]);
						
						// On s'asssure que le tableau est visible
						update_style();
						if ($("#list").hasClass("hidden")) $("#list").removeClass("hidden");
						if (!$(".alert").hasClass("hidden")) $(".alert").addClass("hidden");
					}
					// Aucune invitation
					else {
						$(".alert").removeClass("hidden");
					}
					
				}
				else {
					console.log($obj['data']);
				}
			}
		);
		
		
		// TABLESORTER
		$table1 = $("#invitTab").tablesorter({
			theme: 'sand',
			headers: {'th' : {sorter: true}},
			widgets: ['zebra', 'filter', 'columnSelector'],
		});
		
	});
	
	
	
	/************** INVITATIONS ************/
	
	// Requête ajax d'ajout d'invitation   !! le boutton d'ajout garanti l'existence de la saisie dans la liste des membres
	function add_request() {

		// On change le curseur
		document.body.style.cursor = 'wait';
	
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/add_invitation",
		
			{	
				'senderId': $("#memberLogin").attr("idKey"),
				'receiverId': $("#memberInput").flexdatalist('value')['id'],	// renvoie l'id du membre
				'targetTag': 2,		// 1 : group	2 : jam		3 : repetition
				'targetId': <?php echo $jam_item['id']; ?>
			},
		
			function (return_data) {
				
				//console.log("add_request : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On rempli le tableau
					add_invitation($obj['data'], ($("#list").length)%2 == 1);
					
					// On update le tableau pour le trie
					var resort = true;
					$("#invitTab").trigger("updateAll", [ resort ]);
					
					// On s'asssure que le tableau est visible
					update_style();
					if ($("#list").hasClass("hidden")) $("#list").removeClass("hidden");
					
				}
				else {
					console.log($obj['data']);
				}
			}
		);
	}
	
	
	// Ajout d'invitation dans la liste
	function add_invitation($invit, even) {
	
		console.log("******** add_invitation : "+JSON.stringify($invit));
		console.log("******** add_invitation : "+$invit['receiverPseudo']);
	
		// On créé l'item qui sera affiché
		$listElem = $("<tr id='"+$invit['id']+"' receiverId='"+$invit['receiverId']+"' pseudo='"+$invit['receiverPseudo']+"' prenom='"+$invit['receiverPrenom']+"' nom='"+$invit['receiverNom']+"' mainInstru='"+$invit['receiverMainInstruName']+"'></tr>");
		
			// Avatar
			if ($invit['receiverHasAvatar'] == 0) $imgSrc = "<?php echo base_url('images/icons/avatar2.png'); ?>";
			else $imgSrc = "<?php echo base_url('images/avatar/'); ?>/"+$invit['receiverId']+".png";
				
			$content = "<td id='avatar'><img class='img-circle' src='"+$imgSrc+"' width='26' height='26'></td>";
		
			// Pseudo
			$content += "<td id='pseudo'>"+$invit['receiverPseudo']+"</td>";
			// Prénom + Nom
			$content += "<td id='prenom'>"+$invit['receiverPrenom']+"&nbsp;"+$invit['receiverNom']+"</td>";
			// Nom
			//$content += "<td id='nom'>"+$invit['receiverNom']+"</td>";
			// Instru
			$content += "<td id='mainInstru'>"+$invit['receiverMainInstruName']+"</td>";
			
			// Invité par
			$content += "<td id='senderPseudo'>"+$invit['senderPseudo']+"</td>";
			
			// State et couleur de la tr
			if ($invit['state'] == -1) {
				$state = '<span class="hidden">-1</span><i class="bi bi-question-circle bi_nopadding"></i>';
				$color = '';
			}
			else if ($invit['state'] == 0) {
				$state = '<span class="hidden">0</span><i class="bi bi-x-circle bi_nopadding"></i>';
				$color = "red";
			}
			else if ($invit['state'] == 1) {
				$state = '<span class="hidden">1</span><i class="bi bi-check-circle bi_nopadding"></i>';
				$color = "green";
			}
			$content += "<td id='state' class='"+$color+"'>"+$state+"</td>";
			
			// Supprimer
			$content += "<td id='suppr' class='btnCell'><i class='btn btn-default disabled bi bi-trash bi_nopadding'></i></td>";
			
		$listElem.append($content);
		
		// On définit le btn delete
		if ($invit['senderId'] == $("#memberLogin").attr("idKey") || <?php echo $isSuperAdmin ? 1 : 0; ?>) {
			$listElem.find("#suppr .btn").removeClass("disabled");
			
			$listElem.find("#suppr .btn").on({
				click: function(event) {
					delete_invitation_request($(this).parents("tr").attr("id"));
					event.preventDefault();
				}
			});
		}
	
	
		// On stylise la ligne
		if (even) $listElem.addClass("even");
		else $listElem.addClass("odd");
	
		//console.log("********  : "+$listElem.html());
	
		// On ajoute l'admin à la liste
		$("#invitTab tbody").append($listElem);
		
		// On vire le message d'alert
		if (!$(".alert").hasClass("hidden")) $(".alert").addClass("hidden");
		
		
		// On actualise la FLEXDATALIST
		// On vide l'input
		$("#memberInput").val('');
		// Index du receiver dans la flexdatalist
		/*foundIndex = $('#memberInput').flexdatalist('data').findIndex(x => x.id === $invit["receiverId"]);		
		// On retire cette valeur de la flexdatalist
		$('#memberInput').flexdatalist('data').splice(foundIndex, 1);*/

	}
	
	
	// Requête ajax pour retirer un admin de l'event
	function delete_invitation_request($invitId) {
		
		//console.log("delete_invitation_request : "+$invitId);
		
		// On change le curseur
		document.body.style.cursor = 'wait';
		
		// Requète ajax au serveur
		$.post("<?php echo site_url(); ?>/ajax/delete_invitation",
		
			{	
				'invitId': $invitId
			},
			
			function (return_data) {
				
				//console.log("ajax::delete_invitation : "+return_data);
				$obj = JSON.parse(return_data);
				
				// On change le curseur
				document.body.style.cursor = 'default';
				
				if ($obj['state'] == 1) {
					
					// On remove l'invit du tableau
					$("#invitTab tbody tr[id="+$invitId+"]").remove();
		
					// On actualise l'affichage si plus aucune invit (message)
					if ($("#invitTab tbody tr").length == 0) {
						$("#list").addClass("hidden");
						$(".alert").removeClass("hidden");
					}
				}
			}
		);
	}

 </script>


<!----------- INVITATIONS  ------------>

<!-- Formulaire !-->
<div id="invitations_form" class="">

	<form class="form-horizontal">

		<!--------------  INPUT  ----------------->
		<div class="form-group">
			<label class="control-label col-sm-1" for="memberInput"><i class="bi bi-search"></i></label>
			<div class="input-group col-sm-10">
				<input id="memberInput" class="form-control flexdatalist" type="input" name="memberInput" placeholder="Membres du gro">
				<div class="input-group-btn">
					<button id="addBtn" class="btn btn-default disabled" type="button" onclick="add_request()">
						<i class="glyphicon glyphicon-plus"></i>
					</button>
				</div>
			</div>	

			<!-- On affiche les détails s'il y en a !-->
			<div id="member_details" class="col-sm-offset-1 col-sm-10 soften small panel panel-default hidden flexDetails" memberId="">
				<div class="col" style="display:inline-block; vertical-align:top"><img id="avatar" class="img-circle avatarNotSet" src="<?php echo base_url('images/icons/avatar2.png'); ?>" width="50" height="50"></div>
				<div class="col" style="display:inline-block; padding-left: 8px">
					<div id="prenom_nom" class="hidden"><span id="prenom_nom_span"></span></div>
					<div id="mainInstru" class="hidden"><i class="bi bi-music-note-beamed"></i><span id="mainInstru_span"></span></div>
					<div id="email" class="hidden"><i class="bi bi-envelope-fill"></i><span id="email_span"></span></div>
				</div>
			</div>
			
		</div>
	</form>
	
	
	<hr>
	
	<!---------------- TABLEAU INVITATIONS ----------------------->	
	
	<!-- Affichage des membres invités -->
	<div id="list" class="hidden">
		<table id="invitTab" class="tablesorter focus-highlight" cellspacing="0">
			<thead>
				 <tr>
					<th data-priority="critical" class="centerTD" style="width:40px"><i class="bi bi-person-square bi_nopadding"></i></th>
					<th data-priority="critical">Pseudo</th>
					<th data-priority="5">Prénom/Nom</th>
					<th data-priority="critical" class="centerTD">Instru</th>
					<th data-priority="5" class="centerTD">Invit par</th>
					<th data-priority="critical" class="centerTD" style="width: 30px">Etat</th>
					<th data-priority="critical" class="centerTD dark" style="width: 30px"></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><i class="bi bi-person-square bi_nopadding"></i></th>
					<th>Pseudo</th>
					<th></th>
					<th class="centerTD">Instru</th>
					<th class="centerTD">Invit par</th>
					<th class="centerTD">Etat</th>
					<th class="centerTD"></th>
				</tr>
			</tfoot>
			<tbody>
				
			</tbody>
		</table>
	</div>
	
	<!-- Alerte aucune invitations -->
	<div class="alert alert-warning alert-dismissible hidden">
		<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
		<i class="bi bi-exclamation-triangle"></i>&nbsp; Il n'y a actuellement aucune invitation pour cette jam.
	</div>
	
	
</div>
