# The Framework Version 3

A PHP Development Framework

This version uses more features of PHP than the previous versions, so
as to provide examplars of how they can be used as the Framework is
used in a module teaching server-side development with PHP.

http://catless.ncl.ac.uk/framework/ has details of installation and usage.

Version 3 introduces some breaking changes from previous versions. In particular, formdata handling is
changed (though there is still a compatibility layer for the moment) and AJAX support has been completely changed.
See the documentation at the website above. There are some internal structural changes as well to reduce code
complexity but these are mostly internal to the framework support code.

The framework JavaScript functions currently use jQuery but I am phasing this out in preparation for the release
of BootStrap 5. This is an continuing process at the moment.

## Vagrant

To use Vagrant for The Framework, you can do so with the following commands:

```shell
vagrant up
vagrant ssh
```

The default MySQL root password is root, you should change this. Remember this for
when you are setting up The Framework.

The Vagrant VM is ready for use at http://192.168.33.10/ of your local machine.
