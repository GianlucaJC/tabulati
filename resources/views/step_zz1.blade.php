@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_zz1')

<form method='post' action="{{ route('step_zz2') }}" id='frm_zz2' name='frm_zz2' autocomplete="off">
<input name="_token" type="hidden" value="{{ csrf_token() }}">



	<input type='hidden' name='ref_tabulato' id='ref_tabulato' value='{{ $ref_tabulato }}'>
	<input type='hidden' name='ref_zz' id='ref_zz' value='1000'>
	<div id='div_table_report'>
		<h2>Stai per aggiornare/inserire i 'ZZ' in:</h2><br>
	
		@foreach($ref_pub as $k=>$v) 
			<div class="alert alert-success" role="alert">
				<b>{{ $v }}</b> - <i>{{ $k }}</i>
			</div>
		@endforeach

      <div class="mb-3" id='body_dialog' style='display:none'>
       
      </div>
		
		@if ($info_log>0)				
			<div class="alert alert-warning" role="alert">
				<b>Attenzione</b><hr>
				Per la giornata di oggi esiste già un Backup per gli ZZ. 
				Procedere comunque con un nuovo backup? 
				<input type='radio' name='back_pres[]' class='form-check-input selee' name='back_pres' value='N' checked> No	 			
				<input type='radio' name='back_pres[]' class='form-check-input selee' name='back_pres' value='S'> Si
			</div>
		@else 
			<input type='hidden' name='back_pres' id='back_pres' value="S">
		@endif	
		
		<div class="alert alert-info" role="alert">
			Per utilizzare le formule tenere conto della seguente mappatura (da assegnare alla prima riga del CSV):<br><br>
			<b>A/TA</b> - <i>Addetti</i>&nbsp &nbsp 
			<b>0/T0</b> - <i>Non Iscritti</i>&nbsp &nbsp 
			<b>1/T1</b> - <i>Fillea CGIL</i>&nbsp &nbsp 
			<b>2/T2</b> - <i>Filca CISL</i>&nbsp &nbsp
			<b>3/T3</b> - <i>Feneal UIL</i>&nbsp &nbsp
			<b>N/TN</b> - <i>Non Specificati</i>&nbsp &nbsp
			
			
		</div>		

		<button type="button" name='btn_allegati' id='btn_allegati' class="mb-3 btn btn-info" data-target="#win_dialog" data-toggle="modal" onclick="$('#div_table_pub').hide(150);set_sezione('{{ $ref_tabulato }}');$('#div_altre_opzioni').show()" ><i class="fas fa-paperclip"></i> Allega File CSV delle aziende</button>			


		<button type="submit" id='btn_procedi' name='btn_procedi' class="ml-3 mb-3 btn btn-outline-success" disabled onclick="$('#btn_procedi').prop( 'disabled', true );">
			Procedi con l'importazione
		</button>	
		<button class="mb-3 ml-3 btn btn-outline-secondary" onclick="history.go(-1);">Indietro </button>		
		
		
		
		
		<div id="div_altre_opzioni">
			<div id='div_calcoli'>
				<label for="formula_ni">Formula N.Iscritti</label>
				<input type="text" class="form-control" id="formula_ni" name="formula_ni" aria-describedby="Formula per non iscritti" placeholder="FormulaNI" value="" style='width:250px'><br>
				<label for="formula_nspec">Formula N.Spec</label>
				<input type="text" class="form-control" id="formula_nspec" name="formula_nspec" aria-describedby="Formula per non specificati" placeholder="FormulaNSPEC" value="" style='width:250px'>
				
			</div>
				

			<hr>		
			<div id='div_posizioni'>

				<label for="anno_sind">Anno SindMens5</label>
				<input type="text" class="form-control" id="anno_sind" name="anno_sind" aria-describedby="anno sind_mens5" placeholder="Anno" value="<?php echo date('Y'); ?>" style='width:150px' maxlength=4 onfocusout="set_direct()"><br>

				<i>Indica le posizioni Sind_mens5 da aggiornare</i>
				<select class="form-select" multiple aria-label="Selezione dei mesi sind_mens5" name='omini_sind[]' id='omini_sind' style="height: 350px" onchange="set_direct()">
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
				<hr>
			</div>
		</div>
	
		<input type='hidden' name='enteweb' id='enteweb' value="{{ $enteweb }}">
		
	
		


</form>
@endsection
