@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_riattiva')
	<form method='post' action="{{ route('step_riattiva') }}" id='frm_riattiva' name='frm_riattiva' autocomplete="off">
	<input name="_token" type="hidden" value="{{ csrf_token() }}">



	<h2>Tabulato: <font color='red'>{{ $ref_tabulato }}</font></h2><br>
		<input type='hidden' name='ref_tabulato' value="{{ $ref_tabulato }}">
		@foreach($riattiva as $ente=>$value)
			<?php
			
				$btn_view=false;
				$rilasci=$riattiva[$ente]['rilasci'];
				$ultima_presenza=$riattiva[$ente]['ultima_presenza'];
				$arr_rilasci=explode(";",$rilasci);
				$ente_descr=$riattiva[$ente]['denominazione'];
				echo "<h3>Ente: <font color='red'>$ente_descr</h3></font>";
				echo "<ul class='list-group'>";
					for ($sca=0;$sca<=count($arr_rilasci)-1;$sca++) {
						$act="";
						$rilascio=substr($arr_rilasci[$sca],0,7);
						$info_rilascio="";$render_row="";
						for ($s=0;$s<=count($ultima_presenza)-1;$s++) {
							
							if (array_key_exists("periodo",$ultima_presenza[$s]) && array_key_exists("sindacato",$ultima_presenza[$s])) {
								$periodo=$ultima_presenza[$s]['periodo'];
								$sindacato=$ultima_presenza[$s]['sindacato'];
								$num=$ultima_presenza[$s]['num'];
								if ($periodo==$rilascio) {
									echo "<input type='hidden' name='ultimo[]' value='".$arr_rilasci[$sca]."|$sindacato|$ente'>";
									if (strlen($info_rilascio)!=0) $info_rilascio.="<hr>";
									$render_row.=render_row($sindacato,$num);
									$act="active";
									$btn_view=true;
								}	
							}
						}
						$vis="display:none";$class="rilasci";
						if (strlen($render_row)!=0) {$vis="";$class="";}
						echo "<li class='list-group-item $class' aria-current='true' style='$vis'>";
							echo "<b>$rilascio</b>";
							if (strlen($render_row)!=0) {
								echo $render_row;
							}	
						echo "</li>";	
						
					}	
				echo "</ul>";
				echo "<br><a href='#' onclick=\"$('.rilasci').toggle(200)\">Visiona tutti i rilasci dell'ente</a>";
				
			?>
				<?php
					if ($btn_view==true && count($riattiva)==1) {?>
						<br><br>
						<label for="anno_sind_r">Anno SindMens</label>
						<input type="text" class="form-control" id="anno_sind_r" name="anno_sind_r" aria-describedby="anno sind_mens" placeholder="Anno" value="<?php echo date('Y'); ?>" style='width:150px' maxlength=4><br>

						<label for="sind_ref">SindMens</label>
						<select class="form-select form-select-lg mb-3" style='width:250px' aria-label="Scelta sind_mens" id='sind_ref' name='sind_ref'>
						  <option value="sind_mens1">sind_mens1</option>
						  <option value="sind_mens2">sind_mens2</option>
						  <option value="sind_mens3">sind_mens3</option>
						  <option value="sind_mens4">sind_mens4</option>
						  <option selected value="sind_mens5">sind_mens5</option>
						</select>
						<br>
						<i>Indica le nuove posizioni per Sind_mens</i>
						<select class="mt-2 form-select" multiple aria-label="Selezione dei mesi sind_mens5" name='omini_sind_r[]' id='omini_sind_r' style="height: 350px">
						  <option value="0">Gennaio</option>
						  <option value="1">Febbraio</option>
						  <option value="2">Marzo</option>
						  <option value="3">Aprile</option>
						  <option value="4">Maggio</option>
						  <option value="5">Giugno</option>
						  <option value="6">Luglio</option>
						  <option value="7">Agosto</option>
						  <option value="8">Settembre</option>
						  <option value="9">Ottobre</option>
						  <option value="10">Novembre</option>
						  <option value="11">Dicembre</option>
						</select>
				<?php } ?>
				<hr>
		@endforeach
		
		<?php 
		if (count($riattiva)==1) {?>
			<button type="submit" class="mt-2 mb-2 btn btn-success" onclick="check_riattiva();" id="btn_riattiva">
				Assegna
			</button>	
		<?php }
		else {
			echo "<div class='mt-2 alert alert-warning' role='alert'>";
			  echo "Bottone di assegnazione disponibile solo selezionando un ente per volta";
			echo "</div>";
		}	
			
				
		?>	
		

	</form>
@endsection


<?php
	function render_row($sindacato,$num) {
		$view=null;
		$descr_sind="";
		if ($sindacato=="0") {$descr_sind=" Liberi ";$colo="text-warning";}
		if ($sindacato=="1") {$descr_sind=" Fillea CGIL ";$colo="text-danger";}
		if ($sindacato=="2") {$descr_sind=" Filca Cisl ";$colo="text-success";}
		if ($sindacato=="3") {$descr_sind=" Feneal Uil ";$colo="text-primary";}
		if ($sindacato==" " || $sindacato=="") {$descr_sind=" Non Spec.";$colo="text-secondary";}

		$view.="<div class='mt-2'>";
			$view.="<font color='yellow'>";
				$view.=" <i class='fas fa-user $colo'></i>";
			$view.="</font>";
			$view.=" <small><i>Ultimo rilascio $descr_sind</i></small>";
			$view.="--> <b>$num</b>";

		$view.="</div>";
		return $view;
	}
?>
