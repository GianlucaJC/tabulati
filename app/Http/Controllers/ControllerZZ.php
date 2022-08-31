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
		
		$info_azienda=new azienda;
		
		$ref_tabulato=null;
		if($request->has('ref_tabulato')) $ref_tabulato=$request->input('ref_tabulato');
		else return redirect('step2');
		
		$enteweb=null;
		if($request->has('enteweb')) $enteweb=$request->input('enteweb');

		$formula_ni="";
		if($request->has('formula_ni')) $formula_ni=$request->input('formula_ni');
		$formula_npsec="";
		if($request->has('formula_npsec')) $formula_npsec=$request->input('	formula_npsec');
		if (strlen($formula_ni)==0 && strlen($formula_npsec)==0) {
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
		
		$pos_azienda=-1;
		$no_azienda=false;
		if (isset($map_campi['AZIENDA'])) $pos_azienda=$map_campi['AZIENDA'];
		else $no_azienda=true;
		
		$pos_a=null;$pos_n=null;$pos_0=null;$pos_1=null;$pos_2=null;$pos_3=null;
		if (isset($map_campi['A'])) $pos_a=$map_campi['A'];
		if (isset($map_campi['N'])) $pos_n=$map_campi['N'];
		if (isset($map_campi['0'])) $pos_0=$map_campi['0'];
		if (isset($map_campi['1'])) $pos_1=$map_campi['1'];
		if (isset($map_campi['2'])) $pos_2=$map_campi['2'];
		if (isset($map_campi['3'])) $pos_3=$map_campi['3'];

		if ($pos_a==null && $pos_n==null && $pos_0==null &&  $pos_1==null &&  $pos_2==null && $pos_3==null) {
			$response=response()->json(['status'=>'false','message'=>"Non risultano colonne definite per addetti, non iscritti, etc. (utilizzare A,N,0,1,2,3)"]);
			return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
		}
		
		if ($no_azienda==true) {
			$response=response()->json(['status'=>'false','message'=>"Colonna Azienda non definita (utilizzare lettera 'A')"]);
			return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
		}		
		
		
		print_r($_POST);
		echo "<hr>";
		echo "enteweb $enteweb<br>";
		print_r($map_campi);
		echo "<br><br>";
		print_r($importData_arr);
		echo "<hr>";

		$posizioni=array();
		$posizioni['pos_a']=$pos_a;
		$posizioni['pos_n']=$pos_n;
		$posizioni['pos_0']=$pos_0;
		$posizioni['pos_1']=$pos_1;
		$posizioni['pos_2']=$pos_2;
		$posizioni['pos_3']=$pos_3;
		
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
		if (strlen($formula_npsec)!=0) {
			$analisi_formula_nspec=$this->analisi_formula($formula_npsec,"Formula Non Specificati",$posizioni);
			if ($analisi_formula_nspec['esito']=="KO") {
				$message=$analisi_formula_nspec['message'];
				$response=response()->json(['status'=>'false','message'=>$message]);
				return view('import_zz')->with('enteweb',$enteweb,)->with('ref_tabulato',$ref_tabulato)->with('response',$response);
			}
			$operatori_nspec=$analisi_formula_ni['operatori'];
			$addendi_npec=$analisi_formula_ni['addendi'];
		}
		echo "<hr>";
		print_r($operatori_ni);
		echo "<br><br>";
		print_r($addendi_ni);
		echo "<hr>";

		

		foreach ($importData_arr as $importData) {
			$azienda=$importData[$pos_azienda];
			$num_ni_richiesti=$this->calcolo_zz($importData,$operatori_ni,$addendi_ni,$posizioni);
			$num_nspec_richiesti=$this->calcolo_zz($importData,$operatori_ni,$addendi_ni,$posizioni);
			
			$num_attivi_zz=$info_azienda->num_attivi_zz($enteweb,$ref_tabulato,$azienda,$num_ni_richiesti,$num_nspec_richiesti);
		}

		
				
	}	
	
	public function calcolo_zz($arr,$operatori,$addendi,$posizioni) {
		$pos_a=$posizioni['pos_a'];
		$pos_n=$posizioni['pos_n'];
		$pos_0=$posizioni['pos_0'];
		$pos_1=$posizioni['pos_1'];
		$pos_2=$posizioni['pos_2'];
		$pos_3=$posizioni['pos_3'];
		
		$operatore="";
		$addendo=$addendi[0];
		$pos="pos_".strtolower($addendo);
		
		$res=$arr[$$pos];
		
		for ($sca=1;$sca<=count($addendi)-1;$sca++) {
			if (strlen($operatore)!=0) {
				$pos="pos_".strtolower($addendo);
				$cur=$arr[$$pos];
				if ($operatore=="+") $res+=$cur;
				if ($operatore=="-") $res-=$cur;
			}
			if (isset($operatori[$sca-1])) $operatore=$operatori[$sca-1];
			$addendo=$addendi[$sca];
		}
		if (strlen($operatore)!=0) {
			$pos="pos_".strtolower($addendo);
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
			if (!isset($$pos) || $$pos==null) {
				$esito="KO";
				$message="Nella $from hai definito la colonna $addendo ma nel file CSV non hai definito la colonna relativa";
				break;
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
		$infotab=new infotab;		
		$sele_x="";
		if($request->has('sele_x')) $sele_x=$request->input('sele_x');

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
