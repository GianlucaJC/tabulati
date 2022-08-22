<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class argo_comuni extends Model
{
    use HasFactory;
	public static function comuni() {
		$argo=DB::table('online.argo_comuni')
		->select('id', 'nome', 'pr', 'cap')
		->whereRaw('LENGTH(pr) > ?', [0])
		->get();
		$resp=array();
		foreach ($argo as $comuni) {
			$nome=$comuni->nome;
			$cap=$comuni->cap;
			$pr=$comuni->pr;
			$resp['cap'][$cap]=$nome;
			$resp['comuni'][$nome]=$cap;
			$resp['cap_pro'][$cap]=$pr;
		}
		return $resp;		
	}	
}
