$(document).ready(function() {
    let departmentRatingsChart = null;
    let completionChart = null;

    // Initialize DataTable
    const statsTable = $('#statsTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "columns": [
            { "data": "name" },
            { "data": "total_employees" },
            { "data": "completed" },
            { "data": "pending" },
            { "data": "avg_rating" },
            { "data": null, render: function(data) {
                return `<button class="btn btn-info btn-sm view-details" 
                        data-id="${data.id}">View Details</button>`;
            }}
        ]
    });

    // Initialize Charts
    function initializeCharts(data) {
        // Destroy existing charts if they exist
        if (departmentRatingsChart) departmentRatingsChart.destroy();
        if (completionChart) completionChart.destroy();

        // Department Ratings Chart
        const ratingsCtx = document.getElementById('departmentRatingsChart').getContext('2d');
        departmentRatingsChart = new Chart(ratingsCtx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.name),
                datasets: [{
                    label: 'Average Rating',
                    data: data.map(d => d.avg_rating),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
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

        // Completion Status Chart
        const completionCtx = document.getElementById('completionChart').getContext('2d');
        completionChart = new Chart(completionCtx, {
            type: 'pie',
            data: {
                labels: ['Completed', 'Pending'],
                datasets: [{
                    data: [
                        data.reduce((sum, d) => sum + parseInt(d.completed), 0),
                        data.reduce((sum, d) => sum + parseInt(d.pending), 0)
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(255, 99, 132, 0.5)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            }
        });
    }

    // Load department statistics
    function loadStats() {
        const period_id = $('#periodFilter').val();

        $.ajax({
            url: 'appraisal/ajax/department_reports.php',
            type: 'GET',
            data: { period_id: period_id },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    statsTable.clear().rows.add(response.data).draw();
                    initializeCharts(response.data);
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to load department statistics', 'error');
            }
        });
    }

    // Event handlers
    $('#periodFilter').on('change', loadStats);

    // View department details
    $(document).on('click', '.view-details', function() {
        const departmentId = $(this).data('id');
        const period_id = $('#periodFilter').val();
        window.location.href = `department_detail.php?id=${departmentId}&period=${period_id}`;
    });

    // Initial load
    loadStats();
});