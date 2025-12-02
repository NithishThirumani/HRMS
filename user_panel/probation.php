<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Probation Approvals</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">
</head>
<body class="bg-gray-100 p-10">
    <div class="max-w-4xl mx-auto bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-bold mb-4">Pending Probation Approvals</h2>
        <table class="w-full text-left border-collapse">
            <thead>
                
    <tr>
        <th class="border-b p-2">Employee ID</th>
        <th class="border-b p-2">Name</th>
        <th class="border-b p-2">Date of Joining</th>
        <th class="border-b p-2">Probation Status</th>
        <th class="border-b p-2">Probation Passed Date</th>
        <th class="border-b p-2">Actions</th>
    
                </tr>
            </thead>
            <tbody id="probationTable">
                <!-- Data will be loaded here -->
            </tbody>
        </table>
    </div>

    <script>
       async function fetchProbationData() {
    try {
        const response = await fetch('probation_status.php');
        const data = await response.json();

        const tableBody = document.getElementById('probationTable');
        tableBody.innerHTML = '';

        data.forEach(employee => {
            const doj = employee.doj === '0000-00-00' ? 'Invalid Date' : employee.doj;
            const probationStatus = employee.probation_status;
            const probationPassedDate = employee.probation_passed_date ?? 'N/A';

            const rowClass = probationStatus === 'Completed' ? 'bg-green-100' : (doj === 'Invalid Date' ? 'bg-red-100' : '');

            const row = `
                <tr class="${rowClass}">
                    <td class="border-b p-2">${employee.eid}</td>
                    <td class="border-b p-2">${employee.user_name}</td>
                    <td class="border-b p-2">${doj}</td>
                    <td class="border-b p-2">${probationStatus}</td>
                    <td class="border-b p-2">${probationPassedDate}</td>
                    <td class="border-b p-2">
                        ${probationStatus === 'Pending' ? '<button class="bg-green-500 text-white px-3 py-1 rounded">Approve</button>' : ''}
                    </td>
                </tr>
            `;
            tableBody.innerHTML += row;
        });
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

fetchProbationData();

    </script>
</body>
</html>
