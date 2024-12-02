<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class infotab extends Model
{
    use HasFactory;
	
	public function reports($from=1,$tb="") {
		$group="f.id";
		
		if ($from==1) $group="i.id";
		
		$oper=">=";
		if (strlen($tb)!=0) $oper="=";
		else $tb="0";
		
		$table="report.infotab as i";
		
		$resp = DB::table($table)
		->join('online.fo_argo as f', 'i.TB', '=', 'f.id_arch')
		->select('i.id','i.TB','f.descr_ce', 'f.code_CE', 'f.decorrenza_tab', 'f.fine_tab','i.sigla_pr','i.denominazione','i.descr1','i.descr2','i.descr3','i.descr4','i.descr5')
		->where('i.TB',$oper,"$tb")
		->where('i.reserved','=',0)
		->orderBy('i.denominazione')
		->groupBy($group)
		->get();
		/*
			$resp=array();
			foreach ($r1 as $r) {
				$resp[]=$r;
			}

			$resp[]=(object) array(
				'id' => '1000',
				'TB'=>"t4_cala_a",
				'descr_ce'=>"TEST - Crotone:dismesso",
				'code_CE'=>"C",
				'decorrenza_tab'=>"2000-01-01",
				'fine_tab'=>"2000-01-01",
				'sigla_pr'=>"TT",
				'denominazione'=>"Cassa Edile di TEST - Crotone:dismesso",
				'descr1'=>"Cdescr1",
				'descr2'=>"Xdescr2",
				'descr3'=>"Xdescr3",
				'descr4'=>"Xdescr4",
				'descr5'=>"Xdescr5"
			);
		*/


	

		return $resp;
	}		

	
	public function rilasci_ente($ente,$ref_tabulato) {
		$table="online.fo_argo as f";
		$resp = DB::table($table)
		->select('f.rilasci_tabulato')
		->where('f.id_arch','=',$ref_tabulato)
		->where('f.code_CE','=',$ente)
		->get();
		return $resp;		
	}
	
	public function check_nuovo_anno($ref_tabulato) {
		$anno=date("Y");
//$anno="2025";
		$table="online.fo_argo as f";
		$resp = DB::table($table)
		->select('f.rilasci_tabulato')
		->where('f.rilasci_tabulato','like',"%$anno%")
		->count();
		if ($resp>0) return true;
		else return false;			
	}

	public function detail_tab($tb,$ente){
		$table="report.infotab as i";
		$resp = DB::table($table)
		->join('online.fo_argo as f', 'i.TB', '=', 'f.id_arch')
		->select('f.descr_ce', 'i.denominazione')
		->where('f.code_CE',"=","$ente")
		->where('i.TB',"=","$tb")
		->get();
		return $resp;
		
	}
	

	
}
