$.wpskaLoad([
	"https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.4.3/flatpickr.min.css",
	"https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.4.3/flatpickr.min.js",
], function() {

	var Portuguese = {
		weekdays: {
			shorthand: ["Dom", "Seg", "Ter", "Qua", "Qui", "Sex", "Sáb"],
			longhand: ["Domingo", "Segunda-feira", "Terça-feira", "Quarta-feira", "Quinta-feira", "Sexta-feira", "Sábado",],
		},
		months: {
			shorthand: ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul", "Ago", "Set", "Out", "Nov", "Dez"],
			longhand: ["Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"],
		},
		rangeSeparator: " até ",
	};


	$("[data-flatpickr]").each(function() {
		var params = $(this).wpskaParams("data-flatpickr", {
			dateFormat: 'Y-m-d H:i:S',
			altInput: true,
			altFormat: 'd/m/Y - H:i',
			altInputClass: 'form-control',
			enableTime: true,
			time_24hr: true,
			locale: Portuguese,
		});
		var pickr = flatpickr(this, params);
		// $(this).flatpickr(params);
		this.flatpickr = function() { return pickr; };
	});
});
