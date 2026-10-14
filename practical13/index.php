
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Practical 13 - Student CRUD</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #fff9d6;
            margin: 30px;
        }

        h1 {
            color: #6c4ab6;
        }

        input, button {
            padding: 10px;
            margin: 5px;
        }

        button {
            cursor: pointer;
            background-color: #6c4ab6;
            color: white;
            border: none;
            border-radius: 4px;
        }

        .delete {
            background-color: #c0392b;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }

        th, td {
            border: 1px solid #999;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #6c4ab6;
            color: white;
        }

        #message {
            margin-top: 15px;
        }

        @media (max-width: 700px) {
            body {
                margin: 10px;
            }

            table {
                font-size: 12px;
            }

            th, td {
                padding: 5px;
            }
        }
    </style>
</head>

<body>

    <h1>Student Management System</h1>

    <form id="studentForm">
        <input type="hidden" id="student_id">

        <input type="text" id="name"
               placeholder="Student Name" maxlength="100" required>

        <input type="email" id="email"
               placeholder="Email" maxlength="100" required>

        <input type="text" id="course"
               placeholder="Course" maxlength="100" required>

        <input type="number" id="year"
               placeholder="Year" min="1" max="10" required>

        <button type="submit" id="saveBtn">Add Student</button>
        <button type="button" onclick="resetForm()">Clear</button>
    </form>

    <p id="message" role="status" aria-live="polite">
        Loading students...
    </p>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Year</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody id="studentList"></tbody>
    </table>

    <script>
        const api = "api.php";

        const form = document.getElementById("studentForm");
        const message = document.getElementById("message");
        const list = document.getElementById("studentList");
        const saveBtn = document.getElementById("saveBtn");

        async function request(options = {}) {
            const response = await fetch(api, options);
            const text = await response.text();

            let data;

            try {
                data = JSON.parse(text);
            } catch (error) {
                console.error("Invalid API response:", text);
                throw new Error(
                    "Invalid JSON response. Check PHP errors in the console."
                );
            }

            if (!response.ok) {
                throw new Error(data.error || "Request failed");
            }

            return data;
        }

        // Read and display students
        async function loadStudents() {
            message.textContent = "Loading students...";

            try {
                const students = await request();
                list.innerHTML = "";

                students.forEach(function(student) {
                    const row = document.createElement("tr");

                    [
                        student.student_id,
                        student.name,
                        student.email,
                        student.course,
                        student.year
                    ].forEach(function(value) {
                        const cell = document.createElement("td");
                        cell.textContent = value ?? "";
                        row.appendChild(cell);
                    });

                    const actions = document.createElement("td");

                    const editBtn = document.createElement("button");
                    editBtn.textContent = "Edit";
                    editBtn.onclick = function() {
                        editStudent(student);
                    };

                    const deleteBtn = document.createElement("button");
                    deleteBtn.textContent = "Delete";
                    deleteBtn.className = "delete";
                    deleteBtn.onclick = function() {
                        deleteStudent(student.student_id);
                    };

                    actions.appendChild(editBtn);
                    actions.appendChild(deleteBtn);
                    row.appendChild(actions);
                    list.appendChild(row);
                });

                message.textContent =
                    students.length + " student(s) loaded successfully.";

            } catch (error) {
                message.textContent = "Error: " + error.message;
            }
        }

        // Add or update a student
        form.addEventListener("submit", async function(event) {
            event.preventDefault();

            const id = document.getElementById("student_id").value;

            const data = {
                action: id ? "update" : "add",
                student_id: id,
                name: document.getElementById("name").value.trim(),
                email: document.getElementById("email").value.trim(),
                course: document.getElementById("course").value.trim(),
                year: document.getElementById("year").value
            };

            if (!data.name || !data.email ||
                !data.course || !data.year) {
                message.textContent = "Please fill in all fields.";
                return;
            }

            message.textContent = "Saving student...";
            saveBtn.disabled = true;

            try {
                const result = await request({
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                resetForm();
                await loadStudents();

                message.textContent = result.message;

            } catch (error) {
                message.textContent = "Error: " + error.message;

            } finally {
                saveBtn.disabled = false;
            }
        });

        // Fill form for editing
        function editStudent(student) {
            document.getElementById("student_id").value =
                student.student_id;

            document.getElementById("name").value = student.name;
            document.getElementById("email").value = student.email;
            document.getElementById("course").value = student.course;
            document.getElementById("year").value = student.year;

            saveBtn.textContent = "Update Student";

            message.textContent =
                "Editing student ID " + student.student_id;
        }

        // Delete a student
        async function deleteStudent(id) {
            if (!confirm("Are you sure you want to delete this student?")) {
                return;
            }

            message.textContent = "Deleting student...";

            try {
                const result = await request({
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({
                        action: "delete",
                        student_id: id
                    })
                });

                await loadStudents();
                message.textContent = result.message;

            } catch (error) {
                message.textContent = "Error: " + error.message;
            }
        }

        // Clear the form
        function resetForm() {
            form.reset();
            document.getElementById("student_id").value = "";
            saveBtn.textContent = "Add Student";
        }

        loadStudents();
    </script>

</body>
</html>
