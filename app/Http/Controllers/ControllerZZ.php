<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;
use App\Models\schema_import;
use App\Models\infotab;

use DB;

class ControllerZZ extends Controller
{
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
		/*
		$schema_import=schema_import::all();
		$name=Auth::user()->name;
		return view('modelli')->with('schema_import',$schema_import);
		//->with('reports', $reports)
		*/
	 }
	
	 
	 public function dele_schema($id_dele=0) {
		$schema_import=schema_import::where('id', $id_dele)->first();
		$file_json=$schema_import['file_json'];
		@unlink ("tracciati/".$file_json);

		$deleted = schema_import::where('id', $id_dele)->delete();
		$name=Auth::user()->name;
		
		$schema_import=schema_import::all();
		return view('modelli')->with('schema_import',$schema_import)->with('id_dele',$id_dele);
	 }



}
