#
#!/bin/bash
# Create TXT record
# Test configuration and reload if successful
sleep 10
sudo nginx -t && sudo service nginx reload