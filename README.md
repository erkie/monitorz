Monitorz - Real simple monitoring
=================================

How to set up:

1. Install php
2. Install redis
3. Install your web server of choice
4. `git clone https://github.com/erkie/monitorz.git`
5. `cd monitorz; cp config.json-SAMPLE config.json`
6. Point web server to this directory
7. Bonus step: `crontab -e`
8. Add: `*/1 * * * * /usr/bin/php /web/monitorz/cron.php > /dev/null`

How to monitor
==============

    POST /?name=name-of-thing-to-monitor&value=10
    Host: secret.url.yourdomain.com
