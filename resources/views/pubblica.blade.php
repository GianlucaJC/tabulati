@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_step2')

<form method='post' action="{{ route('step3') }}" id='frm_pub3' name='frm_pub3' autocomplete="off">
<input name="_token" type="hidden" value="{{ csrf_token() }}">

<input type='hidden' name='file_json' id='file_json' value='{{ $file_json }}'>
	<div id='div_table_report'>
			<table id="tb_report">
				<thead>
					<tr>
						<th>#</th>
						<th>TERRITORIO</th>
						

						<th>ENTE</th>
						<th>DESCRIZIONE</th>
						<th>VALIDITA' DA</th>
						<th>VALIDITA' FINO A</th>
					

					</tr>
				</thead>

				<tbody>
						<?php
							$ss=0;
						?>	
						

						@foreach($reports as $report)
							<?php
								$ss++; 
								
								$code_CE=$report->code_CE;
								$descr1=$report->descr1;
								$descr2=$report->descr2;
								$descr3=$report->descr3;
								$descr4=$report->descr4;
								$descr5=$report->descr5;
							?>
							<tr>
								<td>
									<input type='checkbox' name='sele_x[]' class='form-check-input selee' id='s{{ $ss }}'  value='{{ $report->TB }}-{{ $report->code_CE }}' data-idref='{{ $ss }}'>
								</td>
								<td>
									{{ $report->denominazione }}
								</td>
								<td>
									{{ $report->descr_ce }}
								</td>
								
								<td>
									<?php
										if (substr($descr1,0,1)==$code_CE)
											$descr_old=substr($descr1,1);
										if (substr($descr2,0,1)==$code_CE)
											$descr_old=substr($descr2,1);
										if (substr($descr3,0,1)==$code_CE)
											$descr_old=substr($descr3,1);
										if (substr($descr4,0,1)==$code_CE)
											$descr_old=substr($descr4,1);
										if (substr($descr5,0,1)==$code_CE)
											$descr_old=substr($descr5,1);
									?>
									
									<input type="text" class="form-control descr_agg" name="descr_agg[<?php echo $code_CE;?>]" id='descr_agg{{ $ss }}' aria-describedby="descrizione" placeholder="" value="<?php echo $descr_old; ?>">
									<small class="form-text text-muted">
									<?php echo $descr_old; ?>
									</small>

								</td>
								<td>
									<input type="date" class="form-control" name="valido_da[<?php echo $code_CE;?>]" id='valido_da{{ $ss }}'  aria-describedby="valido da" placeholder="" value="<?php echo date('Y-m-d', strtotime($report->decorrenza_tab)); ?>">
									<small class="form-text text-muted">
										{{ date('d-m-Y', strtotime($report->decorrenza_tab)) }}
									</small>

									
								</td>
								<td>
									<input type="date" class="form-control" name="valido_a[<?php echo $code_CE;?>]"  id='valido_a{{ $ss }}' aria-describedby="valido a" placeholder=""  value="<?php echo date('Y-m-d', strtotime($report->fine_tab)); ?>">
									<small class="form-text text-muted">
										<?php 
											if ($report->fine_tab!="0000-00-00")
												echo date('d-m-Y', strtotime($report->fine_tab));
										?>
									</small>


								</td>
							
							</tr>
							
						@endforeach
				</tbody>
			</table>
		<button type="button" class="mb-3 btn btn-success" onclick="check_iscr('0')">
			Aggiorna descrizioni e vai al 3° Step
		</button>	
		<button type="button" class="mb-3 btn btn-primary" onclick="$('#div_riattiva').toggle()">
			Riattiva elenchi storicizzati
		</button>
		
		<input type='hidden' name='riattivazione' id='riattivazione'>
		<button class="mb-3 ml-2 btn btn-outline-secondary" onclick="history.go(-1);">Indietro </button>		

		<div id='div_riattiva' style='display:none' class='mt-2'>
			<hr>
			<h6>Scegli una o più condizioni sindacali da rendere attivi con un periodo stabilito nello step successivo</h6>
			<ul class="list-group-item">
				<li class="list-group-item">
					<input type='checkbox' name='c_riattiva[]' id='ri0' class='sele_riattiva form-check-input' value='0'> Liberi
				</li>
				<li class="list-group-item">
					<input type='checkbox' name='c_riattiva[]' id='ri1' class='sele_riattiva form-check-input' value='1'> Fillea
				</li>
				<li class="list-group-item">
					<input type='checkbox' name='c_riattiva[]' id='ri2' class='sele_riattiva form-check-input' value='2'> Filca
				</li>
				<li class="list-group-item">
					<input type='checkbox' name='c_riattiva[]' id='ri3' class='sele_riattiva form-check-input' value='3'> Feneal
				</li>
				<li class="list-group-item">
					<input type='checkbox' name='c_riattiva[]' id='ri4' class='sele_riattiva form-check-input' value=' '> Non Spec.
				</li>

			</ul>	
			<button type="button" class="mb-3 btn btn-success" onclick="check_iscr('1');">
				Vai allo step riattivazione
			</button>
			
		</div>

</form>
@endsection
