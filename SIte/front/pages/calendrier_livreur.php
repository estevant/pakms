<?php include_once('../includes/header.php'); ?>
<div class="max-w-5xl mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6 text-center text-[#212121]">📆 Mon planning de trajets</h1>
    <div id="calendar"></div>
</div>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales-all.global.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'timeGridWeek',
        locale: 'fr',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,listWeek'
        },
        events: '<?= BASE_API ?>/livreur/planning',
        eventClick: function(info) {
            alert(`Trajet : ${info.event.title}\nDébut : ${info.event.start.toLocaleString()}`);
        }
    });

    calendar.render();
});
</script>
<?php include_once('../includes/footer.php'); ?>
