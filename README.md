# Webtech-Academie-Libre-Projects
Académie Libre is the first online platform in Niger built exclusively for candidats libres, combining past exam papers, interactive quizzes, and AI-powered feedback — all in one place. Unlike other resources, we don’t just give study materials; we provide guidance and motivation like a virtual teacher.

# README – Candidats Libres Learning Platform
### Project Overview

The Candidats Libres Learning Platform is designed to support independent students preparing for national examinations by offering structured weekly modules, complete courses, quizzes, assignments, and downloadable academic resources. The platform also integrates an AI-based assistant to enhance learning support and provide personalized guidance.

The project includes a CMS for content management, a GitHub repository for full source code, and a live server deployment for evaluation. This README provides instructions for setup, deployment, and technical documentation.

# Project Links
### GitHub Repository

👉 https://github.com/Furairah3/Webtech-Academie-Libre-Projects.git

### CMS / WordPress

👉 https://fzakariyaouidi.wixsite.com/academie-libre

### Live Server

👉 http://169.239.251.102:341/~chidima.ugwu/Webtech-Academie-Libre-Projects

Team Member Responsible for Starting the VM

If the VM or server is offline during evaluation, please contact:

Name: Chidima Ugwu
Role: Backend & Frontend Developer
Email: (Add her school email here)

She has full access to the hosted environment and can restart the VM when needed.

### SETUP INSTRUCTIONS (LOCAL OR VM)
1. Clone the Repository
git clone https://github.com/Furairah3/Webtech-Academie-Libre-Projects.git
cd Webtech-Academie-Libre-Projects

2. Install Dependencies

This project contains PHP, HTML/CSS, and MySQL.
No Node.js or Python setup is required.

### Backend (PHP)

Ensure you have:

XAMPP / MAMP / LAMP

PHP 8+

MySQL 5.7 or later

3. Configure Environment Variables

Create a file named:

config.php


Add database connection details:

<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "academie_libre";
?>


4. Database Setup
1. Create the database
CREATE DATABASE academie_libre;

2. Import SQL schema

Inside phpMyAdmin:

Go to Import

Upload: /database/academie_libre.sql

This creates all tables used in the platform.

5. Start the Local Server

Using XAMPP / MAMP:

Move the project folder into:

htdocs/


Start Apache and MySQL

Visit:

http://localhost/Webtech-Academie-Libre-Projects

### DEPLOYMENT ON VM

If hosting on Ubuntu (like your current server):

1. SSH into the Server
ssh username@169.239.251.102
ssh -C chidima.ugwu@169.239.251.102 -p 322
2. Pull the Latest Code
cd /var/www/html
git pull

3. Restart Apache
sudo systemctl restart apache2

4. Access the Live Site

👉 http://169.239.251.102:341/~chidima.ugwu/Webtech-Academie-Libre-Projects

### FEATURES INCLUDED
Learning Features

Full curriculum for candidats libres

Weekly learning modules

Quizzes and assignments

PDF & resource library



User authentication (login & sessions)

Progress tracking with unlock system (Week 2 unlocks after Week 1)

Technical Features

PHP backend

MySQL relational database

CMS for content management (Wix)

Responsive HTML/CSS interface

Sequential learning workflow system

### TESTING
Manual Testing

Verified all page links and navigation

Checked PDF download functionality

Confirmed weekly progression lock/unlock

Testing login sessions and restricted access

Deployment testing on VM (24h debugging due to linking crashes)

Automated / Unit Testing

Limited due to PHP project structure, but includes:

SQL schema validation

Authentication testing

Page rendering and routing checks

## PROJECT CONTRIBUTORS
Name	Role	Contributions
###### Foureiratou Zakariyaou	
Content Lead & Documentation	Course creation, weekly modules, quizzes, assignments, PDF uploads, ER diagram, database support, documentation
###### Chidima Ugwu,
Frontend & Backend Developer. Built major PHP pages, UI styling, routing, sessions, progress tracking system, deployment, and debugging
###### Alan	
Database Engineer,	MySQL schema development, user flow design
###ADDITIONAL NOTES

Do NOT upload config.php to GitHub (contains credentials).

If the live server is down, you can contact Chidima, the VM handler.
An 
AI assistant requires an active API key.

Deployment debugging took over 24 hours due to issues with linking and broken pages.

The team worked with only 3 members, unlike others with 5.
