# BSU-SPMS: Full Feature Report
**Project Name:** Strategic Performance Management System for Benguet State University (BSU-SPMS)  
**Target User:** Benguet State University - Human Resource Development Office (BSU-HRDO)  
**Document Type:** Full System Feature Report  
**Tone:** Plain English / Student Perspective (No robot speak, just real talk about what this system does)

---

## 1. What is this project even about? (The Quick Story)

If you have ever had a job, or even just attended school, you know people have to be evaluated. At Benguet State University (BSU), all the professors, office staff, department heads, and deans have to get graded every semester under the rules of the Civil Service Commission (CSC).

In the past (and in a lot of government offices right now), this whole thing was done with mountains of paper, random printed forms, and endless Microsoft Excel files being emailed back and forth. 
Here is what usually happens when you do it that way:
1. Somebody loses a paper form with physical signatures on it.
2. Nobody in HR knows who has submitted and who is still late until someone spends three days calling departments one by one.
3. Department deans have to manually compute grade averages with a calculator.
4. If someone wants to cheat or change a grade after the deadline, it is pretty easy to just print a new paper and slip it in.

So **BSU-SPMS** is a web system built to put this entire process online. From the moment teachers write down what they plan to do this semester, all the way to uploading proof, getting scored by their bosses, getting audited across the university, and downloading official Civil Service spreadsheets—everything happens inside the browser.

---

## 2. Who actually uses this website?

There are five main types of people who log in, and the system adapts dynamically depending on who you are:

1. **Regular Employees / Faculty (The "Ratees"):**  
   These are the teachers and staff. They log in to write down their targets for the semester, submit them to their department chair, upload digital receipts/proof (MOVs) when they finish tasks, and check their final grades.
2. **Department Chairs (Tier 3 Supervisors):**  
   These are the unit heads. They oversee their department's teachers, approve or return individual teacher targets, create their own departmental commitments based on the college targets, and grade faculty on Quality, Efficiency, and Timeliness.
3. **College Deans (Tier 2 Supervisors):**  
   The heads of the colleges. They review and approve department chair targets, link collegiate deliverables to university targets, and monitor compliance across all departments in their college.
4. **Vice President for Academic Affairs (VPAA) / University Leadership (Tier 1):**  
   The executive leaders who establish university-wide OPCR commitments, cascade them to the 15 colleges, review institutional performance, and ensure overall alignment.
5. **HRDO Administrators (The System Admins):**  
   The HR staff running the whole show. They set up semester cycle dates, manage the organizational structure and plantillas, transfer departments across colleges when needed, monitor real-time compliance dashboards, inspect audit trails, and ensure data integrity.

---

## 3. The Core Process: How the System Works in Real Life

The system operates across two main phases with full 4-tier institutional cascading:

```
[Phase 1: Target Setting] 
VPAA creates University Office Targets (OPCR)
      |
      v (Approve & Release to Deans)
College Deans submit College Division Targets (DPCR)
      |
      v (Approve & Release to Department Chairs)
Department Chairs submit Department Division Targets (DPCR)
      |
      v (Approve & Release to Faculty)
Teachers and Staff submit Individual Targets (IPCR / IPERF)
      |
      v
Supervisors approve or return targets with inline feedback remarks
      |
      v
(Semester happens, people do their actual work)
      |
      v
[Phase 2: Evaluation & Grading]
Employees record actual accomplishments and attach evidence files (MOV)
      |
      v
Supervisors score each task (Quality, Efficiency, Timeliness from 1 to 5)
      |
      v
Multi-tier calibration and supervisor confirmation
      |
      v
Institutional consensus and finalized CSC ratings
      |
      v
Automated CSC Excel (.xlsx) export and print-ready reports generated
```

A cornerstone rule built into the system is **Target Cascading**. In plain English: a teacher cannot submit their goals until their department head has approved goals, the department head cannot submit until the college dean has approved goals, and the college dean cannot submit until the VPAA has approved goals. It guarantees that every single task on campus directly supports the university's official mission.

---

## 4. Deep Dive: Detailed Feature Breakdown

Here is every major feature in the system, broken down into normal words.

---

### A. Account Security & Login
*Nobody wants unauthorized individuals logging in to tamper with grades or private records.*

* **Role-Based Permissions:** When you log in, the app verifies your role and plantilla assignment. Faculty cannot access administrative settings or alter scores.
* **Two-Factor Authentication (2FA via Email OTP):** During login, the system sends a 6-digit one-time code to the user's registered email address. Passwords alone are not enough to breach an account.
* **Password Reset & Forgot Password:** If a user forgets their password, they receive a secure, time-limited reset link via email.
* **Session Expiry:** Inactivity automatically logs users out to protect open sessions in shared faculty offices and computer laboratories.

---

### B. Full 4-Tier SPMS Cascading Architecture [Completed]
*The true government-grade performance tree spanning the entire university.*

* **Tier 1 (VPAA OPCR):** Apex university commitments. Approved by the Admin / PMT and released to all 15 collegiate deans.
* **Tier 2 (College Dean DPCR):** College-level performance commitments parented directly to the VPAA OPCR. Features a dedicated "Approve & Release to Department Chairs" workflow.
* **Tier 3 (Department Chair DPCR):** Academic department commitments parented directly to the Dean's DPCR. Features an "Approve & Release to Faculty" workflow.
* **Tier 4 (Faculty / Staff IPCR & IPERF):** Individual commitments parented directly to the Department Chair's DPCR.
* **Dynamic Superior Basis Sheet:** Subordinates never have to guess what their boss wants. When creating targets, a static reference sheet automatically displays the exact approved commitments of their immediate superior (Faculty view Chair targets, Chairs view Dean targets, Deans view VPAA targets).
* **Return for Revision:** If a supervisor identifies unrealistic or incomplete targets, they can return the document with specific feedback remarks. Once corrected and approved, review remarks are automatically cleaned.

---

### C. Authentic BSU La Trinidad Hierarchy & Department Transfer [Completed]
*Real university organizational structure with flexible administrative tools.*

* **15 Official BSU Colleges & Institutes:** Seeded according to official university charters:
  1. College of Agriculture (CA)
  2. College of Arts and Humanities (CAH)
  3. College of Engineering (COE)
  4. College of Forestry (CF)
  5. College of Human Ecology (CHE)
  6. College of Human Kinetics (CHK)
  7. College of Information Sciences (CIS)
  8. College of Medicine (COM)
  9. College of Natural Sciences (CNS)
  10. College of Numeracy and Applied Sciences (CNAS)
  11. College of Nursing (CN)
  12. College of Public Administration and Governance (CPAG)
  13. College of Social Sciences (CSS)
  14. College of Teacher Education (CTE)
  15. College of Veterinary Medicine (CVM)
* **Constituent Academic Departments:** Every college contains its genuine constituent academic departments parented to the college unit.
* **Admin Unit Edit & Transfer Capability:** When university reorganizations happen (e.g., a department moves to another college), HR admins can reassign the department's parent college using an interactive modal with search filtering.
* **Integrity & Cycle Preservation:** Built-in safeguards prevent self-parenting and circular reference loops. Future target cascades automatically adapt to the new college dean, while past evaluation cycles remain frozen and historically intact.

---

### D. Means of Verification (MOV) Proof Uploads [Completed]
*The "receipts" feature. Employees must back up their accomplishments with verifiable evidence.*

* **Per-Row Evidence Attachments:** Next to each accomplishment item, ratees can upload supporting documents (PDFs, PNG/JPG photos, DOC/DOCX files).
* **In-App Document Preview Modal:** Supervisors and evaluators can click to inspect attached evidence inside a responsive modal viewer without downloading dozens of files to their personal devices.
* **Secure Access Control:** File access is authenticated—only the document owner, assigned evaluators, and system administrators can view or download evidence files.
* **Storage Protection & Cycle Locking:** Once an evaluation cycle is archived and frozen, evidence attachments cannot be tampered with or replaced.

---

### E. Grading & Scoring Engine (Quality, Efficiency, Timeliness) [Completed]
*Under CSC rules, scoring is mathematically structured.*

* **Q-E-T Breakdown (1.00 to 5.00 Scale):**
  * **Quality (Q):** Accuracy, precision, and adherence to standards.
  * **Efficiency (E):** Resource optimization and output volume relative to targets.
  * **Timeliness (T):** Promptness of completion relative to deadlines.
* **Weighted Category Scoring:** Core Functions (typically 60-70%), Strategic Priorities (20-25%), and Support Functions (10-15%) are computed automatically according to template rules.
* **CSC Adjectival Rating Converter:** Raw decimal averages automatically translate to official Civil Service brackets:
  * `4.500 - 5.000` = **Outstanding (O)**
  * `3.500 - 4.499` = **Very Satisfactory (VS)**
  * `2.500 - 3.499` = **Satisfactory (S)**
  * `1.500 - 2.499` = **Unsatisfactory (US)**
  * `Below 1.500` = **Poor (P)**

---

### F. Tamper-Evident Audit Trail & Activity Logs [Completed]
*The security camera that maintains institutional transparency and accountability.*

* **System-Wide Action Logging:** Captures authentication events, target submissions and approvals, score updates, evidence file uploads, and organizational unit transfers.
* **Forensic Metadata:** Records user ID, IP address, user agent, target entity, timestamp, and human-readable event descriptions.
* **Admin Audit Trail Interface:** Provides HRDO admins with a dedicated viewer featuring text search, category filters (AUTH, TARGET, RATING, EVIDENCE, ACCOUNT), date range selectors, and summary metric cards.
* **CSV Export:** Admins can export the filtered audit trail to CSV for external compliance reviews and institutional archiving.

---

### G. Standardized CSC Excel (.xlsx) Export [Completed]
*Because government auditors require standard Civil Service spreadsheets.*

* **Native Spreadsheet Generation:** Generates genuine Microsoft Excel (.xlsx) workbooks styled to match official Civil Service Commission layout standards.
* **Landscape Print Optimization:** Pre-configured with landscape orientation, letter paper size, fit-to-page width scaling, and standardized margin rules.
* **Dynamic Form Adapters:** Intelligently formats OPCR, DPCR, and IPCR forms with correct header banners, university logos, category section dividers, and weighted sum formulas.
* **Signatory Blocks:** Formats official signature boxes for Ratee, Rater/Supervisor, and Approving Authority / PMT Chair.

---

### H. Executive Analytics & Scoped Dashboards [Completed]
*Command center interface for HRDO administrators, Deans, and Department Chairs.*

* **Role-Scoped Oversight:**
  * **College Deans:** View college-wide submission compliance, filter across child departments, and track completion progress across their collegiate team.
  * **Department Chairs:** Focus strictly on their own department faculty compliance without distraction from unrelated departments.
  * **HRDO Admins / University Leadership:** Inspect campus-wide submission percentages, active cycle countdowns, and performance distributions.
* **Rating Spread Charts:** Visualizes the distribution of Outstanding, Very Satisfactory, and Satisfactory marks to identify rating anomalies.
* **Leaderboards & Progress Meters:** Shows at a glance which colleges and departments have finalized evaluations.

---

### I. Automated 15-Stage Lifecycle Verification [Completed]
*Continuous quality assurance and end-to-end process validation.*

* **Full Cycle Automation:** Built into `php spark spms:test-full-cycle`.
* **Automated Stages Tested:** Simulates the complete journey across designated test users (Admin, VPAA, Dean, Chair, Faculty):
  1. Cycle creation
  2. VPAA OPCR submission
  3. Admin OPCR approval & release
  4. Dean DPCR submission & approval
  5. Dean cascade release to College
  6. Chair DPCR submission & approval
  7. Chair cascade release to Faculty
  8. Faculty IPCR submission
  9. Supervisor return with review remarks
  10. Re-submission & auto-cleaning of remarks
  11. Shift to evaluation phase across all 4 tiers
  12. Multi-tier self-rating calculation
  13. Multi-tier supervisor evaluation & calibration
  14. Institutional consensus approval
  15. University scorecard audit
* **Fast Feedback:** Entire 15-stage workflow completes in approximately 0.2 seconds, ensuring regressions are detected immediately.

---

### J. In-Progress & Planned Enhancements

* **Interactive Notification Tray:** Expanding the header notification bell into an active real-time notification tray with unread badge counts and direct folder navigation links.
* **Automated Nightly Worker Scheduling:** Cron-based execution for automatic deadline shifts and email queue processing.

---

## 5. Master Status Table

| Feature Area | Specific Function | Primary Users | Status |
| :--- | :--- | :--- | :---: |
| **Authentication** | Two-Factor Authentication (Email OTP) | All Users | **Finished** |
| **Authentication** | Secure Password Reset via Email Link | All Users | **Finished** |
| **User & Plantilla** | Role-Based Access Control & Appointments | HRDO Admin | **Finished** |
| **Organization** | Authentic 15 BSU Colleges & Departments | HRDO Admin | **Finished** |
| **Organization** | Admin Department Edit & Transfer Tool | HRDO Admin | **Finished** |
| **Cascading** | Full 4-Tier SPMS Hierarchy (OPCR -> DPCR -> IPCR) | All Tiers | **Finished** |
| **Cascading** | Immediate Superior Basis Reference Sheet | Faculty / Chairs / Deans | **Finished** |
| **Cascading** | Dynamic Release Actions by Tier | Supervisors | **Finished** |
| **Workflow** | Return for Revision with Inline Remarks | Supervisors | **Finished** |
| **Workflow** | Auto-Cleaning of Target Review Remarks | System Engine | **Finished** |
| **Scoring** | Automatic Q-E-T Math & Category Weighting | Supervisors | **Finished** |
| **Scoring** | CSC Adjectival Rating Classification | All Users | **Finished** |
| **Evidence (MOV)** | File Uploads (PDF, Images, Word Documents) | Employees | **Finished** |
| **Evidence (MOV)** | In-App Evidence Preview Modal | Supervisors / Evaluators | **Finished** |
| **Audit & Security** | Comprehensive Activity Log & Forensic Tracker | HRDO Admin | **Finished** |
| **Audit & Security** | Audit Trail Filter Viewer & CSV Export | HRDO Admin | **Finished** |
| **Reporting** | Standardized CSC Excel (.xlsx) Exporter | All Users | **Finished** |
| **Reporting** | Printable Accomplishment Report Layout | All Users | **Finished** |
| **Analytics** | Scoped Dashboards (Dean vs Chair vs Admin) | Supervisors / Admin | **Finished** |
| **Analytics** | Campus Compliance Meters & Score Distribution | HRDO / President | **Finished** |
| **Testing** | 15-Stage Automated End-to-End Test Suite | Developers / QA | **Finished** |
| **Notifications** | Active Bell Dropdown & Real-Time Alerts | All Users | **In Progress** |

---

## 6. Bottom Line / Summary

The **BSU-SPMS** project has grown from an initial prototype into an enterprise-grade performance management platform built specifically for Benguet State University. 

The core institutional backbone—full 4-tier target cascading from VPAA down to faculty, authentic 15-college departmental hierarchy with admin reassignment tools, means of verification evidence uploads with in-app preview, mathematically verified Civil Service scoring, forensic activity logs, scoped oversight analytics, and standardized CSC Excel export—is fully implemented and proven through automated full-cycle testing.
