document.addEventListener('DOMContentLoaded', function () {
	var calendarEl = document.getElementById('calendar');
	if (!calendarEl) {
		return;
	}

	var events = Array.isArray(window.scheduleReservationEvents) ? window.scheduleReservationEvents : [];
	var calendar = new FullCalendar.Calendar(calendarEl, {
		initialView: 'dayGridMonth',
		height: 'auto',
		events: events,
		eventTimeFormat: {
			hour: '2-digit',
			minute: '2-digit',
			meridiem: 'short',
		},
	});
	calendar.render();
});
