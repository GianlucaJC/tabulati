<?php
namespace App\Models;


use App\Models\tb_model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
class azienda extends Model
{
    use HasFactory;
	

	public function get_num($info_azienda,$pos) {
		$ente=$info_azienda['enteweb'];
		$ref_tabulato=$info_azienda['ref_tabulato'];
		$azienda=$info_azienda['azienda'];
		
		if ($pos=="pos_t0") $sind="0";
		if ($pos=="pos_t1") $sind="1";
		if ($pos=="pos_t2") $sind="2";
		if ($pos=="pos_t3") $sind="3";
		if ($pos=="pos_tn") $sind=" ";

		$resp = DB::table("anagrafe.".$ref_tabulato)
		->select('*')
		->where('ente','=',$ente)
		->where('denom','=',$azienda);
		if ($pos!="pos_ta") $resp->where('sindacato','=',$sind);
		$r=$resp->count();

		return $r;
			
	}		


	public function set_zz($cont,$provincia,$omini_sind,$anno_sind,$mese_sind,$last_zz,$info_azienda,$num_ni_richiesti,$num_nspec_r) {
		
		$data=trim($anno_sind)."-".$mese_sind."-01 00:00:00";
		$mese="";
		if ($mese_sind=="01") $mese="Gennaio";
		if ($mese_sind=="02") $mese="Febbraio";
		if ($mese_sind=="03") $mese="Marzo";
		if ($mese_sind=="04") $mese="Aprile";
		if ($mese_sind=="05") $mese="Maggio";
		if ($mese_sind=="06") $mese="Giugno";
		if ($mese_sind=="07") $mese="Luglio";
		if ($mese_sind=="08") $mese="Agosto";
		if ($mese_sind=="09") $mese="Settembre";
		if ($mese_sind=="10") $mese="Ottobre";
		if ($mese_sind=="11") $mese="Novembre";
		if ($mese_sind=="12") $mese="Dicembre";
		$periodo=strtoupper($mese)." ".$anno_sind;
		
		
		$ente=$info_azienda['enteweb'];
		$ref_tabulato=$info_azienda['ref_tabulato'];
		$denom=$info_azienda['azienda'];
		$via_azienda=$info_azienda['via_azienda'];
		$loc_azienda=$info_azienda['loc_azienda'];
		$p_iva=$info_azienda['p_iva'];
		$telazi=$info_azienda['telazi'];
		if (strlen($p_iva)!=0) {
			$zeri="";
			for ($sca=strlen($p_iva);$sca<11;$sca++) {
				$zeri.="0";
			}
			$p_iva=$zeri.$p_iva;
		}
		$sind_cond="-";$num_richiesti=0;
		
		for ($ciclo=1;$ciclo<=2;$ciclo++) {
			if ($ciclo==1 && $num_ni_richiesti==0) continue;
			if ($ciclo==2 && $num_nspec_r==0) continue;
			
			if ($ciclo==1) {
				echo "<div>";
					echo "<i>$cont</i>) <b>$denom</b>: <i>NON ISCRITTI</i>";
				echo "</div>";				
				$sind_cond="0";$num_richiesti=$num_ni_richiesti;
			}
			if ($ciclo==2) {
				echo "<div>";
					echo "<b>$denom</b>: <i>NON SPECIFICATI</i>";
				echo "</div>";				
				$sind_cond=" ";$num_richiesti=$num_nspec_r;
			}
			echo "num_richiesti <b>$num_richiesti</b> ";
			$resp=0;
			if ($num_richiesti!=0) {
				$info=array();
				$info['attivi']="N";
				//rende tutti gli zzz dell'azienda specificata ed ente e non iscritti: NON attivi
				DB::table("anagrafe.".$ref_tabulato)
				->where('ente','=',$ente)
				->where('nome','like',"%ZZZZ%")
				->where('denom','=',$denom)
				->where('sindacato','=',$sind_cond)
				->update($info);		
				

				//esegue l'update per numero minimo garantito
				$info=array();

				$info['attivi']="S";
				$info['presenti']= "0";
				//$tb_model->sind_mens5 = $sind_mens5;
				$info['provincia']= $provincia;
				$info['data']= $data;
				$info['periodo']=$periodo;
				$info['viazienda']=$via_azienda;
				$info['locazienda']=$loc_azienda;
				$info['c2']=$p_iva;
				$info['c3']=$telazi;			
				$info['settore']="**";
				
				$num_rec=DB::table("anagrafe.".$ref_tabulato)
					->where('denom', $denom)
					->where('nome','like',"%ZZZZ%")
					->where('ente', $ente)
					->where('sindacato', $sind_cond)
					->limit($num_richiesti)
					->update($info);			
				

					$resp_up =DB::table("anagrafe.".$ref_tabulato)
					->select("id_anagr","sindacato","sind_mens5")
					->where('settore','**')
					->get();				
					foreach($resp_up as $up) {
						$id_anagr=$up->id_anagr;
						$sindacato=$up->sindacato;
						$sind_mens5=$up->sind_mens5;
						//echo "<br><small>-->id_anagr $id_anagr pre $sind_mens5 </small>";
						$pre_sind=$sind_mens5;		
						if ($sind_mens5==null || strlen($sind_mens5)==0) {
							$sind_mens5="0123456789ab";
							for ($sca=0;$sca<=11;$sca++) {
								$old=$sca;
								if ($sca==10) $old="a";
								if ($sca==11) $old="b";
								if (in_array($sca,$omini_sind)) 
									$sind_mens5=str_replace($old,$sindacato,$sind_mens5);
								else
									$sind_mens5=str_replace($old,"*",$sind_mens5);
							}
							$sind_mens5.=$anno_sind;						
						} else {
							
							$str="";
							for ($sca=0;$sca<=11;$sca++) {
								$sub=substr($sind_mens5,$sca,1);
								if (in_array($sca,$omini_sind)) $sub=$sindacato;
								$str.=$sub;
							}
							$sind_mens5=$str.$anno_sind;
						}
						//echo "<small>post $sind_mens5</small>";
						$info=array();
						$info['sind_mens5']=$sind_mens5;
						$info['settore']="";
						DB::table('anagrafe.'.$ref_tabulato)
						->where('id_anagr',$id_anagr)
						->update($info);
					}

					
				////////////////
				
				echo "num_garantito <b>$num_rec</b>";
				
				if ($num_richiesti>$num_rec) {
					echo "  (<font color='red'>".$num_richiesti-$num_rec." --- INSERT</font>)";
					$sindacato=$sind_cond;
					$sind_mens5="0123456789ab";
					for ($sca=0;$sca<=11;$sca++) {
						$old=$sca;
						if ($sca==10) $old="a";
						if ($sca==11) $old="b";
						if (in_array($sca,$omini_sind)) 
							$sind_mens5=str_replace($old,$sindacato,$sind_mens5);
						else
							$sind_mens5=str_replace($old,"*",$sind_mens5);
					}
					$sind_mens5.=$anno_sind;	

					
					//esegue l'inserimento per differenza
					$ref=str_replace("_","",$ref_tabulato);
					$ref=substr($ref,0,6);
					$ref=strtoupper($ref);
					for ($sca=$num_rec;$sca<=$num_richiesti-1;$sca++) {
						$last_zz++;
						$str="";
						for ($sc=strlen($last_zz);$sc<10;$sc++) {
							$str.="0";
						}
						$new_last=$str.$last_zz;
						$nome="ZZZZZZ".$new_last.$ref;
						$tb_model = new tb_model;
						$tb_model->setTable("anagrafe.".$ref_tabulato);
						$tb_model->nome = $nome;
						$tb_model->codfisc = $nome;
						$tb_model->denom = $denom;
						$tb_model->ente = $ente;
						$tb_model->attivi = "S";
						$tb_model->sindacato = $sind_cond;
						$tb_model->presenti = "0";
						$tb_model->sind_mens5 = $sind_mens5;
						$tb_model->provincia = $provincia;
						$tb_model->data = $data;
						$tb_model->periodo = $periodo;
						$tb_model->viazienda = $via_azienda;
						$tb_model->locazienda = $loc_azienda;
						$tb_model->c2 = $p_iva;
						$tb_model->c3 = $telazi;
						$tb_model->save();
					
					}

				}
				echo "<hr>";
			}
		}

		return $last_zz;		
	}	
	
	function last_zz($ref_tabulato) {
				
		$table="anagrafe.$ref_tabulato";

		$resp = DB::table($table)
		->select('nome')
		->where('nome','like',"%ZZZZ%")
		->orderBy('id_anagr', "DESC")
		->first();
		
		if (isset($resp->nome)) {
			$nome=$resp->nome;
			$nome=intval(substr($nome,6,10));
			return $nome;
		}	
		else return "0";
	}
}
