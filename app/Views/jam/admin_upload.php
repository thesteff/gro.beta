<script type="text/javascript">

		// On définit les Hover Btn
		/*$(".clickableBtn").each(function() {
			$(this).css("filter", "grayscale(100%)");
			$(this).hover(function(){
				$(this).css("filter", "grayscale(0%)");
				}, function(){
				$(this).css("filter", "grayscale(100%)");
			});
		});*/
		
		
		// On fixe le comportement de l'upload
		/*$("#uploadInput").on("change", function() {
			// On récupère le nom de fichier
			$new_val = $(this).val().substring($(this).val().lastIndexOf("\\")+1,$(this).val().length);
			popup_upload($new_val);
		});*/
		
		
		// On fixe le comportement des select créés dynamiquement par le popup_upload/popup_generate
		/*$("body").on("change", "select", function() {
			
			// On gère le accessType et le pdfType
			if ($(this).attr("id") == "accessType" || $(this).attr("id") == "pdfType") {

				// On rend la fonction dynamique
				$pre = $(this).attr("id").substr(0,$(this).attr("id").length-4)

				// On remove la select du accessId
				$(this).parent().find("#"+$pre+"Id").remove();
				
				// On doit récupérer la list des access possibles					
				// On change le curseur
				document.body.style.cursor = 'progress';
				
				// Requète ajax au serveur
				$.post("<?php echo site_url(); ?>/ajax/get_access_elem",
				
					{'accessType': $(this).val()},
			
					function (msg) {
						
						// On rétablit le pointeur
						document.body.style.cursor = 'default';

						if (msg != "empty") {
							// On récupère les éléments "option"
							$list_item = JSON.parse(msg);
							$option = "";
							$.each($list_item, function(index, elem) {
								if (index == 0) $option += "<option value='"+elem.id+"' selected>"+elem.name+"</option>";
								else $option += "<option value='"+elem.id+"'>"+elem.name+"</option>";
							});
							
							// On créé le select
							$new_select = '<select id="'+$pre+'Id">';
							$new_select += $option;
							$new_select += '</select>';
							$("#"+$pre+"Type").parent().append($new_select);
						}
					}
				);
			}
			
			
			// On gère le fileType du popup_generate
			else if ($(this).attr("id") == "fileType") {
				
				// On retire le pdf_form si besoin
				if ($("#pdf_form") && $(this).val() != "pdf") $("#pdf_form").remove();

				// Si rien n'est selectionné, le boutton générer est désactivé
				if ($(this).val() == '') $("#upload_block").find("[type=button]").prop('disabled', true);
				else {
					// On active le boutton générer
					$("#upload_block [type=button]").prop('disabled', false);
										
					// On actualise le textFile
					$("#upload_block #textFile").empty();
					if ($(this).val() == 'zip')
						$("#upload_block #textFile").append("Zip rassemblant tous les morceaux de la jam au format mp3");

					// On traite le cas pdf
					else if ($(this).val() == 'pdf') {
						$("#upload_block #textFile").append("Pdf rassemblant toutes les partitions de la jam au format pdf avec un sommaire");
						
						// On créé le select pour les media
						$new_select = "<p id='pdf_form'><label for='pdfType'><small>Sélection de pdf pour chaque morceau</small></label><br>";
						$new_select += '<select id="pdfType" style="margin-right:18px">';
							$new_select += "<option value='all'>Tous les documents</option>";
							$new_select += "<option value='cat'>Catégorie d'instrument</option>";
							$new_select += "<option value='instru'>Instrument</option>";
						$new_select += '</select>';
						$new_select += '</p>';
						
						// On créé le select pour le classement des titres du pdf
						$new_select += "<p id='alpha'><label for='alphaType'><small>Ordre des pdf</small></label><br>";
						$new_select += '<select id="alphaType" style="margin-right:18px">';
							$new_select += "<option value='none'>Ordre de la playlist</option>";
							$new_select += "<option value='asc'>Ordre alphabétique</option>";
						$new_select += '</select>';
						$new_select += '</p>';

						$("#upload_form").append($new_select);
					}
				}
				
				// On actualise la height de la popup
				$("#confirm").css("height",$("#confirm .tcontent").innerHeight());
				$("#confirm").resize();
			}
			
			
			// On vide les champs de saisie sur un reload
			//resetForms();
			//$('#select_repet').val("-1");
			
			// On remplit le CKEditor
			//CKEDITOR.instances.editor1.setData($("#text_tab").html());
			
		});


		// On actualie l'affichage du block d'admin doc
		//update_doc_section();*/
		
		
		/*function resetForms() {
			for (i = 0; i < document.forms.length; i++) {
				document.forms[i].reset();
			}
		}*/
		
		
 </script>