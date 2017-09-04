# The Framework

A PHP Development Framework

http://catless.ncl.ac.uk/framework/ has details of installation and usage

## Vagrant

To use Vagrant for The Framework, you can do so with the following commands:

```shell
vagrant plugin install vagrant-hostmanager
vagrant up
vagrant ssh
sh vagrant/provision_vagrant.sh
```

You should get prompted for a MySQL password. Remember this for
when you are setting up The Framework.

The Vagrant VM is ready for use at http://framework.dev of
your local machine.