@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_riattiva')
	

@if ($resp['esito']=="200") 
	<div class="alert alert-success" role="alert">
		<b>Operazione effettuata con successo!</b>
	</div>
@else
	<div class="alert alert-warning" role="alert">
		<b>Attenzione!/</b><br>
		Si è verificato un problema durante l'operazione. I tabulati non hanno comunque subito variazioni<hr>
		<i>Codice Errore:</i> <b>{{ $resp['esito'] }}</b><br>
		<i>Descrizione Errore:</i>
		<b>
			{{ $resp['message'] }}
		</b>
		
	</div>	
@endif

	
@endsection

