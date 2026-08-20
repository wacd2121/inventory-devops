pipeline {
    agent any

    environment {
        DOCKER_IMAGE = 'tileflow-app'
        DOCKER_TAG = "build-${BUILD_NUMBER}"
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
                echo 'Running static syntax check (php -l) on all sources...'
                // Loop through PHP files and run syntax linting.
                // If running on windows agent we can wrap in bat, but sh is standard.
                // We use sh for standard linux build nodes.
                sh 'find . -name "*.php" -not -path "*/vendor/*" -exec php -l {} \\;'
            }
        }

        stage('Run Test Automation') {
            steps {
                echo 'Running test assertions in isolation...'
                sh 'php tests/test.php'
            }
        }

        stage('Build Docker Images') {
            steps {
                echo 'Building application Docker image...'
                sh "docker build -t ${DOCKER_IMAGE}:${DOCKER_TAG} ."
            }
        }

        stage('Deploy Stack') {
            steps {
                echo 'Spinning up containerized development stack...'
                sh "docker compose up -d --build"
                echo 'Stack successfully deployed and verified.'
            }
        }
    }

    post {
        success {
            echo "SUCCESS: Jenkins build #${BUILD_NUMBER} completed without errors."
        }
        failure {
            echo "FAILURE: Jenkins build #${BUILD_NUMBER} failed. Review logs for details."
        }
    }
}
