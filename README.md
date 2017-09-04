# The Framework Version 2

A PHP Development Framework

This version uses more features of PHP than the previous versions, so
as to provide examplars of how they can be used as the Framework is
used in a module teaching serverside development.

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