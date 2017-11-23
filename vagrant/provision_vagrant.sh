# Sets up Vagrant box for use with The Framework
#!/bin/bash
sudo git clone https://github.com/rivets/framework2.git /var/www/public/webproject
cd /var/www/public/webproject
sudo composer install


echo "Vagrant box has been setup for The Framework"
echo "Please complete setup from http://192.168.33.10/webproject/install.php"
echo "The mysql root password is root"
