# BSU-SPMS: Full Feature Report
**Project Name:** Strategic Performance Management System for Benguet State University (BSU-SPMS)  
**Target User:** Benguet State University - Human Resource Development Office (BSU-HRDO)  
**Document Type:** Full System Feature Report  
**Tone:** Plain English / Student Perspective (No robot speak, just real talk about what this system does)

---

## 1. What is this project even about? (The Quick Story)

If you've ever had a job, or even just attended school, you know people have to be evaluated. At Benguet State University (BSU), all the professors, office staff, and department heads have to get graded every semester under the rules of the Civil Service Commission (CSC).

In the past (and in a lot of government offices right now), this whole thing was done with mountains of paper, random printed forms, and endless Microsoft Excel files being emailed back and forth. 
Here is what usually happens when you do it that way:
1. Somebody loses a paper form with physical signatures on it.
2. Nobody in HR knows who has submitted and who is still late until someone spends three days calling departments one by one.
3. Department deans have to manually compute grade averages with a calculator.
4. If someone wants to cheat or change a grade after the deadline, it's pretty easy to just print a new paper and slip it in.

So **BSU-SPMS** is a web system built to put this entire process online. From the moment teachers write down what they plan to do this semester, all the way to uploading proof, getting scored by their bosses, and getting reviewed by the university committee—everything happens inside the browser.

---

## 2. Who actually uses this website?

There are five main types of people who log in, and the system looks different depending on who you are:

1. **Regular Employees / Faculty (The "Ratees"):**  
   These are the teachers and staff. They log in to write down their targets for the semester, submit them to their boss, upload receipts/proof when they finish tasks, and check their final grades.
2. **Supervisors / Deans / Dept. Chairs (The "Raters"):**  
   These are the bosses. They check what their staff submitted. If a teacher's plan looks bad or unrealistic, they reject it and say "fix this." At the end of the term, they grade their teachers on Quality, Efficiency, and Timeliness.
3. **Technical Working Group (TWG) / Reviewers:**  
   The university quality checkers. Before grades become official, the TWG looks over the evaluations to make sure supervisors weren't just giving free 5.0 scores to their favorite people.
4. **HRDO Administrators (The Admins):**  
   The HR staff running the whole show. They set up the semester dates, add employees, create colleges and departments, look at campus-wide statistics, and back up the database.
5. **University President / Top Executives:**  
   The big bosses who just want to see the charts: *"Is BSU doing good this semester or not? Who is late?"*

---

## 3. The Core Process: How the System Works in Real Life

The whole system basically operates in two big halves:

```
[Phase 1: Target Setting] 
Dean submits Office Targets (OPCR) 
      ↓
Department Head links their targets (DPCR) 
      ↓
Teachers submit individual targets (IPCR) 
      ↓
Supervisor approves or returns targets with notes
      ↓
(Semester happens, people do their actual work)
      ↓
[Phase 2: Evaluation & Grading]
Teachers write down what they actually accomplished + attach proof (MOV)
      ↓
Supervisor scores each task (Quality, Timeliness, Efficiency from 1 to 5)
      ↓
TWG reviews the scores and verifies them
      ↓
Final Official CSC Report generated!
```

A cool rule built into the system is **Target Cascading**. That's a fancy term, but all it means is: a teacher can't submit their goals until their department head has their goals approved, and the department head can't submit theirs until the college dean has theirs approved. It forces everyone to pull in the same direction instead of making up random tasks.

---

## 4. Deep Dive: Detailed Feature Breakdown

Here is every major feature in the system, broken down into normal words.

---

### A. Account Security & Login
*Nobody wants random people logging in to mess with their grades or private info.*

* **Role-Based Permissions:** When you log in, the app checks your role. If you are a faculty member, you can't sneak into the admin area or grade your own boss.
* **Two-Factor Authentication (2FA via Email OTP):** When users log in, the system sends a 6-digit one-time code to their registered email. Even if someone guesses your password, they can't get in without the code.
* **Password Reset & Forgot Password:** If a teacher forgets their password, they can click "Forgot Password" to receive a secure reset link in their email.
* **Session Expiry:** If you leave your computer open in a shared faculty room, the system logs you out after inactivity so nobody else messes with your account.

---

### B. The Performance Forms (OPCR, DPCR, IPCR, IPERF)
*These are the official forms required by the government.*

* **OPCR (Office Performance Commitment and Review):** Used by the top offices and deans for university-level goals.
* **DPCR (Department Performance Commitment and Review):** Used by department heads to break down the dean's goals into department-sized projects.
* **IPCR & IPERF (Individual Performance Forms):** Used by everyday teachers and staff members to list their individual classes, research papers, extension projects, and committee duties.
* **Dynamic Accomplishment Rows:** You can add, edit, and organize multiple output rows inside categories (Strategic Priorities, Core Functions, Support Functions).
* **Return for Revision:** If a supervisor looks at your target and thinks it's sloppy, they don't have to delete it. They click "Return for Revision" and type a note explaining what you need to fix.

---

### C. The Grading & Scoring Engine (Quality, Timeliness, Efficiency)
*Under CSC rules, you don't just give a single random grade like "85%". It has a very specific formula.*

* **Q-E-T Breakdown (1.00 to 5.00 Scale):**
  * **Quality (Q):** Was the work done properly without mistakes?
  * **Efficiency (E):** Did you do more than expected with limited resources?
  * **Timeliness (T):** Did you finish on time or before the deadline?
* **Auto Average Computation:** The computer automatically calculates the average score for each row and multiplies it by its category weight. No more math mistakes or broken formulas in Excel.
* **CSC Adjectival Rating Converter:** The system converts raw decimal numbers into official Civil Service ratings:
  * `4.50 – 5.00` = **Outstanding (O)**
  * `3.50 – 4.49` = **Very Satisfactory (VS)**
  * `2.50 – 3.49` = **Satisfactory (S)**
  * `1.50 – 2.49` = **Unsatisfactory (US)**
  * `Below 1.50` = **Poor (P)**

---

### D. Executive Analytics & Dashboard (Recently Completed ✅)
*This is the fancy command center screen for HRDO and the University President.*

* **Submission Compliance Meters:** Circle and bar progress charts showing the whole campus at a glance: what percent submitted on time, who's overdue, who is still being evaluated, and who has their final approved score.
* **CSC Rating Distribution Visualizer:** A chart showing how many people got "Outstanding", how many got "Very Satisfactory", etc. If 99% of people get Outstanding, HR can tell if supervisors are grading too easily; if half the school got Unsatisfactory, HR knows something went wrong.
* **College Leaderboard:** A quick ranking table comparing colleges (like College of Agriculture, College of Arts & Sciences, College of Nursing, etc.). HR can instantly spot which college finished 100% of their evaluations and which college is holding up the entire university.
* **Quick Stats Cards:** Highlights at the top showing the total active employees evaluated, the university-wide average score, and whether target setting is currently open or locked.

---

### E. Means of Verification (MOV) Proof Uploads (In Progress ⏳)
*The "receipts" feature. You can't claim you did something without showing proof.*

* **Row Attachments:** Next to each accomplishment row, there is a paperclip / upload button where teachers can attach PDFs, photos (PNG/JPG), and Word docs (like student evaluation summaries, certificates, or published papers).
* **In-App Preview Pop-up:** When a supervisor is grading, they can just click "Preview" and a modal window pops up on the screen showing the PDF or image. They don't have to download 50 files to their laptop just to check one receipt.
* **Storage Protection:** Files are stored in a secure folder so unauthorized people can't just guess the web link and download private employee files.

---

### F. Interactive Notification Bell (Next Up ⏳)
*Nobody likes constantly refreshing the page to see if their boss approved their form.*

* **Active Dropdown Menu:** Clicking the bell in the top navigation bar pops open a notification tray, showing a red number badge for unread alerts.
* **Helpful Triggers:**
  * *"Your supervisor returned your targets for revision."*
  * *"Your target commitments have been approved."*
  * *"Your supervisor has submitted your final ratings."*
  * *"TWG has verified your evaluation."*
  * *"Reminder: Target submission deadline is in 3 days."*
* **Direct Navigation:** Clicking an alert jumps you straight to the exact form or folder mentioned.

---

### G. Audit Trail & Activity Logs (Planned ⏳)
*The security camera that keeps everyone honest.*

* **Silent Event Tracker:** Every time somebody logs in, changes a grade, approves a form, or changes user roles, the system writes down: *Who did it, what they did, what the old value was, what the new value is, what their IP address was, and the exact timestamp.*
* **Admin Audit Table:** HRDO admins have a searchable log table. If someone claims *"Hey, my supervisor gave me a 4.5 and now it shows 3.2!"*, the admin can pull up the audit trail and see exactly who edited that number and at what time.

---

### H. Official CSC Forms Export & Printing (Planned ⏳)
*Because government auditors still demand official paperwork.*

* **Standardized Government Layout:** Formats matching the exact look of official Civil Service Commission annex sheets, complete with Benguet State University headers and Philippine government logos.
* **Signature Boxes:** Formatted signatory blocks for the Ratee (Employee), Rater (Supervisor), and Approver (University President / PMT Chair).
* **Clean Print-to-PDF & Excel (.xlsx) Download:** You can click a button to download an Excel sheet that matches the government template, or hit print with special CSS that stops tables from awkwardly splitting across pages.

---

### I. Evaluator Quality-of-Life Tools (Planned ⏳)
*Tools to keep department heads from going crazy when grading 40 different teachers.*

* **Folder Filters:** Quick tab buttons on the folders page so a dean can click *"Needs My Evaluation"* to only see teachers waiting for grades, or *"Done"* to see completed ones.
* **"Next Person" Buttons:** While grading teacher #14, the supervisor can click *"Save & Next"* to immediately load teacher #15, instead of having to click Back to Folder List every single time.
* **Semester Lock / Archiving:** When the semester is officially over, the HRDO admin can click "Archive Cycle." This freezes all scores so nobody can make sneaky edits months later.

---

### J. Presentation & Deployment Polish (Final Stage ⏳)
*Getting ready for the capstone panel defense and real-world rollout.*

* **Realistic Demo Seed Data:** Pre-loaded test accounts representing real BSU departments so during the defense presentation, the panel sees realistic names and data instead of "test1", "asdf", or blank tables.
* **One-Click Database Backup:** A simple button in the admin tools to download a complete `.sql` backup file of the database.
* **Simple User Manuals:** Plain-English, screenshot-guided PDF booklets for Employees, Supervisors, and HR Admins.

---

## 5. Master Status Table

Here is a quick cheat-sheet showing where each part of the project stands:

| Feature Area | Specific Function | Who Uses It | Status |
| :--- | :--- | :--- | :---: |
| **Authentication** | Email OTP Two-Factor Authentication (2FA) | Everyone | **Finished** ✅ |
| **Authentication** | Password Reset via Email Link | Everyone | **Finished** ✅ |
| **User Management** | Roles (Admin, Supervisor, Faculty, TWG) | HRDO Admin | **Finished** ✅ |
| **Cycle Setup** | Semester Cycles & Target Submission Windows | HRDO Admin | **Finished** ✅ |
| **Document Forms** | OPCR, DPCR, and IPCR forms with weights | Faculty & Deans | **Finished** ✅ |
| **Workflow** | Target Cascading (Boss targets approve first) | All Staff | **Finished** ✅ |
| **Workflow** | "Return for Revision" with feedback notes | Supervisors | **Finished** ✅ |
| **Scoring** | Auto Q-E-T Math & CSC Adjectival Brackets | Supervisors | **Finished** ✅ |
| **Review** | Technical Working Group (TWG) Verification | TWG Members | **Finished** ✅ |
| **Analytics** | Campus Compliance Meters & Leaderboard | HRDO / President | **Finished** ✅ |
| **Analytics** | CSC Rating Spread Visualizer (Charts) | HRDO / President | **Finished** ✅ |
| **Evidence (MOV)** | File Attachments (PDF/Image/Word) per row | Employees | **In Progress** ⏳ |
| **Evidence (MOV)** | In-App Document Preview Modal | Supervisors / TWG | **In Progress** ⏳ |
| **Notifications** | Active Bell Dropdown & Unread Counter | Everyone | **Planned** ⏳ |
| **Security** | Tamper-Evident Audit Trail & Score History | HRDO Admin | **Planned** ⏳ |
| **Reporting** | Official CSC Sheet Export (.xlsx & clean PDF) | Everyone | **Planned** ⏳ |
| **Evaluator QoL** | "Needs Evaluation" Filters & Next-Ratee Nav | Supervisors | **Planned** ⏳ |
| **Deployment** | 1-Click Database Backup & Demo Accounts | HRDO Admin | **Planned** ⏳ |

---

## 6. Bottom Line / Summary

Overall, the **BSU-SPMS** project takes a slow, paper-heavy government requirement and turns it into a modern, automated web app. 

The heavy-lifting backend stuff—the accounts, security, forms, cascading rules, grading formulas, TWG reviews, and the new executive analytics dashboard—is already built and working. The remaining tasks (file proof uploads, notification alerts, audit history, and official form exports) will turn it from a working project into a complete, bulletproof system ready for both the capstone defense panel and actual use at Benguet State University.
