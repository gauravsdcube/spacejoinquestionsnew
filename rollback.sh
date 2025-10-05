#!/bin/bash
echo "Rolling back email template changes..."
sudo cp models/EmailTemplate.php.original models/EmailTemplate.php
sudo chown www-data:www-data models/EmailTemplate.php
echo "Rollback complete!"
