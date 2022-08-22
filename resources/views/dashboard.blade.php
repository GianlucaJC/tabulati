@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_report')
<form method='post' action="{{ route('step2') }}" id='frm_pub2' name='frm_pub2' autocomplete="off">
<input name="_token" type="hidden" value="{{ csrf_token() }}">
	<div id='div_table_report'>
			<table id="tb_report">
				<thead>
					<tr>
						<th>#</th>
						<th>TERRITORIO</th>
						<?php if (1==2) {?>

							<th>ENTE</th>
							<th>DESCRIZIONE</th>
							<th>VALIDO DA</th>
							<th>VALIDO FINO A</th>
						<?php } ?>

					</tr>
				</thead>

				<tbody>
						<?php
							$ss=0;
							
						?>	
						@foreach($reports as $report)
							<?php
							
								$code_CE=$report->code_CE;
								$descr1=$report->descr1;
								$descr2=$report->descr2;
								$descr3=$report->descr3;
								$descr4=$report->descr4;
								$descr5=$report->descr5;
							?>
							<tr>
								<td>
									<input type='radio' name='sele_e' class='form-check-input selee' id='s{{ $ss++ }}'  value='{{ $report->TB }}'>
								</td>
								<td>
									{{ $report->denominazione }}
								</td>
								<?php if (1==2) {?>
									<td>
										{{ $report->descr_ce }}
									</td>
									
									<td>
										<?php
											if (substr($descr1,0,1)==$code_CE)
												echo substr($descr1,1);
											if (substr($descr2,0,1)==$code_CE)
												echo substr($descr2,1);
											if (substr($descr3,0,1)==$code_CE)
												echo substr($descr3,1);
											if (substr($descr4,0,1)==$code_CE)
												echo substr($descr4,1);
											if (substr($descr5,0,1)==$code_CE)
												echo substr($descr5,1);
										?>

									</td>
									<td>
										{{ date('d-m-Y', strtotime($report->decorrenza_tab)) }}
									</td>
									<td>
										<?php 
											if ($report->fine_tab!="0000-00-00")
												echo date('d-m-Y', strtotime($report->fine_tab));
										?>
									</td>
								<?php } ?>
							</tr>
							
						@endforeach
				</tbody>
			</table>
			
	</div>
	
	<div id='div_table_schema' class='mt-2'>
			<table id="tb_schema">
				<thead>
					<tr>
						<th>#</th>
						<th>SCHEMA DA USARE PER L'IMPORTAZIONE</th>
					</tr>
				</thead>

				<tbody>
						<?php
							$ss=0;
							
						?>	
						@foreach($schema_import as $schema)
							<?php 
								$def="";
								if ($schema->id==1) $def="checked";
							?>	
							<tr>
								<td>
									<input type='radio' name='file_json' class='form-check-input sele_import' value='{{ $schema->file_json}}' <?php echo $def; ?>>
								</td>
								<td>
									{{ $schema->descrizione_associazione }}
								</td>
							</tr>
							
						@endforeach
				</tbody>
			</table>
			
	</div>	
	
		<button type="submit" class="mb-3 btn btn-success">
			Scegli un tabulato e vai al 2° Step
		</button>
	
</form>



@endsection
