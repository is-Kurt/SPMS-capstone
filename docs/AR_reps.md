Strategic Performance Management System (SPMS)
System Accomplishment Report

Project Title: DEVELOPMENT OF STRATEGIC PERFORMANCE MANAGEMENT SYSTEM FOR HUMAN RESOURCE DEVELOPMENT 
OFFICE OF BENGUET STATE UNIVERSITY (BSU-SPMS)
Project Classification: Information Technology Capstone Project
Target Institution: Benguet State University (BSU), La Trinidad, Benguet, Philippines

Standards and Guidelines Used:

CSC Memorandum Circular No. 6, s. 2012
Administrative Order No. 39
Republic Act No. 10535
CSC Memorandum Circular No. 24, s. 2023

1. Executive Summary
The Benguet State University Strategic Performance Management System (BSU-SPMS) is a web-based system developed as an Information Technology capstone project. The main purpose of the system is to help Benguet State University manage the performance commitments and accomplishments of its employees in a more organized and convenient way.
Before using a system like this, performance documents may be handled using printed forms, separate files, and spreadsheets. This can make it difficult to keep track of documents, deadlines, approvals, and employee accomplishments.
The BSU-SPMS was created to put these processes in one system. It allows administrators, supervisors, employees, and other authorized users to work with performance documents online.
The system covers the main parts of the performance management process, including:

Creating performance targets and commitments
Sending targets from supervisors to their subordinates
Monitoring employee accomplishments
Evaluating and giving ratings
Reviewing ratings through the Technical Working Group (TWG)
Creating the final Accomplishment Report (AR)

The system also includes security features, automatic notifications, deadline checking, and printable reports.

2. What the System Does
The general process of the system starts with the creation of office or university targets. These targets are then passed down to departments, faculty members, and other employees.

The basic flow is:
Admin / University Leadership → OPCR → DPCR → IPCR / IPERF → Evaluation → TWG Review → Final Accomplishment Report

The system has two main phases:
Phase 1 – Target Commitment

During this phase, employees create their performance targets. Supervisors can review the targets and either approve them or return them for changes.
The system also makes sure that an employee cannot submit targets before the required target of their superior has already been approved.

Phase 2 – Evaluation and Rating
After the target-setting period ends, employees can enter their actual accomplishments. Supervisors can then review the accomplishments and give scores based on Quality, Timeliness, and Efficiency.
After the supervisor's evaluation, the results can be forwarded to the TWG for checking and approval.

Main Features
The system includes the following major features:
User login and role-based access
Two-factor authentication
Employee and plantilla management
Performance cycle management
OPCR, DPCR, IPCR, and IPERF forms
Target cascading
Employee accomplishment recording
Quality, Timeliness, and Efficiency scoring
Supervisor evaluation
TWG review
Email notifications
Automatic deadline checking
Accomplishment Report generation
Printable performance reports
3. Detailed System Accomplishments
Module 1: Login, User Roles, and Security

The system has different user roles depending on the user's responsibilities.

The main roles are:

Admin – manages the overall system, users, cycles, templates, and other settings.
Supervisor / Evaluator – reviews employee targets and evaluates accomplishments.
Faculty / Employee – creates targets, records accomplishments, and views their performance reports.
TWG – reviews and checks performance ratings.
HR / Records – manages employee information and related records.

The system also includes security features such as:

Two-factor authentication
CSRF protection
Login attempt limits
Session security
Password protection
Checking user access before opening performance folders

These features help prevent unauthorized users from accessing or changing performance records.
The system also displays the official Philippine Standard Time and includes the required government and university branding.

Module 2: Organizational Structure and Plantilla
The system can store the organizational structure of the university.

It can include:
Colleges
Departments
Administrative offices
Positions
Employees

Employees can also be classified as teaching or non-teaching personnel.
The system uses the employee's position and appointment information to determine which performance document is appropriate.

For example:
OPCR – for office or executive-level performance
DPCR – for departments or divisions
IPCR – for regular employees and faculty
IPERF – for some non-permanent or contractual personnel

This makes it easier to identify which performance form should be used by each employee.

Module 3: Performance Cycle and Folder Management
The administrator can create performance cycles in the system.

Each cycle has two important periods:
Target Setting Period
Evaluation Period

The system records the beginning and ending dates for each period.

Performance folders also have different statuses depending on their current stage.

For example:

Target Setting:
Draft → Pending Approval → Approved / Returned

Evaluation
Draft → Submitted → Evaluated → Approved → TWG Approved

There are also special statuses such as:
Unevaluated
Reevaluate

The system also has a sidebar where users can easily switch between performance folders. When there are more than five folders, the system uses pagination to keep the list organized.

Module 4: Performance Matrix and Scoring
One of the main parts of the system is the digital performance matrix.

The system allows users to enter their different performance targets under three main categories:
Core Functions – 70%
Strategic Priorities – 20%
Support Functions – 10%

Employees can enter their targets and accomplishments in the appropriate sections.

During evaluation, the evaluator can enter scores for:
Quality (Q)
Timeliness (T)
Efficiency (E)

The system automatically calculates the average score instead of requiring the evaluator to calculate it manually.

For example:
Average = (Quality + Timeliness + Efficiency) ÷ Number of Valid Scores

The system also saves changes and keeps track of revisions made during the evaluation process.

Module 5: Target Cascading and Evaluation Routing

The system supports the cascading of targets from higher offices to lower-level employees.

For example:
University → College → Department → Faculty / Employee
This helps make sure that employee targets are connected to the goals of their department and the university.

The system also prevents employees from submitting their targets if the required target of their supervisor has not yet been approved.
There is also a Superior Basis / Guide section where employees can view their supervisor's approved targets. This helps them create targets that are related to the goals of their office or department.
Supervisors can also create groups or teams of employees that they regularly evaluate. These groups can be saved and used again when needed.

Module 6: Automatic Notifications and Background Processes

The system has background processes that help with tasks that do not need to be done manually.
One of these is the email queue. Instead of sending every email while the user is waiting, emails are placed in a queue and processed in the background.
The system can send notifications for events such as:

Target setting has started
Target approval
Evaluation requests
Returned documents
Upcoming deadlines
Other performance-related updates

There are also background workers that check deadlines.

For example, the system can automatically identify folders that have passed their deadlines and update their status when necessary.

This reduces the need for administrators to manually check every folder.

Module 7: Accomplishment Report

The system can create a printable Accomplishment Report (AR) after the performance evaluation process.

The report contains information such as:
Employee information
Performance targets
Actual accomplishments
Quality, Timeliness, and Efficiency scores
Overall rating
Accomplishment percentage
Approval and signature sections

The report also follows the BSU design and includes the required university and government identity elements.
The system can calculate the employee's overall rating and display the corresponding adjectival rating.

The rating categories used in the system are:
4.500 – 5.000: Outstanding
3.500 – 4.499: Very Satisfactory
2.500 – 3.499: Satisfactory
1.500 – 2.499: Unsatisfactory
Below 1.500: Poor

The report is also designed to be printed or saved as a PDF.

4. System Architecture and Technology Used

The BSU-SPMS uses several technologies to build the system.

Part of System	Technology Used	Purpose
Framework	CodeIgniter 4 / PHP 8.2+	Used to develop the main web application
Database	MySQL / SQLite	Stores users, employees, documents, and other records
Frontend	HTML, CSS, Tailwind-compatible styles, Vanilla CSS	Used for the design and layout
JavaScript	Vanilla ES6 JavaScript and Axios	Used for interactive features and requests
Text Editor	TinyMCE	Used for editing documents and additional information
Fonts	Roboto and Inter	Used for the system's text and design
Testing	SpmsLifecycleRunner	Used to test the complete performance process

The system was designed using a simple structure so that it can handle the different parts of the SPMS process without requiring a very large frontend framework.

5. Database Structure

The system uses a relational database to store its information.

Some of the important tables are:

Users – stores user accounts and basic user information.
Roles and User Roles – determines what each user can access.
Units – stores colleges, departments, and offices.
Positions – stores employee positions.
Plantillas – connects employees with their positions and offices.
Document Folders – stores performance cycles and folders.
Documents – stores the actual performance documents and their data.
Templates – stores the different performance form templates.
Evaluation Routings – stores the evaluation process and assigned evaluators.
Routing Presets – stores saved employee groups created by supervisors.
Routing Preset Members – stores the members of each group.
Invitations – handles user account invitations.
Email Queues – stores emails waiting to be sent.
Login Attempts – records login attempts for security purposes.
Migrations – keeps track of database changes.

These tables are connected to each other so that the system can properly manage users, employees, performance documents, and evaluations.

6. Deliverables Accomplishment Matrix
Deliverable / Module	Planned Specification	Implemented Result	Status
Authentication and RBAC	User login, roles, 2FA, and security	Login, user roles, 2FA, login limits, and security checks were implemented.	100%
Plantilla and Unit Management	Manage offices, departments, positions, and employees	Employee and organizational information can be managed in the system.	100%
Performance Templates	Create IPCR, DPCR, OPCR, and IPERF forms	Digital performance forms with the required categories were implemented.	100%
Cycle Management	Create target and evaluation periods	The system supports separate target and evaluation deadlines.	100%
Folder Navigation	Easily browse performance folders	Pagination and folder navigation were implemented.	100%
Target Cascading	Connect higher-level targets to employees	Superior targets can be used as a basis for subordinate targets.	100%
Rubric Reference	Provide Q, T, and E scoring guides	A reference panel was added to the evaluation page.	100%
Evaluation Workflow	Review, score, return, and approve evaluations	The complete evaluation process was implemented.	100%
Background Workers	Automatic deadline checking and notifications	Email queues and automatic deadline checking were implemented.	100%
Accomplishment Report	Generate a printable final report	The system can generate and print the official accomplishment report.	100%
Automated Testing	Test the complete SPMS process	The SPMS lifecycle can be tested automatically using the testing service.	100%
7. Conclusion

The Benguet State University Strategic Performance Management System (BSU-SPMS) was developed to make the performance management process more organized and easier to manage.
The system brings different activities such as target setting, approval, monitoring, evaluation, and reporting into one platform. It also reduces the need for printed documents and separate spreadsheets.
One of the important features of the system is the target cascading process. It helps employees create targets that are connected to the goals of their supervisors, departments, and the university.
The automatic calculations also help reduce errors when computing performance scores. Notifications and deadline checking can also help users keep track of important dates.
Overall, the project provides a digital approach to managing SPMS documents and performance evaluations at Benguet State University. While the system was developed as a capstone project, it also provides a foundation that can be improved further based on the actual requirements and feedback of the university.

Report Date: September 5, 2026
Project: Benguet State University Strategic Performance Management System (BSU-SPMS)
Institution: Benguet State University, La Trinidad, Benguet, Philippines
