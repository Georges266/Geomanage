# 🌍 GeoManage – Enterprise Geo Management System

> A full-stack web application for managing geospatial data, maintenance workflows, and land operations with automated pricing and ML-based predictions.

[![GitHub](https://img.shields.io/badge/GitHub-Repository-blue)](https://github.com/Eliehbk/Geomanage.git)

---

## 📋 Table of Contents
- [Overview](#overview)
- [Key Features](#key-features)
- [Workflow Examples](#workflow-examples)
- [Technical Highlights](#technical-highlights)
- [Architecture](#architecture)
- [Tech Stack](#tech-stack)
- [Installation](#installation)
- [Project Structure](#project-structure)
- [Demo & Screenshots](#demo--screenshots)

---

## 🎯 Overview

GeoManage is a comprehensive enterprise-grade web application developed as a senior project, simulating real-world asset and land management systems used by surveying engineering firms, municipalities, and property management companies.

**The system demonstrates:**
- Complex role-based access control (7 user roles)
- Real-time geospatial data visualization
- Automated financial calculations with engineering formulas
- Machine learning integration for price prediction
- Full CRUD operations with relational database design
- Email automation and PDF/Excel report generation

**Team**: Developed collaboratively by 2 software engineering students

---

## ✨ Key Features

### 🔧 Service Request & Project Workflow
- **Client-initiated requests**: Clients create service requests for their land/property needs with document upload
  - **Dual input methods**: Manual entry or interactive map drawing tool with auto-calculations
  - Map tool automatically calculates area, perimeter, coordinates, and elevation
- **Automatic pricing**: System calculates service cost instantly using engineering formulas
- **Admin oversight**: 
  - View service requests with auto-calculated prices (editable if needed)
  - Access all documents and land information
  - Visualize request location on interactive map (land location, routes, terrain analysis)
  - Create projects from approved service requests
  - Assign projects to Lead Engineers with defined team size limits
- **Lead Engineer management**:
  - Receives assigned projects
  - Access all documents and land information
  - Views project details on interactive map
  - Builds surveyor team (within admin-defined team size limit)
  - Manages project execution
- **Real-time visibility**: All stakeholders see project updates instantly
- **Equipment monitoring**: Track equipment status, maintenance history, and usage across the system

### 🏘️ Land Marketplace
- **Client land listings**: Clients can upload their land for sale on the marketplace
  - Interactive map tool to draw land boundaries
  - Automatic photo generation from drawn maps for listings
  - Browse and search available land listings
- **Salesperson approval workflow**: 
  - Review all client-submitted land sale requests
  - Approve or reject listings for marketplace
  - Mark lands as "Sold" when transactions complete
  - Track sales pipeline (pending → approved → sold)

### 💰 Intelligent Cost Calculation
- **Automated pricing engine** using engineering-based formulas
- **Dynamic recalculation** when services are modified or scope changes
- **Manual override capability** for administrative adjustments
- **Aggregated project totals** from individual service request costs

### 📊 Analytics & Reporting
- **Financial dashboard**: Track expenses, revenue, and profit margins
- **Expense tracking**: 
  - Employee salaries
  - Equipment maintenance costs
  - New equipment purchases
- **Multi-source revenue tracking**: Service fees + land sales commissions
- **Exportable reports**: Generate PDF and Excel reports for stakeholders
- **Financial analytics**: Aggregated statistics for administrative decision-making

### 🗺️ Advanced Geospatial Features
- **Interactive mapping** with Leaflet integration
- **Custom map drawing tool** for land boundary creation with auto-calculations
- **Route optimization**: Distance calculation, travel time estimation
- **Terrain analysis**: Elevation data, slope calculation, area measurement
- **Satellite imagery**: Real-world view using free map providers
- Supports direct planning and visualization for engineering decisions

### 🤖 Machine Learning Price Prediction
- **ML model** for land price estimation based on features
- **Decision support tool** for pricing strategy and market comparison
- Advisory system that complements manual pricing
- Clients can estimate their own land value

### 👥 HR Management System
- **Job posting management**: Create, edit, and manage job opportunities
- **Application processing**: 
  - Review submitted applications and CVs
  - Hire, reject, or schedule interviews
  - Automated rejection emails when positions close
- **Interview scheduling**: Calendar-based appointment system with rescheduling
- **Employee management**: 
  - Promote employees to different roles
  - Terminate employment
  - Adjust salaries (increase/decrease)

### 📧 Client Delivery System
- **Automated email summaries** upon project completion
- **Detailed reports**: Land details, services performed, total costs
- **Access control**: Paid projects → full access | Unpaid → view-only with locks

### 👥 Role-Based Access Control (RBAC)
Seven distinct user roles with granular permissions:
- **Client**: 
  - Upload land for sale on marketplace with map tool
  - Request services (manual or map-based land entry)
  - Track service requests and projects
  - ML-based land valuation
  - Apply for jobs at the firm
- **HR**: 
  - Manage job postings (create, open, close)
  - Process applications (hire, reject, interview)
  - Schedule and manage interviews
  - Employee management (promote, fire, salary adjustments)
- **Surveyor**: 
  - View assigned projects and lands
  - Update land information from field surveillance
  - Upload missing documentation and deliverables
  - Track assigned equipment
  - Request equipment maintenance
- **Salesperson**: 
  - Review land sale requests
  - Approve/reject marketplace listings
  - Mark lands as sold
  - Manage sales pipeline
- **Admin**: 
  - Service request review and price editing
  - Access to all documents and land information
  - Map visualization of requests (land location, routes, analysis)
  - Project creation from service requests
  - Lead Engineer assignment and team size configuration
  - Equipment monitoring and maintenance oversight
  - Financial oversight: salary tracking, equipment purchases, maintenance costs
  - System management
- **Lead Engineer**: 
  - Project assignment reception
  - Access to all project documents and land information
  - Map-based project visualization
  - Surveyor team building (within size constraints)
  - Project management and oversight
  - Can send project completion summary to client
- **Maintenance Technician**: 
  - Equipment maintenance request processing
  - Issue documentation and tracking
  - Maintenance history logging
  - Equipment status updates

---

## 🔄 Workflow Examples

### Complete Service Request to Project Flow
```
Client submits service request + documents
  (using map tool or manual entry)
              ↓
System auto-calculates price
              ↓
Admin reviews:
  • Views location on map (routes, terrain, land details)
  • Reviews documents and land information
  • Edits price if needed
              ↓
Admin creates project from request
              ↓
Admin assigns to Lead Engineer + sets team size limit
              ↓
Lead Engineer:
  • Access documents and land information
  • Views project on map
  • Builds surveyor team (within limit)
  • Manages project execution
              ↓
Surveyors:
  • Update land info from field surveillance
  • Upload deliverables
  • Track equipment
              ↓
Team completes project
              ↓
Admin or Lead Engineer sends summary to client via email
```

### Land Sale Workflow
```
Client uploads land for sale
  • Draws boundaries with map tool
  • System generates listing photos
              ↓
Salesperson reviews request
              ↓
Salesperson approves for marketplace
              ↓
Land appears in public marketplace
              ↓
Transaction occurs
              ↓
Salesperson marks land as "Sold"
```

### HR Hiring Workflow
```
HR creates job posting
              ↓
Clients apply with CV upload
              ↓
HR reviews applications
              ↓
HR schedules interviews
              ↓
HR hires candidate
  • Automatically creates employee record
              ↓
Employee assigned to role
```

### Financial Tracking
```
System tracks expenses:
  • Employee salaries (all roles)
  • Equipment maintenance costs
  • New equipment purchases

System tracks revenue:
  • Service request fees
  • Land sales commissions

Admin generates financial reports (PDF/Excel)
```

---

## 💡 Technical Highlights

### Database Design
- **Normalized relational schema** (MySQL)
- **Complex relationships**: 
  - Many-to-many (equipment-projects, surveyors-projects)
  - One-to-many (users-requests, projects-lands, job_postings-applications)
- **Referential integrity** with foreign key constraints
- **Optimized queries** for performance
- Tables include: lands, land_sales, service_requests, projects, job_postings, job_applications, interviews, employees, equipment_tracking

### Backend Engineering
- **PHP-based MVC architecture**
- **Server-side validation** and sanitization
- **Session management** for authentication
- **RESTful-style endpoints** for AJAX operations
- **File upload handling** for CVs, bills, land documents, and deliverables

### Frontend Development
- **Responsive design** (HTML5, CSS3, vanilla JavaScript)
- **Dynamic UI updates** without page reloads
- **Interactive forms** with client-side validation
- **Map integration** with event handling
- **Modular JavaScript** for maintainability
- **Custom map drawing tool** with Leaflet.js

### Advanced Features
- **Email automation** using PHPMailer
  - Job application notifications
  - Interview scheduling
  - Mass rejection emails
  - Land sale approvals
  - Project completion summaries
- **PDF generation** for reports
- **Excel export** functionality
- **Machine learning model integration** (Python model → PHP interface)
- **Formula-based calculations** for engineering estimates
- **Photo generation from map drawings**

---

## 🏗️ Architecture

```
┌─────────────────────────────────────────────┐
│          Client (Browser)                   │
│   HTML5 | CSS3 | JavaScript | Leaflet       │
└──────────────────┬──────────────────────────┘
                   │ HTTP Requests
                   ↓
┌─────────────────────────────────────────────┐
│          Backend (PHP)                      │
│   • Business Logic                          │
│   • Authentication & Authorization          │
│   • Data Validation                         │
│   • Email & Report Generation               │
│   • ML Model Interface                      │
│   • File Upload Management                  │
└──────────────────┬──────────────────────────┘
                   │ SQL Queries
                   ↓
┌─────────────────────────────────────────────┐
│          Database (MySQL)                   │
│   • Users & Roles                           │
│   • Projects & Lands                        │
│   • Service Requests                        │
│   • Equipment & Bills                       │
│   • Financial Records                       │
│   • Job Postings & Applications             │
│   • Land Sales & Marketplace                │
└─────────────────────────────────────────────┘
```

**Data Flow:**
1. User action triggers frontend event
2. AJAX/Form submits to PHP endpoint
3. PHP validates, processes business logic
4. Database transaction executed
5. Response returned to frontend
6. UI updates dynamically

---

## 🛠️ Tech Stack

| Layer          | Technology                    |
|----------------|-------------------------------|
| **Backend**    | PHP 7.4+                      |
| **Frontend**   | HTML5, CSS3, JavaScript (ES6) |
| **Database**   | MySQL 8.0                     |
| **Mapping**    | Leaflet.js, OpenStreetMap     |
| **ML**         | Python (scikit-learn)         |
| **Server**     | XAMPP (Apache + MySQL)        |
| **Reporting**  | FPDF, PHPExcel                |
| **Email**      | PHPMailer                     |
| **Versioning** | Git & GitHub                  |

---

## ⚙️ Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 8.0 or higher
- XAMPP, WAMP, or similar (Apache + MySQL)
- Git

### Setup Instructions

1. **Clone the repository**
```bash
git clone https://github.com/Eliehbk/Geomanage.git
cd Geomanage
```

2. **Database Configuration**
   - Open phpMyAdmin at `http://localhost/phpmyadmin`
   - Create a new database: `geomanage`
   - Import the SQL file: `database/geomanage.sql`

3. **Configure Database Connection**

Edit the configuration file (e.g., `config/database.php`):
```php
<?php
$dbHost = "localhost";
$dbUser = "root";
$dbPass = "";
$dbName = "geomanage";
?>
```

4. **Start Server**
   - Launch XAMPP Control Panel
   - Start **Apache** and **MySQL** modules
   - Verify services are running

5. **Access Application**

Navigate to:
```
http://localhost/Geomanage
```

### Default Login Credentials
```
Admin: admin / admin123
Client: client1 / client123
HR: hr1 / hr123
Surveyor: surveyor1 / surveyor123
Salesperson: sales1 / sales123
Lead Engineer: engineer1 / engineer123
Technician: tech1 / tech123

(Change these immediately after first login)
```

---

## 📁 Project Structure

```
Geomanage/
├── assets/              # CSS, JS, images
├── config/              # Database and app configuration
├── includes/            # Reusable PHP components
├── models/              # Data models and ML integration
├── views/               # Frontend pages
│   ├── client/          # Client portal (marketplace, requests, ML)
│   ├── hr/              # HR management (jobs, applications, interviews)
│   ├── surveyor/        # Surveyor operations (projects, deliverables)
│   ├── salesperson/     # Sales module (land approvals)
│   ├── admin/           # Admin dashboard
│   ├── engineer/        # Lead engineer management
│   └── technician/      # Maintenance workflows
├── controllers/         # Business logic handlers
├── database/            # SQL schema and migrations
├── uploads/             # User-uploaded files
│   ├── cvs/             # Job application CVs
│   ├── land_docs/       # Land documentation
│   └── deliverables/    # Project deliverables
├── reports/             # Generated PDF/Excel files
└── README.md
```

---

## 📸 Demo & Screenshots

> **Note**: Add screenshots here showcasing:
> - Client map drawing tool for land entry
> - Land marketplace with listings
> - HR application processing dashboard
> - Surveyor project management
> - Salesperson land approval interface
> - Interactive map with land visualization
> - Service request workflow
> - Equipment assignment interface
> - Financial reports
> - ML land valuation tool

---

## 🎓 Learning Outcomes

This project demonstrates proficiency in:
- Full-stack web development (frontend + backend + database)
- Software architecture and design patterns
- Database normalization and query optimization
- Role-based access control implementation
- API integration (maps, ML models)
- Geospatial programming with interactive tools
- Machine learning model deployment
- Financial calculation automation
- HR workflow automation
- Report generation and email automation
- File upload and management systems
- Version control and collaborative development
- Real-world problem-solving for enterprise systems

---

## 🚀 Future Enhancements

- [ ] RESTful API for mobile app integration
- [ ] Real-time notifications using WebSockets
- [ ] Advanced ML models for project timeline prediction
- [ ] Multi-language support (i18n)
- [ ] Cloud deployment (AWS/Azure)
- [ ] Automated testing suite (PHPUnit)
- [ ] Payment gateway integration for services
- [ ] Advanced analytics dashboard with charts

---

## 👨‍💻 Developers

**Team of 2 Software Engineering Students**

Senior Project | Surveying Engineering Firm Management System

---

## 📄 License

This project was developed as an academic senior project. Please contact for usage permissions.

---

## 🙏 Acknowledgments

Special thanks to:
- Project advisors and mentors
- Open-source community for tools and libraries (Leaflet.js, PHPMailer, scikit-learn)
- Surveying engineering professionals for domain expertise

---

**⭐ If you find this project interesting, please consider giving it a star on GitHub!**
