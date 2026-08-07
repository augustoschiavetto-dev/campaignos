# Plano de Rollback em Produção — CampaignOS

Este documento descreve os procedimentos operacionais para restaurar uma versão anterior estável do CampaignOS caso o deploy atual apresente falhas críticas na VPS Hostinger.

## Cenário A: Falha na Aplicação (Sem Alteração de Banco de Dados)
Se o problema decorre apenas de bugs no código PHP, assets ou configurações de rotas.

1. **Reverter o Código no Git para a Tag Anterior Estável:**
   ```bash
   cd /var/www/campaignos
   git fetch --tags
   # Encontre a última tag estável (ex: v1.1.0)
   git checkout v1.1.0
   ```
2. **Atualizar Dependências e Reconstruir Caches:**
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan optimize:clear
   php artisan optimize
   ```
3. **Reiniciar o Processo PHP-FPM:**
   ```bash
   sudo systemctl restart php8.2-fpm
   ```

## Cenário B: Falha com Mudanças no Banco de Dados (Migrations)
Se o novo deploy executou migrations que corromperam os dados ou introduziram bugs estruturais irreversíveis por código simples.

1. **Colocar a Aplicação em Manutenção:**
   ```bash
   php artisan down --secret="rollback-token"
   ```
2. **Restaurar o Banco de Dados e Storage a partir do Backup Diário:**
   * **Passo 1: Restaurar o Banco MySQL:**
     ```bash
     # Drop no banco quebrado
     mysql -u campaignos_user -p -e "DROP DATABASE campaignos_prod; CREATE DATABASE campaignos_prod;"
     # Import do backup de ontem à noite (armazenado no diretório seguro)
     mysql -u campaignos_user -p campaignos_prod < /var/backups/campaignos/db_backup_YYYY-MM-DD.sql
     ```
   * **Passo 2: Restaurar o Storage de Arquivos Privados:**
     ```bash
     # Remover diretório de storage privado atual
     rm -rf /var/www/campaignos_storage
     # Restaurar backup correspondente do storage
     tar -xzf /var/backups/campaignos/storage_backup_YYYY-MM-DD.tar.gz -C /var/www/
     ```
3. **Reverter o Código (Git Checkout):**
   ```bash
   git checkout LAST_KNOWN_GOOD_COMMIT
   composer install --no-dev --optimize-autoloader
   php artisan optimize:clear
   php artisan optimize
   ```
4. **Tirar a Aplicação do Modo de Manutenção:**
   ```bash
   php artisan up
   ```

## Prevenção e Monitoramento Pós-Rollback
- Inspecionar logs em `/var/www/campaignos/storage/logs/laravel.log` para isolar a causa raiz.
- Verificar logs do Nginx em `/var/log/nginx/error.log` e do PHP-FPM em `/var/log/php8.2-fpm.log`.
