# Outpost
Portable WordPress development environments with Vagrant, VMware, and WP-CLI.

**Version**: 0.1.0
| **Licence**: MIT
| **Author**: Nick Cernis [@nickcernis](http://twitter.com/nickcernis)

## Beta warning
**Outpost is experimental and in flux.** It currently only supports VMware virtual machines, but may support VirtualBox in the future. Tested on Mac. Untested on Windows and Linux (should work, but do file bug reports!). Contributions and feature requests are welcome.

## WordPress development environments on demand
Outpost creates a headless virtual server running Ubuntu 14.04 inside your local machine and provisions it with Apache 2.4.7, MySQL 5.5 and PHP 5.5. Then it downloads the latest WordPress and runs the WordPress install for you.

### You end up with:

- A fresh copy of WordPress running from the virtual machine, accessible in your browser at [http://my.outpost.rocks](http://my.outpost.rocks) with WP username 'outpost' and password 'outpost'.
- A 'wp/wp-content' folder in your project's directory, synced live with the virtual machine. Just drop your themes and plugins in, then get coding. This lets you develop with any IDE or editor on your system to get the benefits of using a virtual machine – portability, uniformity – without having to see it or interact with it via the command line (unless you want to).

### Use Outpost to:

1. Launch a fresh WordPress development environment with one command. Like MAMP/WAMP, but without any configuration, setup wizards, or a GUI to get in your way.  (See "Getting Started".)
2. Package a copy of an existing theme, plugin, or entire WordPress site to distribute to a development team, who can recreate your environment and see the working website with one command. (See "Packaging for distribution".)
3. Clone a remote site to work on locally in a similar environment. (Experimental. See "Cloning".)

## Getting started

Before you use Outpost, you'll need to:

1. Purchase and install [VMware Fusion](http://www.vmware.com/products/fusion/) (Mac) or [VMware Workstation](http://www.vmware.com/products/workstation/) (Windows/Linux).
2. [Install Vagrant.](http://docs.vagrantup.com/v2/installation/)
3. Purchase and install the [Vagrant VMware plugin](http://www.vagrantup.com/vmware).

## Launching Outpost

You can launch a new development environment like this:

1. [Download](https://github.com/nickcernis/outpost/archive/master.zip) or [clone](https://github.com/nickcernis/outpost) Outpost.
2. Rename your `outpost` directory to a project name of your choosing.
3. Change to that directory and type `vagrant up` in the terminal.

The first time you do this, it takes a while – Vagrant has to download the Outpost disk image from vagrantcloud.com (about 550MB). Subsequent `vagrant up`s are faster because Vagrant caches the outpost disk image on your hard drive.

Once it's done, visit http://my.outpost.rocks in your browser to see your new development site. The latest WordPress core is already installed and configured with admin username "outpost" and password "outpost". Vagrant will sync any files you drop or edit in `wp/wp-content` to the virtual server.

## Wait, what just happened?

If you're not familiar with [Vagrant](http://vagrantup.com) or virtual machines, what you have at this point is:

- A working copy of WordPress core running in VMware Fusion/Workstation on a virtual machine using Ubuntu 14.04. The machine's accessible in your browser at http://my.outpost.rocks, and via SSH using `vagrant ssh` from your project's root directory.
- A `wp/wp-content` folder on your local machine that syncs to Apache's `/var/www/html/wp-content` folder on the virtual machine. This lets you build themes and plugins locally with your favourite editor – changes you make to files in `wp/wp-content` automatically sync to the virtual machine.

How that happened is:

- [VMware](https://www.virtualbox.org/wiki/Virtualization) runs a [preconfigured server](https://github.com/nickcernis/outpost-packer) on your machine, without you having to mess around and install MySQL or WordPress.
- Vagrant launches the server, syncs folders between your machine and the server, and installs WordPress and other essential tools when you first run `vagrant up`.
- The A record for the my.outpost.rocks domain points to the IP address 192.168.53.53, which is the address of the VMware box on your local machine. When you visit that URL, your browser serves up the website served from your virtual machine on your own computer. (The URL is *not* publicly accessible to your clients or anyone but you.)

## The workflow
When you're done working with Outpost or want to switch projects, you can either:

1. Suspend the virtual machine with `vagrant suspend`. This writes RAM to disk, pauses the machine, and saves RAM and CPU cycles. To resume, type `vagrant resume` again.
2. Destroy the virtual machine with `vagrant destroy -f`. To resume, type `vagrant up`. Outpost will recreate your WordPress site from the database dump it creates every three minutes.

If you restart your computer without using `vagrant suspend` or `vagrant destroy -f`, you can bring your Outpost back again with `vagrant up`.

Because all Outposts use the http://my.outpost.rocks URL and the same IP address, you should only ever run one Outpost at a time. You can develop multiple themes and plugins using one Outpost, though. Or you can suspend one Outpost and run another. You can use the `vagrant global-status` command to see all of the Outposts you've ever created, and destroy ones you're not using to save disk space.

## How MySQL changes work
When you're running Outpost, it dumps the database every three minutes to /wp/wp-data/outpost.sql. When you run `vagrant up`, Outpost looks for the `outpost.sql` file and uses it to recreate the database. This means you can destroy the virtual machine and run `vagrant up` without losing all of your data.

This zero-configuration setup offers the potential to lose data, but I felt it was better than asking users to manually trigger backups. There may be better ways to storing state, though, such as using Vagrant's hooks to trigger SQL dumps after a suspend, halt, or destroy. For now, periodic SQL dumps seems adequate for general theme and plugin development, where database content is typically static and it's mostly theme and plugin files that are changing.

If you need to access MySQL from the console, the MySQL root password is 'mysql'.

## How Outpost is different

Outpost differs from traditional WordPress development environment setups such as WAMP, MAMP, and DesktopServer in these ways:

1. It uses Vagrant and VMware's virtualisation products to create a tiny server running on your own machine that better mimics the average shared server.
2. It automates the entire WordPress setup experience in one terminal command: `vagrant up`. That means less time configuring and more time coding.
3. When you need it, you have complete control over the development server.

Outpost differs from other Vagrant-based WordPress developer setups such as [VVV](https://github.com/Varying-Vagrant-Vagrants/VVV) in a few ways:

1. Outpost uses a [custom box](https://github.com/nickcernis/outpost-packer) that includes PHP, MySQL, Apache, and WP-CLI, so that you don't have to watch all of those things build each time you run `vagrant up`. WordPress itself is the only thing that `vagrant up` adds during the provisioning process.
2. Outpost is a plugin – not just a Vagrant template. You can use it stand-alone to generate new WordPress environments, or you can install it on a WordPress blog and use it to generate an “Outpost” – a packaged group of files to download and recreate that site locally using `vagrant up` for development and debugging.
3. Outpost is optimised for theme and plugin development – it's not designed for hacking on WordPress itself.


## Philosophy
Outpost is still in development, but the project's goals are to help you to:

### Say NO to...

- manual installation and configuration of MySQL, PHP, and Apache or nginx on your local system.
- manual downloads, MySQL database and user creation, and in-browser WordPress setup.
- fixing file permissions and ownership to get local plugin installation and updates working.
- setup of LAMP or WAMP stacks through GUIs or wizards using MAMP, WAMP, or DesktopServer.
- configuring and remembering lists of local dev URLs, editing `/etc/hosts`, or developing on a bare IP address.
- asking new team members to spend their first day recreating your development environment.
- hot hacking on live production sites because it was too hard to set up a local development copy.
- watching impatiently as `vagrant up` builds an entire server stack each time. (Outpost gets around this by providing a [custom box](https://github.com/nickcernis/outpost-packer) preconfigured with a LAMP stack and essential tools, so that `vagrant up` takes around two minutes instead of five times that).

### Say YES to...

- a LAMP environment provisioned for you automatically on a virtual machine, separate from the rest of your system, and easily reproducible.
- the latest version of WordPress core installed and ready to go as part of the service.
- a 'wp-content' folder in your project root for your plugin and theme files, synced to the virtual machine's web root.
- one development URL for all your local WordPress projects at [http://my.outpost.rocks](http://my.outpost.rocks)
- a way to package your themes, plugins, and site data for other developers to launch in an identical state with one command.
- a way to clone remote sites as local dev sites in one click (in development).

### TODO:

- Fix plugin issues to safely clone a live remote WordPress site as a local development environment.
- A way to choose system configuration options prior to startup. (To use nginx instead of Apache, for example.)
- Add PHPMyAdmin.
- Fix permissions issues with WordPress root. (Plugin installation and updates currently work, but WordPress updates prompt for an FTP password.)

## Cloning (experimental)
Outpost is also a WordPress plugin – install it and you'll find that it can download your site as an Outpost that you can launch on your local machine with `vagrant up`. The site packaging and download process is buggy on some servers, though, so it's currently only for the brave. (I'll post it to the WordPress plugin repository when it's stable.)

If you need to recreate a live site locally using Outpost, at the moment I recommend that you:

1. Download Outpost.
2. Rename the `outpost` directory with your project name.
3. Download the contents of your remote `/wp-content/` folder to Outpost's `/wp/wp-content` folder.
4. Dump your live MySQL database (with PHPMyAdmin or with [WP Migrate DB](https://wordpress.org/plugins/wp-migrate-db/)), rename it “outpost.sql” and put it in `/wp/wp-data`
5. Run `vagrant up`.

Eventually Outpost will automate steps one to four so that you can click a button to package and download your live site and then run `vagrant up` locally.

## Deployment and data syncing
Outpost is *not* yet designed to push theme, plugin, or database changes you've made back to a staging or production server. It's worth setting up a separate deployment strategy for that, perhaps using a service such as [Deploy](https://www.deployhq.com/).

I hope to make it easier to pull remote database changes back to your Outpost, but [WP Migrate DB Pro](https://deliciousbrains.com/wp-migrate-db-pro/) has you covered for now. Outpost overrides the site URL in wp-config.php, so you don't need to modify that, but you might consider using the [Root Relative URLs](https://wordpress.org/plugins/root-relative-urls/) plugin on the server and development box to avoid problems with full URLs that appear in your posts and pages.

## Contributing
I welcome all contributions and feature requests.

## Other notes

- If `wp/wp-content/` is blank, Outpost puts a clean copy of WordPress core's wp-content in there.
- If `wp/wp-content/` contains theme and plugin files, Outpost uses your wp-content directory to build your site.
- It's safe to delete the contents of the `wp/wp-content/` and `wp/wp-data` folders to reset your Outpost and force a new download of WordPress on the next `vagrant up`, but don't delete the directories themselves.



