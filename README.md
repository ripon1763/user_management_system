Prerequisites:
    PHP (>= 8.1.25)
    MySQL or MariaDB
    Web server (e.g., Apache, Nginx)

Steps:
    1. Clone the repository
    2. Navigate to the project directory
    3. Create a new MySQL database
    4. Import the user_management.sql file to create the necessary table
    5. Open config.php and update the database connection details
    6. Start the Web Server
    7. Open the web browser and navigate to the project

Usage:
    -Two demo users are already in user_management.sql, one's role is 'admin', another one's role is 'user'. You can login with the user whose role is 'admin' for the first time, password is '123456'
    -Two roles are here mainly. One role is 'admin' and another one is 'user'. You will get a column in 'users' table named 'role' which mainly stores this input while a new user is added by an admin from frontend.
    -Whose role is 'admin' can add new user, view all users list, edit any user info, delete a user too
    -Whose role is 'user' can only view his/her own user info, edit and delete his/her own user info
    -Input validation added where applicable