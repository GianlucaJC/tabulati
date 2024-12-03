<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\infotab;
use App\Models\tb_model;
use App\Models\log_events;
use App\Models\schema_import;
use App\Models\argo_comuni;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


use org\majkel\dbase\Table;
use org\majkel\dbase\Record;

use DB;


class mainController extends Controller
{
	 public function dashboard($select=0) {
		$name=Auth::user()->name;
		$infotab=new infotab;
		$reports=$infotab->reports(1,"");
		$schema_import=schema_import::all();
		return view('dashboard')->with('name', $name)->with('reports', $reports)->with('schema_import',$schema_import);
	 }


	 public function step2(Request $request) {
		$tb=null;
		if($request->has('sele_e')) $tb=$request->input('sele_e');

		$file_json=$request->input('file_json');

		$infotab=new infotab;
		if ($tb==null) return redirect('dashboard');
		
		if($request->has('btn_down') || $request->has('btn_downzz') || $request->has('btn_downc')) {
			$new_f=uniqid();
			if($request->has('btn_down'))
				$this->export_tab($tb,"$new_f.csv",0);
			elseif($request->has('btn_downzz'))
				$this->export_tab($tb,"$new_f.csv","zz");
			elseif($request->has('btn_downc'))
				$this->export_tab($tb,"$new_f.csv",1);
			/////////////////////////////
			$filename =  public_path("allegati/pubblicazioni/$new_f.csv");
			$headers = ['Content-Type: text/csv'];
			return response()->download($filename, 'download.csv', $headers)->deleteFileAfterSend(true);
			/////////////////////////////			
		}
		

		$reports=$infotab->reports(0,$tb);
		
		if($request->has('pub_zz')) {
			return view('pubblica_zz')->with('reports', $reports)->with('file_json', $file_json);
			
		} else {			
			return view('pubblica')->with('reports', $reports)->with('file_json', $file_json);
		}

	 }

	//
	 public function step_riattiva(Request $request) {
		$tb_model = new tb_model;
		$resp=$tb_model->set_release($request);
		return view('releaseok')->with('resp',$resp);
	 }

	 public function step3(Request $request) {
		 
		$name=Auth::user()->name;
		$infotab=new infotab;
		$tb_model = new tb_model;
		$sele_x=null;
		$file_json=$request->input('file_json');

		$riattivazione=false;
		if($request->has('riattivazione') && $request->input('riattivazione')=="1") {
			$riattivazione=true;
		} else {	
			if($request->has('sele_x')) $sele_x=$request->input('sele_x');
			else return redirect('step2');
		}	
			
		
		$detail_tab="";
		$req=$request->get('sele_x');
		$valido_da=$request->get('valido_da');
		$valido_a=$request->get('valido_a');
		$descr_agg=$request->get('descr_agg');
		
		$ref_pub=array();
		$ref_enti=array();
		$ref_tabulato="";$enteweb="";
		$check_fine_anno=false;
		for ($sca=0;$sca<=count($req)-1;$sca++) {
			$info=$req[$sca];
			$arr_info=explode("-",$info);
			$tb=$arr_info[0];$ente=$arr_info[1];
			if (strlen($enteweb)!=0) $enteweb.=";";
			$enteweb.=$ente;
			
			if ($sca==0) $ref_tabulato=$tb;
			if (strlen($detail_tab)==0) {
				//check_fine anno
				$check_fine_anno=$infotab->check_nuovo_anno($ref_tabulato);

				$detail_tab.="<hr>";
			}
			

			
			$detail=$infotab->detail_tab($tb,$ente);
			$ref_pub[$detail[0]->descr_ce]['descrizione']=$detail[0]->denominazione;
			$ref_pub[$detail[0]->descr_ce]['code_CE']=$ente;
			$ref_enti[$ente]=$detail[0]->descr_ce;
		
			
		}
				
		if ($riattivazione==true) {
			$c_riattiva=array();
			if($request->has('c_riattiva')) $c_riattiva=$request->input('c_riattiva');
			$infotab=new infotab;
			$arr_enti=explode(";",$enteweb);
			
			$riattiva=array();
			for ($sca=0;$sca<=count($arr_enti)-1;$sca++) {
				
				$ente=$arr_enti[$sca];
				$rilasci_ente=$infotab->rilasci_ente($ente,$ref_tabulato);		

				$riattiva[$ente]['rilasci']=$rilasci_ente[0]->rilasci_tabulato;
		
				for($s=0;$s<=count($c_riattiva)-1;$s++) {

					$sind=$c_riattiva[$s];
					$last_release=$this->last_release($ref_tabulato,$ente,$sind,$riattiva[$ente]['rilasci']);
					$riattiva[$ente]['ultima_presenza'][]=$last_release;

				}

				
				$riattiva[$ente]['denominazione']=$ref_enti[$ente];
			}

			return view('riattiva')->with('riattiva',$riattiva)->with('ref_enti',$ref_enti)->with('ref_tabulato',$ref_tabulato)->with('c_riattiva',$c_riattiva)->with('check_fine_anno',$check_fine_anno);

		}
		
		$update_deco=$tb_model->update_deco($req,$valido_da,$valido_a,$descr_agg);
		//$info_up=$valido_da."-".$valido_a."-".$descr_agg;
		$info_up = print_r($valido_da,1)." - ";
		$info_up.= print_r($valido_a,1)." - ";
		$info_up.= print_r($descr_agg,1);
		$lista_tab=log_events::lista_tab("");
		//eliminazione vecchi csv inviati
		@unlink("allegati/$ref_tabulato.csv");
		@unlink("allegati/".$ref_tabulato."_aziende.csv"); 

		//caricamento altri tabulati già inviati
		return view('uploadtab')->with('enteweb',$enteweb)->with('ref_enti',$ref_enti)->with('ref_pub',$ref_pub)->with('ref_tabulato',$ref_tabulato)->with('info_up', $info_up)->with('lista_tab', $lista_tab)->with('file_json', $file_json)->with('check_fine_anno',$check_fine_anno);

	 }
	
	 private function last_release($ref_tabulato,$ente,$sind,$rilasci) {		 
		$tb_model = new tb_model; 	
		$resp=$tb_model->last_release($ref_tabulato,$ente,$sind,$rilasci);
		//caricamento altri tabulati già inviati
		return $resp;
	 }


	 public function step4(Request $request) {
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', '-1');
		$argo_comuni = new argo_comuni;
		$comuni=$argo_comuni->comuni();
		 
		$test=false;
		//if($request->has('test_import') && $request->input('test_import')=="test") $test=true;
		
		$repub_tab=false;
		if($request->has('repub_tab') && $request->input('repub_tab')=="1") $repub_tab=true;

		$ref_tabulato=null;
		if($request->has('ref_tabulato')) $ref_tabulato=$request->input('ref_tabulato');
		else return redirect('step2');
		
		
		if ($repub_tab==false) {
			$new_f=uniqid();
			
			$pubblicazione=$this->export_tab($ref_tabulato,"$new_f.csv",0);
			$info_up="Backup-Preventivo prima della pubblicazione";
			if($request->has('info_up')) $info_up=$request->get('info_up');
			
			
			$id_user=Auth::user()->id;
			$log_events = new log_events;
			$log_events->id_user = $id_user;
			$log_events->operazione = $info_up;
			$log_events->nome_file = $new_f;
			$log_events->esito = 2000;
			$log_events->num_record = -1;
			$log_events->tot_new = -1;
			$log_events->tot_up = -1;
			$log_events->ref_tabulato = $ref_tabulato;
			$log_events->save();
		}
		
		
		$enteweb=null;
		if($request->has('enteweb')) $enteweb=$request->input('enteweb');
		else return redirect('step2');
		
		$direct_pub=null;
		if($request->has('direct_pub')) $direct_pub=$request->input('direct_pub');

		$storicizza="";
		if($request->has('storicizza')) $storicizza=$request->input('storicizza');
		if ($storicizza!=true) $storicizza=false; 

		$omini_sind=null;
		if($request->has('omini_sind')) $omini_sind=$request->input('omini_sind');
		
		if ($omini_sind==null) $mese_sind=date("m");
		else $mese_sind=max($omini_sind)+1;
		$mese_sind=trim($mese_sind);
		if (strlen($mese_sind)==1) $mese_sind="0$mese_sind";
		
		$anno_sind=date("Y");
		if($request->has('anno_sind')) $anno_sind=$request->input('anno_sind');
		
		$data=trim($anno_sind)."-".$mese_sind."-01 00:00:00";
		
		$file_json="standard.json";
		if($request->has('file_json')) $file_json=$request->input('file_json');
		if ($direct_pub!=null) $file_json="standard.json";
		
		if (strtoupper($ref_tabulato)=="T4_LAZI_A") $file_json="roma.json";
		
		$filepath="allegati/$ref_tabulato.csv";
		if (!file_exists($filepath)) {
			$response=response()->json(['status'=>'false','message'=>"404-File di input non trovato"]);
			return view('import_csv')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response)->with('test',$test);
		}


		$rip_tab="";
		if($request->has('rip_tab')) $rip_tab=$request->input('rip_tab');
		if (strtoupper($ref_tabulato)=="T4_LAZI_A" && strtoupper($rip_tab)=="T4_LAZI_A" ) {
			//in caso di ripubblicazione di un tabulato roma
			//ripristino i 'vecchi' nuovi assunti da old_new_bk
			$info_ente=explode(";",$enteweb);
			for ($sca=0;$sca<=count($info_ente)-1;$sca++) {
				$ente_up=$info_ente[$sca];
				if (strlen($ente_up)>0) {
					//restore da old_new_bk
					$dele=DB::table('rm_office.old_new')
					->where('ente','=',$ente_up)->delete();
					DB::statement("
					INSERT INTO `rm_office`.old_new
						(`nome`, `datanasc`, `ente` ) 
						SELECT nome,datanasc,ente 
						FROM `rm_office`.old_new_bk
						WHERE ente='$ente_up'");
				}		
			}
			
		}
		
		$file = fopen($filepath, "r");
		$importData_arr = array();
		$i = 0;
		$map_campi=array();
		//Importazione grezza di tutti i campi del csv in un array di comodo
		while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
			$num = count($filedata);
			/*
			if ($file_json!="standard.json") {
				if ($i == 0) {
					$i++;
					continue;
				}
			}
			*/
			
			if ($i == 0) {
				$i++;
				/*
					- Per i CSV NON diretti:
						Il primo record di instestazione DEVE contenere i campi 
						da importare, non è importante la sequenza perchè poi mapperò
						il tutto tramite associazione campi con l'array da riversare nel DB
					
					- Per i CSV direct_pub:
						lo schema di riferimento è standard.json 
				*/
				for ($c = 0; $c < $num; $c++) {
					$map_campi[$filedata[$c]]=$c;
				}

				continue;
			} 

			for ($c = 0; $c < $num; $c++) {
				$importData_arr[$i][] = $filedata[$c];
			}
			$i++;
		}
		
		

		
		fclose($file);
		//Importazione AZIENDE in caso di file di supporto con codici azienda!
		$file_aziende="allegati/".$ref_tabulato."_aziende.csv";
		$arr_azienda=array();

		if (file_exists($file_aziende)) {
			$file = fopen($file_aziende, "r");
			
			//Importazione grezza di tutti i campi del csv in un array di comodo
			while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
				if (isset($filedata[0])) {
					$cod_azi=$filedata[0];
					$azienda="";$locazienda="";$viazienda="";$piva="";$t_azi="";
					if (isset($filedata[1])) $azienda=$filedata[1];
					if (isset($filedata[2])) $locazienda=$filedata[2];
					if (isset($filedata[3])) $viazienda=$filedata[3];
					if (isset($filedata[4])) $piva=$filedata[4];
					if (isset($filedata[5])) $t_azi=$filedata[5];
					//tel
					$arr_azienda[$cod_azi]['azienda']=$azienda;
					$arr_azienda[$cod_azi]['locazienda']=$locazienda;
					$arr_azienda[$cod_azi]['viazienda']=$viazienda;
					$arr_azienda[$cod_azi]['piva']=$piva;
					$arr_azienda[$cod_azi]['telazi']=$t_azi;
				}
			}
			fclose($file);
		}

		/* 
			Note sulla transazione dei dati: 
			In caso di uso di truncate, la rollback, se si riscontrano problemi non ha senso. Tra l'altro anteponendo la DB::beginTransaction() prima della truncate viene sollevata un'eccezione
			ref:https://github.com/laravel/framework/discussions/38260
		*/
		
		if (strtoupper($ref_tabulato)=="T4_LAZI_A") {
			//calcolo nuovi assunti: per ROMA
			$info_ente=explode(";",$enteweb);
			for ($sca=0;$sca<=count($info_ente)-1;$sca++) {
				$ente_up=$info_ente[$sca];
				if (strlen($ente_up)>0) {
					//backup per eventuale ripristino
					$dele=DB::table('rm_office.old_new_bk')
					->where('ente','=',$ente_up)->delete();
					DB::statement("
					INSERT INTO `rm_office`.old_new_bk
						(`nome`, `datanasc`, `ente` ) 
						SELECT nome,datanasc,ente 
						FROM `rm_office`.old_new
						WHERE ente='$ente_up'");

					
					//aggiornamento file di appoggio per i 'vecchi' 1
					$dele=DB::table('rm_office.old_new')
					->where('ente','=',$ente_up)->delete();
					
					DB::statement("
					INSERT INTO `rm_office`.old_new 
						(`nome`, `datanasc`, `ente` ) 
						SELECT nome,datanasc,ente 
						FROM `anagrafe`.t4_lazi_a 
						WHERE c3='1' and ente='$ente_up'");
						
					
					//aggiornamento file di appoggio per vecchio tabulato
					//serve come calcolo alternativo dei nuovi assunti:
					//invece dei sind_mens, confronto tra tabulati

					$dele=DB::table('rm_office.old_tabulato')
					->where('ente','=',$ente_up)->delete();
					
					
					DB::statement("insert into 
						`rm_office`.old_tabulato (`codfisc`, `ente`)
						SELECT codfisc,'$ente_up'
						FROM `anagrafe`.t4_lazi_a
						WHERE ente='$ente_up' and length(codfisc)>0");
				}
			}		
		}
		
		if ($direct_pub==null) {
			try {
				DB::statement("ALTER TABLE `anagrafe_b`.$ref_tabulato ADD INDEX DN (datanasc)");
			} catch (\Illuminate\Database\QueryException $ex) {
			}
			try {
				DB::statement("ALTER TABLE `anagrafe_b`.$ref_tabulato ADD INDEX CF (codfisc)");
			} catch (\Illuminate\Database\QueryException $ex) {
			}
			try {
				DB::statement("ALTER TABLE `anagrafe_b`.$ref_tabulato ADD INDEX DENOM (denom)");
			} catch (\Illuminate\Database\QueryException $ex) {
			}
			try {
				DB::statement("ALTER TABLE `anagrafe_b`.$ref_tabulato ADD INDEX C2 (c2)");
			} catch (\Illuminate\Database\QueryException $ex) {
			}

			
			
			
			$anagrafe = DB::table('anagrafe_b.'.$ref_tabulato)->truncate();
		}	
		else {
			$info_ente=explode(";",$enteweb);
			for ($sca=0;$sca<=count($info_ente)-1;$sca++) {
				$ente_up=$info_ente[$sca];
				if (strlen($ente_up)>0) {			
					$dele=DB::table('anagrafe.'.$ref_tabulato)
					->where('ente','=',$ente_up)->delete();			
				}
			}
			//$anagrafe = DB::table('anagrafe.'.$ref_tabulato)->truncate();
		}
		
		
		if ($test==false) {
			if ($direct_pub==null) {
				DB::beginTransaction();
				if ($storicizza==true) {
					$info=array();
					$info['attivi']="N"; //storicizza tutti in funzione degli enti da pubblicare
					$info_ente=explode(";",$enteweb);
					for ($sca=0;$sca<=count($info_ente)-1;$sca++) {
						$ente_up=$info_ente[$sca];
						if (strlen($ente_up)>0)
							DB::table('anagrafe.'.$ref_tabulato)->where('ente',$ente_up)->update($info);

					}	
				}
			}
		}		
		else {
			$anagrafe = DB::table('fo_admin.test_import')->truncate();
			DB::beginTransaction();
		}	
		
		$ent=0;
		$code="";
		$tot_new=-1;
		$tot_up=-1;		
		
		try {
	
			$j=0;$message="";
			

			$tb_model = new tb_model;
			
			$rowData=[];

			/*
				la function tracciato fa una mappatura tra l'array grezzo e 
				l'array definito per la struttura di riferimento
				
				29.08.2022-->la mappatura con il file json avviene solo per CSV con pubblicazione diretta
				quindi $file_json contiene sempre standard.json (in public/tracciati)
				
			*/
			
			$infocampi=$this->tracciato($file_json);
			$info_ente=explode(";",$enteweb);

			foreach ($importData_arr as $importData) {
				$import_row=true;
				//Verifica congruenza del numero campi fissato ad un minimo di 20
				//if (count($importData)<20) continue;
				$j++;
				$dati=array();	
				if ($direct_pub==null) {
					$cognome="?";$nom="?";
					foreach ($map_campi as $campo=>$pos_campo) {
						$pos_campo=$map_campi[$campo];
						$dato=$importData[$pos_campo];
						if ($campo=="cognome") $cognome=$dato;
						if ($campo=="nom") $nom=$dato;
						if (isset($importData[$pos_campo])) {							
							if ($campo=="datanasc" || $campo=="dataassu"  || $campo=="datalice" || $campo=="datasind" || $campo=="datape" || $campo=="data")
								$dati[$campo]=$this->data_en($dato);
							else {	
								if (isset($infocampi['struttura'][$campo]))
									$dati[$campo]=$dato;
							}
						}
					}
					if ($cognome!="?") $dati['nome']=strtoupper($cognome." ".$nom);
				}
				else {
					foreach($infocampi['struttura'] as $campo=>$value) {
						$pos=$infocampi['struttura'][$campo]['pos'];
						if (isset($infocampi['struttura'][$campo]['tipo'])) {
							//importazione da formattare per altri tipi
							if ($infocampi['struttura'][$campo]['tipo']==1) {				
								//check tipo data!
								if (!isset($importData[$pos]) || $pos=="N")
									 $no_campo=1; //non importo il campo
								else
									$dati[$campo]=$this->data_en($importData[$pos]); //da sistemare!
							}
						} else {
							//importazione diretta per tipi char, int, etc.
							//se la posizione non esiste invalido il record da importare
							if (!isset($importData[$pos]) || $pos=="N")
								$no_campo=1; //non importo il campo						
							else {
								//per i campi data non ho fatto affidamento alla proprietà tipo perchè non l'ho gestito nei json...quindi faccio riferimento in modo statico alle colonne conosciute
								if ($campo=="datanasc" || $campo=="dataassu"  || $campo=="datalice" || $campo=="datasind" || $campo=="datape" || $campo=="data")
									$dati[$campo]=$this->data_en($importData[$pos]);
								else {	
									$dati[$campo]=$importData[$pos];
									/*
									verifico sem importare il dato 
									in funzione dell'ente(i) di pubblicazione
									*/
									if (strtolower($campo)=="ente") {
										if (!in_array($importData[$pos],$info_ente)) $import_row=false;
									}
									
									

								}	
							}	
						}				
					}
				}

				if ($import_row==false) continue;
				//passaggio da array $dati ad $arr per mappatura ORM utile per l'inserimento nel DB
				$arr=array();
				foreach($dati as $k=>$v) {
					$arr[$k]=$v;
				}
				
				//verifica associazione azienda con file esterno
				//se l'array aziende è valorizzato vuol dire che è stato fornito il file supporto delle aziende
				
				if (count($arr_azienda)!=0) {
					if (array_key_exists("denom",$arr) && strlen($arr['denom'])!=0) {
						$k=$arr['denom'];
						if (array_key_exists($k,$arr_azienda)) {
							$arr['denom']=$arr_azienda[$k]['azienda'];
							$arr['locazienda']=$arr_azienda[$k]['locazienda'];
							$arr['viazienda']=$arr_azienda[$k]['viazienda'];
							$arr['c2']=$arr_azienda[$k]['piva'];
							$arr['c3']=$arr_azienda[$k]['telazi'];
						}
					}
				}

				
				//Completamento dati assenti
				//assenza di Cap: tento assegnazione da DB Argo tramite comuni
				if (array_key_exists("cap",$arr) && strlen($arr['cap'])==0) {
					if (array_key_exists("loc",$arr)) {
						if (isset($comuni['comuni'][$arr['loc']])) {
							$cap=$comuni['comuni'][$arr['loc']];
							$arr['cap']=$cap;
						}
					}
				}
				//normalizzazione CAP
				if (array_key_exists("cap",$arr) && strlen($arr['cap'])!=0) {
					$cap=$arr['cap'];
					if (strlen($cap)==1) $cap="0000$cap";
					elseif (strlen($cap)==2) $cap="000$cap";
					elseif (strlen($cap)==3) $cap="00$cap";
					elseif (strlen($cap)==4) $cap="0$cap";						
					$arr['cap']=$cap;
				}
				//assenza di comune: tento assegnazione da DB Argo tramite cap
				if (array_key_exists("loc",$arr) && strlen($arr['loc'])==0) {
					if (array_key_exists("cap",$arr)) {
						if (isset($comuni['cap'][$arr['cap']])) {
							$comune=$comuni['cap'][$arr['cap']];
							$arr['loc']=$comune;
						}	
					}
				}					
				//assenza di provincia: tento assegnazione da DB Argo tramite cap
				if (array_key_exists("pro",$arr) && strlen($arr['pro'])==0) {
					if (array_key_exists("cap",$arr)) {
						if (isset($comuni['cap_pro'][$arr['cap']])) {
							$pro=$comuni['cap_pro'][$arr['cap']];
							$arr['pro']=$pro;
						}	
					}
				}					
				
				//tento assegnazione da codice fiscale-sovrascrive la data di nascita fornita
				if (array_key_exists("codfisc",$arr) && strlen($arr['codfisc'])>10) {
					$codfisc=$arr['codfisc'];
					$aa=intval(substr($codfisc,6,2));
					$gg=intval(substr($codfisc,9,2));
					$ref_mese=substr($codfisc,8,1);
					$mm="01";
					
					
					if ($gg>31) $gg=$gg-40;
					if ($aa<20)  {
						if (strlen($aa)==1) $aa="0$aa";
						$aa="20$aa";
					}	
					else { 
						if (strlen($aa)==1) $aa="0$aa";						
						$aa="19$aa";
					}	
					
					if ($ref_mese=="A") $mm="01";
					if ($ref_mese=="B") $mm="02";
					if ($ref_mese=="C") $mm="03";
					if ($ref_mese=="D") $mm="04";
					if ($ref_mese=="E") $mm="05";
					if ($ref_mese=="H") $mm="06";
					if ($ref_mese=="L") $mm="07";
					if ($ref_mese=="M") $mm="08";
					if ($ref_mese=="P") $mm="09";
					if ($ref_mese=="R") $mm="10";
					if ($ref_mese=="S") $mm="11";
					if ($ref_mese=="T") $mm="12";
					
					
					if (strlen($gg)==1) $gg="0$gg";
					$datanasc="$aa-$mm-$gg 00:00:00";

					$arr['datanasc']=$datanasc;
				}
			
				//fine completamento dati assenti

				$rowData[]=$arr;
				
				
				
				//l'array rowData è mappato in modo da essere riversato sul DB
                if (count($rowData) == 500) {
					if ($test==false) {
						if ($direct_pub==null)
							DB::table("anagrafe_b.".$ref_tabulato)->insert($rowData);
						else
							DB::table("anagrafe.".$ref_tabulato)->insert($rowData);
							
					}	
					else
						DB::table("fo_admin.test_import")->insert($rowData);
                    unset($rowData);
                }					

			}
			//ultima casisitica di inserimento fuori ciclo
			if (isset($rowData) && count($rowData) > 0) {
				if ($test==false) {
					if ($direct_pub==null) {
						DB::table("anagrafe_b.".$ref_tabulato)->insert($rowData);
						//aggiorno tutte le date di nascita nulle che potrebbero dar problemi con la join dopo, usando come chiavi nome e datanasc
						DB::statement("
							UPDATE anagrafe_b.$ref_tabulato b
							SET b.datanasc='0000-00-00 00:00:00' 
							WHERE b.datanasc is null"
						);							
					}
					else
						DB::table("anagrafe.".$ref_tabulato)->insert($rowData);
				}
				else
					DB::table("fo_admin.test_import")->insert($rowData);
				unset($rowData);
			}
			


			if ($direct_pub==null && $test==false) {
				//1.2
			/*
				N.B.
				
				Il campo attivi e i campi sind_mens1-5, nello schema dei modelli di importazione, vengono proprio ignorati, quindi si può anche mettere una posizione ma non verrà tenuta conto perchè:
				- per gli up, il campo attivi verrà popolato con N a seconda se si è scelta la spunta 'storicizza' (altrimenti non saranno toccati)
				- per i new, verranno settati tutti attivi='S': nella query di inserimento in forma statica
				
				analogamente per i sind_mens da 1 a 4, gli up, non saranno toccati e varranno quelli di a, il sind_mens5 sia per gli up che per i new sarà ricalcolato come indicato (continua a leggere...)
				
				Ovviamente le importazioni di questi campi tornano utili quando si vuole caricare direttamente un CSV senza elaborazioni. In questo caso le posizioni sono quelle indicate nello schema (per questo tipo di operazione utilizzare lo schema standard 1)
			*/

			/*
				Inizialmente importo tutto in un archivio speculare riferito alla struttura.
				Questo perchè devo fare un raffronto con la vecchia pubblicazione e in base all'esistenza o meno di un nominativo+datanasc+ente, decido se inserire un nuovo record o aggiornare quello preesistente con determinate regole

				- se anagrafica esiste->sovrascrive tutta anagrafica e calcola SOLO sind_mens5,
				- se non esiste->crea tutto il record con il SOLO calcolo di sind_mens5

				N.B:
				Ci sarà poi una procedura a parte per l'aggiornamento massivo di fine anno per sind_mens per i vari shift.	
			*/

			/*
				Query di aggiornamento da tutti i campi b (appena importato) in a (vecchio tabulato)
				Attenzione:
				se nell'anagrafica nuova mancano i seguenti campi, vegono prelevati dall'anagrafica preesistente:
					->telefono (c1)
					->cf
			*/


				$num_e=1;
				$info_ente=explode(";",$enteweb);
				if (count($info_ente)>1) $num_e=2;
				if ($num_e==1) {
					DB::statement("
					UPDATE anagrafe_b.$ref_tabulato 
					SET ente='".$info_ente[0]."'"
					);				
				}				
					
				$campi_db=$this->campi_db();
				$campi_up=$campi_db['campi_up'];
				$up="";
				foreach($campi_up as $campo) {
					if (strlen($up)!=0) $up.=", "; 
					$up.="a.$campo=b.$campo";
				}
				$up.=",a.attivi='S'";



				//uso il campo settore per tener traccia di tutti i record che saranno interessati all'aggiornamento: sia nuovi che preesistenti...quindi inizializzo settore=''
				DB::statement("
					UPDATE anagrafe.$ref_tabulato set settore=''"
				);
				
				//aggiorna a da b, con i campi essenziali senza codfisc e telefoni, preservando gli originali di a se in b sono assenti
				DB::statement("
					UPDATE anagrafe.$ref_tabulato a 
					INNER JOIN anagrafe_b.$ref_tabulato b on a.nome = b.nome and a.datanasc=b.datanasc and a.ente=b.ente
					SET a.data='$data', a.settore='up',$up"
				);				
				
				
				//eredita da b i campi codfisc e telefono solo se sono presenti in b
				DB::statement("
					UPDATE anagrafe.$ref_tabulato a 
					INNER JOIN anagrafe_b.$ref_tabulato b on a.nome = b.nome and a.datanasc=b.datanasc and a.ente=b.ente
					SET a.c1=b.c1,a.codfisc=b.codfisc
					WHERE (b.c1 is not null and length(b.c1)<>0) and
					(b.codfisc is not null and length(b.codfisc)<>0)"
				);

				//eredita da b il campo codfisc solo se è presente in b
				DB::statement("
					UPDATE anagrafe.$ref_tabulato a 
					INNER JOIN anagrafe_b.$ref_tabulato b on a.nome = b.nome and a.datanasc=b.datanasc and a.ente=b.ente
					SET a.codfisc=b.codfisc
					WHERE b.codfisc is not null and length(b.codfisc)<>0"
				);

				//eredita da b il campo c1 (telefono) solo se è presente in b
				DB::statement("
					UPDATE anagrafe.$ref_tabulato a 
					INNER JOIN anagrafe_b.$ref_tabulato b on a.nome = b.nome and a.datanasc=b.datanasc and a.ente=b.ente
					SET a.c1=b.c1
					WHERE b.c1 is not null and length(b.c1)<>0"
				);


				
				//nuovi inserimenti: sind_mens5 statico!
				$campi_ins=$campi_db['campi_ins'];
				$ins2="";
				foreach($campi_ins as $campo) {
					if (strlen($ins2)!=0) $ins2.=", "; 
					$ins2.="b.$campo";
				}			
				$ins1=str_replace("b.","",$ins2);

				DB::statement("
					INSERT INTO anagrafe.$ref_tabulato ($ins1, attivi, settore, data)
					SELECT $ins2, 'S', 'new', '$data'		
					FROM anagrafe_b.$ref_tabulato b 
					LEFT OUTER JOIN anagrafe.$ref_tabulato a on a.nome = b.nome and a.datanasc=b.datanasc and a.ente=b.ente	
					WHERE a.nome is null"
				);

				/*
					per ogni settore='new' o 'up' aggiorno sind_mens5 con il nuovo valore
					sind_mens5
					- per new è sempre formato da asterischi (*) in corrispondenza di tutti i mesi dell'anno ad eccezione degli 'omini' segnalati nell'aggiornamento
					- per up la stringa deve essere ereditata da quella precedente e sostituita in mid solo dalle posizioni degli omini segnalati nell'aggiornamento
				*/					

				//procedura assegnazione sind_mens5 per i NEW
				$news = DB::table("anagrafe.".$ref_tabulato)
				->select('sindacato','id_anagr')
				->where('settore',"new")
				->get();
				
				$tot_new=0;
				foreach ($news as $new) {
					$tot_new++;
					$id_anagr=$new->id_anagr;
					$sindacato=$new->sindacato;
					$sind_mens5="0123456789ab";
					for ($sca=0;$sca<=11;$sca++) {
						$old=$sca;
						if ($sca==10) $old="a";
						if ($sca==11) $old="b";
						if (in_array($sca,$omini_sind)) 
							$sind_mens5=str_replace($old,$sindacato,$sind_mens5);
						else
							$sind_mens5=str_replace($old,"*",$sind_mens5);
					}
					$sind_mens5.=$anno_sind;
					
					
					$info=array();
					$info['sind_mens5']=$sind_mens5;
					DB::table('anagrafe.'.$ref_tabulato)
					->where('id_anagr',$id_anagr)
					->update($info);
				}
				
				//procedura assegnazione sind_mens5 per gli UP
				$ups = DB::table("anagrafe.".$ref_tabulato)
				->select('nome','sindacato','id_anagr','sind_mens5')
				->where('settore',"up")
				->get();
				
				$tot_up=0;
				foreach ($ups as $up) {
					$tot_up++;
					$nome=$up->nome;
					$id_anagr=$up->id_anagr;
					$sindacato=$up->sindacato;
					$sind_mens5=$up->sind_mens5;
					$pre_sind=$sind_mens5;		
					if ($sind_mens5==null || strlen($sind_mens5)==0) {
						$sind_mens5="0123456789ab";
						for ($sca=0;$sca<=11;$sca++) {
							$old=$sca;
							if ($sca==10) $old="a";
							if ($sca==11) $old="b";
							if (in_array($sca,$omini_sind)) 
								$sind_mens5=str_replace($old,$sindacato,$sind_mens5);
							else
								$sind_mens5=str_replace($old,"*",$sind_mens5);
						}
						$sind_mens5.=$anno_sind;						
					} else {
						
						$str="";
						for ($sca=0;$sca<=11;$sca++) {
							$sub=substr($sind_mens5,$sca,1);
							if (in_array($sca,$omini_sind)) $sub=$sindacato;
							$str.=$sub;
						}
						$sind_mens5=$str.$anno_sind;
					}
					$info=array();
					$info['sind_mens5']=$sind_mens5;
					DB::table('anagrafe.'.$ref_tabulato)
					->where('id_anagr',$id_anagr)
					->update($info);
				}
			


			}	

				//finalizzazione nuovi assunti RM (metodo alternativo:leggi sopra)
			if (strtoupper($ref_tabulato)=="T4_LAZI_A") {
				$info_ente=explode(";",$enteweb);
				for ($sca=0;$sca<=count($info_ente)-1;$sca++) {
					$ente_up=$info_ente[$sca];
						//setto a 1 tutto il tabulato dell'ente da pub
						DB::statement("UPDATE `anagrafe`.t4_lazi_a 
							SET `no_old_tab`=1 
							WHERE ente='$ente_up'");

						//setto a zero quelli in comune tra old e new
						DB::statement("UPDATE `anagrafe`.t4_lazi_a t
							INNER join `rm_office`.old_tabulato o ON t.codfisc=o.codfisc
							SET t.`no_old_tab`=0
							WHERE t.ente='$ente_up' and o.ente='$ente_up'");
						//i rimanenti 1 sono i nuovi assunti con metodo alternativo
				}	
			}
			
			DB::commit();
		} 
		catch (\Illuminate\Database\QueryException $ex) {
			
			$code=$ex->getCode();
			$message=$ex->getMessage();
			DB::rollBack();
		}		
		
		
		if (strlen($code)==0) {
			$up_periodi="";
			//se omini_sind==null -->ripubblicazione
			if ($omini_sind!=null) {
				for ($sca=0;$sca<=count($omini_sind)-1;$sca++) {
					$per=$omini_sind[$sca];
					$per++;
					if ($per<10) $per="0$per";
					if (strlen($up_periodi)!=0) $up_periodi.=";";
					$sind_ref_num="5"; //statico: sind_mens5
					$up_periodi.=$per."/".$anno_sind.$sind_ref_num;
				}		
				
				/*03.02.2023
				Eliminato perchè integrato nella procedura finale di consolidamento - script update_tab
				//Ricalcolo tutte le decorrenze desunte dai vari sind_mens da assegnare a infotab: leggi nota nel model tb_model->set_release()
				$info_ente=explode(";",$enteweb);
				for ($sca=0;$sca<=count($info_ente)-1;$sca++) {
					$ente_up=$info_ente[$sca];			
					$update_periodi=$tb_model->update_periodi($ref_tabulato,$up_periodi,$ente_up);
				}
				*/				

			}
			
			/*
			//per pubblicazione dbf -->vecchi tabulati filleaoffice desktop offline
			//con codice nativo PHP
			$db = dbase_open('prova.dbf', 2);
			if ($db) {
			  dbase_add_record($db, array(
				  'Maxim Topolov', 
				  ));   
			  dbase_close($db);
			}
			*/

			
			// tramite classe ad hoc scaricata con composer
			/*
			$dbf = Table::fromFile('prova.dbf');
			$dbf=new Table('prova.dbf');
			$record = new Record();
			$record->NOME = "nome";
			$dbf->insert($record);
			*/
			/*
			$dbf->insert([
				'NOME' => "-------------------------",

			]);
			*/
			

			$response=response()->json(['status'=>'true','message'=>"$j records importati"]);
		}
		else
			$response=response()->json(['status'=>'false','message'=>$code."-".$message]);		
		
		
		if ($repub_tab==false) {
			$new_f=uniqid();
			if ($test==false) {
				//salvataggio csv di input
				@copy("allegati/$ref_tabulato.csv","allegati/all_upload/$new_f.csv");
				@unlink("allegati/$ref_tabulato.csv");
			}
			
			$pubblicazione=$this->export_tab($ref_tabulato,"$new_f.csv",0);
			$info_up="";
			if($request->has('info_up')) $info_up=$request->get('info_up');
			if ($test==true) $info_up="TEST - ".$info_up;
			
			$id_user=Auth::user()->id;
			$log_events = new log_events;
			$log_events->id_user = $id_user;
			$log_events->operazione = $info_up;
			$log_events->esito = $code;
			$log_events->nome_file = $new_f;
			$log_events->ref_tabulato = $ref_tabulato;
			$log_events->num_record = $j;
			$log_events->tot_new = $tot_new;
			$log_events->tot_up = $tot_up;
			$log_events->save();			
		}
		
		$dati=array();
		if ($test==true) {	
			$dati = DB::table("fo_admin.test_import")
			->select('*')
			->orderByDesc('nome')
			->skip(0)
			->take(50)
			->get();
		}
		
		//cancellazione eventuale del file di supporto per l'assegnazione delle aziende
		@unlink("allegati/".$ref_tabulato."_aziende.csv");
		
		return view('import_csv')->with('enteweb',$enteweb)->with('ref_tabulato',$ref_tabulato)->with('response',$response)->with('test',$test)->with('dati',$dati)->with('comuni', $comuni);

	}
	



	public function export_tab($ref_tabulato,$new_f,$opz) {
        $filename =  public_path("allegati/pubblicazioni/$new_f");
        $handle = fopen($filename, 'w');
		$r=0;
		$db=DB::table('anagrafe.'.$ref_tabulato)
		->when($opz=="zz", function ($db) {			
			return $db->where('nome', "like","%zzz%");
		})		
		->orderBy('nome')
		->chunk(500, function($list) use ($handle,$r,$opz){
			foreach ($list as $row) {
				
				if ($r==0) {
					$arr=array();			
					$r=1;
					foreach ($row as $k=>$v) {
						$arr[]=$k;
					}
					fputcsv($handle, $arr);
				}
				$arr=array();			
				foreach ($row as $k=>$v) {
					//in caso di cript
					if ($opz=="1") {
						if (strtoupper($k)=="NOME") {
							$v=(chr(ord(substr($v,0,1))+1).chr(ord(substr($v,1,1))+2).chr(ord(substr($v,2,1))+3).chr(ord(substr($v,3,1))+4).chr(ord(substr($v,4,1))+5).chr(ord(substr($v,5,1))+6).chr(ord(substr($v,6,1))+7).chr(ord(substr($v,7,1))+8).chr(ord(substr($v,8,1))+9).chr(ord(substr($v,9,1))+10).chr(ord(substr($v,10,1))+9).chr(ord(substr($v,11,1))+8).chr(ord(substr($v,12,1))+7).chr(ord(substr($v,13,1))+6).chr(ord(substr($v,14,1))+5).chr(ord(substr($v,15,1))+4).chr(ord(substr($v,16,1))+3).chr(ord(substr($v,17,1))+2).chr(ord(substr($v,18,1))+1).chr(ord(substr($v,19,1))+2).chr(ord(substr($v,20,1))+3).chr(ord(substr($v,21,1))+4).chr(ord(substr($v,22,1))+5).chr(ord(substr($v,23,1))+6).chr(ord(substr($v,24,1))+7).chr(ord(substr($v,25,1))+8).chr(ord(substr($v,26,1))+9).chr(ord(substr($v,27,1))+10).chr(ord(substr($v,28,1))+9).chr(ord(substr($v,29,1))+8).chr(ord(substr($v,30,1))+7).chr(ord(substr($v,31,1))+6).chr(ord(substr($v,32,1))+5).chr(ord(substr($v,33,1))+4).chr(ord(substr($v,34,1))+3)).'*';
						}
					}
					
					$arr[]=$v;
				}
				fputcsv($handle, $arr);
			}
		});
		fclose($handle);
	}

	
	public function tracciato($file_json)  {
		// Read the JSON file 
		$json = file_get_contents("tracciati/$file_json");
		// Decode the JSON file
		$json_data = json_decode($json,true);
		return $json_data;
	}
	 
	 
	public function data_en($data) {
		$data=str_replace("/","-",$data);
		$data=str_replace(".","-",$data);
		$data=trim($data);
		
		$orig=$data;
		
		$data=$this->mesi($data);
		$data=substr($data,0,10);

		if (substr($data,2,1)=="-") {
			if (strlen($data)<10) {
				$anno=substr($data,6,2);
				$y=date("Y");$y=trim($y);
				$y=substr($y,2);$y=intval($y);
				if (intval($anno)<=$y) $anno="20$anno";
				else $anno="19$anno";
			}	
			else
				$anno=substr($data,6,4);
			$mese=substr($data,3,2);
			$giorno=substr($data,0,2);
			
			$data="$anno-$mese-$giorno";
		}
		
		
		if (preg_match("/\d{4}\-\d{2}-\d{2}/", $data)) {
			$data.=" 00:00:00";
			return $data;
		} else {
			return null;
		}		
	}
	
	public function mesi($data) {
		$data=strtolower($data);
		$data=str_replace("january","01",$data);
		$data=str_replace("jan","01",$data);
		$data=str_replace("february","02",$data);
		$data=str_replace("march","03",$data);
		$data=str_replace("april","04",$data);
		$data=str_replace("may","05",$data);
		$data=str_replace("june","06",$data);
		$data=str_replace("july","06",$data);
		$data=str_replace("august","08",$data);
		$data=str_replace("aug","08",$data);
		$data=str_replace("september","09",$data);
		$data=str_replace("sept","09",$data);
		$data=str_replace("october","10",$data);
		$data=str_replace("oct","10",$data);
		$data=str_replace("november","11",$data);
		$data=str_replace("december","12",$data);
		$data=str_replace("dec","12",$data);


		$data=str_replace("gen","01",$data);
		$data=str_replace("feb","02",$data);
		$data=str_replace("mar","03",$data);
		$data=str_replace("apr","04",$data);
		$data=str_replace("mag","05",$data);
		$data=str_replace("giu","06",$data);
		$data=str_replace("lug","07",$data);
		$data=str_replace("ago","08",$data);
		$data=str_replace("set","09",$data);
		$data=str_replace("ott","10",$data);
		$data=str_replace("nov","11",$data);
		$data=str_replace("dic","12",$data);
		
		
		
		return $data;
	}
	
	public function campi_db() {
			$ente_up="";
			

			$campi_up=array('NOME','VIA','CAP','LOC','PRO','DATANASC','COMUNENASC',	'DATAASSU','DATALICE','SINDACATO','COMPSIND','DATASIND','NUMAPE','DATAPE','CODINPS','ENTE','DENOM','PROVINCIA','PERIODO','PRESENTI','LOCAZIENDA','VIAZIENDA', 'C2');
			$campi['campi_up']=$campi_up;

			$campi_ins=array('NOME','VIA','CAP','LOC','PRO','DATANASC','COMUNENASC','DATAASSU','DATALICE','SINDACATO','COMPSIND','DATASIND','NUMAPE','DATAPE','CODFISC','CODINPS','ENTE','DENOM','PROVINCIA','PERIODO','PRESENTI','LOCAZIENDA','VIAZIENDA', 'C1','C2');
			$campi['campi_ins']=$campi_ins;
			return $campi;
	}
	 
	 
}
