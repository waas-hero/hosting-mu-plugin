#!/bin/bash
# Create TXT record
full_path=$(realpath $0)
mu_plugin_path=$(dirname $full_path)
share_folder=$(dirname $mu_plugin_path )
API_KEY=`cat "$share_folder/wordpress/wp-config.php" | grep WAASHERO_CLIENT_API_KEY | cut -d \' -f 4`
API_URL=`cat "$share_folder/wordpress/wp-config.php" | grep WAASHERO_CLIENT_API_URL | cut -d \' -f 4`

RECORD_ID=$(curl -s -X POST "$API_URL/ultimo/zone/record" \
     -H     "Cache-Control: no-cache" \
     -H     "Authorization: Bearer $API_KEY" \
     -H     "Content-Type: application/json" \
     -H     "Accept: application/json" \
     --data '{"domain":"'"$CERTBOT_DOMAIN"'","content":"'"$CERTBOT_VALIDATION"'"}'  )
echo $RECORD_ID
# Save info for cleanup
if [ ! -d /tmp/CERTBOT_$CERTBOT_DOMAIN ];then
        mkdir -m 0700 /tmp/CERTBOT_$CERTBOT_DOMAIN
fi
 
echo $RECORD_ID > /tmp/CERTBOT_$CERTBOT_DOMAIN/RECORD_ID
# Sleep to make sure the change has time to propagate over to DNS
sleep 35
