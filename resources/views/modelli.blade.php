@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_option')
	<form method='post' action="{{ route('view_schema') }}" id='frm_schema1' name='frm_schema1' autocomplete="off">
	<input name="_token" type="hidden" value="{{ csrf_token() }}">
		<div id='div_table_option'>
			@if(isset($id_dele))
					<div class="alert alert-success" role="alert">
						<b>Schema eliminato con successo!</b>
					</div>				
			@endif
			@if(isset($id_clone))
					<div class="alert alert-success" role="alert">
						<b>Schema clonato con successo!</b>
					</div>				
			@endif


<div class="alert alert-primary" role="alert">
	<b>Attenzione!</b> Il campo attivi e i campi sind_mens1-5, nello schema dei modelli di importazione, vengono proprio ignorati, quindi si può anche mettere una posizione ma non verrà tenuta conto.
	Ovviamente le importazioni di questi campi tornano utili quando si vuole caricare direttamente un CSV senza elaborazioni. In questo caso le posizioni sono quelle indicate nello schema (per questo tipo di operazione viene utilizzato un modello standard non visibile in questo elenco)
</div>

				<table id="tb_option">
					<thead>
						<tr>
							<th style='width:300px'>OPERAZIONI</th>
							<th>DESCRIZIONE ASSOCIAZIONE</th>
							<th>NOTE</th>
						</tr>
					</thead>

					<tbody>
						

						@foreach($schema_import as $schema)
							<?php 
								$def="";
								if ($schema->id==1) continue;
							?>	
						
							<tr>
								<td style='width:300px'>
									<button type='submit' class="btn btn-outline-success" name='id_modello[]' value='{{ $schema->id}}'>
										<i class="fas fa-folder-open"></i>Apri
									</button>
								
								
									<a href="{{ route('clona_schema') }}/{{ $schema->id }}" class="btn btn-outline-primary"  value='{{ $schema->id}}' onclick="if (!confirm('Sicuri di clonare lo schema?')) return false">
										<i class="fas fa-clone"></i> Clona
									</a>
								
									<a href="{{ route('dele_schema') }}/{{ $schema->id }}" class="btn btn-outline-secondary"  value='{{ $schema->id}}' onclick="if (!confirm('Sicuri di cancellare lo schema?')) return false">
										<i class="fal fa-trash-alt"></i> Elimina
									</a>
								</td>
								<td>
									{{ $schema->descrizione_associazione }}
									@if ($schema->clonato==1) 
										<hr>
										(<i>Clone</i>)
									@endif
								</td>
								<td>
									{{ $schema->note }}
								</td>
							</tr>
						@endforeach

					</tbody>
				</table>
		</div>		
	</form>

@endsection
