# Sets up Vagrant box for use with The Framework
sudo apt-get install -y language-pack-en-base
sudo LC_ALL=en_US.UTF-8 add-apt-repository ppa:ondrej/php -y
sudo apt-get update
sudo apt-get install -y apache2 mysql-server php7.0-mysql libapache2-mod-php git-core unzip php7.0-mbstring

sudo rm -rf /var/www/html
sudo ln -s /vagrant /var/www/html
sudo cp /vagrant/vagrant/99-phpsettings.ini /etc/php/7.0/apache2/conf.d/
sudo service apache2 restart

echo "Vagrant box has been setup for The Framework"
echo "Please complete setup from http://framework.dev/install.php"