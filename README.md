# Choreo

Choreo is a mini task management application built using PHP Laravel for the backend, Vue.js for the frontend, and MySQL
for the database. It uses Vite for bundling the assets and TailwindCSS for styling.
It provides a simple and intuitive interface for managing tasks and tracking their progress.

## Getting Started Locally

This project runs on Docker using Laravel Sail. If you prefer not to use Docker, you can use PHP's built-in development 
server instead. In the interest of simplicity, this README only covers the Docker/Sail setup. For instructions on how to 
run the application without Docker, refer to the [Laravel documentation](https://laravel.com/docs/10.x/installation).

### Prerequisites

- Docker 

### Installation

1. Clone the repository to your local machine:

   ```bash
   git clone https://github.com/ashokgelal/choreo.git
   ```

2. Change into the project directory:

   ```bash
   cd choreo
   ```
3. Copy the `.env.example` file to `.env` and make any necessary changes to the environment variables:

   ```bash
   cp .env.example .env
   ```
   
4. Bootstrap the application's dependencies using Sail:

   ```bash
   docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs
   ```
   
5. Start the Docker containers:

   ```bash
   ./vendor/bin/sail up -d
   ```
6. Migrate the database tables:

    ```bash
   ./vendor/bin/sail php artisan migrate
    ```

7. Install the JavaScript dependencies using npm:

   ```bash
   ./vendor/bin/sail npm install
   ```

8. Run the Vite development server:

   ```bash
   ./vendor/bin/sail npm run dev
   ```

9. Browse to `http://localhost` to view the application.

### Optional Steps

1. Seed the database with sample data. This will create 1 user and 20 tasks.

    ```bash
    ./vendor/bin/sail php artisan db:seed
    ```
   
2. Run the application's tests:

    ```bash
    ./vendor/bin/sail php artisan test
    ```

# Choreo Features

Choreo offers several features to help you efficiently manage your tasks and track their 
progress. The key features of the application are as follows:

- **Create Tasks**
 
    Users can create new tasks by simply providing a description. Tasks serve as the main units of work in the application.

- **Create Subtasks for Tasks**

    Tasks can have subtasks associated with them. Subtasks break down larger tasks into smaller, manageable pieces. 

- **Track Task Status**

    Tasks can be assigned different statuses to reflect their progress. The application provides three status options:

    - **Todo**: The default state. This status signifies that the task has been created.
    - **In Progress**: This status indicates that work is actively being done on the task.
    - **Done**: This status signifies that the task has been completed.

## Email Reminder for Tasks in Progress

When a task's status is changed to "In Progress," the application sets a reminder for 24 hours later. If the task is 
still in progress after this timeframe, the application automatically sends an email reminder to the task owner. 
This feature helps users stay on top of their tasks and ensures important deadlines are not missed.

## Laravel Notification and Queue System for Asynchronous Email Delivery

Choreo leverages Laravel's built-in notification and queue system to handle the asynchronous delivery of email 
reminders. This approach ensures that email notifications are sent efficiently without impacting the 
application's performance or responsiveness. We also get the benefit of a robust, well tested, and reliable feature
that is already built into the framework.

# Architectural Design Process

The architectural design process for Choreo involved considering various factors, such as functionality, scalability, 
maintainability, and user experience. Here are some key aspects of the application's architectural design:

## MVC Architecture

The application follows the Model-View-Controller (MVC) architectural pattern, which helps separate concerns and 
improves code organization. The MVC pattern divides the application into three main components:

- **Model**: Represents the data and business logic of the application. In Choreo, the models define the structure 
            and relationships of tasks, subtasks, and users.
- **View**: Handles the presentation and user interface. In this application, Vue.js components are used to render
            the frontend views.
- **Controller**: Acts as the intermediary between the model and view, handling user requests, processing data, and 
            updating the model accordingly. Controllers in the application are responsible for tasks such as creating,
            updating, and retrieving tasks and subtasks. There are also controllers for handling user authentication.

## Authorization Policy

To ensure that users are authorized to perform specific actions, the application employs an authorization policy via
[Laravel's Authorization Policy](https://laravel.com/docs/10.x/authorization#creating-policies). The policy checks if 
a user is authorized to perform certain actions on a task, such as updating its status or modifying its details.
By implementing this policy, the application maintains control over access and ensures that users can only perform 
actions they are authorized to. Also, there are tests in place to verify that the authorization policy is working 
as expected.

## Testing Approach

Choreo emphasizes testing to maintain the quality and reliability of the codebase. The application includes 
integration tests for the crucial parts of the system, such as the controllers, authorization policies, notifications,
and queues. The tests are written using the PHPUnit testing framework and follow the Arrange-Act-Assert (AAA) pattern.

## Simplicity in Feature Implementation

To maintain a clear and unambiguous user experience, some features in Choreo have been deliberately kept simple. 
For example, the application restricts changing the status of a task if it has associated subtasks. This restriction 
helps avoid potential ambiguity and ensures that users follow a logical progression in task management.

Throughout the architectural design process, the focus has been on creating a robust, maintainable, and user-friendly 
Task Management Application. The MVC architecture, authorization policies, comprehensive testing, and simplicity in 
feature implementation contribute to achieving these goals.

# Challenges and Tradeoffs
One of the biggest challenges in developing Choreo was determining the most effective strategy for sending email 
reminders. I considered two options: running a scheduled job or queuing a reminder right away when a task is marked 
as "In Progress." Evaluating and selecting the best approach required careful consideration of several factors.

1. Running a Scheduled Job:

   This option involved implementing a scheduled job that would run at regular intervals (e.g., every minute) to 
   check all tasks marked as "In Progress" for over 24 hours. It would then send email reminders for these tasks. 
   
   It has a big benefit of being able to send one email with all the in-progress tasks. However, it would have 
   required the application to run a scheduled job every minute, even if there were no tasks to be processed.
   This would result in unnecessary resource consumption and impacted the application's performance esp. considering
   Laravel boots up the entire application for each scheduled job.

   This approach also needs writing more code to 
   handle for writing a scheduled job and querying the database for tasks that need to be processed.
   More code means more maintenance and more chances of bugs. 


2. Queueing a Reminder Right Away:

   The alternative approach involved queuing a reminder immediately when a task was marked as "In Progress" for the
   first time. I found this to be quite simpler to implement than the scheduled job approach and definitely needed 
   less code and complexity. It also has the benefit of not requiring the application to run a scheduled job every
   minute. The built-in `delay` feature of Laravel's notification system made it easy to set a reminder for 24 hours
   later without having to run a scheduled job just for checking. Also, the application wouldn't need to query the
   database for tasks that need to be processed every minute.

   However, this approach has one significant drawback -
   It results in one email being sent for each task that were marked as "In Progress" for over 24 hours for one user.


After careful consideration, I decided to go with the second approach. I chose this approach because it was simpler, was
less resource-intensive, and required less code. No two asynchronous "workflows" running - one for the queue and one
for the scheduler, meant it would be easy to maintain and onboard new members. I also felt that the drawback of sending 
one email per task was not significant enough to outweigh the benefits of this approach.

Once I decided to go with the second approach, I had to figure out two technical challenges:
1. How to not send an email reminder if the task's status was changed to "Done" before the 24-hour timeframe.
2. How to not send an email reminder if the task's status was changed to some other status and then back to "In Progress"
   within the 24-hour timeframe.

For the first one, I decided to use the [shouldSend()](https://laravel.com/docs/10.x/notifications#determining-if-the-queued-notification-should-be-sent)
method of the notification class. This method is called before the notification is sent and can be used to determine 
if the notification should be sent. In our case, we check if the status is still "In Progress" or not and return false
if it is not.

For the second one, I decided to tap into `progress_started_at` column of the `tasks` table. This column stores the
timestamp when the task's status was changed to "In Progress" for and only for the first time. If the status
was changed again to "In Progress" within the 24-hour timeframe, the notification would not be sent since the
`progress_started_at` column would not be null the second time. There were other ways to skin this cat, but 
I felt this was the most straightforward and simple approach.

# Choreo Hosted Demo

To make it easier for you to try out the features of Choreo, I have provided a demo version hosted at 
[https://choreo.theashok.xyz](https://choreo.theashok.xyz). This demo version allows you to explore the 
functionalities of the application without the need for local installation.

This will take you to the application's interface, where you can create tasks, manage subtasks, and track their status. 
I deployed the demo version using one of my own SaaS apps [Cleavr](https://cleavr.io). Cleavr is used to host the 
Mailpit web UI as well. You can register an account and start adding your own tasks and subtasks. Alternatively, 
you can use the following demo credentials to log in and explore the application. It has some tasks and subtasks 
already seeded.

    - Url: https://choreo.theashok.xyz
    - Email: demo@example.com
    - Password: password

## Email Reminder Testing

To facilitate testing of the email reminder feature, I have made some modifications in the demo version:

- The email reminder is sent within about a minute and a half (90 seconds) after a task's status is changed 
    to "In Progress".
- To view the emails sent by the application, you can visit [https://mailpit-choreo.theashok.xyz](https://mailpit-choreo.theashok.xyz).
and login using the following credentials to access the email inbox:


    - Url: https://mailpit-choreo.theashok.xyz
    - Email: demo
    - Password: password

Feel free to explore Choreo demo, create tasks and subtasks, change their statuses, and test the email reminder 
functionality.

If you encounter any issues or have any feedback, please don't hesitate to reach out. I hope you find the demo 
helpful in understanding the capabilities of Choreo.

## Contributing

Contributions to this project are welcome. Feel free to open issues or submit pull requests to suggest 
improvements or fixes.

## License

This project is licensed under the [MIT License](LICENSE).
