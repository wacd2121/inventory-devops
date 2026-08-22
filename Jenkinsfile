pipeline {

    agent any

    environment {

        PHP_EXE = 'D:\\xampp\\php\\php.exe'

        DOCKER_EXE =
            'C:\\Users\\Administrator\\AppData\\Local\\Programs\\DockerDesktop\\resources\\bin\\docker.exe'

        COMPOSE_EXE =
            'C:\\Users\\Administrator\\.docker\\cli-plugins\\docker-compose.exe'

        IMAGE_NAME = 'inventory-devops'

        DB_HOST = '127.0.0.1'
        DB_NAME = 'inventory_db'
        DB_USER = 'root'
        DB_PASS = 'rootpassword'
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

        stage('Build Docker Image') {
            steps {

                echo 'Building Docker image...'

                bat '''
                    "%DOCKER_EXE%" --version

                    "%DOCKER_EXE%" build -t %IMAGE_NAME% .
                '''
            }
        }

        stage('Start Docker Stack') {
            steps {

                echo 'Starting MySQL and TileFlow containers...'

                bat '''
                    "%COMPOSE_EXE%" version
                    "%COMPOSE_EXE%" up -d
                '''
            }
        }

        stage('Wait for Database') {
    steps {

        echo 'Waiting for MySQL container...'

        bat '''
            powershell -NoProfile -Command "Start-Sleep -Seconds 15"
        '''
    }
}

        stage('Run Test Automation') {
            steps {

                echo 'Running automated tests...'

                bat '''
                    set DB_HOST=%DB_HOST%
                    set DB_NAME=%DB_NAME%
                    set DB_USER=%DB_USER%
                    set DB_PASS=%DB_PASS%

                    "%PHP_EXE%" tests/test.php
                '''
            }
        }

        stage('Verify Deployment') {
            steps {

                echo 'Verifying Docker deployment...'

                bat '''
                    "%COMPOSE_EXE%" ps
                '''
            }
        }
    }

    post {

        success {
            echo 'SUCCESS: Jenkins pipeline completed successfully.'
        }

        failure {
            echo 'FAILURE: Jenkins pipeline failed. Review the Jenkins console output.'
        }

        always {
            echo 'Jenkins pipeline execution completed.'
        }
    }
}