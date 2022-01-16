# SMTP proxy server

# How to setup an email server
Buy a virtual server, for example DigitalOcean. OS can be Debian/Ubuntu or any.

Check your new server's IP in SPAM blacklists https://mxtoolbox.com/blacklists.aspx

Set PTR record like mail.yourdomain.com 
For DigitalOcean it can made by setting hostname
Check PTR https://mxtoolbox.com/ReverseLookup.aspx 

Create A record on your domain that points to IP of your server

Check that 25-th port is open:
telnet outlook-com.olc.protection.outlook.com 25

Install PHP (cli module). Prefer version is 8.0+
apt install php8.0-cli

Install Git or download zip archive of this repo

Install Composer for PHP libraries
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

Secure practice is creating user for web/php code
adduser esmtp

Install supervisor

# Installation
```shell
sudo apt install php8.0-curl
git clone https://github.com/emailsuite/smtp-proxy.git
cd smtp-proxy
composer install
cd roadrunner
../vendor/bin/rr get-binary
# dont create default config file
cp .rr_sample.yaml .rr.yaml
# provide your host IP in config instead 127.0.0.1:
nano .rr.yaml 
# check roadrunner server
.rr serve
cp supervisor_sample.conf supervisor.conf 
# set right user and full path to /rr file
nano supervisor.conf
# make link of super config:
sudo ln -s /home/esmtp/smtp-proxy/roadrunner/supervisor.conf /etc/supervisor/conf.d/
sudo service supervisor reload

#check rr works
ps aux | grep roadrunner
```
