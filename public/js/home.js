/*
const vm = new Vue({
	el: '#div_table_atleti',
	data: {
		num: 0
	},
	//Metodi dell'istanza
	methods: {
		updateIscr(event) {
			//non interecettato all'interno di una tabella renderizzata con datatable
			this.num++;
		}
	}
});	
*/
	
function set_tipo_imp(value) {
	/*
	if (value=="M") 
		$( ".campiauto" ).prop( "disabled", false );
	else
		$( ".campiauto" ).prop( "disabled", true );
	*/
}	

function re_pub(nome_file,ref_tabulato) {
	if (!confirm("Sicuro di ripubblicare il tabulato selezionato?")) return false;
	$( "#direct_pub" ).prop( "checked", true );
	fetch('set_repub.php', {
		method: 'post',
		//cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached		
		headers: {
		  "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
		},
		body: 'operazione=set_repub&nome_file='+nome_file+'&ref_tabulato='+ref_tabulato
	})
	.then(response => {
		if (response.ok) {
		   return response.json();
		}
		
	})
	.then(resp=>{
		if (resp.status=="ok") {
			$("#repub_tab").val("1")
			$("#body_dialog").hide(150);
			$('#div_table_pub').hide(150);
			$( "#btn_procedi" ).prop( "disabled", false );
			$( "#btn_procedi" ).removeClass( "btn-outline-success");
			$( "#btn_procedi" ).addClass( "btn-success");			

			$( "#btn_procedi_test" ).prop( "disabled", false );
			$( "#btn_procedi_test" ).removeClass( "btn-outline-success");
			$( "#btn_procedi_test" ).addClass( "btn-success");			

		}	
		else {
			alert("Si è verificato un errore durante l'impostazione");
		}
	})
	.catch(status, err => {
		return console.log(status, err);
	})
}

function check_riattiva() {
	event.preventDefault();
	abilita=false
	anno_sind_r=$("#anno_sind_r").val()
	omini_sind_r=$("#omini_sind_r").val();
	if (anno_sind_r.length!=0 && omini_sind_r.length!=0) abilita=true;	
	if (abilita==false) {
		alert("Selezionare anno SindMens5 e nuove posizioni!");
		return false;
	}
	$( "#btn_riattiva" ).prop( "disabled", true );	
	$( "#btn_riattiva" ).text( "Attendere. Operazione in corso..." );	
	$("#frm_riattiva").submit();	
}

function check_iscr(chi) {
	$('#riattivazione').val('');
	if (chi=="1") $('#riattivazione').val('1');
	
	$(".descr_agg").attr("placeholder", "");
	ent=0;err=0;
	
	
	$( '.selee').each( function() {
		stato=$("#"+this.id).prop("checked");
		if (stato==true) {
			ent=1
			id_ref=$(this).data("idref")
			descr_agg=$("#descr_agg"+id_ref).val()
			valido_da=$("#valido_da"+id_ref).val()
			valido_a=$("#valido_a"+id_ref).val()
			
			if (descr_agg.length==0) {
				 $("#descr_agg"+id_ref).attr("placeholder", "Definire descrizione aggiornamento");
				err=1
				msg="Definire correttamente la descrizione associata all'ente selezionato!";
			}else if (valido_da.length==0) {
				err=1
				msg="Definire correttamente l'inizio validità tabulato associata all'ente selezionato!";
			}else if (valido_a.length==0) {
				err=1
				msg="Definire correttamente la fine validità tabulato associata all'ente selezionato!";
			}	
		}
		if (err==1) {
			alert(msg);
			return false;
		}
		
	})
	
	ent1=0
	if (chi=="1") {
		$( '.sele_riattiva').each( function() {
			stato=$("#"+this.id).prop("checked");
			if (stato==true) {
				ent1=1
			}
		})	
		if (ent1==0) {
			msg="Attenzione. Selezionare almeno una condizione sindacale da rendere attiva!";
			alert(msg);
			return false;
		}		
	}
	
	
	if (ent==0) {
		msg="Attenzione. Selezionare almeno un ente da aggiornare!";
		alert(msg);
		return false;
	} else {
		if (err==0) $("#frm_pub3").submit();
	}
}		


function set_sezione(ref_tabulato) {
	ref_aziende=$("#ref_aziende").prop("checked");
	ref_zz=$("#ref_zz").val();
	ref_a=0
	
	if (ref_aziende==true) ref_a=1
	if (ref_zz) ref_a=ref_zz

	if ($("#body_dialog").is(":visible")) {
		$("#body_dialog").hide(150);
		return false;
	}
	var URL = $("#frm_pub2").attr("action");
	var token = $("input[name='_token']").val();

	$(".allegati").empty();
	fetch('class_allegati.php', {
		method: 'post',
		//cache: 'no-cache', // *default, no-cache, reload, force-cache, only-if-cached		
		headers: {
		  "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
		},
		body: 'operazione=refresh_tipo'
	})
	.then(response => {
		if (response.ok) {
		   return response.text();
		}
		
	})
	.then(resp=>{
		$("#body_dialog").html(resp);
		$("#body_dialog").show(150);
		set_class_allegati(ref_tabulato,ref_a); //in demo-config.js
	})
	.catch(status, err => {
		
		return console.log(status, err);
	})
}

function set_direct() {
	direct_pub=$("#direct_pub").prop("checked");
	
	if (1==2) {
		$( "#omini_sind" ).prop( "disabled", false );
		$("#div_posizioni").show(150);
		if (direct_pub==true) {
			$( "#omini_sind" ).prop( "disabled", true ); 
			$("#div_posizioni").hide(0);
		}	
	}
	$( "#storicizza" ).prop( "disabled", false ); 
	$( "#ref_aziende" ).prop( "disabled", false ); 
	if (direct_pub==true) {
		$( "#storicizza" ).prop( "disabled", true ); 
		$( "#ref_aziende" ).prop( "disabled", true ); 
	}	


	$( "#btn_procedi" ).prop( "disabled", true );
	$( "#btn_procedi" ).removeClass( "btn-success");
	$( "#btn_procedi" ).addClass( "btn-outline-success");

	$( "#btn_procedi_test" ).prop( "disabled", true );
	$( "#btn_procedi_test" ).removeClass( "btn-success");
	$( "#btn_procedi_test" ).addClass( "btn-outline-success");	

	abilita=false
	anno_sind=$("#anno_sind").val()
	omini_sind=$("#omini_sind").val();
	if (anno_sind.length!=0 && omini_sind.length!=0) abilita=true;	
	
	if ((direct_pub==true && csv_send==true) || (csv_send==true && abilita==true)) {
		$( "#btn_procedi" ).prop( "disabled", false );
		$( "#btn_procedi" ).removeClass( "btn-outline-success");
		$( "#btn_procedi" ).addClass( "btn-success");

		$( "#btn_procedi_test" ).prop( "disabled", false );
		$( "#btn_procedi_test" ).removeClass( "btn-outline-success");
		$( "#btn_procedi_test" ).addClass( "btn-success");		
	}

}

function repub() {
	$('#body_dialog').hide(150);
	$("#div_altre_opzioni").hide(150);
	$('#div_table_pub').show(150);
	
}

function publish () {
 ref_tabulato=$("#ref_tabulato").val();
 notif = "&notifiche=N"
 script_fine=$("#script_fine").prop("checked");
 notifiche_attive=$("#notifiche_attive").prop("checked");
 if (notifiche_attive==true) notif="&notifiche=S"

 //esempio valorizzazione enteweb
 enteweb=$("#enteweb").val();
 token = "cc1055abc7bd9883721a075066b8ced1"
 locale=0
 pre_url="https://www.filleaoffice.it/";
 
 if (locale==1) {
	pre_url="http://localhost://";
	notif = "&notifiche=N"
 } 
 
    if (script_fine==true) //se lanciare o meno script servizi
        url=pre_url+"sintel/index.php?token=" + token + "&enteweb=" + enteweb + "&tab_agg=" + ref_tabulato + notif 
    else
        url=pre_url="FO/update_tab/update.php?enteweb=" + enteweb + "&tab_agg=" + ref_tabulato + notif 

window.open(url); 

//es: https://www.filleaoffice.it/FO/update_tab/update.php?enteweb=C&tab_agg=t6_cala_a&notifiche=N

}



