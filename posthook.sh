#
#!/bin/bash
# Create TXT record
# Test configuration and reload if successful
sleep 5
conf="/etc/nginx/conf.d/$CERTBOT_DOMAIN.conf"

cat << EOF > $conf
server {
    listen             443 ssl;
    server_name        $CERTBOT_DOMAIN *.$CERTBOT_DOMAIN;
    charset            utf-8;
    ssl_certificate /etc/letsencrypt/live/$CERTBOT_DOMAIN/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/$CERTBOT_DOMAIN/privkey.pem; # managed by Certbot
    ssl_session_cache          shared:SSL:1m;
    ssl_session_timeout        10m;
 
    include wpcommon.conf;
    root /usr/share/wordpress/;
 
    location / {
        index index.php;
        try_files \$uri \$uri/ /index.php?\$args;
        include rh-php-fpm.conf;
    }
}
EOF