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
		->where('i.reserved','=',0)
		->where('i.TB',$oper,"$tb")
		->orderBy('i.denominazione')
		->groupBy($group)
		->get();
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
