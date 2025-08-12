<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.html');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário - Sistema de Engenharia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        :root {
            --cor-principal: #012A4A;
            --cor-vibrante: #00B4D8;
            --cor-fundo-calendario: #FFFFFF;
            --cor-texto-calendario: #333333;
            --cor-borda-calendario: #EAEAEA;
            --cor-dia-hoje: #00B4D8;
            --cor-evento-concluido: #2a9d8f;
            --cor-evento-pendente: #0077b6;
        }

        body, html { height: 100%; margin: 0; padding: 0; font-family: 'Inter', sans-serif; background-color: var(--cor-principal); }

        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 2rem;
            height: auto;
            min-height: calc(100vh - 70px);
            padding: 2rem;
            margin-top: 70px;
        }

        .simple-calendar-container {
            background-color: var(--cor-fundo-calendario);
            color: var(--cor-texto-calendario);
            width: 95%;
            max-width: 1400px;
            /* height: 70vh; Removido para permitir altura dinâmica */
            display: flex;
            flex-direction: column;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            overflow: hidden;
        }

        .calendar-header { display: flex; justify-content: space-between; align-items: center; padding: 1.5rem; border-bottom: 1px solid var(--cor-borda-calendario); }
        .calendar-header h2 { font-size: 1.5rem; font-weight: 600; margin: 0; }
        .calendar-nav { color: var(--cor-principal); text-decoration: none; font-weight: 500; padding: 8px 12px; border-radius: 8px; transition: background-color 0.2s ease; }
        .calendar-nav:hover { background-color: #EAEAEA; }

        .simple-calendar { width: 100%; height: 100%; border-collapse: collapse; table-layout: fixed; }
        .simple-calendar th { padding: 1rem 0.5rem; text-align: center; font-weight: 600; color: var(--cor-texto-calendario); border-bottom: 1px solid var(--cor-borda-calendario); }
        .simple-calendar td { border: 1px solid var(--cor-borda-calendario); vertical-align: top; padding: 0.5rem; }

        .day-content { display: flex; flex-direction: column; height: 100%; }
        .day-number { font-size: 0.85rem; font-weight: 500; margin-bottom: 0.5rem; text-align: left; padding: 4px; }
        .today .day-number { background-color: var(--cor-dia-hoje); color: white; border-radius: 50%; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
        .simple-calendar .empty { background-color: #FCFCFC; }
        .events { flex-grow: 1; overflow-y: auto; text-align: left; }

        .event { font-size: 0.8rem; color: white; padding: 4px 8px; border-radius: 6px; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; transition: opacity 0.2s ease; }
        .event:hover { opacity: 0.85; }
        .event.status-completed { background-color: var(--cor-evento-concluido); }
        .event.status-pending { background-color: var(--cor-evento-pendente); }

        .event-list-container { background-color: var(--cor-fundo-calendario); color: var(--cor-texto-calendario); width: 95%; max-width: 1400px; padding: 1.5rem 2rem; border-radius: 16px; box-shadow: 0 8px 24px rgba(0,0,0,0.15); }
        .event-list-section { margin-bottom: 2rem; }
        .event-list-section:last-child { margin-bottom: 0; }
        .event-list-header { font-size: 1.5rem; font-weight: 600; color: var(--cor-principal); margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--cor-borda-calendario); }
        .event-list ul { list-style: none; padding: 0; margin: 0; }
        .event-list-item { display: flex; align-items: center; padding: 1rem 0.5rem; border-bottom: 1px solid var(--cor-borda-calendario); }
        .event-list-item:last-child { border-bottom: none; }
        .event-item-date { font-weight: 600; padding: 0.5rem 1rem; border-radius: 8px; min-width: 120px; text-align: center; margin-right: 1.5rem; color: white; }
        .event-list-item.status-completed .event-item-date { background-color: var(--cor-evento-concluido); }
        .event-list-item.status-pending .event-item-date { background-color: var(--cor-evento-pendente); }
        .event-item-text { font-size: 1rem; font-weight: 500; }
        .event-list-item.status-completed .event-item-text { text-decoration: line-through; color: #888; }

        .modal-overlay { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.6); display: none; justify-content: center; align-items: center; z-index: 1001; backdrop-filter: blur(5px); }
        .modal-content { background-color: white; padding: 2rem; border-radius: 16px; box-shadow: 0 5px 20px rgba(0,0,0,0.3); width: 90%; max-width: 500px; animation: slide-down 0.3s ease-out; }
        @keyframes slide-down { from { transform: translateY(-30px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #dee2e6; padding-bottom: 1rem; margin-bottom: 1rem; }
        .modal-header h3 { margin: 0; font-size: 1.5rem; color: var(--cor-principal); }
        .close-button { background: none; border: none; font-size: 1.8rem; cursor: pointer; color: #6c757d; transition: color 0.2s ease; }
        .close-button:hover { color: #333; }
        #modal-body p { font-size: 1.1rem; line-height: 1.6; color: var(--cor-texto-calendario); }

        .modal-footer {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid #dee2e6;
            text-align: right;
        }
    </style>
</head>
<body>
    <?php
        include 'includes/header.php';
        include 'includes/sidebar.php';

        // Buscar eventos da API
        $events_json = file_get_contents('http://localhost/polis/api/eventos.php');
        $events_data = json_decode($events_json, true);

        if ($events_data === null && json_last_error() !== JSON_ERROR_NONE) {
            // Lidar com erro na decodificação JSON ou API vazia
            $events_data = [];
            // Opcional: logar o erro ou exibir uma mensagem para depuração
            // error_log("Erro ao decodificar JSON de eventos: " . json_last_error_msg());
        }

        if (isset($_GET['view']) && $_GET['view'] === 'today') {
            $month = date('n');
            $year = date('Y');
        } else {
            $month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
            $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        }

        $daily_events_map = [];
        if (is_array($events_data)) {
            foreach ($events_data as $event) {
                $start_date = new DateTime($event['data_inicio']);
                $end_date = new DateTime($event['data_fim']);
                $end_date->modify('+1 day'); // Para incluir o dia final
                $period = new DatePeriod($start_date, new DateInterval('P1D'), $end_date);
                foreach ($period as $date) {
                    $date_key = $date->format('Y-n-j');
                    $daily_events_map[$date_key][] = $event;
                }
            }
        }

        // Filtrar eventos pendentes e concluídos
        $pending_events = array_filter($events_data, function($e) { return $e['status'] === 'Pendente'; });
        $completed_events = array_filter($events_data, function($e) { return $e['status'] === 'Concluido'; });

        // Ordenar eventos pendentes e concluídos por data de início
        usort($pending_events, function($a, $b) { return strcmp($a['data_inicio'], $b['data_inicio']); });
        usort($completed_events, function($a, $b) { return strcmp($a['data_inicio'], $b['data_inicio']); });

        $first_day_of_month = mktime(0, 0, 0, $month, 1, $year);
        $num_days_in_month = date('t', $first_day_of_month);
        $months_in_portuguese = [1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'];
        $month_name = $months_in_portuguese[$month];
        $first_day_of_week = date('w', $first_day_of_month);
        $prev_month = $month - 1; $prev_year = $year;
        if ($prev_month == 0) { $prev_month = 12; $prev_year--; }
        $next_month = $month + 1; $next_year = $year;
        if ($next_month == 13) { $next_month = 1; $next_year++; }
        $days_of_week = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    ?>
        <div class="simple-calendar-container">
            <div class="calendar-header">
                <a href="?month=<?php echo $prev_month; ?>&year=<?php echo $prev_year; ?>" class="calendar-nav">&lt; Anterior</a>
                <h2><?php echo $month_name . ' ' . $year; ?></h2>
                <a href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>" class="calendar-nav">Próximo &gt;</a>
            </div>
            <table class="simple-calendar" id="calendar-table">
                <thead><tr><?php foreach ($days_of_week as $day) echo "<th>{$day}</th>"; ?></tr></thead>
                <tbody>
                    <tr>
                        <?php
                        for ($i = 0; $i < $first_day_of_week; $i++) { echo '<td class="empty"></td>'; }
                        $current_day = 1;
                        $day_of_week_index = $first_day_of_week;
                        while ($current_day <= $num_days_in_month) {
                            if ($day_of_week_index == 7) { echo '</tr><tr>'; $day_of_week_index = 0; }
                            $class = ($current_day == date('d') && $month == date('m') && $year == date('Y')) ? 'today' : '';
                            echo "<td class='{$class}'>";
                            echo "<div class='day-content'>";
                            echo "<div class='day-number'>{$current_day}</div>";
                            $event_key = "{$year}-{$month}-{$current_day}";
                            if (isset($daily_events_map[$event_key])) {
                                echo '<div class="events">';
                                foreach ($daily_events_map[$event_key] as $event) {
                                    $event_text_html = htmlspecialchars($event['text'], ENT_QUOTES, 'UTF-8');
                                    $status_class = 'status-' . $event['status'];
                                    echo "<div class='event {$status_class}' data-event-text='{$event_text_html}'>{$event['text']}</div>";
                                }
                                echo '</div>';
                            }
                            echo "</div></td>";
                            $current_day++;
                            $day_of_week_index++;
                        }
                        while ($day_of_week_index < 7 && $day_of_week_index != 0) { echo '<td class="empty"></td>'; $day_of_week_index++; }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="event-list-container">
            <div class="event-list-section">
                <h3 class="event-list-header">Não Finalizados</h3>
                <ul>
                    <?php if (empty($pending_events)): ?>
                        <li>Nenhum evento pendente.</li>
                    <?php else: ?>
                        <?php foreach ($pending_events as $event):
                            $start_obj = new DateTime($event['start']);
                            $end_obj = new DateTime($event['end']);
                            $date_display = $start_obj->format('d/m');
                            if ($event['start'] !== $event['end']) { $date_display .= ' a ' . $end_obj->format('d/m'); }
                        ?>
                            <li class="event-list-item status-pending">
                                <div class="event-item-date"><?php echo $date_display; ?></div>
                                <div class="event-item-text"><?php echo htmlspecialchars($event['text']); ?></div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="event-list-section">
                <h3 class="event-list-header">Finalizados</h3>
                <ul>
                    <?php if (empty($completed_events)): ?>
                        <li>Nenhum evento finalizado.</li>
                    <?php else: ?>
                        <?php foreach ($completed_events as $event):
                            $start_obj = new DateTime($event['start']);
                            $end_obj = new DateTime($event['end']);
                            $date_display = $start_obj->format('d/m');
                            if ($event['start'] !== $event['end']) { $date_display .= ' a ' . $end_obj->format('d/m'); }
                        ?>
                            <li class="event-list-item status-completed">
                                <div class="event-item-date"><?php echo $date_display; ?></div>
                                <div class="event-item-text"><?php echo htmlspecialchars($event['text']); ?></div>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </main>

    <div id="eventModal" class="modal-overlay" onclick="closeModal()">
        <div class="modal-content" onclick="event.stopPropagation()">
            <div class="modal-header"><h3>Detalhes do Evento</h3><button class="close-button" onclick="closeModal()">&times;</button></div>
            <div id="modal-body"></div>
            <div class="modal-footer">
                <button class="btn btn-primary">Editar Tarefa</button>
            </div>
        </div>
    </div>

    <script>
        const modal = document.getElementById('eventModal');
        const modalBody = document.getElementById('modal-body');
        const calendarTable = document.getElementById('calendar-table');

        calendarTable.addEventListener('click', function(event) {
            const eventDiv = event.target.closest('.event');
            if (eventDiv) {
                const eventText = eventDiv.getAttribute('data-event-text');
                modalBody.innerHTML = '';
                const p = document.createElement('p');
                p.textContent = eventText;
                modalBody.appendChild(p);
                modal.style.display = 'flex';
            }
        });

        function closeModal() {
            modal.style.display = 'none';
        }
    </script>

</body>
</html>
