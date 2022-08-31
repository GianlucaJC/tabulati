<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class azienda extends Model
{
    use HasFactory;
	
	public function num_attivi_zz($ente,$ref_tabulato,$denom) {		
		$resp = DB::table("anagrafe.".$ref_tabulato)
		->select('*')
		->where('ente','=',$ente)
		->where('attivi','=',"S")
		->where('denom','=',$denom)
		->count();
		return $resp;		
	}	
}
