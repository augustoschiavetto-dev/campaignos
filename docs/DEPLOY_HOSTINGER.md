# Guia de Implantação VPS Hostinger — CampaignOS

Este guia detalha o processo passo a passo para implantar o CampaignOS em produção na VPS da Hostinger rodando Ubuntu LTS (ou Debian) com Nginx e PHP 8.2+.

## 1. Configuração do Servidor VPS
1. **Instalação do PHP 8.2 e Extensões:**
   ```bash
   sudo apt update
   sudo apt install -y php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-curl php8.2-mbstring php8.2-zip php8.2-bcmath
   ```
2. **Instalação do Servidor Web e Banco:**
   ```bash
   sudo apt install -y nginx mysql-server git curl unzip
   ```

## 2. Preparação do Código e Instalação
1. **Clone do Repositório:**
   ```bash
   cd /var/www
   sudo git clone https://github.com/seu-usuario/CampaignOS.git campaignos
   cd campaignos
   ```
2. **Criação do Armazenamento de Arquivos Privados (Fora da Raiz Web):**
   ```bash
   sudo mkdir -p /var/www/campaignos_storage
   sudo chown -R www-data:www-data /var/www/campaignos_storage
   sudo chmod -R 775 /var/www/campaignos_storage
   ```
3. **Configuração de Permissões da Pasta do Projeto:**
   ```bash
   sudo chown -R www-data:www-data /var/www/campaignos
   sudo chmod -R 775 /var/www/campaignos/storage /var/www/campaignos/bootstrap/cache
   ```
4. **Instalação das Dependências do Composer:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

## 3. Banco de Dados e Configuração de Ambiente
1. **Criação do Banco de Dados no MySQL:**
   ```sql
   CREATE DATABASE campaignos_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'campaignos_user'@'localhost' IDENTIFIED BY 'SUA_SENHA_SEGURA';
   GRANT ALL PRIVILEGES ON campaignos_prod.* TO 'campaignos_user'@'localhost';
   FLUSH PRIVILEGES;
   ```
2. **Configuração do `.env`:**
   Copie o exemplo de produção e configure as variáveis:
   ```bash
   cp .env.production.example .env
   php artisan key:generate
   ```
3. **Execução das Migrations e Semente Inicial:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

## 4. Otimização e Cache do Laravel
Execute estes comandos em cada deploy para máxima performance em produção:
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

## 5. Configuração do Nginx (Virtual Host)
Crie um novo arquivo de configuração do Nginx em `/etc/nginx/sites-available/campaignos`:
```nginx
server {
    listen 80;
    server_name seudominio.com www.seudominio.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name seudominio.com www.seudominio.com;
    root /var/www/campaignos/public;

    index index.php;

    charset utf-8;

    # SSL Config (Substituir pelos seus caminhos Let's Encrypt Certbot)
    ssl_certificate /etc/letsencrypt/live/seudominio.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/seudominio.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Headers de Segurança
    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-XSS-Protection "1; mode=block";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "no-referrer-when-downgrade";
    add_header Content-Security-Policy "default-src 'self' https: data: 'unsafe-inline' 'unsafe-eval';";

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache de Ativos Estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|webp|woff|woff2|svg)$ {
        expires 365d;
        add_header Cache-Control "public, no-transform";
    }
}
```
Ative o site e reinicie o Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/campaignos /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

## 6. Configuração do Scheduler (Cron Job)
Para que as pautas, logs e rotinas automáticas funcionem no background, adicione ao crontab do usuário do sistema (`www-data`):
```bash
sudo crontab -u www-data -e
```
Adicione a linha:
```cron
* * * * * cd /var/www/campaignos && php artisan schedule:run >> /dev/null 2>&1
```

## 7. Logs e Rotação
Os logs do Laravel são gravados diariamente (`daily`). Para gerenciar o consumo de disco da VPS, os logs de auditoria do banco de dados expiram automaticamente e os logs de arquivos do Nginx são rotacionados via `logrotate`:
```bash
sudo nano /etc/logrotate.d/nginx
```
Certifique-se de que a rotação padrão está ativa (normalmente semanal com retenção de 52 semanas).
