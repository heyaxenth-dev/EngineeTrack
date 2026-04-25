document.addEventListener('DOMContentLoaded', function () {
	var calendarEl = document.getElementById('calendar');
	if (!calendarEl) {
		return;
	}

	var events = Array.isArray(window.scheduleReservationEvents) ? window.scheduleReservationEvents : [];
	var calendarConfig = window.scheduleReservationCalendarConfig || {};

	function detectStatusFromTitle(title) {
		var normalized = String(title || '').toLowerCase();
		if (normalized.includes('(approved)')) return 'approved';
		if (normalized.includes('(rejected)')) return 'rejected';
		if (normalized.includes('(pending)')) return 'pending';
		return 'other';
	}

	var filteredEvents = events.slice();
	var calendar = new FullCalendar.Calendar(calendarEl, {
		initialView: calendarConfig.initialView || 'dayGridMonth',
		headerToolbar: calendarConfig.headerToolbar || {
			left: 'prev,next today',
			center: 'title',
			right: 'dayGridMonth,timeGridWeek,timeGridDay',
		},
		height: 'auto',
		events: filteredEvents,
		eventTimeFormat: {
			hour: '2-digit',
			minute: '2-digit',
			meridiem: 'short',
		},
	});
	calendar.render();

	var statusFilter = document.getElementById('calendarStatusFilter');
	if (statusFilter) {
		statusFilter.addEventListener('change', function () {
			var selectedStatus = statusFilter.value;
			if (selectedStatus === 'all') {
				filteredEvents = events.slice();
			} else {
				filteredEvents = events.filter(function (eventItem) {
					return detectStatusFromTitle(eventItem.title) === selectedStatus;
				});
			}

			calendar.removeAllEvents();
			calendar.addEventSource(filteredEvents);
		});
	}
});
