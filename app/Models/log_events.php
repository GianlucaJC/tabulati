<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class log_events extends Model
{
    use HasFactory;
	
	
	public static function lista_tab($ref_tab="") {
		$resp = log_events::select('id', 'operazione', 'created_at', 'esito', 'nome_file', 'ref_tabulato', 'num_record', 'tot_new', 'tot_up')
		->orderByDesc('created_at')
		->skip(0)
		->take(200)
		->get();
		return $resp;		
	}
}
