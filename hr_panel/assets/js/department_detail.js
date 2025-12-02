$(document).ready(function() {
    let ratingDistributionChart = null;
    let criteriaChart = null;

    // Initialize DataTable
    const employeeTable = $('#employeeTable').DataTable({
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "columns": [
            { "data": null, render: function(data) {
                return data.first_name + ' ' + data.last_name;
            }},
            { "data": "position" },
            { "data": "status" },
            { "data": "self_rating" },
            { "data": "hod_rating" },
            { "data": "hr_rating" },
            { "data": "final_rating" },
            { "data": null, render: function(data) {
                return `<button class="btn btn-info btn-sm view-appraisal" 
                        data-id="${data.appraisal_id}">View</button>`;
            }}
        ]
    });

    function loadDepartmentData() {
        $.ajax({
            url: 'appraisal/ajax/department_detail.php',
            type: 'GET',
            data: {
                department_id: departmentId,
                period_id: periodId
            },
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    employeeTable.clear().rows.add(response.employees).draw();
                    initializeCharts(response);
                }
            }
        });
    }

    function initializeCharts(data) {
        // Rating Distribution Chart
        if (ratingDistributionChart) ratingDistributionChart.destroy();
        const distributionCtx = document.getElementById('ratingDistributionChart').getContext('2d');
        ratingDistributionChart = new Chart(distributionCtx, {
            type: 'bar',
            data: {
                labels: data.distribution.map(d => d.rating_range + ' Stars'),
                datasets: [{
                    label: 'Number of Employees',
                    data: data.distribution.map(d => d.count),
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
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

    // View individual appraisal
    $(document).on('click', '.view-appraisal', function() {
        const appraisalId = $(this).data('id');
        window.location.href = `review_form.php?id=${appraisalId}`;
    });

    // Initial load
    loadDepartmentData();
});