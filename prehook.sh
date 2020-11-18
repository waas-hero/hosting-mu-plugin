#!/bin/bash
# Create TXT record
full_path=$(realpath $0)
dir_path=$(dirname $full_path)
#relative_path=$(dirname $dir_path )
mu_plugin_path=$(dirname $dir_path )
dir_path=$(dirname $mu_plugin_path )
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

API_KEY=`cat "$dir_path/wp-config.php" | grep WAASHERO_CLIENT_API_KEY | cut -d \' -f 4`
 
RECORD_ID=$(curl -s -X POST "https://waas-builder.com/api/v0/ultimo/zone/record" \
     -H     "Authorization: Bearer $API_KEY" \
     -H     "Content-Type: application/json" \
     --data '{"domain":"'"$CERTBOT_DOMAIN"'","content":"'"$CERTBOT_VALIDATION"'"}'  )
echo $RECORD_ID
# Save info for cleanup
if [ ! -d /tmp/CERTBOT_$CERTBOT_DOMAIN ];then
        mkdir -m 0700 /tmp/CERTBOT_$CERTBOT_DOMAIN
fi
 
echo $RECORD_ID > /tmp/CERTBOT_$CERTBOT_DOMAIN/RECORD_ID
# Sleep to make sure the change has time to propagate over to DNS
sleep 35
