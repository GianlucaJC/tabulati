@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_zz')

<form method='post' action="{{ route('step_zz1') }}" id='frm_zz1' name='frm_zz1' autocomplete="off">
<input name="_token" type="hidden" value="{{ csrf_token() }}">

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
									<input type='radio' name='sele_x[]' class='form-check-input selee' id='s{{ $ss }}'  value='{{ $report->TB }}-{{ $report->code_CE }}' data-idref='{{ $ss }}'>
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
									
									<small class="form-text text-muted">
									<?php echo $descr_old; ?>
									</small>

								</td>
								<td>

									<small class="form-text text-muted">
										{{ date('d-m-Y', strtotime($report->decorrenza_tab)) }}
									</small>

									
								</td>
								<td>

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
		<button type="submit" class="mb-3 btn btn-success" >
			Scegli un ente associato alla struttura
		</button>	

		<button class="mb-3 ml-2 btn btn-outline-secondary" onclick="history.go(-1);">Indietro </button>		


</form>
@endsection
