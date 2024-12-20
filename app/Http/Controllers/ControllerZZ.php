<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;
use App\Models\schema_import;
use App\Models\infotab;
use App\Models\azienda;

use DB;

class ControllerZZ extends Controller
{
	
	public function step_zz2(Request $request) {
		ini_set('memory_limit', -1);
		ini_set('max_execution_time', '-1');
		
		$db_azienda=new azienda;
		$infotab=new infotab;
		
		$ref_tabulato=null;
		if($request->has('ref_tabulato')) $ref_tabulato=$request->input('ref_tabulato');
		else return redirect('step2');
		
		
		$reports=$infotab->reports(0,$ref_tabulato);

		$table="anagrafe.$ref_tabulato";
		
		//inizializzazione campo settore
		$info=array();
		$info['settore']="";
		DB::table($table)->update($info);
		///////
		
		$last_zz=$db_azienda->last_zz($ref_tabulato);
		
		$provincia=$reports[0]->denominazione;
		$time=date("H:i:s");
		echo "$provincia - Operazione iniziata alle $time<br><br>";
		
		$enteweb=null;
		if($request->has('enteweb')) $enteweb=$request->input('enteweb');

		$formula_ni="";
		if($request->has('formula_ni')) $formula_ni=$request->input('formula_ni');
		$formula_nspec="";
		if($request->has('formula_nspec')) $formula_nspec=$request->input('formula_nspec');		
		$formula_filca="";
		if($request->has('formula_filca')) $formula_filca=$request->input('formula_filca');
		$formula_feneal="";
		if($request->has('formula_feneal')) $formula_feneal=$request->input('formula_feneal');

		
		if (strlen($formula_ni)==0 && strlen($formula_nspec)==0 && strlen($formula_filca)==0 && strlen($formula_feneal)==0) {
			$response=response()->json(['status'=>'false','message'=>"Impostare almeno una formula"]);
			return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
		}
	
		$omini_sind=null;
		if($request->has('omini_sind')) $omini_sind=$request->input('omini_sind');
		
		if ($omini_sind==null) $mese_sind=date("m");
		else $mese_sind=max($omini_sind)+1;
		$mese_sind=trim($mese_sind);
		if (strlen($mese_sind)==1) $mese_sind="0$mese_sind";
		
		$anno_sind=date("Y");
		if($request->has('anno_sind')) $anno_sind=$request->input('anno_sind');
		
		$data=trim($anno_sind)."-".$mese_sind."-01 00:00:00";
		
		
		$filepath="allegati/".$ref_tabulato."_zz.csv";
		if (!file_exists($filepath)) {
			$response=response()->json(['status'=>'false','message'=>"404-File di input non trovato"]);
			return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
		}

		
		$file = fopen($filepath, "r");
		$importData_arr = array();
		$i = 0;
		$map_campi=array();
		//Importazione grezza di tutti i campi del csv in un array di comodo
		while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
			$num = count($filedata);
			
			if ($i == 0) {
				$i++;
				for ($c = 0; $c < $num; $c++) {
					$indice=strtoupper($filedata[$c]);
					$map_campi[$indice]=$c;
				}

				continue;
			} 

			for ($c = 0; $c < $num; $c++) {
				$importData_arr[$i][] = $filedata[$c];
			}
			$i++;
		}
		fclose($file);
		
		$pos_azienda=-1;
		$no_azienda=false;
		if (isset($map_campi['AZIENDA'])) $pos_azienda=$map_campi['AZIENDA'];
		else $no_azienda=true;

		$pos_loc_azienda=-1;
		if (isset($map_campi['LOCAZIENDA'])) $pos_loc_azienda=$map_campi['LOCAZIENDA'];
		$pos_via_azienda=-1;
		if (isset($map_campi['VIAZIENDA'])) $pos_via_azienda=$map_campi['VIAZIENDA'];
		$pos_p_iva=-1;
		if (isset($map_campi['C2'])) $pos_p_iva=$map_campi['C2'];
		$pos_telazi=-1;
		if (isset($map_campi['C3'])) $pos_telazi=$map_campi['C3'];

		
		$pos_a=null;$pos_n=null;$pos_0=null;$pos_1=null;$pos_2=null;$pos_3=null;
		$pos_ta=null;$pos_tn=null;$pos_t0=null;$pos_t1=null;$pos_t2=null;$pos_t3=null;
		
		if (isset($map_campi['A'])) $pos_a=$map_campi['A'];
		if (isset($map_campi['N'])) $pos_n=$map_campi['N'];
		if (isset($map_campi['0'])) $pos_0=$map_campi['0'];
		if (isset($map_campi['1'])) $pos_1=$map_campi['1'];
		if (isset($map_campi['2'])) $pos_2=$map_campi['2'];
		if (isset($map_campi['3'])) $pos_3=$map_campi['3'];

		if (isset($map_campi['TA'])) $pos_ta=$map_campi['TA'];
		if (isset($map_campi['TN'])) $pos_tn=$map_campi['TN'];
		if (isset($map_campi['T0'])) $pos_t0=$map_campi['T0'];
		if (isset($map_campi['T1'])) $pos_t1=$map_campi['T1'];
		if (isset($map_campi['T2'])) $pos_t2=$map_campi['T2'];
		if (isset($map_campi['T3'])) $pos_t3=$map_campi['T3'];

		if ($pos_a==null && $pos_n==null && $pos_0==null &&  $pos_1==null &&  $pos_2==null && $pos_3==null && $pos_ta==null && $pos_tn==null && $pos_t0==null &&  $pos_t1==null &&  $pos_t2==null && $pos_t3==null) {
			$response=response()->json(['status'=>'false','message'=>"Non risultano colonne definite per addetti, non iscritti, etc. (utilizzare A,N,0,1,2,3)"]);
			return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
		}
		
		if ($no_azienda==true) {
			$response=response()->json(['status'=>'false','message'=>"Colonna Azienda non definita (utilizzare alias 'AZIENDA')"]);
			return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
		}		

		
		$posizioni=array();
		$posizioni['pos_a']=$pos_a;
		$posizioni['pos_n']=$pos_n;
		$posizioni['pos_0']=$pos_0;
		$posizioni['pos_1']=$pos_1;
		$posizioni['pos_2']=$pos_2;
		$posizioni['pos_3']=$pos_3;
		$posizioni['pos_ta']=$pos_ta;
		$posizioni['pos_tn']=$pos_tn;
		$posizioni['pos_t0']=$pos_t0;
		$posizioni['pos_t1']=$pos_t1;
		$posizioni['pos_t2']=$pos_t2;
		$posizioni['pos_t3']=$pos_t3;

		
		$operatori_ni=null;$addendi_ni=null;
		if (strlen($formula_ni)!=0) {
			$analisi_formula_ni=$this->analisi_formula($formula_ni,"Formula Non Iscritti",$posizioni);
			if ($analisi_formula_ni['esito']=="KO") {
				$message=$analisi_formula_ni['message'];
				$response=response()->json(['status'=>'false','message'=>$message]);
				return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
			}
			$operatori_ni=$analisi_formula_ni['operatori'];
			$addendi_ni=$analisi_formula_ni['addendi'];
		}
		
		$operatori_nspec=null;$addendi_npec=null;
		
		if (strlen($formula_nspec)!=0) {
			$analisi_formula_nspec=$this->analisi_formula($formula_nspec,"Formula Non Specificati",$posizioni);
			if ($analisi_formula_nspec['esito']=="KO") {
				$message=$analisi_formula_nspec['message'];
				$response=response()->json(['status'=>'false','message'=>$message]);
				return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
			}
			$operatori_nspec=$analisi_formula_nspec['operatori'];
			$addendi_npec=$analisi_formula_nspec['addendi'];
		}
		
		$operatori_filca=null;$addendi_filca=null;
		
		if (strlen($formula_filca)!=0) {
			$analisi_formula_filca=$this->analisi_formula($formula_filca,"Formula Filca",$posizioni);
			if ($analisi_formula_filca['esito']=="KO") {
				$message=$analisi_formula_filca['message'];
				$response=response()->json(['status'=>'false','message'=>$message]);
				return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
			}
			$operatori_filca=$analisi_formula_filca['operatori'];
			$addendi_filca=$analisi_formula_filca['addendi'];
		}
		
		$operatori_feneal=null;$addendi_feneal=null;
		if (strlen($formula_feneal)!=0) {
			$analisi_formula_feneal=$this->analisi_formula($formula_feneal,"Formula Feneal",$posizioni);
			if ($analisi_formula_feneal['esito']=="KO") {
				$message=$analisi_formula_feneal['message'];
				$response=response()->json(['status'=>'false','message'=>$message]);
				return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
			}
			$operatori_feneal=$analisi_formula_feneal['operatori'];
			$addendi_feneal=$analisi_formula_feneal['addendi'];
		}

		//inizializzazione tutti non attivi
		$info=array();
		$info['attivi']="N";
		DB::table("anagrafe.".$ref_tabulato)
		->where('ente','=',$enteweb)
		->where('nome','like',"%ZZZZ%")
		->update($info);		

		
		echo "<h2>";
			echo "Totale Aziende da elaborare: <b>".count($importData_arr)."</b>";
		echo "</h2>";
		$cont=1;
		$tot_up_ni=0;
		$tot_new_ni=0;

		$tot_new_spec=0;
		$tot_up_nspec=0;
		
		$tot_new_filca=0;
		$tot_up_filca=0;

		$tot_new_feneal=0;
		$tot_up_feneal=0;


		
		
		foreach ($importData_arr as $importData) {
			$azienda=$importData[$pos_azienda];
			if (strlen($azienda)==0) continue;
			$loc_azienda="";$via_azienda="";$p_iva="";$telazi="";
			if ($pos_loc_azienda!=-1) $loc_azienda=$importData[$pos_loc_azienda];
			if ($pos_via_azienda!=-1) $via_azienda=$importData[$pos_via_azienda];
			if ($pos_p_iva!=-1) $p_iva=$importData[$pos_p_iva];
			if ($pos_telazi!=-1) $telazi=$importData[$pos_telazi];
			
			$info_azienda['enteweb']=$enteweb;
			$info_azienda['ref_tabulato']=$ref_tabulato;
			$info_azienda['azienda']=$azienda;
			$info_azienda['loc_azienda']=$loc_azienda;
			$info_azienda['via_azienda']=$via_azienda;
			$info_azienda['p_iva']=$p_iva;
			$info_azienda['telazi']=$telazi;
			$num_ni_richiesti=0;
			$num_nspec_richiesti=0;
			$num_filca_richiesti=0;
			$num_feneal_richiesti=0;
			if (strlen($formula_ni)!=0) {
				$num_ni_richiesti=$this->calcolo_zz($info_azienda,$importData,$operatori_ni,$addendi_ni,$posizioni);
			}
			
			if (strlen($formula_nspec)!=0) {
				$num_nspec_richiesti=$this->calcolo_zz($info_azienda,$importData,$operatori_nspec,$addendi_npec,$posizioni);

			}

			if (strlen($formula_filca)!=0) {
				$num_filca_richiesti=$this->calcolo_zz($info_azienda,$importData,$operatori_filca,$addendi_filca,$posizioni);

			}

			if (strlen($formula_feneal)!=0) {
				$num_feneal_richiesti=$this->calcolo_zz($info_azienda,$importData,$operatori_feneal,$addendi_feneal,$posizioni);

			}


			//
			$set_zz=$db_azienda->set_zz($provincia,$omini_sind,$anno_sind,$mese_sind,$last_zz,$info_azienda,$num_ni_richiesti,$num_nspec_richiesti,$num_filca_richiesti,$num_feneal_richiesti);
			$last_zz=$set_zz['last_zz'];
			
			$num_garantito_ni=$set_zz['num_garantito_ni'];
			$num_ins_ni=$set_zz['num_ins_ni'];
			$num_garantito_nspec=$set_zz['num_garantito_nspec'];
			$num_ins_nspec=$set_zz['num_ins_nspec'];
			$num_garantito_filca=$set_zz['num_garantito_filca'];
			$num_ins_filca=$set_zz['num_ins_filca'];
			$num_garantito_feneal=$set_zz['num_garantito_feneal'];
			$num_ins_feneal=$set_zz['num_ins_feneal'];

			
			$tot_up_ni+=$num_garantito_ni;
			$tot_new_ni+=$num_ins_ni;
			$tot_up_nspec+=$num_garantito_nspec;
			$tot_new_spec+=$num_ins_nspec;

			$tot_up_filca+=$num_garantito_filca;
			$tot_new_filca+=$num_ins_filca;

			$tot_up_feneal+=$num_garantito_feneal;
			$tot_new_feneal+=$num_ins_feneal;			

			
			echo "<hr><h4>$cont) $azienda</h4>";
			if ($num_ni_richiesti!=0) {
				echo "<u>NON Iscritti</u>: Richiesti <b>$num_ni_richiesti</b> Numero garantito <b>$num_garantito_ni</b>";
				if ($num_ins_ni!=0)
					echo "  (<font color='red'>".$num_ins_ni." --- INSERT</font>)";
				echo "<br>";
			}				
			if ($num_nspec_richiesti!=0) {
				echo "<u>NON Specificati</u>:  Richiesti <b>$num_nspec_richiesti</b> Numero garantito <b>$num_garantito_nspec</b>";
				if ($num_ins_nspec!=0)
					echo "  (<font color='red'>".$num_ins_nspec." --- INSERT</font>)";
				echo "<br>";
			}				

			if ($num_filca_richiesti!=0) {
				echo "<u>Filca</u>:  Richiesti <b>$num_filca_richiesti</b> Numero garantito <b>$num_garantito_filca</b>";
				if ($num_ins_filca!=0)
					echo "  (<font color='red'>".$num_ins_filca." --- INSERT</font>)";
				echo "<br>";
			}	

			if ($num_feneal_richiesti!=0) {
				echo "<u>Feneal</u>:  Richiesti <b>$num_feneal_richiesti</b> Numero garantito <b>$num_garantito_feneal</b>";
				if ($num_ins_feneal!=0)
					echo "  (<font color='red'>".$num_ins_feneal." --- INSERT</font>)";
				echo "<br>";
			}	
			
			if ($cont/50==intval($cont/50)) {
				echo str_repeat(" ", 500);
				ob_flush();
				flush();
			}

			$cont++;
		}
		$time=date("H:i:s");
		echo "<hr><br><br><br><br>";
		echo "Totali Aggiornati Non Iscritti: <b>$tot_up_ni</b><br>";
		echo "Totali New Non Iscritti: <b>$tot_new_ni</b><br>";
		echo "Totali Aggiornati Non Specificati: <b>$tot_up_nspec</b><br>";
		echo "Totali New Non Specificati: <b>$tot_new_spec</b><br>";
		echo "Totali Aggiornati Filca: <b>$tot_up_filca</b><br>";
		echo "Totali New Filca: <b>$tot_new_filca</b><br>";
		echo "Totali Aggiornati Feneal: <b>$tot_up_feneal</b><br>";
		echo "Totali New Feneal: <b>$tot_new_feneal</b><br>";

		
		echo "<h3>Procedura completata ($time)!";


	 $notifs = "&notifiche=S";
	 $notifn = "&notifiche=N";
	 $token = "cc1055abc7bd9883721a075066b8ced1";
	 $locale=0;
	 $pre_url="https://www.filleaoffice.it/";
	 
	 
	 if ($locale==1) {
		$pre_url="http://localhost://";
	 } 
	 
    
	
     $url1=$pre_url."sintel/index.php?token=$token&enteweb=$enteweb&tab_agg=$ref_tabulato".$notifn;
     $url2=$pre_url."FO/update_tab/update.php?enteweb=$enteweb&tab_agg=$ref_tabulato".$notifn;
	 
	 
	 echo "<hr>";
	 echo "<a href='$url1' target='_blank'>Avvia prima script servizi e poi Script 
	 FINALE - SENZA NOTIFICHE</a><br><br>";
	 echo "<a href='$url2' target='_blank'>Avvia SOLO Script FINALE - SENZA NOTIFICHE</a>";

     $url1=$pre_url."sintel/index.php?token=$token&enteweb=$enteweb&tab_agg=$ref_tabulato".$notifs;
     $url2=$pre_url."FO/update_tab/update.php?enteweb=$enteweb&tab_agg=$ref_tabulato".$notifs;
	 
	 echo "<hr>";
	 echo "<a href='$url1' target='_blank'>Avvia prima script servizi e poi Script 
	 FINALE - CON NOTIFICHE</a><br><br>";
	 echo "<a href='$url2' target='_blank'>Avvia SOLO Script FINALE - CON NOTIFICHE</a>";
	 
	}
	public function calcolo_zz($info_azienda,$arr,$operatori,$addendi,$posizioni) {
		$db_azienda=new azienda;
		
		$enteweb=$info_azienda['enteweb'];
		$ref_tabulato=$info_azienda['ref_tabulato'];
		$azienda=$info_azienda['azienda'];
		
		$pos_a=$posizioni['pos_a'];
		$pos_n=$posizioni['pos_n'];
		$pos_0=$posizioni['pos_0'];
		$pos_1=$posizioni['pos_1'];
		$pos_2=$posizioni['pos_2'];
		$pos_3=$posizioni['pos_3'];
		$pos_ta=$posizioni['pos_ta'];
		$pos_tn=$posizioni['pos_tn'];
		$pos_t0=$posizioni['pos_t0'];
		$pos_t1=$posizioni['pos_t1'];
		$pos_t2=$posizioni['pos_t2'];
		$pos_t3=$posizioni['pos_t3'];
		
		$operatore="";
		$addendo=$addendi[0];
		$pos="pos_".strtolower($addendo);


		if ($pos=="pos_ta" || $pos=="pos_tn" || $pos=="pos_t0" || $pos=="pos_t1" || $pos=="pos_t2" || $pos=="pos_t3") {
			//calcolo dati dal DB e non dal CSV
			$res=$db_azienda->get_num($info_azienda,$pos);
		}
		else		
			$res=$arr[$$pos];
		
	
		for ($sca=1;$sca<=count($addendi)-1;$sca++) {
			if (strlen($operatore)!=0) {
				$pos="pos_".strtolower($addendo);
				if ($pos=="pos_ta" || $pos=="pos_tn" || $pos=="pos_t0" || $pos=="pos_t1" || $pos=="pos_t2" || $pos=="pos_t3") {
					//calcolo dati dal DB e non dal CSV
					$cur=$db_azienda->get_num($info_azienda,$pos);
				}
				else
					$cur=$arr[$$pos];
				if ($operatore=="+") $res+=$cur;
				if ($operatore=="-") $res-=$cur;
			}
			if (isset($operatori[$sca-1])) $operatore=$operatori[$sca-1];
			$addendo=$addendi[$sca];
		}
		if (strlen($operatore)!=0) {
			$pos="pos_".strtolower($addendo);
			if ($pos=="pos_ta" || $pos=="pos_tn" || $pos=="pos_t0" || $pos=="pos_t1" || $pos=="pos_t2" || $pos=="pos_t3") {
				//calcolo dati dal DB e non dal CSV
				$cur=$db_azienda->get_num($info_azienda,$pos);
			} else
				$cur=$arr[$$pos];
			
			if ($operatore=="+") $res+=$cur;
			if ($operatore=="-") $res-=$cur;
		}		
		return $res;
	}
	
	public function analisi_formula($formula,$from,$posizioni) {
		$pos_a=$posizioni['pos_a'];
		$pos_n=$posizioni['pos_n'];
		$pos_0=$posizioni['pos_0'];
		$pos_1=$posizioni['pos_1'];
		$pos_2=$posizioni['pos_2'];
		$pos_3=$posizioni['pos_3'];
		$pos_ta=$posizioni['pos_ta'];
		$pos_tn=$posizioni['pos_tn'];
		$pos_t0=$posizioni['pos_t0'];
		$pos_t1=$posizioni['pos_t1'];
		$pos_t2=$posizioni['pos_t2'];
		$pos_t3=$posizioni['pos_t3'];

		
		
		
		$char="";
		$operatori=array();
		$fx=str_replace("+","|",$formula);
		$fx=str_replace("-","|",$fx);
		$fx=str_replace("*","|",$fx);
		$fx=str_replace("/","|",$fx);
		$addendi=explode("|",$fx);
		$esito="OK";$message="";
		for ($sca=0;$sca<=count($addendi)-1;$sca++) {
			$addendo=$addendi[$sca];
			$pos="pos_".strtolower($addendo);
			if (!($pos=="pos_ta" || $pos=="pos_tn" || $pos=="pos_t0" || $pos=="pos_t1" || $pos=="pos_t2" || $pos=="pos_t3")) {
				if (!isset($$pos) || $$pos==null) {
					$esito="KO";
					$message="Nella $from hai definito la colonna $addendo ma nel file CSV non hai definito la colonna relativa";
					break;
				}
			}
		}

		if (strlen($formula)!=0) {
			for ($sc=0;$sc<=strlen($formula)-1;$sc++) {
				$char=$formula[$sc];
				if ($char=="+" || $char=="-" || $char=="*" || $char=="/") $operatori[]=$char;
				
			}
		}	
		$resp=array();
		$resp['esito']=$esito;
		$resp['message']=$message;
		$resp['operatori']=$operatori;
		$resp['addendi']=$addendi;
		return $resp;
	}
	
	public function step_zz1(Request $request) {
		$today=date("Y-m-d");



		$infotab=new infotab;		
		$sele_x="";
		if($request->has('sele_x')) $sele_x=$request->input('sele_x');
		else return redirect('step2');		
		
		
		$detail_tab="";
		$req=$request->get('sele_x');
		$ref_tabulato="";$enteweb="";
		for ($sca=0;$sca<=count($req)-1;$sca++) {
			$info=$req[$sca];
			$arr_info=explode("-",$info);
			$tb=$arr_info[0];$ente=$arr_info[1];
			if (strlen($enteweb)!=0) $enteweb.=";";
			$enteweb.=$ente;
			if ($sca==0) $ref_tabulato=$tb;
			if (strlen($detail_tab)==0) $detail_tab.="<hr>";
			$detail=$infotab->detail_tab($tb,$ente);
			$ref_pub[$detail[0]->descr_ce]=$detail[0]->denominazione;
		}


		return view('step_zz1')->with('sele_x',$sele_x)->with('ref_tabulato',$ref_tabulato)->with('enteweb',$enteweb)->with('ref_pub',$ref_pub);
	 }




}
