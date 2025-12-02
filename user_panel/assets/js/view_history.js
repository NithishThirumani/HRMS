$(document).ready(function() {
    let ratingTrendsChart = null;
    let criteriaChart = null;

    // Initialize DataTable
    const appraisalTable = $('#appraisalTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "order": [[0, "desc"]],
        "columns": [
            { "data": null, render: function(data) {
                return date('M Y', new Date(data.start_date)) + ' - ' + 
                       date('M Y', new Date(data.end_date));
            }},
            { "data": "status" },
            { "data": "self_rating" },
            { "data": "hod_rating" },
            { "data": "hr_rating" },
            { "data": "final_rating" },
            { "data": null, render: function(data) {
                return '<button class="btn btn-info btn-sm view-details" ' +
                       'data-id="' + data.appraisal_id + '">View Details</button>';
            }}
        ]
    });

    function loadAppraisalHistory() {
        const period_id = $('#periodFilter').val();

        $.ajax({
            url: 'appraisal/ajax/history_actions.php',
            type: 'GET',
            data: {
                period_id: period_id
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    appraisalTable.clear().rows.add(response.history).draw();
                    updateCharts(response);
                }
            }
        });
    }

    function updateCharts(data) {
        // Rating Trends Chart
        if (ratingTrendsChart) ratingTrendsChart.destroy();
        const trendsCtx = document.getElementById('ratingTrendsChart').getContext('2d');
        ratingTrendsChart = new Chart(trendsCtx, {
            type: 'line',
            data: {
                labels: data.trends.map(t => date('M Y', new Date(t.start_date))),
                datasets: [{
                    label: 'Self Rating',
                    data: data.trends.map(t => t.self_rating),
                    borderColor: 'rgba(255, 99, 132, 1)',
                    fill: false
                }, {
                    label: 'HOD Rating',
                    data: data.trends.map(t => t.hod_rating),
                    borderColor: 'rgba(54, 162, 235, 1)',
                    fill: false
                }, {
                    label: 'HR Rating',
                    data: data.trends.map(t => t.hr_rating),
                    borderColor: 'rgba(75, 192, 192, 1)',
                    fill: false
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 5
                    }
                }
            }
        });

        // Criteria Performance Chart
        if (criteriaChart) criteriaChart.destroy();
        const criteriaCtx = document.getElementById('criteriaChart').getContext('2d');
        criteriaChart = new Chart(criteriaCtx, {
            type: 'radar',
            data: {
                labels: data.criteria.map(c => c.criteria_name),
                datasets: [{
                    label: 'Self Rating',
                    data: data.criteria.map(c => c.avg_self),
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderWidth: 1
                }, {
                    label: 'HOD Rating',
                    data: data.criteria.map(c => c.avg_hod),
                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }, {
                    label: 'HR Rating',
                    data: data.criteria.map(c => c.avg_hr),
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    r: {
                        beginAtZero: true,
                        max: 5
                    }
                }
            }
        });
    }

    // Helper function for date formatting
    function date(format, date) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 
                       'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        return format.replace('M', months[date.getMonth()])
                    .replace('Y', date.getFullYear());
    }

    // View details button click handler
    $(document).on('click', '.view-details', function() {
        const appraisalId = $(this).data('id');
        window.location.href = `view_appraisal.php?id=${appraisalId}`;
    });

    // Period filter change event handler
    $('#periodFilter').on('change', loadAppraisalHistory);

    // Initial load
    loadAppraisalHistory();
});