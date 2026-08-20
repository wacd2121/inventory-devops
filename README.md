# Inventory DevOps Project

A beautiful, modern, and containerized PHP Inventory Management System built as part of the DevOps lab curriculum.

## Features
- **Dashboard**: High-level metrics showing Total Products, Total Stock, Low Stock count, and Category count. Includes visual indicators for items that fall below their minimum stock thresholds.
- **Product Management**: Create, Read, Update, and Delete (CRUD) operations on products.
- **Category Management**: View and add categories to organize inventory.
- **Authentication**: Secure login/logout system with hashed passwords.
- **Containerized Stack**: Standard Docker configuration (`docker-compose.yml`) utilizing PHP-Apache and MySQL services.
- **CI/CD Pipeline**: Declarative `Jenkinsfile` for building, linting, testing, and deployment stages.

## Git Branching Model
To ensure collaborative development practices, this project uses the following branch layout:
- `main` - Stable, production-ready release branch.
- `develop` - Integration branch for features.
- `feature/login` - Implementation of auth modules.
- `feature/dashboard` - Main metrics and dynamic listing interface.
- `feature/products` - CRUD workflows for products and categories.
- `feature/docker` - Environment virtualization and CI/CD pipelines.

## Getting Started

### Local Development (XAMPP / WAMP)
1. Clone the repository into your web root (e.g., `htdocs/inventory-devops`).
2. Import the database schema from `database/inventory.sql` into MySQL.
3. Configure your database details in `config/database.php`.
4. Open the application in your browser (e.g., `http://localhost/inventory-devops`).

### Docker Setup
To spin up the application along with MySQL automatically:
```bash
docker compose up --build -d
```
The app will be available at `http://localhost:8080`.
