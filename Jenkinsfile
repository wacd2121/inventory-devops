pipeline {

    agent any

    environment {
        IMAGE_NAME = 'inventory-devops'
        CONTAINER_NAME = 'inventory-app'
        PHP_EXE = 'D:\\xampp\\php\\php.exe'
    }

    stages {

        stage('Checkout') {
            steps {
                echo 'Checking out source code...'
                checkout scm
            }
        }

        stage('Lint PHP Syntax') {
            steps {
                echo 'Running PHP syntax validation...'

                bat '''
                    "%PHP_EXE%" -v
                    "%PHP_EXE%" -l index.php
                    "%PHP_EXE%" -l login.php
                    "%PHP_EXE%" -l logout.php
                    "%PHP_EXE%" -l dashboard.php
                    "%PHP_EXE%" -l categories/add.php
                    "%PHP_EXE%" -l categories/index.php
                    "%PHP_EXE%" -l products/add.php
                    "%PHP_EXE%" -l products/edit.php
                    "%PHP_EXE%" -l products/delete.php
                    "%PHP_EXE%" -l products/index.php
                    "%PHP_EXE%" -l config/database.php
                    "%PHP_EXE%" -l includes/auth.php
                    "%PHP_EXE%" -l includes/header.php
                    "%PHP_EXE%" -l includes/footer.php
                '''
            }
        }

        stage('Run Test Automation') {
            steps {
                echo 'Running application tests...'

                bat '''
                    "%PHP_EXE%" tests/test.php
                '''
            }
        }

        stage('Build Docker Images') {
            steps {
                echo 'Building Docker image...'

                bat '''
                    docker build -t %IMAGE_NAME% .
                '''
            }
        }

        stage('Deploy Stack') {
            steps {
                echo 'Deploying Inventory Management System...'

                bat '''
                    docker compose up -d
                '''
            }
        }
    }

    post {
        success {
            echo 'SUCCESS: Jenkins pipeline completed successfully.'
        }

        failure {
            echo 'FAILURE: Jenkins pipeline failed. Review the console output.'
        }

        always {
            echo 'Jenkins pipeline execution completed.'
        }
    }
}