@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_import_zz')

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

			
			

		
			

			<!--
			<button class="mb-3 ml-2 btn btn-outline-secondary" onclick="history.go(-1);">Indietro </button>		
			!-->



	</form>
@endsection
