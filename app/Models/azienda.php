<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class azienda extends Model
{
    use HasFactory;
	
	public function num_attivi_zz($ente,$ref_tabulato,$denom,$num_ni_richiesti,$num_nspec_r) {
		if ($num_ni_richiesti!=0) {
			$info=array();
			$info['attivi']="N";
			//rende tutti gli zzz dell'azienda specificata ed ente e non iscritti: NON attivi
			DB::table("anagrafe.".$ref_tabulato)
			->where('ente','=',$ente)
			->where('nome','like',"%ZZZZ%")
			->where('denom','=',$denom)
			->where('sindacato','=',"0")
			->update($info);		
			
			//conteggio attuale NI
			$resp = DB::table("anagrafe.".$ref_tabulato)
			->select('*')
			->where('ente','=',$ente)
			->where('nome','like',"%ZZZZ%")
			->where('denom','=',$denom)
			->where('sindacato','=',"0")
			->count();
			
			if ($num_ni_richiesti<$resp) {
				//esegue l'update per differenza
			}
		}
		
		//se sono insufficienti 
		
		
		return $resp;		
	}	
}
