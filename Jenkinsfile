pipeline {
    agent any

    environment {
        APP_NAME = 'digisejahtera'
    }
    
    stages {
        stage('Checkout Code') {
            steps {
                checkout scm
            }
        }

        stage('Create .env from Credentials') {
            steps {
                withCredentials([file(credentialsId: "${env.APP_NAME}-env-prod", variable: 'DOTENV_FILE')]) {
                    sh "cp \$DOTENV_FILE .env"
                }
            }
        }

        stage('Blue-Green Deploy') {
            steps {
                script {
                    def composeFile = 'docker-compose.yml'
                    def runningContainers = sh(script: "docker ps --format '{{.Names}}' | grep '${env.APP_NAME}_blue-web' || true", returnStdout: true).trim()
                    
                    def currentColor = 'green'
                    if (runningContainers) {
                        currentColor = 'blue'
                    }
                    def nextColor = (currentColor == 'blue') ? 'green' : 'blue'

                    echo "--- Versi aktif: ${currentColor}. Deploy versi baru: ${nextColor}"

                    try {
                        def commitHash = sh(script: 'git rev-parse --short HEAD', returnStdout: true).trim()

                        withEnv(["GIT_HASH=${commitHash}"]) {
                            sh "docker compose -p ${env.APP_NAME}_${nextColor} -f ${composeFile} up -d --build --force-recreate"
                        }


                        // Matikan versi lama (sudah benar)
                        def oldContainers = sh(script: "docker ps -qf 'name=${env.APP_NAME}_${currentColor}'", returnStdout: true).trim()
                        if (oldContainers) {
                            echo "--- Mematikan versi lama: ${currentColor}"
                            sh "docker compose -p ${env.APP_NAME}_${currentColor} -f ${composeFile} down -v"
                        }
                        echo "--- Deployment berhasil! Versi ${nextColor} sekarang aktif."

                    } catch (e) {
                        echo "--- Terjadi kesalahan, membersihkan deployment ${nextColor}..."
                        sh "docker compose -p ${env.APP_NAME}_${nextColor} -f ${composeFile} down -v --remove-orphans"
                        currentBuild.result = 'FAILURE'
                        error("Deployment gagal: ${e.message}")
                    }
                }
            }
        }
        
        stage('Clean Up Old Images') {
            steps {
                sh 'docker image prune -f'
            }
        }
    }

    post {
        always {
            cleanWs(deleteDirs: true, notFailBuild: true)
        }
    }
}
