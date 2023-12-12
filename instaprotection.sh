echo "####################";
echo "## Daily Cron Job ##";
echo "####################";
echo " ";

php /var/www/html/console/yii sys/send-reminder-plan-registration
php /var/www/html/console/yii sys/send-reminder-plan-clarification
php /var/www/html/console/yii sys/send-reminder-claim-clarification