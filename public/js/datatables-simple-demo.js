window.addEventListener('DOMContentLoaded', event => {
    // Simple-DataTables
    // https://github.com/fiduswriter/Simple-DataTables/wiki

	const datatablesSimple = document.getElementById('tb_report');
    if (datatablesSimple) {
			new simpleDatatables.DataTable(datatablesSimple , {	
				labels: {	
					placeholder: "Cerca...",
					perPage: "{select} tabulati per pagina",
					noRows: "Non ci sono tabulati",
					info: "Range {start} - {end} di {rows} tabulati",
				}
			})
    }

	const datatablesSimple1 = document.getElementById('tb_pub');
    if (datatablesSimple1) {
			new simpleDatatables.DataTable(datatablesSimple1 , {	
				labels: {	
					placeholder: "Cerca...",
					perPage: "{select} pubblicazioni per pagina",
					noRows: "Non ci sono pubblicazioni",
					info: "Range {start} - {end} di {rows} pubblicazioni",
				}
			})
    }

	const datatablesSimple2 = document.getElementById('tb_option');
    if (datatablesSimple2) {
			new simpleDatatables.DataTable(datatablesSimple2 , {	
				labels: {	
					placeholder: "Cerca...",
					perPage: "{select} modelli importazione per pagina",
					noRows: "Non ci sono modelli di importazione",
					info: "Range {start} - {end} di {rows} modelli",
				}
			})
    }
	
	const datatablesSimple3 = document.getElementById('tb_schema');
    if (datatablesSimple3) {
			new simpleDatatables.DataTable(datatablesSimple3 , {	
				labels: {	
					placeholder: "Cerca...",
					perPage: "{select} modelli importazione per pagina",
					noRows: "Non ci sono modelli di importazione",
					info: "Range {start} - {end} di {rows} modelli",
				}
			})
    }	
	
	const datatablesSimple4 = document.getElementById('tb_test');
    if (datatablesSimple4) {
			new simpleDatatables.DataTable(datatablesSimple4 , {	
				labels: {	
					placeholder: "Cerca...",
					perPage: "{select} Nominativi per pagina",
					noRows: "Non ci nominativi",
					info: "Range {start} - {end} di {rows} nominativi",
				}
			})
    }	

});
