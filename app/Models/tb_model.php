<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use DB;

class tb_model extends Model
{
    use HasFactory;

	public $timestamps = false;

	public function update_infotab($tb,$stringa_stor) {
		$tb=strtoupper($tb);
		$sql="UPDATE report.infotab 
				SET storia_tab='$stringa_stor'
				WHERE ucase(tb)='$tb'";
			
		$result = DB::update($sql);					
	
	}
	


	public function update_deco($req,$valido_da,$valido_a,$descr_agg) {

		$tab_ref="?";
		for ($sca=0;$sca<=count($req)-1;$sca++) {
			$info=$req[$sca];
			$arr_info=explode("-",$info);
			$tb=$arr_info[0];$ente=$arr_info[1];$tab_ref=strtoupper($tb);
			$dx=$valido_da[$ente];
			$dz=$valido_a[$ente];

			$tb=strtoupper($tb);
			$sql="UPDATE `online`.`fo_argo` 
				SET decorrenza_tab='$dx', fine_tab='$dz' 
				WHERE ucase(id_arch)='$tb' and code_CE='$ente'";
			$result = DB::update($sql);
		}
		
		$sql="UPDATE `report`.`infotab` 
				SET descr1='', descr2='', descr3='', descr4='', descr5='' 
				WHERE ucase(tb)='$tab_ref'";
		$result = DB::update($sql);
				
		$s=0;
		foreach ($descr_agg as $ente=>$descr) {
			$s++;
			if ($s>5) break;
			$sql="UPDATE `report`.`infotab` SET 
			descr$s='".$ente.$descr."' 
			WHERE ucase(tb)='$tab_ref'";
			$result = DB::update($sql);	
			
		}

	}
	
	public function last_release($ref_tabulato,$ente,$sind,$str_rilasci) {
		$rilasci=explode(";",$str_rilasci);
		$resp=array();
		$resp['esito']="KO";
		$resp['num']=0;
		$resp['periodo']="";
		$resp['sind_mens']="";
		$this->setTable("anagrafe.".$ref_tabulato);
		//es rilasci: 02/20205;08/20194;04/20183;12/20161;07/20161;01/20161
		
		for ($sca=0;$sca<=count($rilasci)-1;$sca++) {
			$periodo=substr($rilasci[$sca],0,7);
			$ref_sind=substr($rilasci[$sca],7,1);
			$pos=intval(substr($rilasci[$sca],0,2));
			$sind_mens="sind_mens$ref_sind";
			//\DB::enableQueryLog(); // Enable query log
			$num = tb_model::where('ente',"$ente")
			->where(\DB::raw("substr($sind_mens, $pos, 1)"), '=' , "$sind")
			->count();
			//dd(\DB::getQueryLog()); // Show results of log
		
			
			if ($num!=0) {
				$resp['esito']="OK";
				$resp['num']=$num;
				$resp['periodo']=$periodo;
				$resp['sindacato']=$sind;
				$resp['sind_mens']=$sind_mens;
				break;
			}	
			
		}
		return $resp;
	}

	public function info_storia_release($ref_tabulato) {
		$resp =DB::table("report.infotab")
		->select("storia_tab")
		->where('TB',$ref_tabulato)
		->get();
		return $resp;
	}

	public function set_release(Request $request) {
		
				
		$ref_tabulato=$request->input('ref_tabulato');
		$sind_ref=$request->input('sind_ref');
		$sind_ref_num=substr($sind_ref,9,1);
		$ultimo=$request->input('ultimo');
		$anno_sind_r=$request->input('anno_sind_r');
		$omini_sind_r=$request->input('omini_sind_r');

		/*
			Seguendo le specifiche di Tony,
			$ultimo è un array che contiene tutte le decorrenze per ogni sindacato (anche per ente ma questa operazione è disponibile selezionando solo un ente per volta...quindi l'ente sarà valorizzato ma sarà uno solo)
			es. 
				12/2022
					ultimo rilascio liberi
					ultimo rilascio fillea
					ultimo rilascio filca
				09/2022
					ultimo rilascio feneal
			in questo scenario tony riattiva(eventualmente) sempre e SOLO quelli più recenti
			quindi, in questo caso solo lberi fillea filca, lasciando fuori feneal che per qualche ragione sono vecchi e non vengono più riattivati
		*/
		
		/*
			N.B.
			Bisogna aggiornare il tabulato scelto selezionando prima tutti i nominativi 
			interessati (indicizzati da $ref_sind)
			L'insieme ottenuto va quindi aggiornato con i nuovi valori di periodo.
			Questa operazione va ripetuta anche per il tabulato regionale, nazionale, globale 
		*/	
		$regione=substr($ref_tabulato,3,4);
		$arch="";$up_periodi="";
		$code="";$message="";
		$ente="";
		DB::beginTransaction();
		
		try {
			for ($sca=0;$sca<=count($omini_sind_r)-1;$sca++) {
				$per=$omini_sind_r[$sca];
				$per++;
				if ($per<10) $per="0$per";
				if (strlen($up_periodi)!=0) $up_periodi.=";";
				$up_periodi.=$per."/".$anno_sind_r.$sind_ref_num;
			}				
			for ($elenco_sind=0;$elenco_sind<=count($ultimo)-1;$elenco_sind++) { 
				$last=$ultimo[$elenco_sind];
				$info=explode("|",$last);
				$rilascio=$info[0];
				$sindacato=$info[1];
				$ente=$info[2];
				$ref_sind="sind_mens".substr($rilascio,7,1);
				$pos=intval(substr($rilascio,0,2));

				
				for ($tab_sca=1;$tab_sca<=4;$tab_sca++) {
					if ($tab_sca==1) $arch="anagrafe.".$ref_tabulato;
					if ($tab_sca==2) $arch="anagrafe_regioni.".$regione;
					if ($tab_sca==3) {$arch="anagrafe.nazionale";}
					if ($tab_sca==4) $arch="anagrafe_regioni.globale";
					

					if ($elenco_sind==0) {
						//storicizzazione iniziale: tutti i sindacati dell'ente selezionato
						$info=array();
						$info['attivi']="N";
						DB::table($arch)
						->where("ente",$ente)
						->update($info);
					}					
					
					if ($tab_sca==1) {
						$news = DB::table($arch)
						->select("$ref_sind as sind_mens","id_anagr")
						->where("ente",$ente)
						->where(\DB::raw("substr($ref_sind, $pos, 1)"), '=' , "$sindacato")
						->get();
					}
					else {
						$news = DB::table($arch)
						->select("$ref_sind as sind_mens","id_anagr")
						->where("idarc",$ref_tabulato)
						->where("ente",$ente)
						->where(\DB::raw("substr($ref_sind, $pos, 1)"), '=' , "$sindacato")
						->get();
					}
					
					


					foreach ($news as $new) {
						
						$id_anagr=$new->id_anagr;
						$sind_mens=trim($new->sind_mens);
						if ($sind_mens==null || strlen($sind_mens)==0) $sind_mens="************$anno_sind_r";
						
						//$sind_mens="0123456789ab";
						$s_new="";
						for ($sca=0;$sca<=11;$sca++) {
							$d=substr($sind_mens,$sca,1);
							if (in_array($sca,$omini_sind_r)) { 
								$s_new.=$sindacato;
							}
							else 
								$s_new.=$d;
						}
						$s_new.=$anno_sind_r;

						//echo "$sind_mens -  $s_new <br>";
						
						
						$info=array();
						$info[$sind_ref]=$s_new;
						$info["attivi"]="S";
						DB::table($arch)
						->where('id_anagr',$id_anagr)
						->update($info);
					}
				}	
				
				DB::commit();
			}
		} 
		catch (\Illuminate\Database\QueryException $ex) {
			$code=$ex->getCode();
			$message=$ex->getMessage();
			DB::rollBack();
		}		
		
		
		if (strlen($code)==0) {	
			/*
				Aggiornamenti periodi di rilascio.
				Nota bene:
					nella tabella report->infotab
					c'è il campo storia_tab che contiene sotto forma di stringa tutti i periodi di rilascio tabulati (calcolati in base ai vari sind_mens).
					Tutti i periodi in questa tabella hanno 2 particolarità:
					- sono messi senza rispettare necessariamente un ordine cronologico
					- NON sono riferiti agli enti ma sono rilasci globali del tabulato
					
				I rilasci per ente vengono gestiti nella tabella online->fo_argo.
				Il campo rilasci_tabulato identifica tutti i periodi indicizzati dal campo code_CE che identifica l'ente.
				In questo caso l'ordine dei rilasci è importante. 
				
				Quindi mi limito a recuperare da infotab tutti gli attuali periodi salvati ed aggiungo le decorrenze scelte in questo contesto.
				Salvo solo la tabella report->infotab perche poi l'altra tabella viene aggiornata dalla procedura di consolidamente FO/update_tab
			*/
			
			$update_periodi=$this->update_periodi($ref_tabulato,$up_periodi,$ente);
		}
		
		$resp=array();
		if (strlen($code)==0) $code="200";
		$resp['esito']=$code;
		$resp['message']=$message;
		return $resp;		
	}
	

	public function update_periodi($ref_tabulato,$up_periodi,$ente) {
			if (strlen($ente)==0) return;
			$info_storia_release=$this->info_storia_release($ref_tabulato);
			$storia_tab=$info_storia_release[0]->storia_tab;
			$arr_storia=explode(";",$storia_tab);
			$info_up=explode(";",$up_periodi);

			for ($sca=0;$sca<=count($info_up)-1;$sca++) {
				$ril=$info_up[$sca];
				if (!in_array($ril,$arr_storia)) {
					$storia_tab.="$ril;";
				}
			}			
			$info=array();
			$info['storia_tab']=$storia_tab;
			
			//Aggiornamento report->infotab (decorrenze globali del tabulato)
			$resp =DB::table("report.infotab")
			->where('TB',$ref_tabulato)
			->update($info);
			//Le decorrenze per ente (online.fo_argo), vengono aggiornate nella procedura di consolidamento FO/update_tab
			return true;			
	}



    public function setTable($table)
    {
        $this->table = $table;
        return $this;
    }	
	
    
	protected $fillable = [
        'nome',
		'via',
		'cap',
		'loc',
		'pro',
		'datanasc',
		'comunenasc',
		'dataassu',
		'datalice',
		'sindacato',
		'compsind',
		'datasind',
		'numape',
		'datape',
        'codfisc',
		'codinps',
		'ente',
		'denom',
		'provincia',
		'periodo',
		'data',
		'presenti',
		'locazienda',
		'viazienda',
		'settore',
		'attivi',
		'sind_mens1',
		'sind_mens2',
		'sind_mens3',
		'sind_mens4',
		'sind_mens5',
		'c1',
		'c2'
    ];

	/*
    public function set_tab(string $table)
       $this->setTable("anagrafe.".$table);
    }
	*/	
}
