@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_step3')

<form method='post' action="{{ route('step4') }}" id='frm_pub4' name='frm_pub4' autocomplete="off">

<input name="_token" type="hidden" value="{{ csrf_token() }}" id='token_csrf'>
<meta name="csrf-token" content="{{{ csrf_token() }}}">

<input type='hidden' name='file_json' id='file_json' value='{{ $file_json }}'>
<input type='hidden' name='rip_tab' id='rip_tab'>

	<input type='hidden' name='ref_tabulato' id='ref_tabulato' value='{{ $ref_tabulato }}'>
	<div id='div_table_report'>
		<h2>Stai per pubblicare:</h2><br>

	
		@foreach($ref_pub as $k=>$v) 
			<div class="alert alert-success" role="alert">
				<b>{{ $v }}</b> - <i>{{ $k }}</i>
			</div>
		@endforeach

		@if ($check_fine_anno==false)
		<div id='div_shift' class="alert alert-warning" role="alert">
				<b>Attenzione!</b> Per questo tabulato non ancora risulta valorizzato sind_mens5 con anno corrente.
				<button type="button" class="btn btn-primary ml-2'" onclick='shift()'>Esegui Shift anni</button>
		</div>
		@endif

      <div class="mb-3" id='body_dialog' style='display:none'>
       
      </div>
		<input type='hidden' name='info_up' id='info_up' value="{{ $info_up }}">
		<input type='hidden' name='repub_tab' id='repub_tab'>

		<button type="button" name='btn_allegati' id='btn_allegati' class="mb-3 btn btn-info" data-target="#win_dialog" data-toggle="modal" onclick="$('#div_table_pub').hide(150);set_sezione('{{ $ref_tabulato }}');$('#div_altre_opzioni').show()" ><i class="fas fa-paperclip"></i> Allega File CSV di pubblicazione</button>			

		<button type="button" name='btn_repub' id='btn_repub' class="mb-3 btn btn-primary ml-3" onclick="repub()" ><i class="fas fa-file"></i> Ripubblica un tabulato inviato</button>

		<input type='hidden' id='test_import' name='test_import'>

		<div style='display:none'>
			<button type="submit" id='btn_procedi_test'  class="ml-3 mb-3 btn btn-outline-success" disabled onclick="$('#btn_procedi_test').prop( 'disabled', true);$('#btn_procedi').prop( 'disabled', true);$('#test_import').val('test')";>
				Test di importazione
			</button>	
		</div>


		<button type="submit" id='btn_procedi' name='btn_procedi' class="ml-3 mb-3 btn btn-outline-success" disabled onclick="$('#btn_procedi_test').prop( 'disabled', true );$('#test_import').val('')">
			Procedi con l'importazione
		</button>	
		
		
		<button class="mb-3 ml-3 btn btn-outline-secondary" onclick="history.go(-1);">Indietro </button>		
		
		<hr>
		
		<div id="div_altre_opzioni">
			<input type='checkbox' name='direct_pub' id='direct_pub' class='form-check-input' value='dir_pub' onclick='set_direct(this.value)'>
			Metti la spunta se il file CSV è già pronto e non deve essere elaborato
			<br>
			<div id='div_posizioni'>
				<input type='checkbox' name='storicizza' id='storicizza' class='form-check-input' checked>
					Storicizza i dati presenti
				<br>	

				<input type='checkbox' name='ref_aziende' id='ref_aziende' class='form-check-input' onclick="$('#body_dialog').hide(150);">
					Se spuntato il file da inviare è riferito alle aziende

				
				<br><br>	

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
		
		<div class='container-fluid' style='display:none' id='div_table_pub'>
				<table id="tb_pub">
					<thead>
						<tr>
							<th>RIPUBBLICA</th>
							<th>CSV DI ORIGINE</th>
							<th>DATA PUBBLICAZIONE</th>
							<th>ESITO</th>
							<th>NUM RECORD</th>
							<th>NUM NEW</th>
							<th>NUM UP</th>
							<th>TERRITORIO</th>
							<th>ALTRE INFO</th>
						</tr>
					</thead>

					<tbody>
						@foreach ($lista_tab as $lista)
							<tr>
								<td style='text-align:center'>
									<button type="button" class="btn btn-primary" onclick="re_pub('{{ $lista['nome_file']}}','{{ $lista['ref_tabulato'] }}')">Ripubblica</button>
								</td>
								<td style='text-align:center'>
								<?php
									$href="allegati/pubblicazioni/".$lista['nome_file'].".csv";
								?>
								<a href="<?php echo $href;?>">
									<button type="button" class="btn btn-info">
									Apri
									</button>
								</a>	
								</td>
								<td>
									{{ $lista['created_at'] }}
								</td>
								<td>
									@if ($lista['esito']=="0") 
										<font color='green'>Pubblicato</font>
										@if ($lista['tot_new']==-1)
											<hr>
											<i>Ripubblicazione o Pubblicazione senza elaborazione
											</i>	
										@endif	
									@else
										@if ($lista['esito']=="1000") 
											Backup Preventivo prima della Pubblicazione 'ZZ'
										@elseif ($lista['esito']=="2000") 
											Backup Preventivo prima della Pubblicazione
										@else	
										<font color='red'>Codice Errore: <b>{{ $lista['esito'] }}</b></font>
										@endif
									@endif
									
								</td>
								<td>
									@if ($lista['num_record']!=-1)
										{{ $lista['num_record'] }}
									@endif
								</td>
								<td>
									@if ($lista['tot_new']!=-1)
										{{ $lista['tot_new'] }}
									@endif
								</td>
								<td>
									@if ($lista['tot_up']!=-1)
										{{ $lista['tot_up'] }}
									@endif
								</td>


								<td>
									{{ $lista['ref_tabulato'] }}
								</td>
								<td>
									<?php
									?>
									<a href='javascript:void(0)' onclick="$('#detail_oper{{ $lista['id']}}').toggle(150)">Dettagli</a>
									<div id="detail_oper{{$lista['id']}}" style="display:none">
										{{ $lista['operazione'] }}
									</div>
								</td>
								
							</tr>
						@endforeach
					</tbody>
				</table>	
		</div>		
		


</form>
@endsection
