@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_step4')

	<form method='post' action="{{ route('dashboard') }}" id='frm_pub5' name='frm_pub5' autocomplete="off">
	<input name="_token" type="hidden" value="{{ csrf_token() }}">

		<input type='hidden' name='ref_tabulato' id='ref_tabulato' value='{{ $ref_tabulato }}'>
		<div id='div_table_report'>
			<h2>Risultato Importazione</h2><br>

		  <div class="mb-3" id='body_dialog'>
			<?php
				$content=json_decode($response->getContent());
				$status=$content->status;
				$message=$content->message;
				$out="outline-";$dis="disabled";
			?>	
				
				<input type='hidden' name='enteweb' id='enteweb' value='{{ $enteweb }}'>
				
				@if ($status==="false")					
					<div class="alert alert-danger" role="alert">
						<b>Attenzione!</b> Problemi riscontrati durante la procedura di importazione (<i>gli archivi in questo caso non subiscono nessun aggiornamento e rimangono nello stesso stato prima della procedura di importazione</i>):<hr>
						<i>{{ $message }}</i>
					</div>
				@else
					<?php 
						$out="";$dis="";
					?>
					<div class="alert alert-success" role="alert">
					@if ($test===true)	
						<b>Importazione TEST completata</b><hr>
					@else
						<b>Importazione completata</b><hr>
					@endif
					<i>{{ $message }}</i>
					</div>
				@endif


		  </div>

		@if ($test===true)	
			<table id="tb_test">
				<thead>
					<tr>
						<th>NOME</th>
						<th>VIA</th>
						<th>CAP</th>
						<th>LOC</th>
						<th>PRO</th>
						<th>DATANASC</th>
						<th>COMUNENASC</th>
						<th>DATAASSU</th>
						<th>DATALICE</th>
						<th>SINDACATO</th>
						<th>DATASIND</th>
						<th>NUMAPE</th>
						<th>DATAPE</th>
						<th>CODFISC</th>
						<th>CODINPS</th>
						<th>ENTE</th>
						<th>DENOM</th>
						<th>PROVINCIA</th>
						<th>PERIODO</th>
						<th>DATA</th>
						<th>PRESENTI</th>
						<th>LOCAZIENDA</th>
						<th>VIAZIENDA</th>
						<th>SETTORE</th>
						<th>ATTIVI</th>
						<th>SIND_MENS1</th>
						<th>SIND_MENS2</th>
						<th>SIND_MENS3</th>
						<th>SIND_MENS4</th>
						<th>SIND_MENS5</th>
						<th>C1</th>
						<th>C2</th>
					</tr>
				</thead>

				<tbody>
					@foreach ($dati as $dato)
						<tr>
							<td>{{ $dato->NOME }}</td>
							<td>{{ $dato->VIA }}</td>
							<td>{{ $dato->CAP }}</td>
							<td>{{ $dato->LOC }}</td>
							<td>{{ $dato->PRO }}</td>
							<td>{{ $dato->DATANASC }}</td>
							<td>{{ $dato->COMUNENASC }}</td>
							<td>{{ $dato->DATAASSU }}</td>
							<td>{{ $dato->DATALICE }}</td>
							<td>{{ $dato->SINDACATO }}</td>
							<td>{{ $dato->DATASIND }}</td>
							<td>{{ $dato->NUMAPE }}</td>
							<td>{{ $dato->DATAPE }}</td>
							<td>{{ $dato->CODFISC }}</td>
							<td>{{ $dato->CODINPS }}</td>
							<td>{{ $dato->ENTE }}</td>
							<td>{{ $dato->DENOM }}</td>
							<td>{{ $dato->PROVINCIA }}</td>
							<td>{{ $dato->PERIODO }}</td>
							<td>{{ $dato->DATA }}</td>
							<td>{{ $dato->PRESENTI }}</td>
							<td>{{ $dato->LOCAZIENDA }}</td>
							<td>{{ $dato->VIAZIENDA }}</td>
							<td>{{ $dato->SETTORE }}</td>
							<td>{{ $dato->ATTIVI }}</td>
							<td>{{ $dato->SIND_MENS1 }}</td>
							<td>{{ $dato->SIND_MENS2 }}</td>
							<td>{{ $dato->SIND_MENS3 }}</td>
							<td>{{ $dato->SIND_MENS4 }}</td>
							<td>{{ $dato->SIND_MENS5 }}</td>
							<td>{{ $dato->C1 }}</td>
							<td>{{ $dato->C2 }}</td>
							
						</tr>
					@endforeach
				</tbody>
			</table>
		<br>
		<button class="mb-3 ml-3 btn btn-outline-secondary" onclick="history.go(-1);">Indietro </button>		
			
		@else
			@if ($ref_tabulato!="t4_cala_a")
				<input type='checkbox' name='script_fine' class='form-check-input selee' id='script_fine' checked> Avvia prima script servizi<br>
				<input type='checkbox' name='notifiche_attive' class='form-check-input selee' id='notifiche_attive' checked> Notifiche attive<br><br>
				<button type="button" id='btn_pubblica' class="mb-3 btn btn-<?php echo $out;?>success" <?php echo $dis; ?> onclick='publish()'>
					Completa la pubblicazione
				</button>
			@endif
		@endif
			
			

		
			

			<!--
			<button class="mb-3 ml-2 btn btn-outline-secondary" onclick="history.go(-1);">Indietro </button>		
			!-->



	</form>
@endsection
