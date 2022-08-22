@extends('viewboot.dist.index')
@section('title', 'Tabulati-FO')

@section('content_table_option')
	<form method='post' action="{{ route('view_schema') }}" id='frm_schema2' name='frm_schema2' autocomplete="off">
		<input name="_token" type="hidden" value="{{ csrf_token() }}">
		
		<input type='hidden' name='id_modello[]' value='{{ $id_modello }}'>
			<div class="alert alert-info" role="alert">
				Definizione dello schema
			</div>					

			  @if ($save==true) 
					<div class="alert alert-success" role="alert">
						<b>Dati salvati con successo!</b>
					</div>					
			  @endif
			  <div class="form-group row mt-2">
				<label for="{{ $associazione }}" class="col-sm-2 col-form-label">Descrizione Associazione</label>
				<div class="col-sm-10">
				  <input type="text" class="form-control" id="{{ $associazione }}" placeholder="Descrizione" value='{{ $associazione }}' name='associazione'>
				</div>
			  </div>				
			  <div class="form-group row mt-2">
				<label for="{{ $associazione }}" class="col-sm-2 col-form-label">Note</label>
				<div class="col-sm-10">
					<textarea class="form-control" id="note" name="note" rows="3" placeholder='Note ulteriori'>{{ $note }}</textarea>
				</div>
			  </div>				
			  
				<hr>
			<?php

				$json = file_get_contents("tracciati/standard.json");
				$infocampi = json_decode($json,true);
				
				$json = file_get_contents("tracciati/".$file_json);
				$tracciato = json_decode($json,true);

				?>
				

				
				<div class="form-group row mt-2">
					<label for="tipo_importazione" class="col-sm-2 col-form-label">Tipo Importazione</label>
					<div class="col-sm-4">
						<select class="form-control" id="tipo_importazione" name="tipo_importazione" onchange="set_tipo_imp(this.value)">
						  <option value="M"
						    <?php
								if ($tipo_importazione==null || $tipo_importazione=="M")
									echo " selected ";
						    ?>
						  >Manuale (dati csv già pronti)
						  </option>

						  <option value="A"
						    <?php
								if ($tipo_importazione=="A")
									echo " selected ";
						    ?>

						  >Con automatismi (sind_mens, attivi, etc.)
						  </option>

						</select>
					</div>	
				</div>	
						
				<hr>
				<div class="form-group row mt-4">
					<div class="col-sm-2"><b>Campo</b></div>
					<div class="col-sm-2"><b>Posizione</b></div>
				</div>
				<hr>

				<?php	


				foreach($infocampi['struttura'] as $campo=>$value) {
					$pos="N";
					$dis="";
					if (isset($tracciato['struttura'][$campo]['pos']))
						$pos=$tracciato['struttura'][$campo]['pos'];
						$class="";
						if ($campo=="attivi") $dis="disabled";
						if ($campo=="sind_mens1" || $campo=="sind_mens2" || $campo=="sind_mens3" || $campo=="sind_mens4" || $campo=="sind_mens5") {
							$class="campiauto";
							if ($tipo_importazione=="A") $dis="disabled";
							
						}	
						
					?>		
					  <div class="form-group row mt-2">
						<label for="{{ $campo }}" class="col-sm-2 col-form-label">{{ $campo }}</label>
						<div class="col-sm-2">

							<select class="form-control <?php echo $class; ?>" id="{{ $campo }}" name="{{ $campo }}" >
							 <!-- {{ $dis }} !-->
							  <option value="N"
							  <?php if ($pos=="N") echo "selected"; ?>
							  >Non Importare
							  </option>
							  <?php
								for ($sca=0;$sca<=100;$sca++) {
									echo "<option value='$sca'";
									if ($sca==$pos) echo " selected ";
									echo ">$sca</option>";
								}
							  ?>
							</select>
							 
							 <!--
							 <input type="text" class="form-control" id="{{ $campo }}" name="{{ $campo }}" placeholder="{{ $campo }}" value='{{ $pos }}'>
							 !-->
							 
						</div>
					  </div>				
					<?php 
				}
			?>
			<hr>
			<button type='submit' class="btn btn-success mb-4" name='save_schema' value='save'>
				<i class="fas fa-save"></i> Salva Schema
			</button><br>
			<a href="{{ url('modelli_import') }}">Torna all'elenco</a>
			<hr>
			
			
	
	</form>
@endsection
