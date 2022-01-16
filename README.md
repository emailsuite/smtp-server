# SMTP proxy server

# How to setup an email server
Buy a virtual server, for example DigitalOcean. OS can be Debian/Ubuntu or any.

Check your new server's IP in SPAM blacklists https://mxtoolbox.com/blacklists.aspx

Set PTR record like mail.yourdomain.com 
For DigitalOcean it can made by setting hostname
Check PTR https://mxtoolbox.com/ReverseLookup.aspx 

Check that 25-th port is open:
telnet outlook-com.olc.protection.outlook.com 25

