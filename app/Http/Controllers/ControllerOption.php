<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;


use Illuminate\Http\Request;
use App\Models\schema_import;

use DB;

class ControllerOption extends Controller
{
	 public function modelli_import(Request $request) {
		$schema_import=schema_import::all();
		$name=Auth::user()->name;
		return view('modelli')->with('schema_import',$schema_import);
		//->with('reports', $reports)
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

	 public function clona_schema($id_clone=0) {
		
		$schema_import=schema_import::where('id', $id_clone)->first();
		$file_json=$schema_import['file_json'];
		$new_file=uniqid().".json";
		copy ("tracciati/$file_json","tracciati/$new_file");
		
		$task = schema_import::find($id_clone);
		$new = $task->replicate();
		$new->clonato=1;
		$new->file_json=$new_file;
		$new->save();
		
		$name=Auth::user()->name;
		
		
		$schema_import=schema_import::all();
		return view('modelli')->with('schema_import',$schema_import)->with('id_clone',$id_clone);
	 }	 

	 public function view_schema(Request $request) {
		$name=Auth::user()->name;
		$id_modello=$request->get('id_modello')[0];
		$schema_import=schema_import::where('id', $id_modello)->first();
		$file_json=$schema_import['file_json'];
		$save=false;
		if($request->has('save_schema')) {
			//salvataggio POST dati nel file json corrispondente e nel DB
				$json = file_get_contents("tracciati/standard.json");
				$infocampi = json_decode($json,true);
				
				$data=array();
				foreach($infocampi['struttura'] as $campo=>$value) {
					$pos="N";
					if ($request->has($campo)) $pos=$request->input($campo);

					$data[$campo]['pos']=$pos;
				}
				$array=array("struttura"=>$data);
				$json = json_encode($array);
				file_put_contents("tracciati/$file_json", $json);		
				
				$info=array();
				$info['descrizione_associazione']=$request->input('associazione');
				$info['note']=$request->input('note');
				$info['clonato']=0;
				$info['tipo_importazione']=$request->input('tipo_importazione');

				DB::table('schema_imports')->where('id',$id_modello)->update($info);
				$save=true;
				$schema_import=schema_import::where('id', $id_modello)->first();
		}
		
		$associazione=$schema_import['descrizione_associazione'];
		$note=$schema_import['note'];
		$tipo_importazione=$schema_import['tipo_importazione'];
		
		return view('view_schema')->with('file_json',$file_json)->with('associazione',$associazione)->with('note',$note)->with('id_modello',$id_modello)->with('save',$save)->with('tipo_importazione',$tipo_importazione);
	 }

	 public function shift(Request $request) {
		$ref_tabulato=$request->input('ref_tabulato');
		$resp=array();

		DB::statement("
			UPDATE anagrafe.$ref_tabulato a
			SET a.sind_mens1=a.sind_mens2,a.sind_mens2=a.sind_mens3,a.sind_mens3=a.sind_mens4,a.sind_mens4=a.sind_mens5"
		);		

		DB::statement("
			UPDATE anagrafe.$ref_tabulato a
			SET a.sind_mens5=''"
		);		

		$resp['header']="OK";

		return  json_encode($resp);
	 }

}
