#!/bin/bash

# Configurações do Banco de Dados
DB_NAME="almox_facom"
DB_USER="postgres" # Troque pelo seu usuário de produção
export PGPASSWORD="root123" # Troque pela senha de produção

# Onde o backup será salvo
BACKUP_DIR="/var/backups/almoxarifado"
DATA_HOJE=$(date +%Y-%m-%d_%H-%M)

# Cria a pasta se ela não existir
mkdir -p $BACKUP_DIR

# Nome do arquivo de backup
ARQUIVO="$BACKUP_DIR/backup_$DATA_HOJE.sql"

# Executa o dump do banco
pg_dump -U $DB_USER -h localhost -d $DB_NAME -f $ARQUIVO

# Verifica se o arquivo foi criado com sucesso
if [ -f "$ARQUIVO" ]; then
    echo "Backup realizado com sucesso: $ARQUIVO"
    
    # Compacta o arquivo (opcional, para economizar espaço)
    gzip $ARQUIVO
    
    # Remove backups mais antigos que 30 dias
    find $BACKUP_DIR -type f -name "*.sql.gz" -mtime +30 -exec rm {} \;
else
    echo "Erro ao realizar o backup."
fi