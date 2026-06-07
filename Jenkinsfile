pipeline {
    
    agent any
    
    stages {
        stage('Checkout Code from GitHub') {
            steps {
                echo 'Mengambil kode terbaru...'
                checkout scm
            }
        }

        stage('Create .env from Credentials') {
            steps {
                withCredentials([file(credentialsId: 'digisejahtera-env-prod', variable: 'DOTENV_FILE')]) {
                    sh "cp \$DOTENV_FILE .env"
                }
            }
        }
    stage('Prepare XLSX Templates') {
        steps {
            withCredentials([
                file(
                    credentialsId: 'kartu-rekening-digisejahtera',
                    variable: 'REKENING_XLSX_FILE'
                )
            ]) {
                sh '''
                    rm -rf .docker-secrets
                    mkdir -p .docker-secrets/templates/xlsx
                    cp "$REKENING_XLSX_FILE" ".docker-secrets/templates/xlsx/template.xlsx"
                '''
            }
        }
    }

        stage('Build and Deploy Application') {
            steps {
                echo '--- Menghentikan container yang jalan ---'
                sh 'docker compose down -v --remove-orphans'
                echo '--- MEMBANGUN IMAGE APLIKASI BARU ---'
                sh 'docker compose build'

                echo '--- MEN-DEPLOY SEMUA LAYANAN ---'
                sh 'docker compose up -d'

                echo '--- MEMBERSIHKAN IMAGE DOCKER LAMA ---'
                sh 'docker image prune -f'
            }
        }
    }

    post {
        always {
            cleanWs(deleteDirs: true, notFailBuild: true)
        }
        success {
            echo 'Pipeline berhasil!'
        }
        failure {
            echo 'Pipeline GAGAL!'
        }
    }
}
