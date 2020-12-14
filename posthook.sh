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

#!/bin/bash
# Create TXT record
full_path=$(realpath $0)

mu_plugin_path=$(dirname $full_path)
#relative_path=$(dirname $dir_path )

share_folder=$(dirname $mu_plugin_path )


API_KEY=`cat "$share_folder/wordpress/wp-config.php" | grep WAASHERO_CLIENT_API_KEY | cut -d \' -f 4`

RECORD_ID=$(curl -s -X DELETE "https://waas-builder.com/api/v0/ultimo/zone/record" \
     -H     "Authorization: Bearer $API_KEY" \
     -H     "Content-Type: application/json" \
     --data '{"domain":"'"$CERTBOT_DOMAIN"'","content":"'"$CERTBOT_VALIDATION"'"}'  )